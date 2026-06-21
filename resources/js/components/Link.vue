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
          <rd-link-form v-model="modal" :settings="settings"></rd-link-form>
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
  import { ref, computed } from 'vue';
  import _ from 'lodash';
  import { useUiStore } from '../stores/ui';

  const props = defineProps(['name', 'id', 'value', 'settings']);
  const emit = defineEmits(['input', 'update:modelValue']);

  const ui = useUiStore();

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
  const elementId = ref(null);
  const active = ref(false);

  const isSimple = computed(() => !!(props.settings && props.settings.simple));

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

  function openModal() {
    if (linkModel.value) {
      modal.value = _.cloneDeep(linkModel.value);
    }

    active.value = true;
    ui.link.active = true;
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
    emit('update:modelValue', linkModel.value);
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
</script>

<style scoped>
.link__type {
  flex: 1;
}

.link__type--with-button {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  align-items: center;
}

.link__file {
  flex: 1;
}

.link__type aside {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

/* keep the browse/clear buttons at their natural size — don't let the flex
   row stretch or shrink them when space gets tight */
.link__type aside .button {
  flex: 0 0 auto;
  white-space: nowrap;
}

.rd-link .sitemap__inner {
  max-height: 492px;
}

.rd-link .sitemap__fields {
  min-height: 402px;
}
</style>
