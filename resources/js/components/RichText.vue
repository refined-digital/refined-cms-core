<template>
  <div class="rich-editor" :class="{ 'rich-editor--focused': focused }">
    <div class="rich-editor__toolbar" v-if="editor">
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': sourceView }" title="View HTML" @click="toggleSource"><i class="fas fa-code"></i></button>

      <span class="rich-editor__divider"></span>

      <template v-if="!reduced">
        <button type="button" class="rich-editor__btn" title="Undo" @click="editor.chain().focus().undo().run()"><i class="fas fa-undo"></i></button>
        <button type="button" class="rich-editor__btn" title="Redo" @click="editor.chain().focus().redo().run()"><i class="fas fa-redo"></i></button>

        <span class="rich-editor__divider"></span>
      </template>

      <select class="rich-editor__format" :value="currentBlock" @change="applyFormat($event.target.value)">
        <option v-for="f in formats" :key="f.value" :value="f.value">{{ f.label }}</option>
      </select>

      <span class="rich-editor__divider"></span>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('bold') }" title="Bold" @click="editor.chain().focus().toggleBold().run()"><i class="fas fa-bold"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('italic') }" title="Italic" @click="editor.chain().focus().toggleItalic().run()"><i class="fas fa-italic"></i></button>
      <button type="button" v-if="!reduced" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('strike') }" title="Strikethrough" @click="editor.chain().focus().toggleStrike().run()"><i class="fas fa-strikethrough"></i></button>

      <template v-if="!reduced">
        <span class="rich-editor__divider"></span>

        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('superscript') }" title="Superscript" @click="editor.chain().focus().toggleSuperscript().run()"><i class="fas fa-superscript"></i></button>
        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('subscript') }" title="Subscript" @click="editor.chain().focus().toggleSubscript().run()"><i class="fas fa-subscript"></i></button>
      </template>

      <span class="rich-editor__divider"></span>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('link') }" title="Insert Link" @click="openLinkModal"><i class="fas fa-link"></i></button>
      <button type="button" class="rich-editor__btn" title="Remove Link" @click="editor.chain().focus().unsetLink().run()"><i class="fas fa-unlink"></i></button>
      <button type="button" v-if="!reduced" class="rich-editor__btn" title="Insert Image" @click="openImageModal()"><i class="fas fa-image"></i></button>
      <button type="button" v-if="!reduced" class="rich-editor__btn" title="Embed Video" @click="embedVideo"><i class="fab fa-youtube"></i></button>

      <template v-if="!reduced">
        <span class="rich-editor__divider"></span>

        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }" title="Align Left" @click="editor.chain().focus().setTextAlign('left').run()"><i class="fas fa-align-left"></i></button>
        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }" title="Align Center" @click="editor.chain().focus().setTextAlign('center').run()"><i class="fas fa-align-center"></i></button>
        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }" title="Align Right" @click="editor.chain().focus().setTextAlign('right').run()"><i class="fas fa-align-right"></i></button>
        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'justify' }) }" title="Justify" @click="editor.chain().focus().setTextAlign('justify').run()"><i class="fas fa-align-justify"></i></button>

        <span class="rich-editor__divider"></span>

        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('bulletList') }" title="Bullet List" @click="editor.chain().focus().toggleBulletList().run()"><i class="fas fa-list-ul"></i></button>
        <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('orderedList') }" title="Numbered List" @click="editor.chain().focus().toggleOrderedList().run()"><i class="fas fa-list-ol"></i></button>

        <span class="rich-editor__divider"></span>

        <button type="button" class="rich-editor__btn" title="Horizontal Rule" @click="editor.chain().focus().setHorizontalRule().run()"><i class="fas fa-minus"></i></button>
        <button type="button" class="rich-editor__btn" title="Clear Formatting" @click="editor.chain().focus().unsetAllMarks().clearNodes().run()"><i class="fas fa-eraser"></i></button>
      </template>

      <template v-if="tokens && tokens.length">
        <span class="rich-editor__divider"></span>
        <select class="rich-editor__format" @change="insertToken($event)">
          <option value="">Insert field…</option>
          <option v-for="t in tokens" :key="t.token" :value="t.token">{{ t.label }}</option>
        </select>
      </template>
    </div>

    <editor-content v-show="!sourceView" :editor="editor" class="rich-editor__content form__control" />
    <textarea v-if="sourceView" class="rich-editor__content rich-editor__source-view form__control" v-model="data" spellcheck="false"></textarea>
    <textarea :name="name" :id="id" class="rich-editor__source" v-model="data" hidden></textarea>

    <!-- Image modal -->
    <div class="rich-editor__modal-overlay" v-if="imageModal.open" @click.self="closeImageModal">
      <div class="rich-editor__modal-box">
        <h3>{{ imageModal.editing ? 'Edit Image' : 'Insert Image' }}</h3>
        <div class="rich-editor__modal">
          <div class="rich-editor__modal-image">
            <div class="rich-editor__modal-thumb" :style="imageModal.src ? `background-image: url(${imageModal.src})` : ''"></div>
          </div>
          <button type="button" class="button button--green button--small" @click="browseImage">Browse Server</button>
        </div>
        <input type="hidden" v-model="imageModal.src" :id="`${editorId}-image-src`">
        <label>Alternative Text</label>
        <input type="text" class="form__control" v-model="imageModal.alt">
        <label>Id</label>
        <input type="text" class="form__control" v-model="imageModal.elId">
        <label>Class</label>
        <input type="text" class="form__control" v-model="imageModal.className">
        <footer class="rich-editor__modal-actions">
          <button type="button" class="button button--grey button--small" @click="closeImageModal">Cancel</button>
          <button type="button" class="button button--green button--small" @click="confirmImage">Confirm</button>
        </footer>
      </div>
    </div>

    <!-- Link modal -->
    <div class="rich-editor__modal-overlay" v-if="linkModal.open" @click.self="closeLinkModal">
      <div class="rich-editor__modal-box">
        <h3>Insert Link</h3>

        <rd-link-form v-model="linkModalModel" :settings="{ simple: false }"></rd-link-form>

        <footer class="rich-editor__modal-actions">
          <button type="button" class="button button--grey button--small" @click="closeLinkModal">Cancel</button>
          <button type="button" class="button button--green button--small" @click="confirmLink">Confirm</button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount, onMounted, onUnmounted } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import eventBus from '../eventBus';
import { useUiStore } from '../stores/ui';
import { useConfigStore } from '../stores/config';
import { buildExtensions } from '../tiptap/extensions';

// `reduced` trims the toolbar to code/format/bold/italic/link (used by the
// form-builder notification editor). `tokens` adds an "insert field" dropdown
// of { label, token } entries that drop the raw token text into the content.
const props = defineProps(['name', 'id', 'content', 'reduced', 'tokens']);
const emit = defineEmits(['input', 'blur', 'update:modelValue']);

const ui = useUiStore();
const config = useConfigStore();

// unique per-instance id so the right editor responds to media/sitemap picks
const editorId = `rich-editor-${props.id}-${Date.now()}`;
const richConfig = config.richEditor || {};

// token label map ({ '[[field:5]]': 'Name', '[[fields]]': 'All form fields' })
const tokenLabels = {};
(props.tokens || []).forEach((t) => { tokenLabels[t.token] = t.label; });
const hasTokens = (props.tokens || []).length > 0;

// build a regex matching the field tokens ([[fields]] / [[field:N]]) plus any
// other registered literal tokens (e.g. [Form Name]).
function escapeRegex(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
const literalTokens = (props.tokens || [])
  .map((t) => t.token)
  .filter((t) => !/^\[\[field/.test(t) && t !== '[[fields]]');
const tokenPattern = new RegExp(
  ['\\[\\[fields\\]\\]', '\\[\\[field:\\d+\\]\\]', ...literalTokens.map(escapeRegex)].join('|'),
  'g',
);

// raw stored tokens -> chip spans so they render as chips in the editor
function tokensToChips(html) {
  if (!hasTokens || typeof html !== 'string') return html;
  return html.replace(tokenPattern, (token) => {
    const label = tokenLabels[token] || token;
    return `<span data-fb-token="${token}">${label}</span>`;
  });
}

// chip spans -> raw tokens for the stored value (what the server expects)
function chipsToTokens(html) {
  if (!hasTokens || typeof html !== 'string') return html;
  return html.replace(/<span[^>]*data-fb-token="([^"]+)"[^>]*>.*?<\/span>/g, (m, token) => token);
}

const data = ref(props.content || '');
const focused = ref(false);
const sourceView = ref(false);

// toggle between the WYSIWYG editor and a raw HTML source view; when leaving
// source view, push the edited HTML back into the editor
function toggleSource() {
  if (sourceView.value) {
    // re-chip raw tokens when returning to the WYSIWYG view
    editor.value.commands.setContent(tokensToChips(data.value), false);
    emit('input', data.value);
    emit('update:modelValue', data.value);
  }
  sourceView.value = !sourceView.value;
}

const formats = [
  { label: 'Paragraph', value: 'paragraph' },
  { label: 'Heading 1', value: 'h1' },
  { label: 'Heading 2', value: 'h2' },
  { label: 'Heading 3', value: 'h3' },
  { label: 'Heading 4', value: 'h4' },
  { label: 'Heading 5', value: 'h5' },
  { label: 'Heading 6', value: 'h6' },
  { label: 'Quote', value: 'blockquote' },
  { label: 'Code Block', value: 'codeBlock' },
];

const editor = useEditor({
  content: tokensToChips(props.content || ''),
  extensions: buildExtensions({ ...richConfig, tokenLabels }),
  editorProps: {
    attributes: { class: 'rich-editor__editable' },
  },
  onUpdate: ({ editor: e }) => {
    // store raw tokens, never the chip markup
    data.value = chipsToTokens(e.getHTML());
    emit('input', data.value);
    emit('update:modelValue', data.value);
  },
  onFocus: () => { focused.value = true; },
  onBlur: () => {
    focused.value = false;
    emit('blur', data.value);
  },
});

const currentBlock = computed(() => {
  if (!editor.value) return 'paragraph';
  for (let level = 1; level <= 6; level += 1) {
    if (editor.value.isActive('heading', { level })) return `h${level}`;
  }
  if (editor.value.isActive('blockquote')) return 'blockquote';
  if (editor.value.isActive('codeBlock')) return 'codeBlock';
  return 'paragraph';
});

function applyFormat(value) {
  const chain = editor.value.chain().focus();
  if (value === 'paragraph') {
    chain.setParagraph().run();
  } else if (value === 'blockquote') {
    chain.toggleBlockquote().run();
  } else if (value === 'codeBlock') {
    chain.toggleCodeBlock().run();
  } else if (/^h[1-6]$/.test(value)) {
    chain.setHeading({ level: parseInt(value.slice(1), 10) }).run();
  }
}

// insert a field token as a chip node (renders the field name, stores the token)
function insertToken(event) {
  const token = event.target.value;
  event.target.value = '';
  if (!token) return;
  editor.value.chain().focus().insertContent({
    type: 'fieldToken',
    attrs: { token },
  }).run();
}

function embedVideo() {
  const url = window.prompt('Enter a YouTube URL');
  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
  }
}

/* ---------------- image modal ---------------- */

const imageModal = reactive({
  open: false,
  editing: false,
  src: '',
  alt: '',
  elId: '',
  className: '',
});

function openImageModal(existing = null) {
  imageModal.editing = !!existing;
  imageModal.src = existing?.src || '';
  imageModal.alt = existing?.alt || '';
  imageModal.elId = existing?.id || '';
  imageModal.className = existing?.class || '';
  imageModal.open = true;
}

function closeImageModal() {
  imageModal.open = false;
}

function browseImage() {
  ui.openMediaModal({ type: 'Image', model: editorId, fieldId: `${editorId}-image` });
  eventBus.emit('media-set-type', 'Image');
  eventBus.emit('media-reload');
}

function confirmImage() {
  if (!imageModal.src) {
    closeImageModal();
    return;
  }
  editor.value.chain().focus().setImage({
    src: imageModal.src,
    alt: imageModal.alt || null,
    id: imageModal.elId || null,
    class: imageModal.className || null,
  }).run();
  closeImageModal();
}

/* ---------------- link modal ---------------- */

const linkModal = reactive({
  open: false,
});

// shared LinkForm operates on this object (same shape as the page Link field)
const linkModalModel = reactive(emptyLinkModel());

function emptyLinkModel() {
  return {
    text: null,
    type: 'internal',
    url: null,
    title: null,
    id: null,
    classes: null,
    file: { name: null, url: null },
  };
}

function getLinkType(href) {
  if (!href) return 'internal';
  if (href.includes('mailto:')) return 'email';
  if (href.includes('tel:')) return 'phone';
  if (href.startsWith('#')) return 'anchor';
  if (href.includes('http://') || href.includes('https://')) return 'external';
  if (href.includes('/storage/uploads')) return 'file';
  return 'internal';
}

function openLinkModal() {
  const { state } = editor.value;
  const { from, to } = state.selection;
  const selectedText = state.doc.textBetween(from, to, ' ');
  const attrs = editor.value.getAttributes('link');

  let url = attrs.href || '';
  const type = getLinkType(url);
  if (url.startsWith('mailto:')) url = url.replace('mailto:', '');
  if (url.startsWith('tel:')) url = url.replace('tel:', '');
  if (url.startsWith('#')) url = url.slice(1);

  Object.assign(linkModalModel, emptyLinkModel(), {
    text: selectedText || attrs.text || null,
    type,
    url: url || null,
    title: attrs.title || null,
    id: attrs.id || null,
    classes: attrs.class || null,
  });

  if (type === 'file') {
    linkModalModel.file = { name: null, url: attrs.href || null };
  }

  linkModal.open = true;
}

function closeLinkModal() {
  linkModal.open = false;
}

function confirmLink() {
  const m = linkModalModel;
  let url = m.url;

  // file links carry the resolved url on the file object
  if (m.type === 'file') {
    url = m.file?.url || m.url;
  }

  if (!url) {
    closeLinkModal();
    return;
  }

  if (m.type === 'external' && !(url.startsWith('http://') || url.startsWith('https://'))) {
    // mirror the old validation — require a protocol for external links
    return;
  }
  if (m.type === 'email' && !url.startsWith('mailto:')) {
    url = `mailto:${url}`;
  }
  if (m.type === 'phone' && !url.startsWith('tel:')) {
    url = `tel:${url.replace(/\s/g, '')}`;
  }
  if (m.type === 'anchor' && !url.startsWith('#')) {
    url = `#${url}`;
  }

  // mirror the content-type link: external/file open in a new window,
  // external links get rel="nofollow"
  let target = null;
  let rel = null;
  if (m.type === 'external' || m.type === 'file') {
    target = '_blank';
  }
  if (m.type === 'external') {
    rel = 'nofollow';
  }

  const attrs = {
    href: url,
    target,
    rel,
    title: m.title || null,
    id: m.id || null,
    class: m.classes || null,
  };

  const { from, to } = editor.value.state.selection;
  const hasSelection = from !== to;
  const text = m.text || url;

  if (hasSelection) {
    // apply the link to the current selection, replacing its text if the user
    // edited the Link Text field
    const selectedText = editor.value.state.doc.textBetween(from, to, ' ');
    if (text && text !== selectedText) {
      editor.value.chain().focus()
        .insertContentAt({ from, to }, text)
        .setTextSelection({ from, to: from + text.length })
        .setLink(attrs)
        .run();
    } else {
      editor.value.chain().focus().extendMarkRange('link').setLink(attrs).run();
    }
  } else {
    // no selection — insert the link text as a linked node
    editor.value.chain().focus()
      .insertContent(`<a href="${url}">${text}</a>`)
      .run();
    // re-apply attributes the inline html may not carry
    editor.value.chain().focus().extendMarkRange('link').setLink(attrs).run();
  }

  closeLinkModal();
}

/* ---------------- image pick handler (scoped by editorId) ------------------- */
// link picks (sitemap / file) are handled inside the shared rd-link-form

function onSelectingFile(payload) {
  document.querySelector('body')?.classList.remove('body-has-modal');
  if (payload.model !== editorId) return;

  const url = payload.link.original.replace(payload.link.basePath, '/');
  imageModal.src = url;
  if (!imageModal.open) openImageModal({ src: url });
  eventBus.emit('media-clear');
}

onMounted(() => {
  eventBus.on('selecting-file', onSelectingFile);
});

onUnmounted(() => {
  eventBus.off('selecting-file', onSelectingFile);
});

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy();
  }
});
</script>
