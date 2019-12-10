<template>
  <div class="sitemap" :class="{ 'sitemap--active' : $root.sitemap.showModal }">
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

<script>

    export default {

      props: [ 'siteUrl', 'config', 'modules' ],

      data() {
        return {
          pages: [],
          parents: {}
        }
      },

      created () {
        // get pages
        this.$root.loading = true;

        eventBus.$on('sitemap-close', this.closeModal);
        eventBus.$on('sitemap-reload', this.resetSitemap);

        axios
          .get('/refined/pages/get-tree-basic')
          .then(r => {
            this.$root.loading = false;

            if (r.status == 200) {
              this.pages = r.data;
              this.setupParents();
            }
          })
          .catch(e => {
            this.$root.loading = false;
          })
        ;
      },

      methods: {
        toggleSubMenu(item) {
          if (item.children.length || item.type == 'holder') {
            item.show = !item.show;
          }
        },

        loadPage(item) {
          this.link = this.buildLink(item);
          this.value = this.link;
          this.$emit('input', this.link);
          eventBus.$emit('selecting-link', this.link);
          this.closeModal();
        },

        setupParents() {
          this.parents = {};
          this.addParents(this.pages, 0);
        },

        addParents(items, depth) {
          if (items.length) {
            items.forEach(item => {
              // add to the flat listing
              if (item.type == 'holder') {
                this.parents['-'+item.id] = item;
              } else {
                this.parents[item.id] = item;
              }

              if (item.children.length) {
                this.addParents(item.children, depth + 1);
              }

            });
          }
        },

        buildLink(item) {
          let link = [];
          let c = 0;
          link.push(item.meta.uri);
          while (item.parent_id > -1) {
            c++;
            if (item.parent_id > 0 && typeof this.parents[item.parent_id] != 'undefined') {
              item = this.parents[item.parent_id];
              link.push(item.meta.uri);
            }

            if (c > 10) {
              break;
            }
          }

          return '/'+link.reverse().join('/');
        },

        closeModal() {
          this.$root.sitemap.showModal = false;
        },

        resetSitemap() {
          if (this.pages.length) {
            this.resetSection(this.pages);
          }
          this.pages[0].show = true;
          this.pages[0].on = true;
        },

        resetSection(pages) {
          pages.forEach(page => {
            page.show = false;
            page.on = false;
            if (page.children.length) {
              this.resetSection(page.children);
            }
          });

        }
      }
    }
</script>
