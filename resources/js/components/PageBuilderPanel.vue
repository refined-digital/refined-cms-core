<template>
  <div class="page-builder__panel">
    <!-- list view: compact rows, drag to reorder, click to edit -->
    <div class="page-builder__list" v-show="selectedIndex === null">
      <button class="button button--green page-builder__add" @click.prevent.stop="$emit('open-block-modal')">
        <i class="fa fa-plus"></i> Add content block
      </button>

      <p class="page-builder__empty" v-if="!blocks.length">
        No content blocks yet &mdash; add one to get started.
      </p>

      <draggable
        v-model="blocks"
        item-key="id"
        handle=".fa-sort"
        animation="150"
        class="page-builder__blocks"
        @end="onReorder"
      >
        <template #item="{ element, index }">
          <div
            class="page-builder__block-row"
            :data-block-index="index"
            @click="select(index)"
            @mouseenter="emit('block-hover', index)"
            @mouseleave="emit('block-hover', null)"
          >
            <i class="fa fa-sort" @click.stop></i>
            <span class="page-builder__block-name">{{ element.name }}</span>
            <i class="fa fa-times" @click.stop="removeBlock(index)"></i>
          </div>
        </template>
      </draggable>
    </div>

    <!-- block view: only the selected block's fields, with a way back -->
    <div class="page-builder__block-editor" v-if="selectedBlock">
      <header class="page-builder__block-editor-header">
        <button class="page-builder__back" @click="select(null)"><i class="fa fa-chevron-left"></i></button>
        <h4>{{ selectedBlock.name }}</h4>
        <i class="fa fa-times page-builder__block-remove" @click="removeBlock(selectedIndex)"></i>
      </header>

      <div class="form">
        <div
          class="page-builder__form-row form__row"
          v-for="field of selectedBlock.fields"
          :key="`${selectedBlock.id}_${field.id}`"
          v-show="canShow(field, selectedBlock)"
          @input="onFieldInput(selectedIndex, field, $event)"
        >
          <label :for="`form--content-${field.id}`" class="form__label">{{ field.name }}</label>
          <rd-content-editor-field :item="field"></rd-content-editor-field>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, provide } from 'vue';
import swal from 'sweetalert';
import draggable from 'vuedraggable';

import { usePagesImageNote } from '../composables/usePagesImageNote';
import { usePagesRepeatable } from '../composables/usePagesRepeatable';
import { formatContent, canShow, phpFieldKey } from '../composables/useContentBlocks';

const props = defineProps(['page', 'content', 'config', 'selectedIndex']);
const emit = defineEmits(['update:selectedIndex', 'field-input', 'block-hover', 'open-block-modal']);

// page.content is the single source of truth - the same array savePage() posts
const blocks = computed({
  get: () => props.page.content || [],
  set: (value) => { props.page.content = value; },
});

const selectedBlock = computed(() =>
  props.selectedIndex !== null ? blocks.value[props.selectedIndex] ?? null : null
);

const pageRef = computed(() => props.page);
const { getImageNote } = usePagesImageNote(pageRef);
const { addRepeatable } = usePagesRepeatable(pageRef, { getImageNote });

// child PagesRepeatable components inject the nearest addRepeatable
provide('pages:addRepeatable', addRepeatable);

// STATIC, PLAIN and NUMBER fields are cheap enough to echo into the preview
// per keystroke; everything else waits for the debounced server render
const echoTypes = [2, 3, 8];

function onFieldInput(index, field, event) {
  const tag = event.target.tagName;
  if (!echoTypes.includes(field.page_content_type_id) || (tag !== 'INPUT' && tag !== 'TEXTAREA')) {
    return;
  }

  emit('field-input', {
    index,
    field: phpFieldKey(field.name),
    value: event.target.value,
  });
}

function select(index) {
  emit('update:selectedIndex', index);
}

function removeBlock(index) {
  swal({
    title: 'Are you sure?',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  }).then(value => {
    if (value) {
      const next = [...blocks.value];
      next.splice(index, 1);
      props.page.content = next;

      if (props.selectedIndex !== null) {
        if (props.selectedIndex === index) {
          emit('update:selectedIndex', null);
        } else if (props.selectedIndex > index) {
          emit('update:selectedIndex', props.selectedIndex - 1);
        }
      }
    }
  });
}

function onReorder({ oldIndex, newIndex }) {
  // keep the selection on the same block after a drag
  const selected = props.selectedIndex;
  if (selected === null || oldIndex === newIndex) {
    return;
  }

  if (selected === oldIndex) {
    emit('update:selectedIndex', newIndex);
  } else if (oldIndex < selected && selected <= newIndex) {
    emit('update:selectedIndex', selected - 1);
  } else if (newIndex <= selected && selected < oldIndex) {
    emit('update:selectedIndex', selected + 1);
  }
}

onMounted(() => {
  props.page.content = blocks.value.map(item => formatContent(item));
});
</script>
