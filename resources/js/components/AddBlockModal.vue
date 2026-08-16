<template>
  <div class="block-modal" @click.self="$emit('close')">
    <div class="block-modal__dialog">
      <header class="block-modal__header">
        <h3>Add a content block</h3>
        <input
          ref="searchInput"
          type="text"
          class="form__control block-modal__search"
          placeholder="Search blocks..."
          v-model="query"
        >
        <button class="block-modal__close" @click="$emit('close')"><i class="fa fa-times"></i></button>
      </header>

      <div class="block-modal__groups">
        <p class="page-builder__empty" v-if="!hasResults">No blocks match "{{ query }}"</p>

        <div class="block-modal__group" v-for="(blocks, category) in grouped" :key="category">
          <h4>{{ category }}</h4>
          <div class="block-modal__grid">
            <button
              class="block-modal__item"
              v-for="item in blocks"
              :key="item.name"
              @click="$emit('add', item)"
            >
              <span class="block-modal__item-name">{{ item.name }}</span>
              <span class="block-modal__item-desc" v-if="item.description" v-html="item.description"></span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps(['content']);
const emit = defineEmits(['add', 'close']);

const query = ref('');
const searchInput = ref(null);

const grouped = computed(() => {
  const term = query.value.trim().toLowerCase();
  const groups = {};

  (props.content || []).forEach(item => {
    if (term && !item.name.toLowerCase().includes(term)) {
      return;
    }

    const category = item.category || 'General';
    (groups[category] = groups[category] || []).push(item);
  });

  // alphabetical categories, General first
  return Object.fromEntries(
    Object.entries(groups).sort(([a], [b]) => {
      if (a === 'General') return -1;
      if (b === 'General') return 1;
      return a.localeCompare(b);
    })
  );
});

const hasResults = computed(() => Object.keys(grouped.value).length > 0);

function onKeydown(event) {
  if (event.key === 'Escape') {
    event.preventDefault();
    emit('close');
  }
}

onMounted(() => {
  document.addEventListener('keydown', onKeydown);
  searchInput.value?.focus();
});

onUnmounted(() => document.removeEventListener('keydown', onKeydown));
</script>
