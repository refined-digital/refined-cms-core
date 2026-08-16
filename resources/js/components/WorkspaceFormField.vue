<template>
  <div class="pages-workspace__field form__row">
    <label class="form__label" v-if="showLabel">{{ field.label }}</label>

    <input
      v-if="widget === 'input'"
      :type="inputType"
      class="form__control"
      v-model="model"
    >

    <textarea v-else-if="widget === 'textarea'" class="form__control" v-model="model"></textarea>

    <rd-rich-text
      v-else-if="widget === 'richtext'"
      :content="model"
      :name="field.name"
      @update:model-value="model = $event"
    ></rd-rich-text>

    <select v-else-if="widget === 'select'" class="form__control" v-model="model">
      <option v-for="opt in selectOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>

    <rd-date-time-picker
      v-else-if="widget === 'datetime'"
      :field="{ name: field.name, label: field.label }"
      :value="model"
      @update:model-value="model = $event"
    ></rd-date-time-picker>

    <rd-date-picker
      v-else-if="widget === 'date'"
      :field="{ name: field.name, label: field.label }"
      :value="model"
      @update:model-value="model = $event"
    ></rd-date-picker>

    <rd-image v-else-if="widget === 'image'" v-model="model" :value="model"></rd-image>

    <rd-file v-else-if="widget === 'file'" v-model="model" :value="model" :id="field.name" :name="field.label"></rd-file>

    <rd-link v-else-if="widget === 'link'" v-model="model" :value="model"></rd-link>

    <rd-colour-picker v-else-if="widget === 'colour'" v-model="model"></rd-colour-picker>

    <rd-tags
      v-else-if="widget === 'tags'"
      :field="{ name: field.name, label: field.label }"
      :type="field.tagType || 'tags'"
      :values="tagValues"
      @update:model-value="model = $event"
    ></rd-tags>

    <rd-form-repeatable
      v-else-if="widget === 'repeatable'"
      :item="field"
      :name="field.name"
      :value="model"
      @update:model-value="model = $event"
    ></rd-form-repeatable>

    <input v-else-if="widget === 'readonly'" type="text" class="form__control" :value="model" readonly>

    <div class="form__note" v-if="field.pre_note" v-html="field.pre_note"></div>
    <div class="form__note" v-if="field.note" v-html="field.note"></div>
    <div class="form__note" v-if="field.imageNote" v-html="field.imageNote"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

// adapter from the form-builder field schema to the admin's field components.
// `values` is the workspace's reactive value map, keyed by posted field name
const props = defineProps(['field', 'values', 'tagValues']);

const model = computed({
  get: () => props.values[props.field.name],
  set: (value) => { props.values[props.field.name] = value; },
});

const widget = computed(() => {
  const type = props.field.type || 'text';

  switch (type) {
    case 'textarea': return 'textarea';
    case 'richtext': return 'richtext';
    case 'select':
    case 'userLevels':
    case 'tagType': return 'select';
    case 'datetime': return 'datetime';
    case 'date': return 'date';
    case 'image': return 'image';
    case 'file': return 'file';
    case 'link': return 'link';
    case 'colour-picker': return 'colour';
    case 'tags': return 'tags';
    case 'repeatable': return 'repeatable';
    case 'url': return 'readonly';
    case 'readonly': return 'readonly';
    default: return 'input';
  }
});

const inputType = computed(() => {
  const type = props.field.type || 'text';
  return ['email', 'number', 'password', 'url'].includes(type) ? type : 'text';
});

// the date pickers render their own label
const showLabel = computed(() =>
  !props.field.hideLabel && !['datetime', 'date'].includes(widget.value)
);

// options arrive either as [{label, value}] arrays or as php assoc maps
// json-decoded to objects ({"1": "Yes"})
const selectOptions = computed(() => {
  const options = props.field.options;

  if (Array.isArray(options)) {
    return options.map(opt =>
      typeof opt === 'object' ? opt : { label: opt, value: opt }
    );
  }

  if (options && typeof options === 'object') {
    return Object.entries(options).map(([value, label]) => ({
      label,
      value: isNaN(Number(value)) ? value : Number(value),
    }));
  }

  return [];
});
</script>
