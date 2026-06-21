<template>
  <div class="form__control--options" v-if="item.type == 'repeatable'">
    <input type="hidden" :name="name" v-model="values"/>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">{{ item.repeatableName || 'Content' }}</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </thead>
        <tbody v-sortable-repeatable-table :data-id="id">
          <tr v-for="(item, index) in items" class="form__control--options-row" :data-index="index" :key="item._key">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="items.length > 1"></i></td>
            <td class="data-table__cell">
              <template v-for="(cell, cellKey, index) of item" :key="`hidden-${cellKey}`">
                <input
                  type="hidden"
                  v-model="cell.content"
                  v-if="cell.hide_field && cellKey !== '_key'"
                />
              </template>
              <div class="data-table__repeatable" :class="`data-table__repeatable--${name}`">
                <template v-for="(cell, cellKey, childIndex) of item" :key="cell._key">
                  <div
                    class="data-table__cell--repeatable"
                    :class="`data-table__cell--repeatable-${childIndex}`"
                    v-if="!cell.hide_field"
                  >
                    <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                    <rd-content-editor-field :item="cell"></rd-content-editor-field>
                  </div>
                </template>
              </div>
            </td>
            <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(item, index)"></i></td>
          </tr>
        </tbody>
        <tfoot v-if="items && items.length > 1">
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell"></th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
  import { ref, watch, onMounted, onUnmounted } from 'vue';
  import _ from 'lodash';
  import eventBus from '../eventBus';

  const props = defineProps(['item', 'name', 'value']);
  const emit = defineEmits(['input']);

  const items = ref([]);
  const values = ref([]);
  const id = ref(Date.now());

  function uid() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
  }

  function addRepeatable(item) {
    let data = {};
    item.fields.forEach(field => {
      let f = JSON.parse(JSON.stringify(field));
      f.content = '';
      data[f.field] = f;
    });

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

  function onDragend(data) {
    if (data.id === id.value.toString()) {
      const orderedArray = [];
      data.indexes.forEach(index => {
        orderedArray.push(items.value[index]);
      })

      orderedArray.forEach((item, index) => {
        items.value[index] = item;
      })
    }
  }

  watch(items, () => {
    emit('input', items.value);
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

  onMounted(() => {
    eventBus.on('sortable-repeatable-table.dragend', onDragend);
  });

  onUnmounted(() => {
    eventBus.off('sortable-repeatable-table.dragend', onDragend);
  });
</script>
