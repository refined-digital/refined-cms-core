<template>
  <ul class="tree__trunk"
    v-if="data.children.length"
    v-show="data.show"
    :data-id="id"
    :class="{
      'tree__trunk--sortable' : data.id > 1
    }"
  >
    <li class="tree__branch tree__branch--master"
      :class="{
        'tree__branch--has-children' : page.children.length,
        'tree__branch--active' : page.show,
      }"
      v-for="page of data.children"
      :data-parent="page.parent_id"
      :data-id="page.id"

    >
      <div v-droppable-media>
        <i class="fas" @click="toggleSubMenu(page)"></i>
        <span
          class="tree__leaf"
          :class="{ 'tree__leaf--active' : page.on }"
          @click="loadCategory(page)"
        >{{ page.name}}</span>
      </div>

      <rd-media-branch @media-dropped="mediaDropped" :data="page" :id="page.id"></rd-media-branch>

    </li>
  </ul>
</template>

<script>

  export default {
    props: [ 'data', 'id' ],

    methods: {
      loadCategory(page) {
        this.$parent.loadCategory(page);
      },
      toggleSubMenu(page) {
        this.$parent.toggleSubMenu(page);
      },
      mediaDropped(e) {
        this.$parent.mediaDropped(e);
      }
    }
  }
</script>