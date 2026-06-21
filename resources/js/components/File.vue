<template>
  <div class="form__control--file">
    <input type="hidden" v-model="item" :name="name" :id="id">
    <div class="form__file">
      <div class="form__file-name">
        <strong class="form__file--title" v-if="file.name">{{ file.name }}</strong> <span v-if="file.name">/</span>
        <span class="form__file--size" v-if="file.size">{{ file.size }} / </span>
        <a :href="file.link.original" v-if="file.link.original" target="_blank" class="form__file--link">View File</a>
      </div>
      <aside>
        <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearFile" class="button button--red button--small">Clear File</a>
      </aside>
    </div><!-- / form image -->
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, getCurrentInstance } from 'vue';
import eventBus from '../eventBus';
import { useUiStore } from '../stores/ui';

const props = defineProps(['name', 'id', 'value']);
const emit = defineEmits(['input']);

const ui = useUiStore();
const uid = getCurrentInstance().uid;

const clone = (data) => JSON.parse(JSON.stringify(data));

const item = ref('');
const file = ref({
  name: '',
  size: '',
  link: { original: '' },
});
const defaultFile = {
  link: { thumb: '' },
};

function clearFile() {
  item.value = null;
  file.value = clone(defaultFile);
  emit('input', item.value);
}

function loadModal() {
  eventBus.emit('media-set-type', 'File');
  ui.media.showModal = true;
  ui.media.model = uid;
}

function updateFile(data) {
  if (data.model === uid) {
    file.value = data;
    item.value = file.value.id;
    emit('input', item.value);
    eventBus.emit('media-close');
  }
}

function loadFile() {
  item.value = props.value;
  if (props.value) {
    axios
      .get(`${window.siteUrl}/refined/media/${props.value}`)
      .then((r) => {
        ui.loading = false;
        if (r.status === 200) {
          file.value = r.data.file;
        }
      })
      .catch(() => {
        ui.loading = false;
      });
  } else {
    file.value = defaultFile;
  }
}

watch(() => props.value, loadFile);

onMounted(() => {
  loadFile();
  eventBus.on('selecting-file', updateFile);
});

onUnmounted(() => {
  eventBus.off('selecting-file', updateFile);
});
</script>
