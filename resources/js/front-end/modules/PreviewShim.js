// runs inside the page builder preview iframe. the preview endpoints wrap each
// block in <!--rb:N--> comment markers (comments so no wrapper elements upset
// site css like `main > *`); this indexes those ranges, patches them with
// morphdom when the admin posts fresh HTML, and echoes typed text instantly
// for blades that carry data-field attributes.

import morphdom from 'morphdom';

const REGION = 'rb-region';

function findComments(node, found = []) {
  for (const child of node.childNodes) {
    if (child.nodeType === Node.COMMENT_NODE) {
      found.push(child);
    } else {
      findComments(child, found);
    }
  }
  return found;
}

// {region: {start, end}, blocks: Map<index, {start, end}>}
function indexMarkers(root) {
  const comments = findComments(root);
  const region = {};
  const blocks = new Map();

  for (const comment of comments) {
    const value = comment.nodeValue.trim();

    if (value === REGION) {
      region.start = comment;
    } else if (value === '/' + REGION) {
      region.end = comment;
    } else {
      const open = value.match(/^rb:(\d+)$/);
      const close = value.match(/^\/rb:(\d+)$/);

      if (open) {
        blocks.set(Number(open[1]), { start: comment });
      } else if (close) {
        const entry = blocks.get(Number(close[1]));
        if (entry) entry.end = comment;
      }
    }
  }

  return { region, blocks };
}

let markers = indexMarkers(document.body);

// nodes strictly between a range's start/end comments
function rangeNodes(range) {
  const nodes = [];
  let node = range.start.nextSibling;
  while (node && node !== range.end) {
    nodes.push(node);
    node = node.nextSibling;
  }
  return nodes;
}

function rangeElement(range) {
  const elements = rangeNodes(range).filter(n => n.nodeType === Node.ELEMENT_NODE);
  return elements.length === 1 ? elements[0] : null;
}

function removeRange(range) {
  rangeNodes(range).forEach(n => n.remove());
  range.start.remove();
  range.end.remove();
}

function insertAfter(reference, nodes) {
  let cursor = reference;
  nodes.forEach(node => {
    cursor.after(node);
    cursor = node;
  });
  return cursor;
}

function applyUpdate(html) {
  if (!markers.region.start || !markers.region.end) {
    return;
  }

  const scrollX = window.scrollX;
  const scrollY = window.scrollY;

  const tpl = document.createElement('template');
  tpl.innerHTML = html;
  const fresh = indexMarkers(tpl.content);

  let cursor = markers.region.start;
  const oldBlocks = markers.blocks;

  for (const [index, freshRange] of fresh.blocks) {
    const old = oldBlocks.get(index);
    const oldEl = old ? rangeElement(old) : null;
    const freshEl = rangeElement(freshRange);

    if (old && oldEl && freshEl) {
      // typical case: one root element per block - patch in place, keeping
      // untouched nodes, loaded images and scroll position intact
      morphdom(oldEl, freshEl);
      cursor = old.end;
      oldBlocks.delete(index);
    } else if (old) {
      // multi/zero root blades - swap the range wholesale
      const nodes = [freshRange.start, ...rangeNodes(freshRange), freshRange.end];
      removeRange(old);
      cursor = insertAfter(cursor, nodes);
      oldBlocks.delete(index);
    } else {
      // new block
      const nodes = [freshRange.start, ...rangeNodes(freshRange), freshRange.end];
      cursor = insertAfter(cursor, nodes);
    }
  }

  // blocks removed in the admin
  for (const old of oldBlocks.values()) {
    removeRange(old);
  }

  markers = indexMarkers(document.body);
  window.scrollTo(scrollX, scrollY);

  // site js (sliders, accordions) can re-init off this
  document.dispatchEvent(new CustomEvent('rcms:updated'));
}

// instant text echo for cheap field types; the authoritative server render
// reconciles ~200ms behind. requires the blade to mark the output element
// with data-field="<snake_case_field>", otherwise it's a no-op.
function applyEcho({ index, field, value }) {
  const range = markers.blocks.get(index);
  if (!range) return;

  const target = rangeNodes(range)
    .filter(n => n.nodeType === Node.ELEMENT_NODE)
    .map(el => (el.matches(`[data-field="${field}"]`) ? el : el.querySelector(`[data-field="${field}"]`)))
    .find(Boolean);

  if (target) {
    target.textContent = value;
  }
}

// ---- selection / hover -----------------------------------------------------

let selectedIndex = null;
// index -> {name, anchor}, sent by the admin
let blockLabels = {};

function copyAnchor(el) {
  const text = el.dataset.rcmsAnchor;

  const done = () => {
    const original = el.textContent;
    el.textContent = 'Copied!';
    setTimeout(() => { try { el.textContent = original; } catch (e) {} }, 1200);
  };

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(text).then(done).catch(() => fallbackCopy(text, done));
  } else {
    fallbackCopy(text, done);
  }
}

function fallbackCopy(text, done) {
  const area = document.createElement('textarea');
  area.value = text;
  document.body.appendChild(area);
  area.select();
  document.execCommand('copy');
  area.remove();
  done();
}

function makeOverlay(border, background) {
  const el = document.createElement('div');
  el.style.cssText = 'position:absolute;display:none;pointer-events:none;z-index:2147483000;'
    + `border:2px solid ${border};background:${background};border-radius:2px;`;

  // block name tag pinned to the top left of the outline
  const tag = document.createElement('div');
  tag.style.cssText = 'position:absolute;top:-2px;left:-2px;display:none;'
    + `background:${border};color:#fff;font:600 11px/1.7 -apple-system,BlinkMacSystemFont,sans-serif;`
    + 'padding:1px 9px;border-radius:2px 0 3px 0;max-width:70%;'
    + 'overflow:hidden;text-overflow:ellipsis;white-space:nowrap;letter-spacing:0.02em;';
  // the anchor span inside re-enables pointer events for click-to-copy
  tag.addEventListener('click', (event) => {
    const anchor = event.target.closest('[data-rcms-anchor]');
    if (anchor) {
      copyAnchor(anchor);
    }
  });
  el.appendChild(tag);
  el._tag = tag;

  document.body.appendChild(el);
  return el;
}

const selectOverlay = makeOverlay('#4a90d9', 'rgba(74,144,217,0.06)');
const hoverOverlay = makeOverlay('rgba(74,144,217,0.85)', 'transparent');

function rangeRect(range) {
  const elements = rangeNodes(range).filter(n => n.nodeType === Node.ELEMENT_NODE);
  if (!elements.length) return null;

  let rect = null;
  for (const el of elements) {
    const r = el.getBoundingClientRect();
    if (!r.width && !r.height) continue;
    rect = rect
      ? {
          top: Math.min(rect.top, r.top),
          left: Math.min(rect.left, r.left),
          right: Math.max(rect.right, r.right),
          bottom: Math.max(rect.bottom, r.bottom),
        }
      : { top: r.top, left: r.left, right: r.right, bottom: r.bottom };
  }
  return rect;
}

function positionOverlay(overlay, index) {
  const range = index === null ? null : markers.blocks.get(index);
  const rect = range ? rangeRect(range) : null;

  if (!rect) {
    overlay.style.display = 'none';
    return;
  }

  overlay.style.display = 'block';
  overlay.style.top = (rect.top + window.scrollY) + 'px';
  overlay.style.left = (rect.left + window.scrollX) + 'px';
  overlay.style.width = (rect.right - rect.left) + 'px';
  overlay.style.height = (rect.bottom - rect.top) + 'px';

  const meta = blockLabels[index];
  overlay._tag.innerHTML = '';

  if (meta && (meta.name || meta.anchor)) {
    overlay._tag.appendChild(document.createTextNode(meta.name || ''));

    if (meta.anchor) {
      const anchor = document.createElement('span');
      anchor.textContent = meta.anchor;
      anchor.dataset.rcmsAnchor = meta.anchor;
      anchor.title = 'Click to copy';
      anchor.style.cssText = 'pointer-events:auto;cursor:pointer;margin-left:8px;'
        + 'text-decoration:underline;text-underline-offset:2px;';
      overlay._tag.appendChild(anchor);
    }

    overlay._tag.style.display = 'block';
  } else {
    overlay._tag.style.display = 'none';
  }
}

function refreshSelection() {
  positionOverlay(selectOverlay, selectedIndex);
}

function findBlockIndexFor(node) {
  let el = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
  while (el && el !== document.body) {
    for (const [index, range] of markers.blocks) {
      for (const n of rangeNodes(range)) {
        if (n === el) return index;
      }
    }
    el = el.parentElement;
  }
  return null;
}

document.addEventListener('click', (event) => {
  // the preview is for looking, not navigating
  if (event.target.closest('a, button')) {
    event.preventDefault();
  }

  const index = findBlockIndexFor(event.target);
  if (index !== null) {
    selectedIndex = index;
    refreshSelection();
    send({ type: 'rcms:block-click', index });
  }
}, true);

document.addEventListener('submit', (event) => event.preventDefault(), true);

document.addEventListener('mouseover', (event) => {
  // moving onto an overlay's tag (e.g. to click its anchor) must not hide it
  if (selectOverlay.contains(event.target) || hoverOverlay.contains(event.target)) {
    return;
  }

  const index = findBlockIndexFor(event.target);
  positionOverlay(hoverOverlay, index === selectedIndex ? null : index);
});

// clear the hover outline when the pointer leaves the preview
document.documentElement.addEventListener('mouseleave', () => {
  positionOverlay(hoverOverlay, null);
});

window.addEventListener('scroll', refreshSelection, { passive: true });
window.addEventListener('resize', refreshSelection, { passive: true });

function selectBlock(index) {
  selectedIndex = index;
  const range = index === null ? null : markers.blocks.get(index);

  if (range) {
    const el = rangeNodes(range).find(n => n.nodeType === Node.ELEMENT_NODE);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    // wait for the smooth scroll before measuring
    setTimeout(refreshSelection, 350);
  }

  refreshSelection();
}

// ---- messaging -------------------------------------------------------------

function send(message) {
  window.parent.postMessage({ ...message, source: 'rcms-preview' }, location.origin);
}

window.addEventListener('message', (event) => {
  if (event.origin !== location.origin || !event.data || !event.data.type) {
    return;
  }

  switch (event.data.type) {
    case 'rcms:update':
      applyUpdate(event.data.html);
      refreshSelection();
      break;
    case 'rcms:echo':
      applyEcho(event.data);
      break;
    case 'rcms:select':
      selectBlock(event.data.index);
      break;
    case 'rcms:hover':
      positionOverlay(hoverOverlay, event.data.index === selectedIndex ? null : event.data.index);
      break;
    case 'rcms:block-meta':
      blockLabels = event.data.labels || {};
      refreshSelection();
      break;
  }
});

send({ type: 'rcms:ready', blocks: markers.blocks.size });
