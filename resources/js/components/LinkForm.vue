<template>
  <div class="link-form">
    <div class="form__group">
      <div class="form__row" v-show="!isSimple">
        <label class="form__label" :for="`form--link-text-${uid}`">Link Text</label>
        <input type="text" class="form__control" :id="`form--link-text-${uid}`" v-model="model.text"/>
      </div>

      <div class="form__row">
        <label class="form__label" :for="`form--link-type-${uid}`">Link Type</label>
        <select class="form__control" :id="`form--link-type-${uid}`" v-model="model.type">
          <option :value="item.value" v-for="item in options" :key="item.value">{{ item.label }}</option>
        </select>
      </div>

      <div class="form__row">
        <label class="form__label" :for="`form--link-url-${uid}`">{{ urlLabel }}</label>
        <div class="link__type" :class="classList">
          <input :type="model.type === 'file' ? 'hidden' : 'text'" class="form__control" :id="`form--link-url-${uid}`" v-model="model.url"/>

          <div class="link__file form__file-name" v-if="model.type === 'file'">
            <strong class="form__file--title" v-if="model.file.name">{{ model.file.name }}</strong>
            <span v-if="model.file.name"> / </span>
            <a :href="model.file.url" v-if="model.file.url" target="_blank" class="form__file--link">View File</a>
          </div>

          <aside v-if="hasButton.includes(model.type)">
            <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
            <a href="" @click.prevent.stop="clearFile" class="button button--red button--small" v-if="model.type === 'file'">Clear File</a>
          </aside>

          <div class="form__note" v-if="model.type === 'external'">Must start with <code>http://</code> or <code>https://</code></div>
        </div>
      </div>

      <div class="form__row" v-show="!isSimple">
        <label class="form__label" :for="`form--link-title-${uid}`">Element Title</label>
        <input type="text" class="form__control" :id="`form--link-title-${uid}`" v-model="model.title"/>
      </div>

      <div class="form__row" v-show="!isSimple">
        <label class="form__label" :for="`form--link-id-${uid}`">Element ID</label>
        <input type="text" class="form__control" :id="`form--link-id-${uid}`" v-model="model.id"/>
      </div>

      <div class="form__row" v-show="!isSimple">
        <label class="form__label" :for="`form--link-classes-${uid}`">Element Classes</label>
        <input type="text" class="form__control" :id="`form--link-classes-${uid}`" v-model="model.classes"/>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed, onMounted, onUnmounted, getCurrentInstance } from 'vue';
  import eventBus from '../eventBus';
  import { useUiStore } from '../stores/ui';

  // shared link form used by both the page content-type Link field (Link.vue)
  // and the rich text editor's link modal. it operates on a link object via
  // v-model and owns its own sitemap/media browsing, scoped by its instance id.
  // mutated in place through the shared object (no update:modelValue emit needed)
  const props = defineProps(['modelValue', 'settings']);

  const ui = useUiStore();
  const uid = getCurrentInstance().uid;

  const model = computed(() => props.modelValue);

  const hasButton = ['internal', 'file'];

  const isSimple = computed(() => !!(props.settings && props.settings.simple));

  const options = computed(() => {
    const opts = [
      { value: 'internal', label: 'Internal Page' },
      { value: 'external', label: 'External Link' },
    ];

    if (!isSimple.value) {
      opts.push(
        { value: 'file', label: 'File / Image' },
        { value: 'email', label: 'Email' },
        { value: 'phone', label: 'Phone' },
        { value: 'anchor', label: 'Anchor' },
      );
    }

    return opts;
  });

  const urlLabel = computed(() => {
    if (model.value.type === 'email') return 'Email Address';
    if (model.value.type === 'phone') return 'Phone';
    if (model.value.type === 'anchor') return 'Element ID';
    return 'URL';
  });

  const classList = computed(() => hasButton.includes(model.value.type) ? 'link__type--with-button' : '');

  function clearFile() {
    model.value.url = null;
    model.value.file.name = null;
    model.value.file.url = null;
  }

  function loadModal() {
    if (model.value.type === 'internal') {
      eventBus.emit('sitemap-reload');
      ui.sitemap.showModal = true;
      ui.sitemap.model = uid;
    }

    if (model.value.type === 'file') {
      eventBus.emit('media-set-type', '*');
      eventBus.emit('media-reload');
      ui.media.showModal = true;
      ui.media.model = uid;
    }
  }

  // sitemap pick — payload is a bare URL string, scoped via the store
  function onSelectingLink(url) {
    if (ui.sitemap.model != uid) return;
    model.value.url = url;
    eventBus.emit('sitemap-close');
  }

  // media pick — payload is the file object, scoped by its own model id
  function onSelectingFile(data) {
    if (!data || data.model != uid) return;
    model.value.url = data.id;
    model.value.file.name = data.file;
    model.value.file.url = data.link.original.replace(data.link.basePath, '/');
    eventBus.emit('media-close');
  }

  onMounted(() => {
    eventBus.on('selecting-link', onSelectingLink);
    eventBus.on('selecting-file', onSelectingFile);
  });

  onUnmounted(() => {
    eventBus.off('selecting-link', onSelectingLink);
    eventBus.off('selecting-file', onSelectingFile);
  });
</script>

<style scoped>
.link__type {
  flex: 1;
}

.link__type--with-button {
  display: flex;
  gap: 20px;
  align-items: center;
}

.link__file {
  flex: 1;
}

.link__type aside {
  display: flex;
  gap: 8px;
}

/* the global select.form__control is width: auto — make the link-type
   select fill its row like the other fields */
.link-form select.form__control {
  width: 100%;
}

.link-form .form__row {
  flex: 0 0 100%;
}
</style>
