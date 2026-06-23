<template>
  <div class="settings">
     <div class="form form__horz">
      <div
          class="form__row form__row--inline-label"
          v-for="(item, index) in settings"
          :key="item.name"
      >
        <template v-if="show(item)">
          <label class="form__label" :for="'form--content-'+item.id">{{ item.name }}</label>
          <div class="form__horz-group">
            <rd-content-editor-field :item="item"></rd-content-editor-field>
          </div>
        </template>
      </div>
    </div>
  </div>

</template>

<script setup>
const props = defineProps(['settings', 'page']);

function show(item) {
  if (typeof item.show_on_page !== 'undefined') {
    return item.show_on_page.includes(props.page.id)
  }

  if (typeof item.show_in_holder !== 'undefined') {
    return item.show_in_holder.includes(props.page.page_holder_id)
  }

  return true;
}
</script>
