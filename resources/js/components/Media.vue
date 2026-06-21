<template>
  <div class="media-library" ref="root" :class="{ 'media__modal' : modal, 'media-library--active' : ui.media.showModal }">

    <div class="pages">
      <aside class="app__trigger" @click="mobileMenuActive = !mobileMenuActive">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M0 88C0 74.7 10.7 64 24 64l400 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 112C10.7 112 0 101.3 0 88zM0 248c0-13.3 10.7-24 24-24l400 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 272c-13.3 0-24-10.7-24-24zM448 408c0 13.3-10.7 24-24 24L24 432c-13.3 0-24-10.7-24-24s10.7-24 24-24l400 0c13.3 0 24 10.7 24 24z"/></svg>
      </aside>
      <aside class="pages__tree" :class="mobileMenuActive ? 'pages__tree--active' : ''">
        <aside class="app__trigger" @click="mobileMenuActive = !mobileMenuActive">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
        </aside>
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

              <rd-media-branch :data="holder" :id="-holder.id"></rd-media-branch>

            </li>
          </ul>
        </nav>
      </aside>
      <section class="pages__content">
        <div class="pages__header">
          <h3>{{ category.name }}</h3>
          <aside>
            <template v-if="show.buttons.controls">
              <template v-if="tab === 'details-category'">
                <a href="" class="button button--grey button--small" @click.prevent.stop="categorySave">Save</a>
                <a href="" class="button button--red button--small" @click.prevent.stop="categoryCancel">Cancel</a>
              </template>
              <template v-if="tab === 'details-file'">
                <a href="" class="button button--grey button--small" @click.prevent.stop="mediaSave">Save</a>
                <a href="" class="button button--red button--small" @click.prevent.stop="loadFiles">Cancel</a>
              </template>
            </template>

            <template v-if="!show.buttons.controls">
              <a href="" class="button button--grey button--small" @click.prevent.stop="mediaOpen">Add Files</a>
              <a href="" class="button button--grey button--small" @click.prevent.stop="categoryAdd">Add a Category</a>
              <a href="" class="button button--grey button--small" @click.prevent.stop="refresh" title="Reload the library from the server"><i class="fas fa-sync"></i></a>
            </template>

            <template v-if="modal">
              <span>|</span>
              <a href="" class="button button--red button--small" @click.prevent.stop="close">Close</a>
            </template>

            <template v-if="bulk.length && !show.buttons.controls && ui.media.display === 'list'">
              <span>|</span>
              <a href="" class="button button--red button--small" @click.prevent.stop="bulkDelete">Delete</a>
            </template>
          </aside>
        </div><!-- / header -->

        <div class="pages__tabs" v-if="!modal">
          <nav>
            <ul>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab === 'files') }" @click="loadFiles" v-if="show.tabs.files">Files</li>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab === 'details-file') }" @click="tab = 'details-file'" v-if="show.tabs.details.file">Details</li>
              <li class="pages__tab" :class="{ ' pages__tab--active' : (tab === 'details-category') }" @click="categoryShow" v-if="show.tabs.details.category">Details</li>
            </ul>
          </nav>
        </div>

        <div class="pages__info">

          <div class="pages__tab-pane" v-show="tab === 'details-category' && !search">
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

          <div class="pages__tab-pane" v-show="tab === 'details-file' && !search">
            <header class="pages__tab-pane-header">
              <h3>File Details</h3>
            </header>

            <div class="media__details">
              <div class="media__details-thumbnail">

                <rd-media-file :file="file" :site-url="siteUrl"></rd-media-file>

                <a :href="file.external_url || file.link.original" target="_blank" class="media__file-link">View File</a>
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
                        <input type="text" id="form--slug" :value="file.external_url || file.link.original" readonly required="required" class="form__control">
                        <span class="copy-url" @click="copyUrl"><i class="fas fa-link"></i></span>
                      </div>
                    </div>
                  </div><!-- / form row -->

                </div>

              </div>
            </div>

          </div>

          <div class="pages__tab-pane pages__tab-pane--full" v-show="tab === 'files' && !search">
            <div class="media-files">
              <header class="pages__tab-pane-header">
                <h3>File Listing</h3>
                <aside class="media__toggle">
                  <i class="fas fa-th-large" :class="{ 'media__toggle--active' : ui.media.display === 'thumb' }" @click="mediaDisplay('thumb')"></i>
                  <i class="fas fa-th-list" :class="{ 'media__toggle--active' : ui.media.display === 'list' }" @click="mediaDisplay('list')"></i>
                </aside>
              </header>

              <p v-if="category.files.length < 1">There are no files in this folder</p>

              <div class="media__files" :class="'media__files--'+ui.media.display" id="dropzone-2">
                <template v-for="file of category.files">
                  <div class="media__file"
                    v-draggable-media
                    :data-id="file.id"
                    v-if="type === file.type || type === '*' || (type === 'Image' && file.type === 'Video')"
                  >

                    <div class="media__file--control">
                        <div class="form__checkbox form__checkbox--no-input">
                            <input type="checkbox" v-model="bulk" :id="`item--${file.id}`" name="bulk[]" :value="file.id"/>
                            <label :for="`item--${file.id}`"></label>
                        </div>
                    </div>
                    <div class="media__file--item" @click="mediaLoad(file)">
                      <rd-media-file :file="file" :site-url="siteUrl"></rd-media-file>
                    </div>
                  </div>
                </template>

              </div><!-- / files -->

            </div><!-- / media files -->

            <div class="media__file--template">
              <div class="media__file media__file--uploading">
                <i class="spinner"></i>
                <figure>
                    <span class="media__file-thumb dz-image">
                      <img :src="`${siteUrl}/vendor/refined/core/img/ui/media-thumb.png`">
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
                  <rd-media-file :file="file" :site-url="siteUrl"></rd-media-file>
                </div>

              </div><!-- / files -->

            </div><!-- / media files -->

          </div>

        </div><!-- / info -->
      </section>


      <div class="media-uploader" v-show="ui.media.active">
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

<script setup>
  import { ref, reactive, computed, watch, onMounted, onUpdated, onUnmounted, nextTick, provide } from 'vue';
  import { storeToRefs } from 'pinia';
  import Dropzone from 'dropzone';
  import eventBus from '../eventBus';
  import { useUiStore } from '../stores/ui';
  import { useMediaStore } from '../stores/media';

  const props = defineProps({
    modal: {
      type: Boolean,
      default: null
    },
    maxFilesize: {
      type: Number,
      default: 10
    }
  });

  const ui = useUiStore();
  const media = useMediaStore();

  // persistent media data lives in the store (loaded once, mutated in place).
  // `category` is the active folder; `tree`/`files` are the store's flat indexes.
  const { categories, searchableFiles, activeCategory: category, leaf } = storeToRefs(media);
  const tree = media.tree;
  const files = media.files;

  const clone = (data) => JSON.parse(JSON.stringify(data));

  const root = ref(null);

  const tab = ref('details');
  const type = ref('*');
  const siteUrl = ref('');

  const search = ref('');

  const file = ref({
    name: '',
    alt: '',
    description: '',
    type: '',
    size: '',
    link: {
      original: '',
      thumb: ''
    },
    external_url: '',
    external_id: '',
  });

  let categoryClone = {};
  const bulk = ref([]);

  let sortable = null;
  let drop = null;
  let drop2 = null;

  const show = reactive({
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
  });

  const mobileMenuActive = ref(false);

  const filterFiles = computed(() => {
    return searchableFiles.value.filter(item => {
      let s = search.value.toLowerCase();
      let name = item.name.toLowerCase();

      if (type.value === item.type || type.value === '*') {
        return name.indexOf(s) !== -1;
      }

      return false;

    });
  });

  watch(search, (value) => {
    if (tab.value !== 'files' && value.length > 0) {
      loadFiles();
    }
  });

  // force a fresh fetch from the server (manual refresh button). the store
  // preserves the open folder where possible; re-sync the view to it.
  async function refresh() {
    ui.loading = true;
    try {
      await media.refresh();
      const current = category.value?.id ? media.tree[category.value.id] : null;
      if (current) {
        loadCategory(current);
      } else if (media.tree[1]?.children?.length) {
        loadCategory(media.tree[1].children[0]);
      }
    } finally {
      ui.loading = false;
    }
  }

  // show / hide the tree
  function toggleSubMenu(item) {
    if (item.children.length) {
      item.show = !item.show;
    }
  }

  // this just turns off all on pages
  function turnOffCategories() {
    for (let i in tree) {
      let branch = tree[i];
      if (branch.children.length < 1) {
        branch.show = false;
      }
      branch.on = false;
    }
  }

  function turnOnParents(cat) {
    if (cat.parent_id > 0 && typeof tree[cat.parent_id] != 'undefined') {
      cat.show = true;
      turnOnParents(tree[cat.parent_id]);
    }

    // always have the media category open
    if (cat.id === 1) {
      cat.show = true;
    }
  }

  function initSort() {
    if (sortable == null) {
      let elements = document.querySelectorAll('.media-library .tree__trunk--sortable');

      let containers = [];
      if (elements.length) {
        elements.forEach(element => {
          containers.push(element);
        })

        sortable = dragula(containers, {
          direction: 'vertical'
        })
        .on('drop', (e) => {
          let parent = document.querySelector('.media-library .tree__trunk[data-id="'+e.dataset.parent+'"');
          let newParent = e.closest('.tree__trunk');
          let children = [];
          let parentId = e.dataset.parent;

          // check to see if the parent has updated
          if (parent.dataset.id !== newParent.dataset.id) {
            // we have a different parent, need to update
            children = newParent.querySelectorAll(':scope > .tree__branch');
            parentId = parseInt(newParent.dataset.id);

            // find and update the new leaf
            if (typeof tree[e.dataset.id] != 'undefined') {
              media.moveCategory(parseInt(e.dataset.id), parentId);

              // now update the leaf in the db
              categoryUpdateParent(e.dataset.id, parentId);
            }

          } else {
            children = parent.querySelectorAll(':scope > .tree__branch');
          }

          if (children.length) {
            let ids = [];
            children.forEach(el => {
              ids.push(el.dataset.id);
            });

            reposition(ids, parentId);
          }

        })
        ;
      }
    }
  }

  function loadFiles() {
    tab.value = 'files';
    show.tabs.details.file = false;
    show.tabs.details.category = true;
    show.buttons.controls = false;
  }

  // finds the folder the particular page is in
  function findFolder() {
    let key = category.value.parent_id;
    if (typeof tree[key] != 'undefined' && tree[key].show === false) {
      tree[key].show = true;
    }
  }

  function scroll() {
    root.value.querySelector('.pages__info').scrollTop = 0;
  }

  //////
  /// category

  // load the page for editing
  function loadCategory(item) {
    turnOffCategories();
    scroll();

    category.value = item;
    category.value.on = true;
    category.value.show = true;
    show.tabs.files = true;
    show.tabs.details.category = true;
    show.buttons.categoryDelete = (!(category.value.files.length || category.value.children.length));
    categoryClose();

    turnOnParents(category.value);
    loadFiles();
  }

  // show the add category stuff
  function categoryShow() {
    show.buttons.controls = true;
    tab.value = 'details-category';
    categoryClone = clone(category.value);
  }

  function categoryAdd() {
    let newData = clone(leaf.value.category);
    newData.parent_id = category.value.id;
    loadCategory(newData);
    show.buttons.controls = true;
    show.buttons.categoryDelete = false;
    show.tabs.files = false;

    if (typeof tree[newData.parent_id] != 'undefined') {
      tree[newData.parent_id].on = true;
    }

    tab.value = 'details-category';
  }

  // removes the add category form
  function categoryCancel() {
    category.value.name = categoryClone.name;
    show.buttons.controls = false;
    show.tabs.files = true;
    categoryClone = {};
    loadFiles();
  }

  function categoryClose() {
    show.buttons.controls = false;
    show.tabs.files = true;
    categoryClone = {};
    tab.value = 'files';
  }

  function categorySave() {

    let check   = /^.+[\s]{0,4}/,
        errors  = [],
        validationData = document.createElement('ul')
    ;

    // do the validation
    if (!check.test(category.value.name) || category.value.name === null) {
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

      ui.loading = true;
      let config = {
        url: `${window.siteUrl}/refined/media/categories`,
        method: 'POST',
        data: category.value,
      }

      if (typeof category.value.newPage === 'undefined') {
        config.method = 'PUT';
        config.url += '/'+ category.value.id
      }

      axios
        .request(config)
        .then(r => {
          ui.loading = false;
          if (r.data.success) {

            if (typeof category.value.newPage !== 'undefined') {
              // we have just added a category, so insert it into the tree
              media.addCategory(r.data.leaf);

              // load the new category
              loadCategory(tree[r.data.leaf.id]);
              findFolder();
              categoryClose();
            }

            swal({
              title: 'Success',
              text: 'Category has been successfully saved',
              icon: 'success'
            });

            categoryClose();

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
          ui.loading = false;
          swal({
            title: 'Something went wrong',
            text: e.message,
            icon: 'error'
          });
        })
      ;

    }
  }

  function categoryDelete() {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    }).then(value => {
      if (value) {
      ui.loading = true;
        axios
          .delete(`${window.siteUrl}/refined/media/categories/${category.value.id}`)
          .then(r => {
            ui.loading = false;

            if (r.data.success) {

              // find the page and splice from parent
              categoryFindAndRemove(categories.value, category.value);

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
            ui.loading = false;
            swal({
              title: 'Something went wrong',
              text: e.message,
              icon: 'error'
            });
          })
        ;
      }
    });
  }

  // remove a category via the store, then load the fallback folder it returns
  function categoryFindAndRemove(items, item) {
    const fallback = media.removeCategory(item);
    if (fallback) {
      loadCategory(fallback);
    }
  }

  // run the db to reset position items
  function reposition(ids, parent) {
    if (Array.isArray(ids) && ids.length) {
      axios
        .post(`${window.siteUrl}/refined/media/categories/position`, {
          positions: ids,
          parent,
        })
        .catch(error => {
          console.log('Sort Error', error);
        })
      ;
    }

  }

  // updates the parent ids in the db
  function categoryUpdateParent(itemId, parentId) {
    let config = {
      url: `${window.siteUrl}/refined/media/categories/${itemId}/update-parent`,
      method: 'PUT',
      data: { parentId }
    };

    axios
      .request(config)
      .catch(error => {
        console.log('Sort Error', error);
      })
    ;
  }

  /// category
  //////

  //////
  /// drop zone
  function setDropZone() {

    if (drop == null) {
      let args = {
        url: `${window.siteUrl}/refined/media/upload-file`,
        acceptedFiles: 'image/*,.pdf,.docx,.doc,.xls,.xlsx,.zip,.mp4,.json',
        maxFilesize: props.maxFilesize,
        timeout: 300000,
        createImageThumbnails: true,
        previewsContainer: root.value.querySelector('.media-uploader__preview-listing'),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      };

      drop = new Dropzone(
        '#dropzone',
        args
      ).on('error', err => {
          dropError(err);
        })
        .on('sending', (f, xhr, data) => {
          dropSending(data);
        })
        .on('success', f => {
          dropSuccess(f);
        })
      ;

      let args2 = clone(args);
      args2.clickable = false;
      args2.previewTemplate = root.value.querySelector('.media__file--template').innerHTML
      delete args2.previewsContainer;

      drop2 = new Dropzone(
        '#dropzone-2',
        args2
      ).on('error', err => {
          dropError(err);
        })
        .on('sending', (f, xhr, data) => {
          dropSending(data);
        })
        .on('success', f => {
          dropSuccess(f, true);
        })
      ;
    }
  }

  function dropError(err) {
    if (err.status === 'error') {
      const error = err.previewElement.querySelector('.dz-error-mark');
      if (error) {
        error.addEventListener('click', () => {
          $(err.previewElement).fadeOut(300).remove();
        })
      }
      if (typeof err.xhr != 'undefined') {
        let msg = JSON.parse(err.xhr.response);
        let message = '';
        if (typeof msg.exception != 'undefined') {
          message = msg.message ? msg.message : msg.exception;

          if (message === 'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException') {
            message = 'Route not found, please contact support';
          }

        }

        if (message) {
          err.previewElement.querySelector('[data-dz-errormessage]').innerText = message;
        }
      }
    }
  }

  function dropSending(data) {
    data.append('media_category_id', category.value.id);
  }

  function dropSuccess(f, inline = false) {
    if (!f.type.match('image/')) {
      f.previewElement.querySelector('.dz-image').classList.add('dz-file');
    }

    setTimeout(() => {
      $(f.previewElement).fadeOut(300).remove();
    }, 500);

    // push the uploaded file into the store (no refetch needed)
    if (typeof f.xhr != 'undefined') {
      let response = JSON.parse(f.xhr.response);
      if (response.file) {
        if (!response.file.media_category_id) {
          response.file.media_category_id = category.value.id;
        }
        media.addFile(response.file);
      }
    }

    if (inline) {
      drop2.removeFile(f);
    }
  }

  /// drop zone
  //////


  function mediaClose() {
    ui.media.active = false;
    const element = document.querySelector('#app');
    if (element && element.classList.contains('app--has-media')) {
      element.classList.remove('app--has-media');
    }
    drop.removeAllFiles();
  }

  function mediaOpen() {
    ui.media.active = true;
    const element = document.querySelector('#app');
    if (element && !element.classList.contains('app--has-media')) {
      element.classList.add('app--has-media');
    }
    drop.removeAllFiles();
  }

  function mediaDisplay(t) {
    ui.media.display = t;
  }

  function mediaDropped(e) {
    if (typeof files[e.mediaId] != 'undefined' && typeof tree[e.categoryId] != 'undefined') {
      // move in the store (updates both categories + indexes)
      media.moveFile(e.mediaId, e.categoryId);

      // update the db
      axios
        .post(`${window.siteUrl}/refined/media/update-parent`, {
          media: e.mediaId,
          media_category_id: e.categoryId,
        })
        .then(response => {})
        .catch(error => {
          console.log('Sort Error', error);
        })
      ;

    }
  }

  function mediaLoad(item) {
    if (!props.modal) {
      file.value = item;
      tab.value = 'details-file';
      show.tabs.details.file = true;
      show.tabs.details.category = false;
      show.buttons.controls = true;
    } else {
      // fire an event for the chosen file
      const newItem = {...item};
      newItem.model = ui.media.model;
      eventBus.emit('selecting-file', newItem);
    }
  }

  function mediaSave() {

    let check   = /^.+[\s]{0,4}/,
        errors  = [],
        validationData = document.createElement('ul')
    ;

    // do the validation
    if (!check.test(file.value.name) || file.value.name == null) {
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

      ui.loading = true;
      let config = {
        url: `${window.siteUrl}/refined/media/${file.value.id}`,
        method: 'PUT',
        data: file.value,
      }

      axios
        .request(config)
        .then(r => {
          ui.loading = false;
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
          ui.loading = false;
          swal({
            title: 'Something went wrong',
            text: e.message,
            icon: 'error'
          });
        })
      ;

    }
  }

  function mediaUpdated(item) {
    media.updateFile(item);

    if (file.value.id == item.id && item.external_url) {
      file.value.external_url = item.external_url;
      file.value.external_id = item.external_id;
    }
  }

  function mediaDelete(item) {

    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    })
    .then((value) => {
      ui.loading = true;

      if (value) {
        axios
          .delete(`${window.siteUrl}/refined/media/${item.id}`)
          .then(r => {
            ui.loading = false;

            if (r.data.success) {
              // find the page and splice from parent
              mediaFindAndRemove(categories.value, item);
              loadFiles();

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
            ui.loading = false;
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
  }

  function bulkDelete() {
    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    })
    .then((value) => {
      if (value) {
        ui.loading = true;
        axios
          .post(`${window.siteUrl}/refined/media/bulk`, {
            ids: bulk.value,
          })
          .then(r => {
            ui.loading = false;
            if (r.data.success) {
              // find the items
              const items = bulk.value.map(id => {
                return files[id] || null
              }).filter(item => !!item);

              if (items.length) {
                items.forEach(item => {
                  // find the page and splice from parent
                  mediaFindAndRemove(categories.value, item);
                })
              }

              loadFiles();
              bulk.value = [];

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
            ui.loading = false;
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
  }

  function mediaFindAndRemove(items, item) {
    media.removeFile(item);
  }


  // copy url
  function copyUrl() {
    let string = file.value.link.original;
    let el = document.createElement('textarea');

    el.value = string;

    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');

    document.body.removeChild(el);

  }

  // clearing out the search
  function clearSearch() {
    search.value = '';
  }

  function close() {
    ui.media.showModal = false;
    ui.media.active = false;
    ui.media.model = 0;
    reset();
    scroll();
    document.querySelector('body').classList.remove('body-has-modal');
  }

  function clear() {
    close();
    ui.media.display = 'thumb';
    ui.media.model = null;
    ui.media.fieldId = null;
    ui.media.type = 'image';
  }

  function setType(t) {
    type.value = t;
    scroll();
  }

  // only clear the transient filters on close — the open folder is kept so the
  // modal reopens exactly where the user left it.
  function reset() {
    search.value = '';
    type.value = '*';
  }

  // created: load the tree once (no-op on subsequent modal opens)
  siteUrl.value = window.siteUrl;
  ui.loading = true;
  media.load().finally(() => {
    ui.loading = false;
    // open the first real category the first time only
    if (!category.value?.id && media.tree[1]?.children?.length) {
      loadCategory(media.tree[1].children[0]);
    }
  });

  onMounted(() => {
    eventBus.on('media-close', close);
    eventBus.on('media-set-type', setType);
    // NB: 'media-reload' is intentionally not handled — the library loads once
    // and is kept fresh by in-place store mutations. Use the Refresh button to
    // force a server refetch.
    eventBus.on('media-clear', clear);
    eventBus.on('media-updated', mediaUpdated);
    eventBus.on('media-dropped', mediaDropped);

    setDropZone();
  });

  onUpdated(() => {
    nextTick(() => {
      initSort();
    });
  });

  onUnmounted(() => {
    eventBus.off('media-close', close);
    eventBus.off('media-set-type', setType);
    eventBus.off('media-clear', clear);
    eventBus.off('media-updated', mediaUpdated);
    eventBus.off('media-dropped', mediaDropped);
  });

  // recursive MediaBranch children inject these instead of walking the parent chain
  provide('media:loadCategory', loadCategory);
  provide('media:toggleSubMenu', toggleSubMenu);
</script>
