<template>
  <div class="form__control--image">
    <input type="hidden" v-model="image" :name="name" :id="id">
    <div class="form__image">
      <figure>
        <div class="form__image-thumb">
          <img
              class="form__image-thumb-img"
              v-if="file.type == 'Image'"
              :src="fileUrl"
          />
          <div class="spinner" v-if="file.type === 'Image' && file.external_id && !file.external_url"></div>
          <span class="form__image-thumb-img form__image-thumb-video" v-if="file.type == 'Video'">
            <i class="fas fa-video"></i>
            <span class="form__image-thumb-name">{{ file.name }}</span>
          </span>
        </div>
        <figcaption>
          <span :title="file.name">{{ file.name }}</span>
        </figcaption>
      </figure>

      <aside>
        <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearFile" class="button button--red button--small">Clear File</a>
      </aside>
    </div><!-- / form image -->

    <div class="form__image--note form__note" v-if="file.note" v-html="file.note"></div>
  </div>

</template>

<script setup>

    import { ref, computed, watch, onMounted, onUnmounted, getCurrentInstance } from 'vue';
    import eventBus from '../eventBus';
    import { useUiStore } from '../stores/ui';

    const props = defineProps([
      'name',
      'id',
      'value',
      'modelValue',
      'width',
      'height',
    ]);
    const emit = defineEmits(['input', 'update:modelValue']);

    // support both the v-model (modelValue) and explicit :value bindings
    const currentValue = () => props.value ?? props.modelValue;

    const ui = useUiStore();
    const uid = getCurrentInstance().uid;

    const clone = (data) => JSON.parse(JSON.stringify(data));

    const image = ref('');

    const file = ref({
      link: {
        thumb: '',
      },
      external_id: '',
      external_url: '',
      note: null,
      type: '',
    });

    const defaultFile = {
      link: {
        thumb: '',
      },
      external_id: '',
      external_url: '',
    };

    const fileUrl = computed(() => {
      if (file.value.external_url) {
        return file.value.external_url;
      }

      return file.value.link.thumb;
    });

    function clearFile() {
      image.value = null;
      file.value = clone(defaultFile);
      emitInput();
    }

    function loadModal() {
      eventBus.emit('media-set-type', 'Image');
      eventBus.emit('media-reload');
      ui.media.showModal = true;
      ui.media.model = uid;
    }

    function mediaUpdated(data) {
      if (data.external_url) {
        file.value.external_url = data.external_url;
      }
    }

    function updateFile(data) {
      if (data.model === uid) {
        file.value = data;
        image.value = file.value.id;
        emitInput();
        eventBus.emit('media-close');
      }
    }

    function loadFile() {
      const value = currentValue();
      image.value = value;

      if (value) {
        axios
          .get(`${window.siteUrl}/refined/media/${value}`)
          .then(r => {
            ui.loading = false;
            if (r.status === 200 && r.data.file) {
              file.value = r.data.file;
              setFileNote();
              if (typeof props.name !== 'undefined') {
                emitInput();
              }
            }
          })
          .catch(() => {
            ui.loading = false;
          })
        ;
      } else {
        file.value = defaultFile;
        setFileNote();
        if (typeof props.name !== 'undefined') {
          emitInput();
        }
      }

    }

    function setFileNote() {

      if (props.width || props.height) {

        const n = [];
        if (props.width) {
          n.push(`${props.width}px wide`)
        }
        if (props.height) {
          n.push(`${props.height}px tall`)
        }

        const dimensions = `<strong>${n.length > 1 ? 'fit within ' : ''}${n.join(' x ')}</strong>`

        file.value.note = `
          Image will be resized to ${dimensions}
          <br>If you are having trouble with images, <a href="https://www.iloveimg.com/photo-editor" target="_blank">visit this page</a> to create your image.</div>
        `;
      }
    }

    function emitInput() {
      emit('input', image.value);
      emit('update:modelValue', image.value);
    }

    watch(() => [props.value, props.modelValue], loadFile);

    loadFile();

    onMounted(() => {
      eventBus.on('selecting-file', updateFile);
      eventBus.on('media-updated', mediaUpdated);
    });

    onUnmounted(() => {
      eventBus.off('selecting-file', updateFile);
      eventBus.off('media-updated', mediaUpdated);
    });
</script>
