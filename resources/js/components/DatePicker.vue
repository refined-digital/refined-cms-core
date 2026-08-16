<template>
    <div>
      <label :for="'form--'+field.name" class="form__label">{{ field.label }}</label>
      <input type="date" class="form__control" :name="field.name" required :id="'form--'+field.name" autocomplete="off">
    </div>
</template>

<script setup>
import { reactive, onMounted, onUnmounted } from 'vue';
import Flatpickr from 'flatpickr';
import { format, parse, parseISO, isValid } from 'date-fns';
import 'flatpickr/dist/themes/material_blue.css';

const props = defineProps(['field', 'value']);
const emit = defineEmits(['update:modelValue']);

const dateFormat = 'yyyy-MM-dd';
let datePicker = null;

const config = reactive({
  altInput: true,
  altFormat: 'F j, Y',
  dateFormat: 'Y-m-d',
  enableTime: false,
  defaultDate: '',
  // the hidden form input keeps working as before; the emits let the
  // workspace bind the value with v-model. onReady propagates the default
  // date so a brand new record saves with a value
  onChange: (dates, dateStr) => emit('update:modelValue', dateStr),
  onReady: (dates, dateStr) => emit('update:modelValue', dateStr),
});

// parse leniently like moment did: try the exact format, then fall back to ISO
function toDate(value) {
  let d = parse(value, dateFormat, new Date());
  if (!isValid(d)) {
    d = parseISO(value);
  }
  return isValid(d) ? d : new Date();
}

onMounted(() => {
  config.defaultDate = props.value
    ? format(toDate(props.value), dateFormat)
    : format(new Date(), dateFormat);
  datePicker = new Flatpickr('#form--' + props.field.name, config);
});

onUnmounted(() => {
  // a selector-based Flatpickr returns an array of instances
  const instances = Array.isArray(datePicker) ? datePicker : [datePicker];
  instances.forEach((instance) => instance && instance.destroy());
});
</script>
