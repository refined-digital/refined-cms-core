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

<script>

  export default {

    props: [ 'file', 'siteUrl' ],

    created() {
      eventBus.$on('media-updated', this.mediaUpdated);
    },

    data() {
      return {
        external_url: undefined,
      }
    },

    computed: {
      fileUrl() {
        if (this.file.external_url) {
          return this.file.external_url;
        }

        if (this.external_url) {
          return this.external_url;
        }

        return this.file.link.thumb;
      }
    },

    methods: {
      mediaUpdated(item) {
        if (item.id === this.file.id && item.external_url) {
          this.external_url = item.external_url;
        }
      }
    }

  }
</script>
