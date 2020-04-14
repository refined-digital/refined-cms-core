<template>
    <figure>
      <span class="media__file-thumb">
        <img src="/vendor/refinedcms/img/ui/media-thumb.png">
        <span
          class="media__file-thumb-image"
          v-if="file.type == 'Image'" :style="{ backgroundImage: 'url('+ file.link.thumb +')' }"
        ></span>
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

    props: [ 'file' ],

  }
</script>
