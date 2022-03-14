<template>
  <div class="colour-picker" ref="colourPicker">
    <div class="colour-picker__input">
      <input type="hidden" :name="field.name" :id="'form--'+field.name" :value="chosenValue">
    </div>
    <div class="colour-picker__box" :style="{ backgroundColor: chosenValue}">

    </div>
  </div>
</template>

<script>
  import Picker from 'vanilla-picker/csp';
  import 'vanilla-picker/dist/vanilla-picker.csp.css';

  export default {

    props: [ 'field', 'value' ],

    data() {
      return {
        picker: null,
        chosenValue: null,
      }
    },

    mounted() {
      const $this = this;
      this.picker = new Picker({
        parent: this.$refs['colourPicker'],
        alpha: false,
        popup: 'bottom',
        color: this.value,
        onChange(colour) {
          $this.chosenValue = colour.hex
        }
      })
      if (this.value) {
        this.chosenValue = this.value;
      }

    },

  }
</script>

<style lang="scss">
  .colour-picker {
    &__box {
      width: 40px;
      height: 40px;
      border: 1px solid #000;
      background: transparent;
    }
  }
</style>
