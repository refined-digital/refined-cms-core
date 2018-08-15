<template>

    <div class="form__horz-group">
      <template v-if="item.page_content_type_id === 1">
        <rd-rich-text :name="item.name" :id="'form--content-'+item.id" v-model="item.content" :content="item.content"></rd-rich-text>
      </template>

      <template v-if="item.page_content_type_id === 2">
        <textarea :id="'form--content-'+item.id" v-model="item.content" required="required" class="form__control"></textarea>
      </template>

      <template v-if="item.page_content_type_id === 3">
        <input type="text" :id="'form--content-'+item.id" v-model="item.content" required="required" class="form__control">
      </template>

      <template v-if="item.page_content_type_id === 4">
        <rd-image v-model="item.content" :value="item.content"></rd-image>
      </template>

      <template v-if="item.page_content_type_id === 5">
        <rd-file v-model="item.content" :value="item.content"></rd-file>
      </template>

      <div class="form__note" v-if="item.note" v-html="item.note"></div>
    </div>

</template>

<script>

  export default {

    props: [ 'item' ],

    data() {
      return {

      }
    },

  }
</script>