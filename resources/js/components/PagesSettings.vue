<template>
  <div class="settings">
     <div class="form form__horz">
      <div class="form__row form__row--inline-label" v-for="(item, index) in settings" :key="item.name">
        <label class="form__label" :for="'form--content-'+item.id">{{ item.name }}</label>
        <div class="form__horz-group">
          <rd-content-editor-field :item="item"></rd-content-editor-field>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
export default {

  props: ['settings'],

}
</script>

<style>
.setting-controls {
  display: flex;
  justify-content: space-between;
  padding-right: 10px;
}
</style>
