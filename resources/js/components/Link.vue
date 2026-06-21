<template>
  <div class="form__control--link">
    <input type="hidden" class="form__control" v-model="link" :name="name" :id="elementId"/>


    <div class="link__type link__type--with-button">
      <div class="link__file form__file-name">
        <strong class="form__file--title" v-if="linkModel && linkModel.text">{{ linkModel.text }}</strong>
        <span v-if="linkModel && linkModel.text"> / </span>
        <a :href="linkModel && linkModel.type === 'file' ? linkModel.file.url : linkModel.url" target="_blank" class="form__file--link" v-if="linkModel && linkModel.url">View Link</a>
      </div>

      <aside>
        <a href="" @click.prevent.stop="openModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearLink" class="button button--red button--small">Clear Link</a>
      </aside>
    </div>

    <div class="rd-link form__horz sitemap" :class="active ? 'sitemap--active' : ''">
      <div class="sitemap__inner">
        <div class="sitemap__fields">
          <div class="form__group">
            <div class="form__row" v-show="!isSimple">
              <label class="form__label" :for="`form--link-text-${elementId}`">Link Text</label>
              <input type="text" class="form__control" name="link_text" :id="`form--link-text-${elementId}`" v-model="modal.text"/>
            </div>

            <div class="form__row">
              <label class="form__label" :for="`form--link-type-${elementId}`">Link Type</label>
              <select class="form__control" name="link_type" :id="`form--link-type-${elementId}`" v-model="modal.type">
                <option :value="item.value" v-for="item in options" :key="item.label">{{ item.label }}</option>
              </select>
            </div>

            <div class="form__row">
              <label class="form__label" :for="`form--link-url-${elementId}`">{{ urlLabel }}</label>
              <div class="link__type" :class="classList">
                <input :type="modal.type === 'file' ? 'hidden' : 'text'" class="form__control" name="link_url" :id="`form--link-url-${elementId}`" v-model="modal.url"/>


                <div class="link__file form__file-name" v-if="modal.type === 'file'">
                  <strong class="form__file--title" v-if="modal.file.name">{{ modal.file.name }}</strong>
                  <span v-if="modal.file.name"> / </span>
                  <a :href="modal.file.url" v-if="modal.file.url" target="_blank" class="form__file--link">View File</a>
                </div>

                <aside v-if="hasButton.includes(modal.type)">
                  <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
                  <a href="" @click.prevent.stop="clearFile" class="button button--red button--small" v-if="modal.type === 'file'">Clear File</a>
                </aside>

                <div class="form__note" v-if="modal.type === 'external'">Must start with <code>http://</code> or <code>https://</code></div>
              </div>
            </div>

            <div class="form__row" v-show="!isSimple">
              <label class="form__label" :for="`form--link-title-${elementId}`">Element Title</label>
              <input type="text" class="form__control" name="link_title" :id="`form--link-title-${elementId}`" v-model="modal.title"/>
            </div>

            <div class="form__row" v-show="!isSimple">
              <label class="form__label" :for="`form--link-id-${elementId}`">Element ID</label>
              <input type="text" class="form__control" name="link_id" :id="`form--link-id-${elementId}`" v-model="modal.id"/>
            </div>

            <div class="form__row" v-show="!isSimple">
              <label class="form__label" :for="`form--link-classes-${elementId}`">Element Classes</label>
              <input type="text" class="form__control" name="link_classes" :id="`form--link-classes-${elementId}`" v-model="modal.classes"/>
            </div>
          </div>
        </div>

        <footer class="sitemap__footer">
          <button class="button button--blue button--small" @click.prevent.stop="save">Save</button>
          <button class="button button--red button--small" @click.prevent.stop="closeModal">Close</button>
        </footer>
      </div>
    </div>
  </div>

</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted, getCurrentInstance } from 'vue';
  import _ from 'lodash';
  import eventBus from '../eventBus';
  import { useUiStore } from '../stores/ui';

  const props = defineProps(['name', 'id', 'value', 'settings']);
  const emit = defineEmits(['input']);

  const ui = useUiStore();
  const uid = getCurrentInstance().uid;

  const link = ref(null);
  const linkModel = ref(null);
  const modal = ref({
    text: null,
    type: 'internal',
    url: null,
    title: null,
    id: null,
    classes: null,
    file: {
      name: null,
      url: null
    },
  });
  const hasButton = ref([
    'internal',
    'file'
  ]);
  const elementId = ref(null);
  const active = ref(false);

  const urlLabel = computed(() => {
    if (modal.value.type === 'email') {
      return 'Email Address';
    }

    if (modal.value.type === 'phone') {
      return 'Phone';
    }

    if (modal.value.type === 'anchor') {
      return 'Element ID';
    }

    return 'URL'
  });

  const classList = computed(() => {
    if (hasButton.value.includes(modal.value.type)) {
      return 'link__type--with-button';
    }

    return '';
  });

  const isSimple = computed(() => {
    if (!props.settings) {
      return false;
    }

    if (props.settings.simple) {
      return true;
    }

    return false;
  });

  const options = computed(() => {

    const items = [
      { value: 'internal', label: 'Internal Page' },
      { value: 'external', label: 'External Link' },
    ];

    const items2 = [
      { value: 'file', label: 'File / Image' },
      { value: 'email', label: 'Email' },
      { value: 'phone', label: 'Phone' },
      { value: 'anchor', label: 'Anchor' },
    ];

    const opts = [
      ...items
    ];

    if (!isSimple.value) {
      opts.push(...items2);
    }

    return opts;
  });

  function clearLink() {
    link.value = null;
    linkModel.value.text = null;
    linkModel.value.type = 'internal';
    linkModel.value.url = null;
    linkModel.value.id = null;
    linkModel.value.title = null;
    linkModel.value.classes = null;
    linkModel.value.file.name = null;
    linkModel.value.file.url = null;
  }

  function clearFile() {
    modal.value.url = null;
    modal.value.file.name = null;
    modal.value.file.url = null;
  }

  function openModal() {
    if (linkModel.value) {
      modal.value = _.cloneDeep(linkModel.value);
    }

    active.value = true;
    ui.link.active = true;
  }

  function loadModal() {
    if (modal.value.type === 'internal') {
      eventBus.emit('sitemap-reload');
      ui.sitemap.showModal = true;
      ui.sitemap.model = uid;
    }

    if (modal.value.type === 'file') {
      eventBus.emit('media-set-type', '*');
      eventBus.emit('media-reload');
      ui.media.showModal = true;
      ui.media.model = uid;
    }
  }

  function updateLink(data) {
    if (ui.sitemap.model == uid) {
      modal.value.url = data;
      eventBus.emit('sitemap-close');
    }

    if (ui.media.model == uid) {
      modal.value.url = data.id;
      modal.value.file.name = data.file;
      const url = data.link.original.replace(data.link.basePath, '/');
      modal.value.file.url = url;
      eventBus.emit('media-close');
    }
  }

  function loadLink() {
    let value = props.value;
    if (typeof value === 'string' && value.length) {
      try {
        value = JSON.parse(value);
      } catch (e) {
        console.warn(e.message);
        value = _.cloneDeep(modal.value);
      }
    }

    if (typeof value !== 'object' || !value) {
      modal.value = _.cloneDeep(modal.value);
    } else {
      modal.value = value;
    }

    link.value = JSON.stringify(value);
    linkModel.value = value;
  }

  function save() {
    const fieldsToCheck = [modal.value.url];
    if (!isSimple.value) {
      fieldsToCheck.push(modal.value.text);
    }

    const check = fieldsToCheck.filter(item => !!item);

    if (check.length !== fieldsToCheck.length) {

      let msg = 'The following fields are required: ';
      if (!modal.value.text && !isSimple.value) msg += "\n - Link Text";
      if (!modal.value.url) msg += "\n - URL";
      alert(msg);

      return;
    }

    linkModel.value = _.cloneDeep(modal.value);
    link.value = JSON.stringify(linkModel.value);
    emit('input', linkModel.value);
    closeModal();
  }

  function closeModal() {
    active.value = false;
    modal.value.text = null;
    modal.value.type = 'internal';
    modal.value.url = null;
    modal.value.id = null;
    modal.value.title = null;
    modal.value.classes = null;
    modal.value.file.name = null;
    modal.value.file.url = null;
    ui.link.active = false;
  }

  // created
  loadLink();

  if (props.id) {
    elementId.value = props.id;
  } else {
    elementId.value = Date.now();
  }

  onMounted(() => {
    eventBus.on('selecting-link', updateLink);
    eventBus.on('selecting-file', updateLink);
  });

  onUnmounted(() => {
    eventBus.off('selecting-link', updateLink);
    eventBus.off('selecting-file', updateLink);
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

.rd-link .sitemap__inner {
  max-height: 492px;
}

.rd-link .sitemap__fields {
  min-height: 402px;
}

.rd-link .form__row {
  flex: 0 0 100%;
}


</style>
