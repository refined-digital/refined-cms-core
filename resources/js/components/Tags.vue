<template>
  <div class="form__control--tags">
    <input type="hidden" :value="tags" :name="fieldName" :id="'form--'+field.name">
    <Multiselect
      v-model="value"
      :options="options"
      :multiple="true"
      :taggable="!dontAllowCreate"
      :close-on-select="false"
      :clear-on-select="false"
      :preserve-search="true"
      label="name"
      track-by="id"
      :placeholder="placeholder"
      @tag="addTag"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import Multiselect from 'vue-multiselect';

const props = defineProps([
  'field', 'type', 'values', 'choices', 'dontAllowCreate',
  'valueField', 'asSelect', 'valueType',
]);
const emit = defineEmits(['input', 'update:modelValue']);

const delimiter = '|';
const setType = props.type || 'tags';
const value = ref([]);
const tags = ref('');
const options = ref([]);
const placeholder = ref('Select Tags');
const fieldName = ref('modelTags[]');

placeholder.value = `Select ${props.field.label ? props.field.label : setType.replace(/-/gi, ' ')}`;
fieldName.value = `modelTags[${props.field.name.replace('data__', '')}]`;
if (props.asSelect) {
  fieldName.value = props.field.name;
}

if (props.choices) {
  props.choices.forEach((choice) => {
    options.value.push({ ...choice });
  });
}

// seed the initial selection
if (typeof props.values !== 'undefined') {
  if (Array.isArray(props.values[setType])) {
    props.values[setType].forEach((tag) => {
      value.value.push(tag);
    });
  } else if (props.asSelect) {
    const valuesAsArray = props.values.split(delimiter).filter((item) => !!item).map((id) => parseInt(id, 10));
    value.value = options.value.filter((option) => valuesAsArray.includes(option.id) || valuesAsArray.includes(parseInt(option.id, 10)));
  } else {
    tags.value = props.values[setType];
  }
}

function addTag(input) {
  const tag = {
    id: `t-${new Date().getTime()}`,
    name: input,
  };
  options.value.push(tag);
  value.value.push(tag);
}

// get the available tags
axios.get(`${window.siteUrl}/refined/tags/get-all-tags`).then((r) => {
  if (r.status === 200 && r.data.length) {
    r.data.forEach((d) => {
      if (d.type === setType) {
        const type = { id: d.id, name: d.name };
        if (options.value.indexOf(type) < 0 && !props.choices) {
          options.value.push(type);
        }
      }
    });
  }
});

watch(
  value,
  (val) => {
    const v = [];
    val.forEach((i) => {
      if (props.valueType === 'id') {
        v.push(i.id);
      } else {
        v.push(i.name);
      }
    });
    // the hidden input (and form submission) used the pipe delimiter
    tags.value = v.join(delimiter);
    emit('input', tags.value);
    emit('update:modelValue', tags.value);
  },
  { deep: true }
);
</script>
