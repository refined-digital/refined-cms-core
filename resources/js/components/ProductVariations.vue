<template>
  <div class="form__control--options">
    <div class="form__row">
      <div class="form__label">Select your Variation Types</div>
      <Multiselect
        v-model="selectedTypes"
        :options="types"
        :multiple="true"
        :close-on-select="false"
        label="name"
        track-by="id"
        placeholder="Select Variation Types"
      />
    </div>

    <input type="hidden" :name="name" v-model="values"/>
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">Content</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable(item)"></i></th>
          </tr>
        </thead>
        <tbody v-sortable-repeatable-table>
          <tr v-for="(item, index) in items" class="form__control--options-row" :data-index="index">
            <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="items.length > 1"></i></td>
            <td class="data-table__cell">
              <template v-for="(cell, cellKey) of item" :key="`hidden-${cellKey}`">
                <input v-if="cell.hide_field" type="hidden" v-model="cell.content"/>
              </template>
              <div class="data-table__repeatable" :class="`data-table__repeatable--${name}`">
                <template v-for="(cell, cellKey) of item" :key="`field-${cellKey}`">
                  <div
                    v-if="!cell.hide_field && cell.field !== 'price' && cell.field !== 'sale_price'"
                    class="data-table__cell--repeatable"
                    :class="[
                      `data-table__cell--repeatable-${cell.field}`,
                      { 'data-table__cell--repeatable-has-error' : errors.includes(item) }
                    ]"
                  >
                    <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                    <rd-content-editor-field :item="cell"></rd-content-editor-field>
                  </div>
                </template>
                <div class="data-table__cell--repeatable data-table__cell--repeatable-break"></div>
                <template v-for="(cell, cellKey) of item" :key="`price-${cellKey}`">
                  <div
                    v-if="!cell.hide_field && (cell.field === 'price' || cell.field === 'sale_price')"
                    class="data-table__cell--repeatable"
                    :class="`data-table__cell--repeatable-${cell.field}`"
                  >
                    <label class="form__label" v-if="!cell.hide_label">{{ cell.name }}</label>
                    <rd-content-editor-field :item="cell"></rd-content-editor-field>
                  </div>
                </template>

                <div class="form__note form__note--error" v-if="errors.includes(item)">You have used this combination already</div>
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
import Multiselect from 'vue-multiselect';
import swal from 'sweetalert';
import eventBus from '../eventBus';

const props = defineProps(['item', 'name', 'value', 'variations', 'types', 'field', 'statuses']);
const emit = defineEmits(['input']);

const items = ref([]);
const values = ref([]);
const variationTypes = ref([]);
const selectedTypes = ref([]);
const errors = ref([]);

if (typeof props.value.variationTypes !== 'undefined') {
  variationTypes.value = props.value.variationTypes;
  // hydrate the multiselect from the saved variation types
  if (Array.isArray(variationTypes.value)) {
    selectedTypes.value = [...variationTypes.value];
  }
}

if (typeof props.value.items !== 'undefined') {
  items.value = props.value.items;
}

function formatType(type) {
  return {
    content: 0,
    field: `type_${type.id}`,
    name: `${type.name}${type.display_name ? `(${type.display_name})` : ''}`,
    page_content_type_id: 6,
    options: type.values.map((v) => ({ value: v.id, label: v.name })),
  };
}

function formatItem() {
  const item = {};
  if (variationTypes.value.length) {
    variationTypes.value.forEach((type) => {
      item[`type_${type.id}`] = formatType(type);
    });
  }

  item.price = { content: '', field: 'price', name: 'Price', page_content_type_id: 8 };
  item.sale_price = { content: '', field: 'sale_price', name: 'Sale Price', page_content_type_id: 8 };
  item.product_status_id = {
    content: '',
    field: 'product_status_id',
    name: 'Product Status Id',
    page_content_type_id: 6,
    options: [...props.statuses],
  };

  return item;
}

function addRepeatable() {
  items.value.push(formatItem());
}

function removeRepeatable(item, index) {
  swal({
    title: 'Are you sure?',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  }).then((value) => {
    if (value) {
      items.value.splice(index, 1);
    }
  });
}

function adjustVariationTypes() {
  items.value = items.value.map((item) => {
    const newItem = formatItem();
    for (const key in newItem) {
      if (typeof item[key] !== 'undefined') {
        newItem[key].content = item[key].content;
      }
    }
    return newItem;
  });
}

function checkForDuplicates() {
  const duplicateCheck = [];
  const dontCheck = ['price', 'sale_price'];
  const found = [];
  items.value.forEach((item) => {
    const check = [];
    for (const key in item) {
      if (!dontCheck.includes(key)) {
        check.push(`${key}:${item[key].content}`);
      }
    }
    const checkValue = check.join(',');
    if (duplicateCheck.includes(checkValue)) {
      found.push(item);
    } else {
      duplicateCheck.push(checkValue);
    }
  });

  errors.value = found;
}

// the selectize onChange resolved the chosen ids back to full variation objects;
// the multiselect already binds full objects, so mirror them into variationTypes
// and rebuild the rows.
watch(selectedTypes, (val) => {
  variationTypes.value = props.variations.filter((variation) => val.some((t) => t.id === variation.id));
  adjustVariationTypes();
});

watch(
  items,
  () => {
    const data = {
      variationTypes: variationTypes.value,
      items: items.value,
    };
    emit('input', data);
    values.value = JSON.stringify(data);
  },
  { deep: true }
);

function onSelectChanged() {
  checkForDuplicates();
}

onMounted(() => {
  eventBus.on('content-editor.select.changed', onSelectChanged);
});

onUnmounted(() => {
  eventBus.off('content-editor.select.changed', onSelectChanged);
});
</script>
