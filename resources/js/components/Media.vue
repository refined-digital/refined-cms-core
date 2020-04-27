<template>
  <div class="media-library" :class="{ 'media__modal' : modal, 'media-library--active' : $root.media.showModal }">

    <div class="pages">
      <aside class="pages__tree">
        <div class="tree__search">
          <input type="text" v-model="search" placeholder="Search..."/>
          <a href="#" @click.prevent.stop="clearSearch" v-if="search" class="tree__search-clear"><span>&times;</span></a>
        </div>

        <nav class="tree">
          <ul class="tree__trunk">
            <li class="tree__branch tree__branch--master"
              :class="{ 'tree__branch--has-children' : holder.children.length, 'tree__branch--active' : holder.show }"
              v-for="holder of categories"
            >
              <div>
                <i class="fas" @click="toggleSubMenu(holder)"></i>
                <span class="tree__leaf" @click="toggleSubMenu(holder)">{{ holder.name }}</span>
              </div>

              <rd-media-branch @media-dropped="mediaDropped" :data="holder" :id="-holder.id"></rd-media-branch>

            </li>
          </ul>
        </nav>
      </aside>
      <section class="pages__content">
        <div class="pages__header">
          <h3>{{ category.name }}</h3>
          <aside>
            <template v-if="show.buttons.controls">
              <template v-if="tab == 'details-category'">
                <a href="" class="button button--grey button--small" @click.prevent.stop="categorySave">Save</a>
                <a href="" class="button button--red button--small" @click.prevent.stop="categoryCancel">Cancel</a>
              </template>
              <template v-if="tab == 'details-file'">
                <a href="" class="button button--grey button--small" @click.prevent.stop="mediaSave">Save</a>
                <a href="" class="button button--red button--small" @click.prevent.stop="loadFiles">Cancel</a>
              </template>
            </template>

            <template v-if="!show.buttons.controls">
              <a href="" class="button button--grey button--small" @click.prevent.stop="mediaOpen">Add Files</a>
              <a href="" class="button button--grey button--small" @click.prevent.stop="categoryAdd">Add a Category</a>
            </template>

            <template v-if="modal">
              <span>|</span>
              <a href="" class="button button--red button--small" @click.prevent.stop="close">Close</a>
            </template>
          </aside>
        </div><!-- / header -->

        <div class="pages__tabs" v-if="!modal">
          <nav>
            <ul>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == 'files') }" @click="loadFiles" v-if="show.tabs.files">Files</li>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == 'details-file') }" @click="tab = 'details-file'" v-if="show.tabs.details.file">Details</li>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == 'details-category') }" @click="categoryShow" v-if="show.tabs.details.category">Details</li>
            </ul>
          </nav>
        </div>

        <div class="pages__info">

          <div class="pages__tab-pane" v-show="tab == 'details-category' && !search">
            <header class="pages__tab-pane-header">
              <h3>Category Details</h3>
              <aside>
                <a href="#" @click.prevent.stop="categoryDelete()" class="button button--red button--small" v-if="show.buttons.categoryDelete">Delete Category</a>
              </aside>
            </header>

            <div class="form form__horz">
              <div class="form__row form__row--inline-label">
                <label for="form--menu-text" class="form__label">Name</label>
                <div class="form__horz-group">
                  <input type="text" id="form--menu-text" v-model="category.name" required="required" class="form__control">
                </div>
              </div><!-- / form row -->
            </div>

          </div>

          <div class="pages__tab-pane" v-show="tab == 'details-file' && !search">
            <header class="pages__tab-pane-header">
              <h3>File Details</h3>
            </header>

            <div class="media__details">
              <div class="media__details-thumbnail">

                <rd-media-file :file="file"></rd-media-file>

                <a :href="file.link.original" target="_blank" class="media__file-link">View File</a>
                <a href="#" @click.prevent.stop="mediaDelete(file)" target="_blank" class="media__file-delete">Delete File</a>

              </div>
              <div class="media__details-details">

                <div class="form form__horz">

                  <div class="form__row form__row--inline-label">
                    <label for="form--file-name" class="form__label">Name</label>
                    <div class="form__horz-group">
                      <input type="text" id="form--file-name" v-model="file.name" required="required" class="form__control">
                    </div>
                  </div><!-- / form row -->

                  <div class="form__row form__row--inline-label">
                    <label for="form--file-alt" class="form__label">Alternate Text (alt text)</label>
                    <div class="form__horz-group">
                      <input type="text" id="form--file-alt" v-model="file.alt" required="required" class="form__control">
                    </div>
                  </div><!-- / form row -->

                  <div class="form__row form__row--inline-label">
                    <label for="form--file-desc" class="form__label">Description</label>
                    <div class="form__horz-group">
                      <textarea id="form--file-desc" v-model="file.description" required="required" class="form__control"></textarea>
                    </div>
                  </div><!-- / form row -->

                  <div class="form__row form__row--inline-label">
                    <label class="form__label">File Url</label>
                    <div class="form__horz-group">
                      <div class="form__control--url">
                        <input type="text" id="form--slug" v-model="file.link.original" readonly required="required" class="form__control">
                        <span class="copy-url" @click="copyUrl"><i class="fas fa-link"></i></span>
                      </div>
                    </div>
                  </div><!-- / form row -->

                </div>

              </div>
            </div>

          </div>

          <div class="pages__tab-pane pages__tab-pane--full" v-show="tab == 'files' && !search">
            <div class="media-files">
              <header class="pages__tab-pane-header">
                <h3>File Listing</h3>
                <aside class="media__toggle">
                  <i class="fas fa-th-large" :class="{ 'media__toggle--active' : $root.media.display == 'thumb' }" @click="mediaDisplay('thumb')"></i>
                  <i class="fas fa-th-list" :class="{ 'media__toggle--active' : $root.media.display == 'list' }" @click="mediaDisplay('list')"></i>
                </aside>
              </header>

              <p v-if="category.files.length < 1">There are no files in this folder</p>

              <div class="media__files" :class="'media__files--'+$root.media.display" id="dropzone-2">
                <div class="media__file"
                  v-for="file of category.files"
                  v-draggable-media
                  :data-id="file.id"
                  @click="mediaLoad(file)"
                  v-if="type == file.type || type == '*' || (type === 'Image' && file.type === 'Video')"
                >
                  <rd-media-file :file="file"></rd-media-file>
                </div>

              </div><!-- / files -->

            </div><!-- / media files -->

            <div class="media__file--template">
              <div class="media__file media__file--uploading">
                <i class="spinner"></i>
                <figure>
                    <span class="media__file-thumb dz-image">
                      <img src="/vendor/refinedcms/img/ui/media-thumb.png">
                      <span class="media__file-thumb-holder"><img data-dz-thumbnail></span>
                    </span>
                  <figcaption>
                    <span class="media__file-title"><span data-dz-name></span></span>
                    <span class="media__file-upload"><span data-dz-uploadprogress></span></span>
                    <span class="media__file-size"><span data-dz-size></span></span>
                    <span class="media__file-error"><span data-dz-errormessage></span></span>
                  </figcaption>
                </figure>
              </div>
            </div>
          </div><!-- / details -->

          <div class="pages__tab-pane pages__tab-pane--full" v-show="search">
            <div class="media-files">
              <header class="pages__tab-pane-header">
                <h3>Search Results</h3>
                <aside class="media__toggle">
                  <a href="#" @click.prevent.stop="clearSearch" class="button button--red button--small">Clear Search</a>
                </aside>
              </header>

              <p v-if="filterFiles.length < 1">There are no files matching your search</p>

              <div class="media__files media__files--list">
                <div class="media__file"
                  v-for="file of filterFiles"
                  :data-id="file.id"
                  @click="mediaLoad(file)"
                >
                  <rd-media-file :file="file"></rd-media-file>
                </div>

              </div><!-- / files -->

            </div><!-- / media files -->

          </div>

        </div><!-- / info -->
      </section>


      <div class="media-uploader" v-show="$root.media.active">
        <div class="media-uploader__inner">

          <header class="media-uploader__header">
            Upload Files
            <aside>
              <i class="fas fa-times" @click="mediaClose"></i>
            </aside>
          </header>

          <div class="media-uploader__drag-zone" id="dropzone">
            <div class="media-uploader__dropzone dz-message">
              <i class="fas fa-cloud-upload-alt"></i>
              <p>
                  Drop files here to upload, or click to browse your computer
              </p>
            </div>
            <div class="media-uploader__preview">
              <div class="media-uploader__preview-listing">

              </div>
            </div>
          </div>

        </div>
      </div><!-- / media uploaded -->
    </div><!-- / pages -->

  </div>
</template>

<script>
  import Dropzone from 'dropzone';

  export default {

    props: {
      modal: {
        type: Boolean,
        default: null
      },
    },

    created () {
      // get pages
      this.$root.loading = true;

      this.reload();

      eventBus.$on('media-close', this.close);
      eventBus.$on('media-set-type', this.setType);
      eventBus.$on('media-reload', this.reload);
    },

    data() {
      return {
        tab: 'details',
        type: '*',

        search: '',

        category: {
          files: []
        },

        file: {
          name: '',
          alt: '',
          description: '',
          type: '',
          size: '',
          link: {
            original: '',
            thumb: ''
          },
        },

        categoryClone: {},
        categories: [],
        tree: {},
        files: {},
        searchableFiles: [],

        leaf: {
          category: {},
          media: {}
        },

        sortable: null,
        drop: null,
        drop2: null,

        show: {
          buttons: {
            controls: false,
            categoryDelete: true,
          },
          tabs: {
            files: true,
            details: {
              category: false,
              file: false,
            },
          }
        },

        forms: {
          category: {
            name: ''
          }
        }

      }
    },

    updated() {
      this.$nextTick(() => {
        this.initSort();
      });
    },

    mounted() {
      this.setDropZone();
    },

    watch: {
      search(value) {
        if (this.tab != 'files' && value.length > 0) {
          this.loadFiles();
        }
      }
    },

    computed: {
      filterFiles() {
        return this.searchableFiles.filter(item => {
          let search = this.search.toLowerCase();
          let name = item.name.toLowerCase();

          if (this.type == item.type || this.type == '*') {
            return name.indexOf(search) !== -1;
          }

          return false;

        });
      }
    },

    methods: {
      // reload the media library
      reload() {
        axios
          .get('/refined/media/get-tree')
          .then(r => {
            this.$root.loading = false;
            if (r.status == 200) {
              this.categories = r.data.tree;
              this.leaf.category = r.data.categoryLeaf;
              this.leaf.media = r.data.mediaLeaf;

              // setting the initial page
              if (this.categories.length) {
                this.setTree();
                this.setFiles();
                this.loadCategory(this.tree[1].children[0]);
              }

            }
          })
          .catch(e => {
            this.$root.loading = false;
          })
        ;
      },

      // show / hide the tree
      toggleSubMenu(item) {
        if (item.children.length) {
          item.show = !item.show;
        }
      },

      // this just turns off all on pages
      turnOffCategories() {
        for (let i in this.tree) {
          let branch = this.tree[i];
          if (branch.children.length < 1) {
            branch.show = false;
          }
          branch.on = false;
        }
      },

      turnOnParents(category) {
        if (category.parent_id > 0 && typeof this.tree[category.parent_id] != 'undefined') {
          category.show = true;
          this.turnOnParents(this.tree[category.parent_id]);
        }

        // always have the media category open
        if (category.id == 1) {
          category.show = true;
        }
      },

      // sets the tree
      setTree() {
        this.tree = {};
        this.addBranches(this.categories);
      },

      addBranches(items) {
        if (items.length) {
          items.forEach(item => {
            this.tree[item.id] = item;

            if (item.children.length) {
              this.addBranches(item.children);
            }
          })
        }
      },

      setFiles() {
        this.files = {};
        this.searchableFiles = [];
        this.setFile(this.categories);
      },

      setFile(items) {
        if (items.length) {
          items.forEach(item => {

            if (item.files.length) {
              item.files.forEach(file => {
                this.files[file.id] = file;
                this.searchableFiles.push(file);
              });
            }

            if (item.children.length) {
              this.setFile(item.children);
            }
          })
        }
      },

      initSort() {
        if (this.sortable == null) {
          let elements = document.querySelectorAll('.tree__trunk--sortable');

          let containers = [];
          if (elements.length) {
            elements.forEach(element => {
              containers.push(element);
            })

            this.sortable = dragula(containers, {
              direction: 'vertical'
            })
            .on('drop', (e) => {
              let parent = document.querySelector('.tree__trunk[data-id="'+e.dataset.parent+'"');
              let newParent = e.closest('.tree__trunk');
              let children = [];
              let parentId = e.dataset.parent;

              // check to see if the parent has updated
              if (parent.dataset.id != newParent.dataset.id) {
                // we have a different parent, need to update
                children = newParent.querySelectorAll(':scope > .tree__branch');
                parentId = parseInt(newParent.dataset.id);

                // find and update the new leaf
                if (typeof this.tree[e.dataset.id] != 'undefined') {
                  this.tree[e.dataset.id].parent_id = parentId;

                  // now update the leaf in the db
                  this.categoryUpdateParent(e.dataset.id, parentId);
                }

              } else {
                children = parent.querySelectorAll(':scope > .tree__branch');
              }

              if (children.length) {
                let ids = [];
                children.forEach(el => {
                  ids.push(el.dataset.id);
                });

                this.reposition(ids, parentId);
              }

            })
            ;
          }
        }
      },

      loadFiles() {
        this.tab = 'files';
        this.show.tabs.details.file = false;
        this.show.tabs.details.category = true;
        this.show.buttons.controls = false;
      },

      // finds the folder the particular page is in
      findFolder() {
        let key = this.category.parent_id;
        if (typeof this.tree[key] != 'undefined' && this.tree[key].show == false) {
          this.tree[key].show = true;
        }
      },

      scroll() {
        this.$el.querySelector('.pages__info').scrollTop = 0;
      },

      //////
      /// category

      // load the page for editing
      loadCategory(item) {
        this.turnOffCategories();
        this.scroll();

        this.category = item;
        this.category.on = true;
        this.category.show = true;
        this.show.tabs.files = true;
        this.show.tabs.details.category = true;
        this.show.buttons.categoryDelete =  (this.category.files.length || this.category.children.length) ? false : true;
        this.categoryClose();

        this.turnOnParents(this.category);
        this.loadFiles();
      },

      // show the add category stuff
      categoryShow() {
        this.show.buttons.controls = true;
        this.tab = 'details-category';
        this.categoryClone = this.$root.clone(this.category);
      },

      categoryAdd() {
        let newData = this.$root.clone(this.leaf.category);
        newData.parent_id = this.category.id;
        this.loadCategory(newData);
        this.show.buttons.controls = true;
        this.show.buttons.categoryDelete = false;
        this.show.tabs.files = false;

        if (typeof this.tree[newData.parent_id] != 'undefined') {
          this.tree[newData.parent_id].on = true;
        }

        this.tab = 'details-category';
      },

      // removes the add category form
      categoryCancel() {
        this.category.name = this.categoryClone.name;
        this.show.buttons.controls = false;
        this.show.tabs.files = true;
        this.categoryClone = {};
        this.loadFiles();
      },

      categoryClose() {
        this.show.buttons.controls = false;
        this.show.tabs.files = true;
        this.categoryClone = {};
        this.tab = 'files';
      },

      categorySave() {

        let check   = /^.+[\s]{0,4}/,
            errors  = [],
            validationData = document.createElement('ul')
        ;

        // do the validation
        if (!check.test(this.category.name) || this.category.name == null) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please enter a Name';
          validationData.appendChild(child);
        }

        if (errors.length) {
          swal({
            title: 'You have some errors in your form.',
            content: validationData,
            icon: 'error',
            dangerMode: true,
          });
        } else {

          this.$root.loading = true;
          let config = {
            url: '/refined/media/categories',
            method: 'POST',
            data: this.category,
          }

          if (typeof this.category.newPage == 'undefined') {
            config.method = 'PUT';
            config.url += '/'+ this.category.id
          }

          axios
            .request(config)
            .then(r => {
              this.$root.loading = false;
              if (r.data.success) {

                if (typeof this.category.newPage != 'undefined') {
                  // we have just added a page, so insert it into the menu
                  this.tree[r.data.leaf.id] = r.data.leaf;
                  if (typeof this.tree[this.category.parent_id] != 'undefined') {
                    this.tree[this.category.parent_id].children.push(r.data.leaf);
                  }

                  // load the page
                  this.loadCategory(this.tree[r.data.leaf.id]);
                  this.findFolder();
                  this.categoryClose();
                }

                swal({
                  title: 'Success',
                  text: 'Category has been successfully saved',
                  icon: 'success'
                });

                this.categoryClose();

              }
              else {
                swal({
                  title: 'Something went wrong',
                  text: r.data.msg,
                  icon: 'error'
                });
              }
            })
            .catch(e => {
              console.log(e);
              this.$root.loading = false;
              swal({
                title: 'Something went wrong',
                text: e.message,
                icon: 'error'
              });
            })
          ;

        }
      },

      categoryDelete() {
        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        }).then(value => {
          if (value) {
          this.$root.loading = true;
            axios
              .delete('/refined/media/categories/'+this.category.id)
              .then(r => {
                this.$root.loading = false;

                if (r.data.success) {

                  // find the page and splice from parent
                  this.categoryFindAndRemove(this.categories, this.category);

                } else {
                  swal({
                    title: 'Something went wrong',
                    text: r.data.msg,
                    icon: 'error'
                  });
                }
              })
              .catch(e => {
                console.log(e);
                this.$root.loading = false;
                swal({
                  title: 'Something went wrong',
                  text: e.message,
                  icon: 'error'
                });
              })
            ;
          }
        });
      },

      // find the page in the parent and remove
      categoryFindAndRemove(items, item) {
        if (items.length) {
          items.forEach((category, index) => {
            if (item.id == category.id) {
              items.splice(index, 1);
              delete this.tree[item.id];

              // set the first category in the list to the new active category
              if (items.length) {
                this.loadCategory(items[0]);
              } else {
                // load the parent
                this.loadCategory(this.tree[item.parent_id]);
              }
            }

            if (category.children.length) {
              this.categoryFindAndRemove(category.children, item);
            }
          });
        }

      },

      // run the db to reset position items
      reposition(ids, parent) {
        if (Array.isArray(ids) && ids.length) {
          axios
            .post('/refined/media/categories/position', {
              positions: ids,
              parent: parent,
            })
            .then(response => {})
            .catch(error => {
              console.log('Sort Error', error);
            })
          ;
        }

      },

      // updates the parent ids in the db
      categoryUpdateParent(itemId, parentId) {
        let config = {
          url: '/refined/media/categories/'+itemId+'/update-parent',
          method: 'PUT',
          data: { parentId: parentId }
        };

        axios
          .request(config)
          .then(response => {})
          .catch(error => {
            console.log('Sort Error', error);
          })
        ;
      },

      /// category
      //////

      //////
      /// drop zone
      setDropZone() {

        if (this.drop == null) {
          let self = this;
          let args = {
            url: '/refined/media/upload-file',
            acceptedFiles: 'image/*,.pdf,.docx,.doc,.xls,.xlsx,.zip,.mp4',
            maxFilesize: 10,
            timeout: 300000,
            createImageThumbnails: true,
            previewsContainer: self.$el.querySelector('.media-uploader__preview-listing'),
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
          };

          this.drop = new Dropzone(
            '#dropzone',
            args
          ).on('error', err => {
              this.dropError(err);
            })
            .on('sending', (file, xhr, data) => {
              this.dropSending(data);
            })
            .on('success', file => {
              this.dropSuccess(file);
            })
          ;

          let args2 = this.$root.clone(args);
          args2.clickable = false;
          args2.previewTemplate = self.$el.querySelector('.media__file--template').innerHTML,
          delete args2.previewsContainer;

          this.drop2 = new Dropzone(
            '#dropzone-2',
            args2
          ).on('error', err => {
              this.dropError(err);
            })
            .on('sending', (file, xhr, data) => {
              this.dropSending(data);
            })
            .on('success', file => {
              this.dropSuccess(file, true);
            })
          ;
        }
      },

      dropError(err) {
        if (err.status == 'error') {
          if (typeof err.xhr != 'undefined') {
            let msg = JSON.parse(err.xhr.response);
            let message = '';
            if (typeof msg.exception != 'undefined') {
              message = msg.message ? msg.message : msg.exception;

              if (message == 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException') {
                message = 'Route not found, please contact support';
              }

            }

            if (message) {
              err.previewElement.querySelector('[data-dz-errormessage]').innerText = message;
            }
          }
        }
      },

      dropSending(data) {
        data.append('media_category_id', this.category.id);
      },

      dropSuccess(file, inline = false) {
        if (!file.type.match('image/')) {
          file.previewElement.querySelector('.dz-image').classList.add('dz-file');
        }

        setTimeout(() => {
          $(file.previewElement).fadeOut(300).remove();
        }, 500);

        // here we need to add the returned data into the files array
        if (typeof file.xhr != 'undefined') {
          let response = JSON.parse(file.xhr.response);
          if (response.file) {
            this.category.files.push(response.file);
          }
        }

        if (inline) {
          this.drop2.removeFile(file);
        }
      },

      /// drop zone
      //////


      mediaClose() {
        this.$root.media.active = false;
        this.drop.removeAllFiles();
      },

      mediaOpen() {
        this.$root.media.active = true;
        this.drop.removeAllFiles();
      },

      mediaDisplay(type) {
        this.$root.media.display = type;
      },

      mediaDropped(e) {
        if (typeof this.files[e.mediaId] != 'undefined' && typeof this.tree[e.categoryId] != 'undefined') {
          let file = this.files[e.mediaId];

          let newCategory = this.tree[e.categoryId];

          let oldCategory = this.tree[file.media_category_id];
          // find the media in the listing
          if (oldCategory.files.length) {
            oldCategory.files.forEach((f, index) => {
              if (f.id == file.id) {
                oldCategory.files.splice(index, 1);
              }
            });
          }

          // add the file to the new category
          file.media_category_id = e.categoryId;
          newCategory.files.push(file);

          // update the db
          axios
            .post('/refined/media/update-parent', {
              media: e.mediaId,
              media_category_id: e.categoryId,
            })
            .then(response => {})
            .catch(error => {
              console.log('Sort Error', error);
            })
          ;

        }
      },

      mediaLoad(item) {
        if (!this.modal) {
          this.file = item;
          this.tab = 'details-file';
          this.show.tabs.details.file = true;
          this.show.tabs.details.category = false;
          this.show.buttons.controls = true;
        } else {
          // fire an event for the chosen file
          const newItem = {...item};
          newItem.model = this.$root.media.model;
          eventBus.$emit('selecting-file', newItem);
        }
      },

      mediaSave() {

        let check   = /^.+[\s]{0,4}/,
            errors  = [],
            validationData = document.createElement('ul')
        ;

        // do the validation
        if (!check.test(this.file.name) || this.file.name == null) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please enter a Name';
          validationData.appendChild(child);
        }

        if (errors.length) {
          swal({
            title: 'You have some errors in your form.',
            content: validationData,
            icon: 'error',
            dangerMode: true,
          });
        } else {

          this.$root.loading = true;
          let config = {
            url: '/refined/media/'+this.file.id,
            method: 'PUT',
            data: this.file,
          }

          axios
            .request(config)
            .then(r => {
              this.$root.loading = false;
              if (r.data.success) {

                swal({
                  title: 'Success',
                  text: 'The file has been successfully saved',
                  icon: 'success'
                });

              }
              else {
                swal({
                  title: 'Something went wrong',
                  text: r.data.msg,
                  icon: 'error'
                });
              }
            })
            .catch(e => {
              console.log(e);
              this.$root.loading = false;
              swal({
                title: 'Something went wrong',
                text: e.message,
                icon: 'error'
              });
            })
          ;

        }
      },

      mediaDelete(item) {

        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        })
        .then((value) => {
          this.$root.loading = true;

          if (value) {
            axios
              .delete('/refined/media/'+item.id)
              .then(r => {
                this.$root.loading = false;

                if (r.data.success) {
                  // find the page and splice from parent
                  this.mediaFindAndRemove(this.categories, item);
                  this.loadFiles();

                } else {
                  swal({
                    title: 'Something went wrong',
                    text: r.data.msg,
                    icon: 'error'
                  });
                }
              })
              .catch(e => {
                console.log(e);
                this.$root.loading = false;
                swal({
                  title: 'Something went wrong',
                  text: e.message,
                  icon: 'error'
                });
              })
            ;
          }
        })
        ;
      },

      mediaFindAndRemove(items, item) {
        if (items.length) {
          items.forEach(category => {
            if (typeof category.files !== 'undefined' && category.files.length) {
              category.files.forEach((file, index) => {
                if (file.id === item.id) {
                  category.files.splice(index, 1);
                  delete this.files[item.id];
                }
              });
            }

            if (category.children.length) {
              this.mediaFindAndRemove(category.children, item);
            }
          });
        }

      },


      // copy url
      copyUrl() {
        let string = this.file.link.original;
        let el = document.createElement('textarea');

        el.value = string;

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');

        document.body.removeChild(el);

      },

      // clearing out the search
      clearSearch() {
        this.search = '';
      },

      close() {
        this.$root.media.showModal = false;
        this.$root.media.active = false;
        this.$root.media.model = 0;
        this.reset();
        this.scroll();
      },

      setType(type) {
        this.type = type;
        this.scroll();
      },

      reset() {
        this.search = '';
        this.type = '*';
        if (this.tree.length) {
          this.loadCategory(this.tree[1].children[0]);
        }
      }

    }
  }
</script>
