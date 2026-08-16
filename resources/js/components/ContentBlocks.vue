<template>
  <div>
    <div class="content-editor__header" v-if="content">
      <div class="content-editor__buttons">
        <template v-for="item of content">
          <button class="button button--small button--green" :class="{ 'button--has-note' : item.description }" @click.prevent.stop="loadContentBlock(item)">
              <span class="button__text">
                {{ item.name }}
              </span>
              <span class="content-editor__button-note" v-if="item.description">
                <span class="fa fa-question-circle"></span>
                <span class="content-editor__button--content" v-html="item.description"></span>
              </span>
          </button>
        </template>
      </div>
    </div><!-- / content editor controls -->

    <div class="content-editor__data form form__horz" v-sortable-content-item>
      <div
        class="content-editor__item"
        :class="{ 'open' : openBlocks[content.id]}"
        v-for="(content, index) of data"
        :data-index="index"
        :data-id="content.id"
        :key="content.id"
      >
        <div class="content-editor__item-header">
          <header>
            <div class="content-editor__item-toggle" @click="toggleContentBlockContent(content.id)">
              <i class="fa fa-chevron-right"></i>
              <i class="fa fa-chevron-down"></i>
            </div>
            <h5>
              <span @click="toggleContentBlockContent(content.id)">
                {{ content.name }}
              </span>
              <small v-if="canShowAnchors" class="content-editor__anchor">
                Anchor: <span @click="selectAndCopy">#{{ anchorPrefix+index }}</span>
              </small>
            </h5>
          </header>
          <aside class="content-editor__item-sort">
            <i class="fa fa-sort" v-if="page[name] && page[name].length > 1"></i>
            <i class="fa fa-times" @click="removeContentBlock(index)"></i>
          </aside>
        </div>
        <div class="content-editor__item-content" v-show="openBlocks[content.id]">
          <div class="form form__horz">
            <div
              class="content-editor__form-row form__row form__row--inline-label"
              v-for="field of content.fields"
              v-show="canShow(field, content)"
            >
              <label :for="`form--content-${field.id}`" class="form__label">{{field.name}}</label>
              <rd-content-editor-field :item="field" :key="`${content.id}_${field.id}`"></rd-content-editor-field>
            </div>
          </div>
        </div>
      </div>
    </div>

    <textarea :name="name" :value="JSON.stringify(data)" style="display: none;"></textarea>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, provide } from 'vue';
import swal from 'sweetalert';
import _ from 'lodash';
import eventBus from '../eventBus';

import { usePagesImageNote } from '../composables/usePagesImageNote';
import { usePagesRepeatable } from '../composables/usePagesRepeatable';
import { formatContent, canShow } from '../composables/useContentBlocks';

const props = defineProps(['config', 'page', 'name', 'content']);

const data = ref([]);
// per-block open state keyed by block id (survives re-render and reorder)
const openBlocks = ref({});

const pageRef = computed(() => props.page);
const { getImageNote } = usePagesImageNote(pageRef);
const { addRepeatable } = usePagesRepeatable(pageRef, { getImageNote });

// child PagesRepeatable components inject the nearest addRepeatable
provide('pages:addRepeatable', addRepeatable);

const canShowAnchors = computed(() => {
  if (!props.config) {
    return false;
  }

  if (!props.config.show_page_anchors) {
    return false;
  }

  if (!props.config.show_page_anchors.enabled) {
    return false;
  }

  return true;
});

const anchorPrefix = computed(() => {
  if (!props.config) {
    return ''
  }

  if (!props.config.show_page_anchors) {
    return '';
  }

  if (!props.config.show_page_anchors.class) {
    return '';
  }

  return props.config.show_page_anchors.class;
});

function loadContentBlock(content) {
  const formattedContent = formatContent(_.cloneDeep(content));

  data.value.push(formattedContent);
  openBlocks.value[formattedContent.id] = true;
  props.page[props.name] = data.value;
}

function removeContentBlock(index) {
  swal({
    title: 'Are you sure?',
    icon: 'warning',
    buttons: true,
    dangerMode: true,
  }).then(value => {
    if (value) {
      data.value.splice(index, 1);
      props.page[props.name] = data.value;
    }
  });
}

function toggleContentBlockContent(id) {
  openBlocks.value[id] = !openBlocks.value[id];
}

function reorderContentBlocks(order) {
  const contentLookup = _.keyBy(data.value, 'id');

  data.value = order.map(item => {
    return contentLookup[item.id];
  });

  props.page[props.name] = data.value;
}

function selectAndCopy(event) {
  const target = event.target;

  // Select the content of the target element
  const textToCopy = target.innerText || target.textContent;

  // Use the modern Clipboard API to copy the text
  navigator.clipboard.writeText(textToCopy)
    .then(() => {
      alert('Text copied to clipboard');
    })
    .catch(err => {
      console.error('Failed to copy text: ', err);
      alert('Failed to copy text');
    });
}

function onContentItemDragend(order) {
  reorderContentBlocks(order);
}

// created
eventBus.on('pages.sortable.content-item.dragend', onContentItemDragend);

onMounted(() => {
  const initial = props.page[props.name];
  data.value = initial.map(item => formatContent(item));
  // preserve the old default: first block open
  if (data.value.length) {
    openBlocks.value[data.value[0].id] = true;
  }
});

onUnmounted(() => {
  eventBus.off('pages.sortable.content-item.dragend', onContentItemDragend);
});
</script>
