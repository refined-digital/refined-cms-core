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
      label="email"
      track-by="email"
      :placeholder="'Add an email'"
      tag-placeholder="Add this email"
      @tag="addEmail"
    />
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Multiselect from 'vue-multiselect';

const REGEX_EMAIL = "([a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@"
  + '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

const props = defineProps(['field', 'value']);
const emit = defineEmits(['input']);

const internalValue = ref([]);
const tags = ref('');
const options = ref([]);

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

// seed from the incoming value (comma-delimited string or array)
if (typeof props.value === 'string' && props.value) {
  const emails = props.value.split(',').map((s) => s.trim()).filter(Boolean);
  internalValue.value = emails.map((email) => ({ email }));
  options.value = emails.map((email) => ({ email }));
} else if (Array.isArray(props.value)) {
  internalValue.value = props.value.map((email) => ({ email }));
  options.value = props.value.map((email) => ({ email }));
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
});

onMounted(() => {});
</script>
