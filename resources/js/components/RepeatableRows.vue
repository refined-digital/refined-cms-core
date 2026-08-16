<template>
  <div class="repeatable-rows">
    <p class="repeatable-rows__empty" v-if="!rows || !rows.length">
      Nothing here yet.
    </p>

    <draggable
      :list="rows"
      :item-key="rowKey"
      handle=".repeatable-rows__handle"
      animation="150"
      @end="open = {}; $emit('reorder', $event)"
    >
      <template #item="{ element: row, index }">
        <div class="repeatable-rows__row" :class="{ 'is-open': isOpen(index) }">
          <div class="repeatable-rows__bar" @click="toggle(index)">
            <i class="fa fa-sort repeatable-rows__handle" v-if="rows.length > 1" @click.stop></i>
            <i class="fa fa-chevron-right repeatable-rows__chevron"></i>
            <span class="repeatable-rows__summary">{{ summaryFor(row, index) }}</span>
            <i class="fa fa-times repeatable-rows__remove" @click.stop="$emit('remove', { row, index })"></i>
          </div>
          <!-- v-show keeps editors (tiptap etc.) mounted while collapsed -->
          <div class="repeatable-rows__body" v-show="isOpen(index)">
            <slot name="row" :row="row" :index="index"></slot>
          </div>
        </div>
      </template>
    </draggable>

    <button class="button button--green repeatable-rows__add" @click.prevent.stop="$emit('add')">
      <i class="fa fa-plus"></i> {{ addLabel || 'Add row' }}
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import draggable from 'vuedraggable';

// shared collapsible-card presentation for every repeatable (form-builder
// model repeatables, content-block repeatables, the images variant). the
// consumer owns the data and add/remove logic; this owns the look
const props = defineProps(['rows', 'addLabel']);
defineEmits(['add', 'remove', 'reorder']);

const open = ref({});

// a single row starts open; new rows are opened by the length watcher below
if ((props.rows || []).length === 1) {
  open.value[0] = true;
}

let knownLength = (props.rows || []).length;

watch(
  () => (props.rows || []).length,
  (length) => {
    if (length > knownLength) {
      // a freshly added row lands at the end - open it for editing
      open.value = { [length - 1]: true };
    } else {
      // removal shifts indexes; collapse rather than highlight the wrong row
      open.value = {};
    }
    knownLength = length;
  }
);

function isOpen(index) {
  return !!open.value[index];
}

function toggle(index) {
  open.value[index] = !open.value[index];
}

// stable identity even for rows without their own keys
const keyMap = new WeakMap();
let uid = 0;

function rowKey(row) {
  if (row === null || typeof row !== 'object') {
    return String(row);
  }
  if (row._key || row.key) {
    return row._key || row.key;
  }
  if (!keyMap.has(row)) {
    keyMap.set(row, ++uid);
  }
  return `row-${keyMap.get(row)}`;
}

function summaryFor(row, index) {
  // first non-empty text-ish cell wins
  for (const key in row) {
    if (key === '_key' || key === 'key') continue;
    const cell = row[key];
    const content = typeof cell === 'object' && cell !== null ? cell.content : cell;

    if (typeof content === 'string' && content.trim()) {
      const text = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
      if (text) {
        return text.length > 60 ? text.slice(0, 60) + '…' : text;
      }
    }
  }

  return `Item ${index + 1}`;
}
</script>
