<template>
  <div class="form__control--options" v-if="item.type === 'repeatable' || (typeof item.page_content_type_id !== 'undefined' && item.page_content_type_id == 9)">
    <rd-repeatable-rows
      :rows="data"
      :add-label="`Add ${heading && heading !== 'Content' ? heading.toLowerCase().replace(/s$/, '') : 'row'}`"
      @add="addRepeatable()"
      @remove="removeRepeatable($event.row, $event.index)"
    >
      <template #row="{ row }">
        <template v-for="(cell, cellKey, index) of fields" :key="`field_${row[cell.field]?.id || index}`">
          <div class="repeatable-rows__field" v-if="row[cell.field]" v-show="row[cell.field].show">
            <label class="form__label" :for="`form--content-${row[cell.field].id}`" v-if="!cell.hide_label">{{ cell.name }}</label>
            <rd-content-editor-field :item="getItem(row, cell)" :options="cell"></rd-content-editor-field>
          </div>
        </template>
      </template>
    </rd-repeatable-rows>
  </div>
</template>

<script setup>
  import { inject, onMounted, onUnmounted } from 'vue';
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
