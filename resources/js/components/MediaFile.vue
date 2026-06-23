<template>
    <figure>
      <span class="media__file-thumb">
        <img
          class="media__file-thumb-image"
          v-if="file.type == 'Image'"
          :src="fileUrl"
        />
        <div class="spinner" v-if="file.type === 'Image' && file.external_id && !file.external_url && !external_url"></div>
        <i class="fas fa-file" v-if="file.type == 'File'"></i>
        <i class="fas fa-video" v-if="file.type == 'Video'"></i>
      </span>
      <figcaption class="media__file-details">
        <span class="media__file-title"><span :title="file.name">{{ file.name }}</span></span>
        <span class="media__file-type"><span>{{ file.type }}</span></span>
        <span class="media__file-size"><span>{{ file.size }}</span></span>
      </figcaption>
    </figure>

</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import eventBus from '../eventBus';

const props = defineProps(['file', 'siteUrl']);

const external_url = ref(undefined);

const fileUrl = computed(() => {
  if (props.file.external_url) {
    return props.file.external_url;
  }

  if (external_url.value) {
    return external_url.value;
  }

  return props.file.link.thumb;
});

function mediaUpdated(item) {
  if (item.id === props.file.id && item.external_url) {
    external_url.value = item.external_url;
  }
}

onMounted(() => {
  eventBus.on('media-updated', mediaUpdated);
});

onUnmounted(() => {
  eventBus.off('media-updated', mediaUpdated);
});
</script>
