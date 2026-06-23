<template>
  <div class="colour-picker" ref="colourPicker">
    <div class="colour-picker__input">
      <input type="hidden" :name="field.name" :id="'form--'+field.name" :value="chosenValue">
    </div>
    <div class="colour-picker__box" :style="{ backgroundColor: chosenValue}">

    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, onUnmounted } from 'vue';
  import Picker from 'vanilla-picker/csp';
  import 'vanilla-picker/dist/vanilla-picker.csp.css';

  const props = defineProps(['field', 'value']);

  const colourPicker = ref(null);
  let picker = null;
  const chosenValue = ref(null);

  onMounted(() => {
    picker = new Picker({
      parent: colourPicker.value,
      alpha: false,
      popup: 'bottom',
      color: props.value,
      onChange(colour) {
        chosenValue.value = colour.hex;
      },
    });
    if (props.value) {
      chosenValue.value = props.value;
    }
  });

  onUnmounted(() => {
    if (picker) picker.destroy();
  });
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
