<template>
  <div class="sitemap" :class="{ 'sitemap--active' : ui.sitemap.showModal }">
    <div class="sitemap__inner">
      <div class="pages__tree">
        <nav class="tree">
          <ul class="tree__trunk">
            <li class="tree__branch tree__branch--master tree__branch--has-children"
              :class="{
                'tree__branch--active' : holder.show,
              }"
              v-for="holder of pages"
            >
              <div>
                <i class="fas" @click="toggleSubMenu(holder)"></i>
                <span class="tree__leaf" @click="toggleSubMenu(holder)">{{ holder.name }}</span>
              </div>

              <rd-pages-branch :data="holder" :id="-holder.id"></rd-pages-branch>

            </li>
          </ul>
        </nav>
      </div>

      <footer class="sitemap__footer">
        <button class="button button--red button--small" @click="closeModal">Close</button>
      </footer>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, onUnmounted, provide } from 'vue';
  import eventBus from '../eventBus';
  import { useUiStore } from '../stores/ui';

  const props = defineProps(['siteUrl', 'config', 'modules']);
  const emit = defineEmits(['input']);

  const ui = useUiStore();

  const pages = ref([]);
  let parents = {};
  const link = ref(null);
  const value = ref(null);

  function toggleSubMenu(item) {
    if (item.children.length || item.type == 'holder') {
      item.show = !item.show;
    }
  }

  function loadPage(item) {
    link.value = buildLink(item);
    value.value = link.value;
    emit('input', link.value);
    eventBus.emit('selecting-link', link.value);
    closeModal();
  }

  function setupParents() {
    parents = {};
    addParents(pages.value, 0);
  }

  function addParents(items, depth) {
    if (items.length) {
      items.forEach(item => {
        // add to the flat listing
        if (item.type == 'holder') {
          parents['-'+item.id] = item;
        } else {
          parents[item.id] = item;
        }

        if (item.children.length) {
          addParents(item.children, depth + 1);
        }

      });
    }
  }

  function buildLink(item) {
    let lnk = [];
    let c = 0;
    lnk.push(item.meta.uri);
    while (item.parent_id > -1) {
      c++;
      if (item.parent_id > 0 && typeof parents[item.parent_id] != 'undefined') {
        item = parents[item.parent_id];
        lnk.push(item.meta.uri);
      }

      if (c > 10) {
        break;
      }
    }

    const joint = lnk.reverse().join('/');

    if (joint.startsWith('[')) {
      return joint;
    }

    return `/${joint}`;
  }

  function closeModal() {
    ui.sitemap.showModal = false;
    ui.sitemap.active = false;
  }

  function clearSitemap() {
    console.log('clearing sitemap');
    ui.sitemap.showModal = false;
    ui.sitemap.active = false;
    ui.sitemap.fieldId = null;
    ui.sitemap.model = null;
  }

  function resetSitemap() {
    if (pages.value.length) {
      resetSection(pages.value);
    }
    pages.value[0].show = true;
    pages.value[0].on = true;
  }

  function resetSection(items) {
    items.forEach(page => {
      page.show = false;
      page.on = false;
      if (page.children.length) {
        resetSection(page.children);
      }
    });

  }

  // created
  ui.loading = true;

  axios
    .get(`${window.siteUrl}/refined/pages/get-tree-basic`)
    .then(r => {
      ui.loading = false;

      if (r.status == 200) {
        pages.value = r.data;
        setupParents();
      }
    })
    .catch(e => {
      ui.loading = false;
    })
  ;

  onMounted(() => {
    eventBus.on('sitemap-close', closeModal);
    eventBus.on('sitemap-reload', resetSitemap);
    eventBus.on('sitemap-clear', clearSitemap);
  });

  onUnmounted(() => {
    eventBus.off('sitemap-close', closeModal);
    eventBus.off('sitemap-reload', resetSitemap);
    eventBus.off('sitemap-clear', clearSitemap);
  });

  // recursive PagesBranch children inject these instead of walking the parent chain
  provide('pages:loadPage', loadPage);
  provide('pages:toggleSubMenu', toggleSubMenu);
</script>
