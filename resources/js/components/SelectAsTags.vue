<template>
  <div class="form__control--tags">
    <input type="hidden" :value="tags" :name="field.name" :id="'form--'+field.name">
    <Multiselect
      v-model="internalValue"
      :options="options"
      :multiple="true"
      :close-on-select="false"
      :clear-on-select="false"
      :preserve-search="true"
      label="name"
      track-by="id"
      :placeholder="placeholder"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Multiselect from 'vue-multiselect';

const props = defineProps(['field', 'values', 'choices', 'value']);
const emit = defineEmits(['input']);

const internalValue = ref([]);
const tags = ref('');
const options = ref([]);
const placeholder = ref('Select');

placeholder.value = `Select ${props.field.label}`;

if (props.choices) {
  props.choices.forEach((choice) => {
    options.value.push({ ...choice });
  });
}

// seed the initial selection from either `value` or `values`
const initialValue = props.value || props.values;
if (typeof initialValue !== 'undefined') {
  if (Array.isArray(initialValue)) {
    initialValue.forEach((tag) => {
      internalValue.value.push(tag);
    });
  } else {
    tags.value = initialValue;
  }
}

// keep the hidden input (the value the form submits) in sync with the selection
watch(
  internalValue,
  (val) => {
    tags.value = val.map((i) => i.name).join(',');
  },
  { deep: true }
);

watch(tags, (val) => {
  emit('input', val);
});

onMounted(() => {
  // if the initial value was a delimited string, hydrate the selection from it
  if (tags.value && !internalValue.value.length) {
    const names = tags.value.split(',').map((s) => s.trim()).filter(Boolean);
    internalValue.value = options.value.filter((o) => names.includes(o.name));
  }
});
</script>
