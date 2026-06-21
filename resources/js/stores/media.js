import { defineStore } from 'pinia';

// the media library tree + files, loaded once and then mutated in place so the
// modal never has to refetch (and never loses the open folder). uploads, moves,
// edits and deletes push directly into this store.
export const useMediaStore = defineStore('media', {
  state: () => ({
    categories: [], // nested category tree (the array the sidebar renders)
    // NB: `tree` and `files` are captured by-reference in Media.vue's setup, so
    // they must always be mutated IN PLACE — never reassign this.tree/this.files
    // (indexTree/indexFiles clear via delete/splice for exactly this reason).
    tree: {}, // id -> category node (flat index into `categories`)
    files: {}, // id -> file (flat index across all categories)
    searchableFiles: [], // flat list of every file, for search
    activeCategory: { files: [], children: [], name: '' },
    leaf: {
      category: {},
      media: {},
    },
    loaded: false,
    loading: false,
  }),

  actions: {
    // fetch the tree from the server. only runs once unless `force` is set.
    async load(force = false) {
      if (this.loaded && !force) {
        return;
      }

      this.loading = true;
      try {
        const r = await axios.get(`${window.siteUrl}/refined/media/get-tree`);
        if (r.status === 200) {
          const previousId = this.activeCategory?.id;

          this.categories = r.data.tree;
          this.leaf.category = r.data.categoryLeaf;
          this.leaf.media = r.data.mediaLeaf;

          this.indexTree();
          this.indexFiles();

          // on a forced refresh, re-point the active folder at the same id so the
          // component re-syncs to it; the component drives the first-load default.
          if (force && previousId && this.tree[previousId]) {
            this.setActiveCategory(this.tree[previousId]);
          }

          this.loaded = true;
        }
      } finally {
        this.loading = false;
      }
    },

    // force a fresh fetch from the server (manual refresh button)
    async refresh() {
      await this.load(true);
    },

    // (re)build the id -> category index from the nested tree. clears in place so
    // existing references to `tree` (held by the component) stay valid.
    indexTree() {
      Object.keys(this.tree).forEach((k) => delete this.tree[k]);
      const walk = (items) => {
        items.forEach((item) => {
          this.tree[item.id] = item;
          if (item.children?.length) {
            walk(item.children);
          }
        });
      };
      walk(this.categories);
    },

    // (re)build the id -> file index and the flat searchable list (in place)
    indexFiles() {
      Object.keys(this.files).forEach((k) => delete this.files[k]);
      this.searchableFiles.splice(0);
      const walk = (items) => {
        items.forEach((item) => {
          if (item.files?.length) {
            item.files.forEach((f) => {
              this.files[f.id] = f;
              this.searchableFiles.push(f);
            });
          }
          if (item.children?.length) {
            walk(item.children);
          }
        });
      };
      walk(this.categories);
    },

    setActiveCategory(item) {
      this.activeCategory = item;
    },

    /* ---------------- file mutations (no refetch) ---------------- */

    // add a freshly-uploaded file to its category + indexes
    addFile(file) {
      const category = this.tree[file.media_category_id] || this.activeCategory;
      if (category) {
        category.files.push(file);
      }
      this.files[file.id] = file;
      this.searchableFiles.push(file);
    },

    // move a file between categories
    moveFile(mediaId, categoryId) {
      const file = this.files[mediaId];
      const newCategory = this.tree[categoryId];
      if (!file || !newCategory) {
        return;
      }

      const oldCategory = this.tree[file.media_category_id];
      if (oldCategory?.files?.length) {
        const i = oldCategory.files.findIndex((f) => f.id === file.id);
        if (i > -1) {
          oldCategory.files.splice(i, 1);
        }
      }

      file.media_category_id = categoryId;
      newCategory.files.push(file);
    },

    // merge updated metadata into an existing file
    updateFile(item) {
      if (this.files[item.id]) {
        Object.assign(this.files[item.id], item);
      }
    },

    // remove a file from its category + indexes
    removeFile(item) {
      const walk = (items) => {
        items.forEach((cat) => {
          if (cat.files?.length) {
            const i = cat.files.findIndex((f) => f.id === item.id);
            if (i > -1) {
              cat.files.splice(i, 1);
            }
          }
          if (cat.children?.length) {
            walk(cat.children);
          }
        });
      };
      walk(this.categories);
      delete this.files[item.id];
      const si = this.searchableFiles.findIndex((f) => f.id === item.id);
      if (si > -1) {
        this.searchableFiles.splice(si, 1);
      }
    },

    /* ---------------- category mutations (no refetch) ---------------- */

    // insert a newly-created category under its parent
    addCategory(category) {
      this.tree[category.id] = category;
      const parent = this.tree[category.parent_id];
      if (parent) {
        parent.children.push(category);
      }
    },

    // remove a category from the tree, returns the sibling/parent to fall back to
    removeCategory(item) {
      let fallback = null;
      const walk = (items) => {
        const i = items.findIndex((cat) => cat.id === item.id);
        if (i > -1) {
          items.splice(i, 1);
          delete this.tree[item.id];
          fallback = items.length ? items[0] : this.tree[item.parent_id];
        }
        items.forEach((cat) => {
          if (cat.children?.length) {
            walk(cat.children);
          }
        });
      };
      walk(this.categories);
      return fallback;
    },

    // reflect a category being dragged to a new parent
    moveCategory(itemId, parentId) {
      if (this.tree[itemId]) {
        this.tree[itemId].parent_id = parentId;
      }
    },
  },
});
