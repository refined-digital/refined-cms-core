<template>
  <ul class="tree__trunk tree__trunk--sortable" v-show="data.show" :data-id="id">
    <li class="tree__branch"
      :class="{
        'tree__branch--has-children' : page.children.length,
        'tree__branch--active' : page.show,
        'tree__branch--hide-from-menu' : page.hide_from_menu,
        'tree__branch--hide-page' : !page.active,
      }"
      v-for="page of data.children"
      :data-parent="page.parent_id"
      :data-id="page.id"
    >
      <div>
        <i class="fas fa-file" @click="toggleSubMenu(page)"></i>
        <span
          class="tree__leaf"
          :class="{ 'tree__leaf--active' : page.on }"
          @click="loadPage(page)"
        >{{ page.name}}</span>
      </div>

      <rd-pages-branch :data="page" :id="page.id"></rd-pages-branch>

    </li>
  </ul>
</template>

<script setup>
import { inject } from 'vue';

defineProps(['data', 'id']);

// the branch is recursive; Pages provides the real implementations once and
// every branch injects them directly (no parent-chain walking).
const loadPage = inject('pages:loadPage');
const toggleSubMenu = inject('pages:toggleSubMenu');
</script>
