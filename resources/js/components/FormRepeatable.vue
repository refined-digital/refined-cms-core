<template>
  <div class="form__control--options" v-if="item.type == 'repeatable'">
    <input type="hidden" :name="name" v-model="values"/>

    <rd-repeatable-rows
      :rows="items"
      :add-label="`Add ${item.repeatableName || 'row'}`"
      @add="addRepeatable(item)"
      @remove="removeRepeatable($event.row, $event.index)"
    >
      <template #row="{ row }">
        <template v-for="(cell, cellKey) of row" :key="`hidden-${cellKey}`">
          <input
            type="hidden"
            v-model="cell.content"
            v-if="cell.hide_field && cellKey !== '_key'"
          />
        </template>
        <template v-for="(cell, cellKey) of row" :key="cell._key">
          <div class="repeatable-rows__field" v-if="cellKey !== '_key' && !cell.hide_field">
            <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
            <rd-content-editor-field :item="cell"></rd-content-editor-field>
          </div>
        </template>
      </template>
    </rd-repeatable-rows>
  </div>
</template>

<script setup>
  import { ref, watch } from 'vue';
  import swal from 'sweetalert';

  const props = defineProps(['item', 'name', 'value']);
  const emit = defineEmits(['input', 'update:modelValue']);

  const items = ref([]);
  const values = ref([]);

  function uid() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }

  function addRepeatable(item) {
    let data = {};
    item.fields.forEach(field => {
      let f = JSON.parse(JSON.stringify(field));
      f.content = '';
      f._key = `repeatable_${f.field}_${uid()}`;
      data[f.field] = f;
    });
    data._key = `repeatable_${uid()}`;

    items.value.push(data);

  }

  function removeRepeatable(item, index) {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    }).then(value => {
      if (value) {
        items.value.splice(index, 1);
      }
    });
  }

  watch(items, () => {
    emit('input', items.value);
    emit('update:modelValue', items.value);
    values.value = JSON.stringify(items.value);
  }, { deep: true });

  // created
  let value = props.value;
  // convert the value to an object, if it is a string
  if (value && typeof value === 'string') {
    value = JSON.parse(value);
  }
  if (value && value.length > 0) {
    items.value = [];
    value.forEach(item => {
      items.value.push(item);
    });
  }

  items.value = items.value.map(item => {
    for (const key in item) {
      if (key === '_key') {
        continue;
      }

      item[key]._key = `repeatable_${key}_${uid()}`
    }

    item._key = `repeatable_${uid()}`
    return item;
  })
</script>
