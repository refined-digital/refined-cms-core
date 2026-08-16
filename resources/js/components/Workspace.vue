<template>
  <Teleport to="body">
  <div class="pages-workspace">
    <header class="pages-workspace__header">
      <div class="pages-workspace__header-left">
        <button class="pages-workspace__tree-toggle" :class="{ 'is-open': railOpen }" @click="railOpen = !railOpen" :title="`Toggle ${config.heading} list`">
          <i class="fa fa-bars"></i>
        </button>
        <h3>{{ record ? (values.name || 'New ' + cleanButton) : config.heading }}</h3>
      </div>

      <nav class="pages-workspace__tabs" v-if="record">
        <button
          v-for="t in tabs"
          :key="t.slug"
          :class="{ 'is-active': tab === t.slug }"
          @click="tab = t.slug"
        >{{ t.name }}</button>
      </nav>

      <aside class="pages-workspace__actions">
        <span class="page-builder__error" v-if="previewError" :title="previewError">
          <i class="fa fa-exclamation-triangle"></i> Preview error
        </span>
        <template v-if="record">
          <a href="" class="button button--grey button--small" @click.prevent.stop="save">Save</a>
          <a href="" class="button button--grey button--small" v-if="!record.isNew && recordUrl" @click.prevent.stop="view">View</a>
          <a href="" class="button button--grey button--small" @click.prevent.stop="addRecord">Add {{ config.button }}</a>
          <a href="" class="button button--red button--small" v-if="!record.isNew" @click.prevent.stop="destroyRecord">Delete</a>
        </template>
        <a :href="exitUrl" class="button button--small pages-workspace__exit" title="Back to admin"><i class="fa fa-sign-out-alt"></i> Exit</a>
      </aside>
    </header>

    <div class="pages-workspace__body">
      <aside class="pages-workspace__tree" v-show="railOpen">
        <div class="pages-workspace__rail-search">
          <input type="text" class="form__control" placeholder="Search..." v-model="listQuery">
        </div>

        <nav class="pages-workspace__tree-nav pages-workspace__rail-list">
          <a
            href=""
            class="pages-workspace__rail-item"
            :class="{ 'is-active': record && item.id === record.id, 'is-inactive': !item.active }"
            v-for="item in filteredItems"
            :key="item.id"
            @click.prevent.stop="guardedLoad(item.id)"
          >
            <span class="pages-workspace__rail-name">{{ item.name }}</span>
            <span class="pages-workspace__rail-date" v-if="item.date">{{ item.date }}</span>
          </a>
          <p class="page-builder__empty pages-workspace__rail-empty" v-if="!filteredItems.length">
            {{ listQuery ? 'No matches' : 'Nothing here yet' }}
          </p>
        </nav>

        <footer class="pages-workspace__tree-footer" v-if="config.moduleLinks && config.moduleLinks.length">
          <a :href="link.url" v-for="link in config.moduleLinks" :key="link.url">
            <i class="fa fa-cog"></i> {{ link.name }}
          </a>
        </footer>
      </aside>

      <section class="pages-workspace__panel" v-if="record">
        <div class="pages-workspace__pane" v-show="tab === 'content'" v-if="contentField">
          <div class="form__note pages-workspace__pane-note" v-if="record.isNew">
            Save the {{ lowerButton }} first &mdash; the content editor needs a saved record.
          </div>
          <rd-page-builder-panel
            v-else
            :page="record"
            :content="config.palette"
            :config="{}"
            :key="`panel--${record.id}`"
            v-model:selected-index="selectedIndex"
            @field-input="onFieldInput"
            @block-hover="onBlockHover"
            @open-block-modal="blockModalOpen = true"
          ></rd-page-builder-panel>
        </div>

        <div
          class="pages-workspace__pane"
          v-for="t in schemaTabs"
          :key="t.slug"
          v-show="tab === t.slug"
        >
          <template v-for="(group, gi) in tabGroups(t.tab)" :key="gi">
            <h4 class="pages-workspace__group-heading" v-if="group.name && group.name !== t.name">{{ group.name }}</h4>
            <template v-for="(row, ri) in group.rows" :key="ri">
              <template v-for="field in rowFields(row)" :key="field.name">
                <rd-workspace-form-field
                  v-if="renderable(field)"
                  :field="field"
                  :values="values"
                  :tag-values="modelTags"
                ></rd-workspace-form-field>
              </template>
            </template>
          </template>
        </div>
      </section>

      <section class="pages-workspace__preview">
        <iframe
          v-if="record && !record.isNew"
          ref="frame"
          :src="previewSrc"
          :key="`preview--${record.id}`"
          title="Preview"
        ></iframe>
        <div class="pages-workspace__preview-empty" v-else>
          <p v-if="record && record.isNew">Save to see a live preview.</p>
          <p v-else>Select an item to start editing.</p>
        </div>
      </section>
    </div>

    <rd-add-block-modal
      v-if="blockModalOpen"
      :content="config.palette"
      @add="addBlockFromModal"
      @close="blockModalOpen = false"
    ></rd-add-block-modal>
  </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import swal from 'sweetalert';
import _ from 'lodash';

import { useUiStore } from '../stores/ui';
import { formatContent } from '../composables/useContentBlocks';
import { usePreviewChannel } from '../composables/usePreviewChannel';

const props = defineProps(['config']);
const config = props.config;

const ui = useUiStore();

const exitUrl = `${window.siteUrl}/refined/dashboard`;

// rail
const railOpen = ref(true);
const items = ref([]);
const listQuery = ref('');

const filteredItems = computed(() => {
  const term = listQuery.value.trim().toLowerCase();
  if (!term) return items.value;
  return items.value.filter(item => item.name.toLowerCase().includes(term));
});

// record state
const record = ref(null);
const values = reactive({});
const modelTags = ref({});
const schema = ref([]);
const recordUrl = ref(null);
const tab = ref('content');
const selectedIndex = ref(null);
const blockModalOpen = ref(false);

let snapshot = '';

const lowerButton = computed(() => (config.button || 'item').toLowerCase());
// button labels read 'a Post' / 'an Item'; the article reads oddly after 'New'
const cleanButton = computed(() => (config.button || 'Item').replace(/^an? /i, ''));

// ---- schema helpers --------------------------------------------------------

function rowFields(row) {
  return Array.isArray(row) ? row : Object.values(row);
}

function allFields(schemaTabs) {
  const fields = [];
  schemaTabs.forEach(t => {
    tabGroups(t).forEach(group => group.rows.forEach(row => rowFields(row).forEach(f => fields.push(f))));
  });
  return fields;
}

function tabGroups(t) {
  const groups = [];
  const pushBlocks = (blocks) => (blocks || []).forEach(b => groups.push({ name: b.name, rows: b.fields || [] }));

  if (t.sections) {
    pushBlocks(t.sections.left?.blocks);
    pushBlocks(t.sections.right?.blocks);
    pushBlocks(t.sections.bottom?.blocks);
  }
  if (t.blocks) pushBlocks(t.blocks);
  if (t.fields) groups.push({ name: null, rows: t.fields });

  return groups;
}

function isContentBlocksTab(t) {
  const fields = [];
  tabGroups(t).forEach(g => g.rows.forEach(row => rowFields(row).forEach(f => fields.push(f))));
  return fields.length > 0 && fields.every(f => f.type === 'contentBlocks');
}

const contentField = computed(() => {
  const fields = allFields(schema.value);
  return fields.find(f => f.type === 'contentBlocks')?.name || null;
});

const schemaTabs = computed(() =>
  schema.value
    .filter(t => !isContentBlocksTab(t))
    .map(t => {
      let slug = _.kebabCase(t.name);
      let name = t.name;

      // models often name their details tab 'Content', which collides with
      // the content blocks tab
      if (slug === 'content' && contentField.value) {
        slug = 'details';
        name = 'Details';
      }

      return { slug, name, tab: t };
    })
);

const tabs = computed(() => {
  const out = [];
  if (contentField.value) out.push({ slug: 'content', name: 'Content' });
  return out.concat(schemaTabs.value);
});

function renderable(field) {
  return field && field.name && !['contentBlocks', 'className', 'comment'].includes(field.type);
}

// ---- loading ---------------------------------------------------------------

async function loadList() {
  const r = await axios.get(config.routes.list);
  if (r.data.success) items.value = r.data.items;
}

async function loadRecord(id) {
  ui.loading = true;
  try {
    const r = await axios.get(config.routes.data.replace('RECORD_ID', id));
    const data = r.data;

    schema.value = data.schema;
    recordUrl.value = data.url || null;
    modelTags.value = data.modelTags || {};

    Object.keys(values).forEach(k => delete values[k]);
    Object.assign(values, data.values || {});

    // meta fields render through the same generic renderer
    values['meta[uri]'] = data.meta?.uri || '';
    values['meta[title]'] = data.meta?.title || '';
    values['meta[description]'] = data.meta?.description || '';

    // seed tag values so an untouched tags field round-trips unchanged
    allFields(schema.value).forEach(f => {
      if (f.type === 'tags') {
        const type = f.tagType || 'tags';
        values[f.name] = (data.modelTags?.[type] || []).map(t => t.name).join('|');
      }
    });

    record.value = reactive({
      id: id === 'new' ? 0 : data.id,
      isNew: id === 'new',
      content: Array.isArray(data.content) ? data.content : [],
    });

    tab.value = id === 'new' || !contentField.value ? (id === 'new' ? firstDetailsTab() : 'content') : 'content';
    selectedIndex.value = null;
    resetPreview();

    nextTick(() => takeSnapshot());
  } finally {
    ui.loading = false;
  }
}

function firstDetailsTab() {
  return schemaTabs.value[0]?.slug || 'content';
}

function guardedLoad(id) {
  if (record.value && id === record.value.id) return;

  if (!isDirty()) {
    loadRecord(id);
    return;
  }

  swal({
    title: 'Discard unsaved changes?',
    text: 'This item has changes that have not been saved.',
    icon: 'warning',
    buttons: ['Keep editing', 'Discard'],
    dangerMode: true,
  }).then(discard => {
    if (discard) loadRecord(id);
  });
}

function addRecord() {
  if (record.value?.isNew) return;
  if (!isDirty()) {
    loadRecord('new');
    return;
  }
  swal({
    title: 'Discard unsaved changes?',
    text: 'This item has changes that have not been saved.',
    icon: 'warning',
    buttons: ['Keep editing', 'Discard'],
    dangerMode: true,
  }).then(discard => {
    if (discard) loadRecord('new');
  });
}

// ---- dirty tracking --------------------------------------------------------

function stateString() {
  return JSON.stringify({ values, content: record.value?.content || [] });
}

function takeSnapshot() {
  snapshot = stateString();
}

function isDirty() {
  return record.value !== null && stateString() !== snapshot;
}

// ---- saving ----------------------------------------------------------------

const repeatableNames = computed(() =>
  allFields(schema.value).filter(f => f.type === 'repeatable').map(f => f.name)
);

const tagFields = computed(() => allFields(schema.value).filter(f => f.type === 'tags'));

function buildPayload() {
  const payload = { action: 'save' };

  Object.entries(values).forEach(([key, value]) => {
    const metaMatch = key.match(/^meta\[(.+)\]$/);
    if (metaMatch) {
      payload.meta = payload.meta || {};
      payload.meta[metaMatch[1]] = value;
      return;
    }

    if (repeatableNames.value.includes(key)) {
      payload[key] = JSON.stringify(value || []);
      return;
    }

    payload[key] = value;
  });

  if (tagFields.value.length) {
    payload.modelTags = {};
    tagFields.value.forEach(f => {
      payload.modelTags[f.tagType || 'tags'] = values[f.name] || '';
    });
    tagFields.value.forEach(f => delete payload[f.name]);
  }

  // string, not array: some form requests validate content as required, and
  // the saved() hook json-decodes it either way
  payload[contentField.value || 'content'] = JSON.stringify(record.value?.content || []);

  return payload;
}

async function save() {
  ui.loading = true;

  const isNew = record.value.isNew;
  const url = isNew
    ? config.routes.store
    : config.routes.update.replace('RECORD_ID', record.value.id);

  try {
    const r = await axios.request({ url, method: isNew ? 'POST' : 'PUT', data: buildPayload() });

    if (r.data && r.data.success) {
      await loadList();
      if (isNew) {
        await loadRecord(r.data.id);
      } else {
        takeSnapshot();
      }
      swal({ title: 'Success', text: 'Successfully saved', icon: 'success' });
    } else {
      // an html response here is a validation redirect from the form request
      const msg = typeof r.data === 'string'
        ? 'The save was rejected - please check the required fields are filled in.'
        : (r.data?.msg || '');
      swal({ title: 'Something went wrong', text: msg, icon: 'error' });
    }
  } catch (e) {
    const errors = e.response?.data?.errors;
    const msg = errors
      ? Object.values(errors).flat().join('\n')
      : (e.response?.data?.message || e.message);
    swal({ title: 'Something went wrong', text: msg, icon: 'error' });
  } finally {
    ui.loading = false;
  }
}

function destroyRecord() {
  swal({
    title: 'Are you sure?',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  }).then(async (value) => {
    if (!value) return;
    ui.loading = true;
    try {
      const r = await axios.delete(config.routes.destroy.replace('RECORD_ID', record.value.id));
      if (r.data.success) {
        record.value = null;
        await loadList();
        if (items.value.length) loadRecord(items.value[0].id);
      } else {
        swal({ title: 'Something went wrong', text: 'Failed to delete', icon: 'error' });
      }
    } finally {
      ui.loading = false;
    }
  });
}

function view() {
  if (recordUrl.value) window.open(recordUrl.value);
}

// ---- content blocks + preview ---------------------------------------------

const frame = ref(null);

const previewSrc = computed(() =>
  record.value ? config.routes.preview.replace('RECORD_ID', record.value.id) : ''
);

const {
  previewReady,
  previewError,
  postToFrame,
  renderPreview,
  debouncedRender,
  resetPreview,
} = usePreviewChannel({
  frame,
  previewUrl: () => previewSrc.value,
  getContent: () => record.value?.content || [],
});

watch(
  () => record.value?.content,
  () => {
    if (previewReady.value) debouncedRender();
  },
  { deep: true }
);

watch(selectedIndex, (index) => {
  postToFrame({ type: 'rcms:select', index });
});

// block labels for the preview's hover/selection tag (workspace modules have
// no page-anchor config, so the block name alone)
const blockLabels = computed(() => {
  const labels = {};
  (record.value?.content || []).forEach((block, index) => {
    labels[index] = { name: block.name || 'Block', anchor: null };
  });
  return labels;
});

function sendBlockMeta() {
  postToFrame({ type: 'rcms:block-meta', labels: blockLabels.value });
}

watch(blockLabels, () => {
  if (previewReady.value) sendBlockMeta();
});

function onFieldInput({ index, field, value }) {
  postToFrame({ type: 'rcms:echo', index, field, value });
}

function onBlockHover(index) {
  postToFrame({ type: 'rcms:hover', index });
}

function onMessage(event) {
  if (event.origin !== window.location.origin || !event.data || event.data.source !== 'rcms-preview') {
    return;
  }

  switch (event.data.type) {
    case 'rcms:ready':
      previewReady.value = true;
      sendBlockMeta();
      renderPreview();
      break;
    case 'rcms:block-click':
      tab.value = 'content';
      selectedIndex.value = event.data.index;
      break;
  }
}

function addBlockFromModal(item) {
  const block = formatContent(_.cloneDeep(item));
  record.value.content = [...(record.value.content || []), block];
  blockModalOpen.value = false;
  selectedIndex.value = record.value.content.length - 1;
}

// ---- boot ------------------------------------------------------------------

onMounted(async () => {
  window.addEventListener('message', onMessage);
  document.body.classList.add('page-builder-open');

  ui.loading = true;
  await loadList();
  ui.loading = false;

  const params = new URLSearchParams(window.location.search);
  if (params.get('new')) {
    loadRecord('new');
  } else if (params.get('edit')) {
    loadRecord(parseInt(params.get('edit'), 10));
  } else if (items.value.length) {
    loadRecord(items.value[0].id);
  }
});

onUnmounted(() => {
  window.removeEventListener('message', onMessage);
  document.body.classList.remove('page-builder-open');
  debouncedRender.cancel();
});
</script>
