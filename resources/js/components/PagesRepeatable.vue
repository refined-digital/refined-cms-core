<template>
  <div class="form__control--options" v-if="item.type === 'repeatable' || (typeof item.page_content_type_id !== 'undefined' && item.page_content_type_id == 9)">
    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th class="data-table__cell data-table__cell--sort">&nbsp;</th>
            <th class="data-table__cell">{{ heading }}</th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </thead>
        <draggable
          :list="data"
          handle=".data-table__cell--sort"
          tag="tbody"
          item-key="key"
        >
          <template #item="{ element: row, index }">
            <tr class="form__control--options-row" :data-index="index" :key="`${item.key}_${index}`">
              <td class="data-table__cell data-table__cell--sort"><i class="fa fa-sort" v-if="data.length > 1"></i></td>
              <td class="data-table__cell">
                <div class="data-table__repeatable" :class="`data-table__repeatable--${item.name}`">
                  <div
                    class="data-table__cell--repeatable"
                    :class="`data-table__cell--repeatable-${index}`"
                    v-for="(cell, cellKey, index) of fields"
                    v-show="row[cell.field].show"
                  >
                    <label class="form__label" :for="`form--content-${row[cell.field].id}`" v-if="!cell.hide_label">{{ cell.name }}</label>
                    <rd-content-editor-field :item="getItem(row, cell)" :options="cell" :key="`field_${row[cell.field].id}`"></rd-content-editor-field>
                  </div>
                </div>
              </td>
              <td class="data-table__cell data-table__cell--options-delete"><i class="fa fa-times" @click="removeRepeatable(row, index)"></i></td>
            </tr>
          </template>
        </draggable>
        <tfoot v-if="data && data.length > 0">
          <tr>
            <th class="data-table__cell data-table__cell--sort"></th>
            <th class="data-table__cell"></th>
            <th class="data-table__cell data-table__cell--options-plus"><i class="fa fa-plus" @click="addRepeatable()"></i></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup>
  import { inject, onMounted, onUnmounted } from 'vue';
  import draggable from 'vuedraggable';
  import { keyBy } from 'lodash';
  import swal from 'sweetalert';
  import eventBus from '../eventBus';

  const props = defineProps(['item', 'data', 'fields', 'heading']);

  // the nearest ancestor that provides addRepeatable (Pages or ContentBlocks)
  const parentAddRepeatable = inject('pages:addRepeatable');

  const fieldsByKey = keyBy(props.fields, 'field');

  function selectChanged(item) {
    props.data.forEach(row => {
      if (!item.options || (item.options && !row[item.options.field])) {
        return;
      }
      if (item.item.id === row[item.options.field].id) {
        const keyCheck = `${item.options.field}:${item.item.content}`;
        for (const field in row) {
          const f = row[field];
          const fDetails = fieldsByKey[field];
          if (fDetails.showOn) {
            f.show = keyCheck === fDetails.showOn
          }
        }
      }
    })
  }

  function addRepeatable() {
    parentAddRepeatable(props.data, props.fields);
  }

  function removeRepeatable(item, index) {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    })
    .then((value) => {
      if (value) {
        props.data.splice(index, 1);
      }
    });
  }

  function getItem(row, cell) {
    const item = row[cell.field];

    // attach the width and height to the image item;
    if (item.page_content_type_id === 4 && (cell.width || cell.height)) {
      item.width = cell.width || null;
      item.height = cell.height || null;
    }

    return item;
  }

  onMounted(() => {
    eventBus.on('content-editor.select.changed', selectChanged);
  });

  onUnmounted(() => {
    eventBus.off('content-editor.select.changed', selectChanged);
  });
</script>
