<template>
    <div class="form__horz-group">
      <template v-if="item.page_content_type_id === 1">
        <rd-rich-text :id="'form--content-'+item.id" v-model="item.content" :content="item.content" :key="item.key"></rd-rich-text>
      </template>

      <template v-if="item.page_content_type_id === 2">
        <textarea :id="'form--content-'+item.id" v-model="item.content" required="required" class="form__control"></textarea>
      </template>

      <template v-if="item.page_content_type_id === 3">
        <input
            :type="item.fieldType || 'text'"
            :id="'form--content-'+item.id"
            v-model="item.content"
            required="required"
            class="form__control"
            :step="item.step || 'any'"
            :min="item.min || null"
            :max="item.max || null"
        >
      </template>

      <template v-if="item.page_content_type_id === 4">
        <rd-image
            v-model="item.content"
            :value="item.content"
            :key="item._key || item.id"
            :width="item.width"
            :height="item.height"
        ></rd-image>
      </template>

      <template v-if="item.page_content_type_id === 5">
        <rd-file v-model="item.content" :value="item.content" :id="item.id" :name="item.name"></rd-file>
      </template>

      <template v-if="item.page_content_type_id === 6 && item.options">
        <select v-model="item.content" required="required" class="form__control" :change="selectChanged(item, options)">
          <option :value="opt.value" v-for="opt in item.options">{{ opt.label }}</option>
        </select>
      </template>

      <template v-if="item.page_content_type_id === 7">
        <rd-link v-model="item.content" :value="item.content"></rd-link>
      </template>

      <template v-if="item.page_content_type_id === 8">
        <input type="number" v-model="item.content" inputmode="decimal" required="required" class="form__control">
      </template>

      <template v-if="item.page_content_type_id === 9">
        <rd-pages-repeatable
          :item="item"
          :data="item.content"
          :fields="item.fields || options.fields"
          :key="item.key"
          :heading="item.heading || 'Content'"
        ></rd-pages-repeatable>
      </template>

      <template v-if="item.page_content_type_id === 10">
        <input type="password" v-model="item.content" class="form__control--color">
      </template>

      <template v-if="item.page_content_type_id === 11">
        <input type="color" v-model="item.content" class="form__control--color">
      </template>

      <div class="form__note" v-if="item.note && item.page_content_type_id !== 9" v-html="item.note"></div>
    </div>

</template>

<script>

  export default {

    props: [ 'item', 'options' ],

    created() {
      if (this.item.page_content_type_id == 6 && this.item.content == '' && this.item.options && this.item.options.length > 0) {
        this.item.content = this.item.options[0].value;
      }
    },

    methods: {
      selectChanged(item, options) {
        eventBus.$emit('content-editor.select.changed', { item, options });
      }
    }

  }
</script>
