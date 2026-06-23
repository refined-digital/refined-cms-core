<template>
  <div class="form__control--email-tags" ref="row">
    <input type="hidden" :value="tags" :name="field.name" :id="'form--'+field.name">
    <Multiselect
      v-model="internalValue"
      :options="options"
      :multiple="true"
      :taggable="true"
      :close-on-select="false"
      :clear-on-select="false"
      :preserve-search="true"
      :custom-label="optionLabel"
      label="email"
      track-by="email"
      :placeholder="placeholder"
      tag-placeholder="Add this email"
      @tag="addEmail"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';

const REGEX_EMAIL = "([a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@"
  + '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

// `fields` (optional): email-type form fields offered as selectable chips. Each
// is stored as the raw token `field{id}` and shown by its friendly label; the
// backend swaps the token for the submitted value at send time. When omitted,
// the component behaves exactly as before (type literal emails only).
const props = defineProps(['field', 'value', 'modelValue', 'fields']);
const emit = defineEmits(['input', 'update:modelValue']);

const initialValue = props.value ?? props.modelValue;

const internalValue = ref([]);
const tags = ref('');

// preset dropdown options: one per email field (label shown, token stored)
const fieldOptions = (Array.isArray(props.fields) ? props.fields : []).map((f) => ({
  email: `field${f.id}`,
  label: f.name,
  isField: true,
}));
const options = ref([...fieldOptions]);

const placeholder = fieldOptions.length ? 'Add an email or pick a field' : 'Add an email';

// the chip / dropdown label: friendly name for field tokens, the address otherwise
function optionLabel(option) {
  return option.label || option.email;
}

function isEmail(input) {
  const regex = new RegExp(`^${REGEX_EMAIL}$`, 'i');
  return !!input.match(regex);
}

function addEmail(input) {
  const email = input.trim();
  if (!isEmail(email)) {
    return;
  }
  if (internalValue.value.some((i) => i.email === email)) {
    return;
  }
  const option = { email };
  options.value.push(option);
  internalValue.value.push(option);
}

// rebuild a stored token/email into a chip object, matching a known field
// option so its friendly label shows
function toChip(token) {
  return fieldOptions.find((o) => o.email === token) || { email: token };
}

// seed from the incoming value (comma-delimited string or array)
if (typeof initialValue === 'string' && initialValue) {
  internalValue.value = initialValue.split(',').map((s) => s.trim()).filter(Boolean).map(toChip);
} else if (Array.isArray(initialValue)) {
  internalValue.value = initialValue.map(toChip);
}

watch(
  internalValue,
  (val) => {
    tags.value = val.map((i) => i.email).join(',');
  },
  { deep: true, immediate: true }
);

watch(tags, (val) => {
  emit('input', val);
  emit('update:modelValue', val);
});
</script>
