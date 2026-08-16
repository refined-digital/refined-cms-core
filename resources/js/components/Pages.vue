<template>
  <Teleport to="body">
  <div class="pages-workspace">
    <header class="pages-workspace__header">
      <div class="pages-workspace__header-left">
        <button class="pages-workspace__tree-toggle" :class="{ 'is-open': treeOpen }" @click="treeOpen = !treeOpen" title="Toggle sitemap">
          <i class="fa fa-bars"></i>
        </button>
        <h3>{{ page.name }}</h3>
      </div>

      <nav class="pages-workspace__tabs">
        <template v-for="item in defaultTabs">
          <button
            v-if="showDefaultTab(item)"
            :class="{ 'is-active': tab === item.tab }"
            @click="switchTab(item.tab)"
          >{{ item.name }}</button>
        </template>
        <template v-for="item in tabs">
          <button
            v-if="showTab(item)"
            :class="{ 'is-active': tab === item.tab }"
            @click="switchTab(item.tab)"
          >{{ item.name }}</button>
        </template>
        <button :class="{ 'is-active': tab === 'meta' }" @click="switchTab('meta')">Meta</button>
      </nav>

      <aside class="pages-workspace__actions">
        <span class="page-builder__error" v-if="previewError" :title="previewError">
          <i class="fa fa-exclamation-triangle"></i> Preview error
        </span>
        <a href="" class="button button--grey button--small" @click.prevent.stop="savePage">Save</a>
        <template v-if="!page.newPage">
          <a href="" class="button button--grey button--small" @click.prevent.stop="viewPage">View</a>
          <a href="" class="button button--grey button--small" @click.prevent.stop="addPage">Add a Page</a>
          <a href="" class="button button--grey button--small" @click.prevent.stop="duplicatePage">Duplicate</a>
        </template>
        <template v-if="page.newPage">
          <a href="" class="button button--red button--small" @click.prevent.stop="cancelPage">Cancel</a>
        </template>
        <template v-if="page.id > 1 && !page.newPage">
          <a href="" class="button button--red button--small" @click.prevent.stop="deletePage">Delete</a>
        </template>
        <a :href="exitUrl" class="button button--small pages-workspace__exit" title="Back to admin"><i class="fa fa-sign-out-alt"></i> Exit</a>
      </aside>
    </header>

    <div class="pages-workspace__body">
      <aside class="pages-workspace__tree" v-show="treeOpen">
        <nav class="tree pages-workspace__tree-nav">
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

        <footer class="pages-workspace__tree-footer" v-if="moduleLinks && moduleLinks.length">
          <a :href="link.url" v-for="link in moduleLinks" :key="link.url">
            <i class="fa fa-cog"></i> {{ link.name }}
          </a>
        </footer>
      </aside>

      <section class="pages-workspace__panel">
        <div class="pages-workspace__pane" v-show="tab === 'content'">
          <div class="form__note pages-workspace__pane-note" v-if="page.newPage">
            Save the page details first &mdash; the content editor needs a saved page.
          </div>
          <rd-page-builder-panel
            v-else
            :page="page"
            :content="content"
            :config="config"
            :key="`panel--${page.id}`"
            v-model:selected-index="selectedIndex"
            @field-input="onFieldInput"
            @block-hover="onBlockHover"
            @open-block-modal="blockModalOpen = true"
          ></rd-page-builder-panel>
        </div>

        <div class="pages-workspace__pane" v-show="tab === 'details'">
          <h3>Page Details</h3>

          <div class="form">
            <div class="form__row">
              <label for="form--menu-text" class="form__label">Menu Text</label>
              <input type="text" id="form--menu-text" v-model="page.name" required="required" class="form__control" @keyup="updateSlug()">
              <div class="form__note">This is used for the text in the menu.</div>
            </div><!-- / form row -->

            <div class="form__row" v-if="page.id > 1 || page.newPage">
              <label for="form--active" class="form__label">Show Page</label>
              <select id="form--active" v-model="page.active" required="required" class="form__control">
                <option :value="1">Yes</option>
                <option :value="0">No</option>
              </select>
              <div class="form__note">Do you want the page to be viewed by the public? Set to No for draft mode.</div>
            </div><!-- / form row -->

            <div class="form__row">
              <label for="form--hide-from-menu" class="form__label">Show in Menus</label>
              <select id="form--hide-from-menu" v-model="page.hide_from_menu" required="required" class="form__control">
                <option :value="1">No</option>
                <option :value="0">Yes</option>
              </select>
              <div class="form__note">Do you want the page to be shown in menus?</div>
            </div><!-- / form row -->

            <template v-if="page.id > 1 || page.newPage">

              <div class="form__row">
                <label for="form--hide-from-sitemap" class="form__label">Show in Sitemap.xml</label>
                <select id="form--hide-from-sitemap" v-model="page.hide_from_sitemap" required="required" class="form__control">
                  <option :value="1">No</option>
                  <option :value="0">Yes</option>
                </select>
                <div class="form__note">Do you want the page to be shown in the sitemap.xml?</div>
              </div><!-- / form row -->

              <div class="form__row">
                <label for="form--page-type" class="form__label">Page Type</label>
                <select id="form--page-type" v-model="page.page_type" required="required" class="form__control">
                  <option :value="1">Page</option>
                  <option :value="0">Holder</option>
                  <option :value="2">Redirect to Home Page</option>
                </select>
                <div class="form__note">
                  Page: A standard page<br/>
                  Holder: A menu item, not a page<br/>
                  Redirect: Sets a session and redirects to the home page</div>
              </div><!-- / form row -->

              <div class="form__row">
                <label for="form--template" class="form__label">Template</label>
                <select id="form--template" v-model="page.meta.template_id" required="required" class="form__control">
                  <option :value="item.id" v-for="item in templates">{{ item.name }}</option>
                </select>
                <div class="form__note">Which layout do you need for this page?</div>
              </div><!-- / form row -->

            </template>

            <div class="form__row" v-if="showForms()">
              <label for="form--form" class="form__label">Form</label>
              <select id="form--form" v-model="page.form_id" required="required" class="form__control">
                <option :value="item.id" v-for="item in forms">{{ item.name }}</option>
              </select>
              <div class="form__note">Which form do you want to display on this page?</div>
            </div><!-- / form row -->
          </div><!-- / form -->
        </div><!-- / details -->

        <div class="pages-workspace__pane" v-show="tab === 'meta'">
          <h3>Meta Data</h3>

          <div class="form">

            <div class="form__row">
              <label for="form--slug" class="form__label">Page URL</label>
              <div class="form__control--url">
                <span>{{ url }}</span>
                <input type="text" id="form--slug" v-model="page.meta.uri" readonly required="required" class="form__control">
                <span class="copy-url" @click="copyUrl"><i class="fas fa-link"></i></span>
              </div>
            </div><!-- / form row -->

            <div class="form__row">
              <label for="form--title" class="form__label">Page Title</label>
              <input type="text" id="form--title" v-model="page.meta.title" required="required" class="form__control">
              <div class="form__note">
                This area appears in the title of the browser<br/>
                A maximum of 70 characters is allowed
              </div>
            </div><!-- / form row -->

            <div class="form__row">
              <label for="form--desc" class="form__label">Meta Description</label>
              <textarea id="form--desc" v-model="page.meta.description" required="required" class="form__control"></textarea>
              <div class="form__note">
                This area is used to describe the business to search engines<br>A maximum of <code>160</code> characters is allowed
              </div>
            </div><!-- / form row -->

          </div><!-- / form -->
        </div>

        <div class="pages-workspace__pane" v-show="tab === 'settings'">
          <h3>Page Settings</h3>
          <rd-pages-settings :settings="page.settings" :page="page"></rd-pages-settings>
        </div>

        <template v-for="item in tabs">
          <div class="pages-workspace__pane" v-show="tab === item.tab" v-if="!item.default">
            <h3>{{ item.name }}</h3>
            <template v-if="item.type === 'repeatable'">
              <rd-pages-repeatable
                :item="item"
                :data="page.data[item.tab]"
                :fields="item.fields"
                :model-id="page.id"
              ></rd-pages-repeatable>
            </template>

            <template v-if="item.type === 'fields'">
              <div class="pages__content-editor">

                <div class="content-editor__fields form">
                  <div class="content-editor__field" v-for="(field, index) in item.fields">

                    <div class="form__row" v-if="page.data[item.tab]">

                      <div class="form__label form__label--with-controls">
                        <label :for="'form--content-'+field.id" v-if="!field.hide_label">{{ field.name }}</label>
                      </div>

                      <rd-content-editor-field :item="page.data[item.tab][field.index]"></rd-content-editor-field>

                    </div><!-- / form row -->
                  </div><!-- / field -->
                </div><!-- / fields -->
              </div><!-- content editor -->
            </template>
          </div>
        </template>
      </section>

      <section class="pages-workspace__preview">
        <iframe
          v-if="page.id && !page.newPage"
          ref="frame"
          :src="previewUrl"
          :key="`preview--${page.id}`"
          title="Page preview"
        ></iframe>
        <div class="pages-workspace__preview-empty" v-else>
          <p>Save the page details to see a live preview.</p>
        </div>
      </section>
    </div>

    <rd-add-block-modal
      v-if="blockModalOpen"
      :content="content"
      @add="addBlockFromModal"
      @close="blockModalOpen = false"
    ></rd-add-block-modal>
  </div><!-- / pages workspace -->
  </Teleport>
</template>

<script setup>
  import { ref, reactive, computed, watch, nextTick, provide, onUpdated, onUnmounted } from 'vue';
  import swal from 'sweetalert';
  import naturalSort from 'javascript-natural-sort';
  import _ from 'lodash';

  import { useUiStore } from '../stores/ui';
  import { usePagesImageNote } from '../composables/usePagesImageNote';
  import { usePagesRepeatable } from '../composables/usePagesRepeatable';
  import { formatContent } from '../composables/useContentBlocks';

  const props = defineProps(['siteUrl', 'publicUrl', 'config', 'modules', 'content', 'moduleLinks']);

  const moduleLinks = props.moduleLinks;

  const ui = useUiStore();

  const clone = (data) => JSON.parse(JSON.stringify(data));

  const tab = ref('details');

  const page = ref({
    id: 0,
    meta: {
      template_id: 0,
    },
    data: {},
    content: []
  });

  const leaf = ref({});
  let sortables = null;

  const pages = ref([]);
  const flatPages = reactive({});
  const templates = ref([]);
  const forms = ref([]);
  const formTemplates = ref([]);
  const contentTypes = ref([]);
  const parents = ref([]);
  const parent = reactive({
    updated: false,
    currentParent: null,
    newParent: null
  });

  let contentSort = null;

  const url = ref(null);

  const defaultTabs = ref([
    { tab: 'content', name: 'Content' },
    { tab: 'details', name: 'Details' },
    { tab: 'settings', name: 'Settings' },
  ]);

  const tabs = ref([]);

  // workspace state
  const treeOpen = ref(true);
  const blockModalOpen = ref(false);
  const selectedIndex = ref(null);

  const exitUrl = `${window.siteUrl}/refined/dashboard`;

  // compose the old mixins
  const { getImageNote } = usePagesImageNote(page);
  const {
    addRepeatable,
    pageContentAsArray,
    getRepeatableConfigField,
    getRepeatableFieldNote,
    getRepeatableFieldIndex,
  } = usePagesRepeatable(page, { getImageNote });

  // child PagesBranch / PagesRepeatable components inject these; the loadPage
  // provided to the tree is guarded so unsaved content edits aren't lost
  provide('pages:loadPage', guardedLoadPage);
  provide('pages:toggleSubMenu', toggleSubMenu);
  provide('pages:addRepeatable', addRepeatable);

  ////////////////////////////////
  //// live preview channel
  ///////////////////////////////

  const frame = ref(null);
  const previewError = ref(null);
  const previewReady = ref(false);
  // latest-wins: responses from superseded requests are dropped
  let renderSequence = 0;
  // snapshot of the page's content when it was loaded/saved, for dirty checks
  let contentSnapshot = '[]';

  const previewUrl = computed(() => `${window.siteUrl}/refined/pages/${page.value.id}/preview`);

  function postToFrame(message) {
    if (frame.value && frame.value.contentWindow) {
      frame.value.contentWindow.postMessage(message, window.location.origin);
    }
  }

  async function renderPreview() {
    const sequence = ++renderSequence;

    try {
      const response = await axios.post(previewUrl.value, { content: page.value.content });

      if (sequence !== renderSequence) {
        return;
      }

      if (response.data.success) {
        previewError.value = null;
        postToFrame({ type: 'rcms:update', html: response.data.html });
      } else {
        // keep the last good preview, just flag it
        previewError.value = response.data.msg || 'The preview failed to render';
      }
    } catch (e) {
      if (sequence === renderSequence) {
        previewError.value = e.message;
      }
    }
  }

  const debouncedRender = _.debounce(renderPreview, 150);

  watch(
    () => page.value.content,
    () => {
      if (previewReady.value) {
        debouncedRender();
      }
    },
    { deep: true }
  );

  watch(selectedIndex, (index) => {
    postToFrame({ type: 'rcms:select', index });
  });

  // switching pages loads a fresh iframe; wait for its shim to say hello again
  watch(() => page.value.id, () => {
    previewReady.value = false;
    previewError.value = null;
    selectedIndex.value = null;
  });

  // block labels for the preview's hover/selection tag: the block name, plus
  // its anchor id when the page anchors config is on (old Content tab format)
  const blockLabels = computed(() => {
    const labels = {};
    const anchors = props.config?.show_page_anchors;

    (page.value.content || []).forEach((block, index) => {
      labels[index] = {
        name: block.name || 'Block',
        anchor: anchors?.enabled ? `#${anchors.class || ''}${index}` : null,
      };
    });

    return labels;
  });

  function sendBlockMeta() {
    postToFrame({ type: 'rcms:block-meta', labels: blockLabels.value });
  }

  watch(blockLabels, () => {
    if (previewReady.value) sendBlockMeta();
  });

  // instant client-side echo for cheap text fields; the debounced server
  // render reconciles right behind it
  function onFieldInput({ index, field, value }) {
    postToFrame({ type: 'rcms:echo', index, field, value });
  }

  function onBlockHover(index) {
    postToFrame({ type: 'rcms:hover', index });
  }

  function onMessage(event) {
    if (event.origin !== window.location.origin || !event.data || event.data.source !== 'rcms-preview') {
      return;
    }

    switch (event.data.type) {
      case 'rcms:ready':
        previewReady.value = true;
        sendBlockMeta();
        // sync any admin-side content the saved preview doesn't have yet
        renderPreview();
        break;
      case 'rcms:block-click':
        tab.value = 'content';
        selectedIndex.value = event.data.index;
        break;
    }
  }

  window.addEventListener('message', onMessage);

  function addBlockFromModal(item) {
    const block = formatContent(_.cloneDeep(item));
    page.value.content = [...(page.value.content || []), block];
    blockModalOpen.value = false;
    selectedIndex.value = page.value.content.length - 1;
  }

  function takeContentSnapshot() {
    contentSnapshot = JSON.stringify(page.value.content || []);
  }

  function contentIsDirty() {
    return JSON.stringify(page.value.content || []) !== contentSnapshot;
  }

  // wraps loadPage with an unsaved-content check; provided to the page tree
  function guardedLoadPage(item) {
    if (item === page.value) {
      return;
    }

    if (!contentIsDirty()) {
      loadPage(item);
      return;
    }

    swal({
      title: 'Discard unsaved changes?',
      text: 'This page has content changes that have not been saved.',
      icon: 'warning',
      buttons: ['Keep editing', 'Discard'],
      dangerMode: true,
    }).then(discard => {
      if (discard) {
        page.value.content = JSON.parse(contentSnapshot);
        loadPage(item);
      }
    });
  }

  ////////////////////////////////
  //// page
  ///////////////////////////////

  // switch tab
  function switchTab(t) {
    tab.value = t;
  }

  function showDefaultTab(t) {
    if (t.name === 'Content' && props.config.content && props.config.content.length < 1) {
      return false;
    }

    if (t.name === 'Settings') {
      return page.value.settings && page.value.settings.length;
    }

    return true;
  }

  function showTab(t) {
    let show = false;

    if (typeof t.active === 'object') {
      if (page.value.id === 1 && typeof t.active.home !== 'undefined' && t.active.home) {
        show = true;
      }

      if (page.value.id !== 1 && typeof t.active.internal !== 'undefined' && t.active.internal) {
        show = true;
      }

      if (typeof t.active.show_on_pages !== 'undefined') {
        show = false;

        if (t.active.show_on_pages.indexOf(page.value.id) > -1) {
          show = true;
        }
      }

      if (typeof t.active.hide_on_pages !== 'undefined' && t.active.hide_on_pages.indexOf(page.value.id) > -1) {
        show = false;
      }

    } else if (typeof t.active === 'boolean') {
      show = t.active;
    }


    return show;
  }

  // show / hide the tree
  function toggleSubMenu(item) {
    if (item.children.length || item.type == 'holder') {
      item.show = !item.show;
    }
  }

  // load the page for editing
  function loadPage(item) {
    turnOffPages(pages.value);

    page.value = item;
    page.value.on = true;

    // content editing needs a saved page; new pages start on details
    tab.value = item.newPage ? 'details' : 'content';
    selectedIndex.value = null;

    if (page.value.data == null) {
      page.value.data = {};
      addFieldData();
    } else {
      resetPageData();
    }

    // if the page has children, auto show the child menu
    if (item.children.length) {
      page.value.show = true;
    }

    // set the url string
    setUrl();

    // set current parent
    setCurrentParent();

    // reset the parent listing, this is so we can re-enable any page that is hidden as to not add the page to be a parent of its self
    resetParents();

    // record the loaded content so page switches can warn about unsaved edits.
    // the panel normalises ids onto the content on mount, so snapshot after
    nextTick(() => takeContentSnapshot());

    contentSort = dragula([document.querySelector('.content-editor__fields')], {
      direction: 'vertical',
      moves: (el, container, handle) => {
        return handle.classList.contains('fa-sort');
      }
    })
    .on('drop', () => {
      let fields = document.querySelectorAll('.content-editor__field');

      if (fields.length) {
        fields.forEach((field, index) => {
          if (!field.classList.contains('gu-mirror')) {
            page.value.content.forEach(content => {
              if (content.id == field.dataset.id) {
                content.position = index;
              }
            })
          }
        })
      }

    });

  }

  // this just turns off all on pages
  function turnOffPages(pagesList) {
    if (pagesList.length) {
      pagesList.forEach(p => {
        if (p.type == 'page' && p.on) {
          p.on = false;
        }

        if (p.children.length) {
          turnOffPages(p.children);
        }
      });
    }
  }

  // updates the parent listing
  function resetParents() {
    addParents(pages.value, 0);
  }

  // set the parents
  function addParents(items, depth) {
    if (items.length) {
      items.forEach(item => {
        // add to the flat listing
        if (item.type == 'holder') {
          flatPages['-'+item.id] = item;
        } else {
          flatPages[item.id] = item;
        }

        if (item.children.length) {
          addParents(item.children, depth + 1);
        }

      });
    }
  }

  // copy url
  function copyUrl() {
    let string = url.value + page.value.meta.uri;
    let el = document.createElement('textarea');

    el.value = string;

    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');

    document.body.removeChild(el);

  }

  // set the url for the meta section
  function setUrl() {
    let urlBits = [window.publicUrl];
    if (page.value.parent_id != 0) {
      // find the parent
      let parentUri = findParentUri(page.value.parent_id);
      if (parentUri) {
        urlBits.push(parentUri);
      }
    }
    url.value = urlBits.join('/') + '/';
  }

  // finds the parent page
  function findParentUri(id) {
    let parentUri = false;
    parents.value.forEach(p => {
      if (p.id == id) {
        parentUri = p.uri;
        return;
      }
    });

    return parentUri;
  }

  // finds the folder the particular page is in
  function findFolder() {
    let key = -page.value.page_holder_id;
    if (typeof flatPages[key] != 'undefined' && flatPages[key].show == false) {
      flatPages[key].show = true;
    }
  }

  // load the add form
  function addPage() {
    let newData = clone(leaf.value);
    newData.parent_id = page.value.id == 1 ? -1 : page.value.id;
    newData.page_holder_id = page.value.page_holder_id;

    guardedLoadPage(newData);
  }

  // delete the page
  function deletePage() {

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
          .delete(`${window.siteUrl}/refined/pages/${page.value.id}`)
          .then(r => {
            ui.loading = false;

            if (r.data.success) {
              // find the page and splice from parent
              findAndRemove(pages.value, page.value);
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

  // duplicate the page
  function duplicatePage() {
    ui.loading = true;

    axios
      .post(`${window.siteUrl}/refined/pages/${page.value.id}/duplicate`)
      .then(r => {
        ui.loading = false;

        if (r.data.success) {
          // Add the duplicated page to the tree
          flatPages[r.data.leaf.id] = r.data.leaf;

          if (typeof flatPages[page.value.parent_id] != 'undefined') {
            flatPages[page.value.parent_id].children.push(r.data.leaf);
          }

          // Load the duplicated page
          loadPage(flatPages[r.data.leaf.id]);
          findFolder();

          // Reload the parents list
          resetParents();

          swal({
            title: 'Success',
            text: 'Page has been successfully duplicated',
            icon: 'success'
          });
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

  // view page
  function viewPage() {
    let u = url.value + page.value.meta.uri;
    let i = 0;
    while(u.endsWith('/')) {
      u = u.slice(0, -1);
      i += 1;
      if (i > 5) {
        break;
      }
    }
    window.open(u);
  }

  // save the page
  function savePage() {
    ui.loading = true;
    let config = {
      url: `${window.siteUrl}/refined/pages`,
      method: 'POST',
      data: {
        page: page.value,
        parent: parent
      }
    }

    if (typeof page.value.newPage == 'undefined') {
      config.method = 'PUT';
      config.url += '/'+ page.value.id
    }

    axios
      .request(config)
      .then(r => {
        ui.loading = false;
        if (r.data.success) {
          if (typeof page.value.newPage != 'undefined') {
            // we have just added a page, so insert it into the menu
            flatPages[r.data.leaf.id] = r.data.leaf;
            if (typeof flatPages[page.value.parent_id] != 'undefined') {
              flatPages[page.value.parent_id].children.push(r.data.leaf);
            }

            // load the page
            loadPage(flatPages[r.data.leaf.id]);
            findFolder();
          }

          if (parent.updated) {
            moveLeaf(pages.value, page.value);
          }

          // reload the parents list
          resetParents();

          // the saved content is the new baseline for dirty checks
          takeContentSnapshot();

          swal({
            title: 'Success',
            text: 'Page has been successfully saved',
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

  // cancel page
  function cancelPage() {
    loadParent(page.value);
  }


  // find the page in the parent and remove
  function findAndRemove(pagesList, item) {
    if (pagesList.length) {
      pagesList.forEach((p, index) => {
        if (p.type == 'page' && item.id == p.id) {
          pagesList.splice(index, 1);
          // set the first page in the list to the new active page
          if (pagesList.length) {
            loadPage(pagesList[0]);
          } else {
            // set the parent
            loadParent(item);
          }

        }

        if (p.children.length) {
          findAndRemove(p.children, item);
        }
      });
    }

  }

  // loads the parent item, used when deleting
  function loadParent(item) {
    if (typeof flatPages[item.parent_id] != 'undefined') {
      // if we have a page, set the first child of the parent
      if (item.parent_id > -1) {
        loadPage(flatPages[item.parent_id]);
      } else {
        // the folder is empty, so lets go and load the home page
        loadPage(flatPages[1]);

        // close the folder
        if (flatPages[item.parent_id].children.length < 1) {
          flatPages[item.parent_id].show = false;
        }

      }
    }
  }

  // updates the uri slug
  function updateSlug() {
    page.value.meta.uri = slugify(page.value.name);
  }

  // sets the current parent data before the update, used for moving
  function setCurrentParent() {
    parent.updated = false;
    parent.currentParent = page.value.parent_id;
    parent.newParent = null;
  }

  // updates the parent ids in the db
  function updateParentDB(item) {
    let config = {
      url: `${window.siteUrl}/refined/pages/${item.id}/update-parent`,
      method: 'PUT',
      data: {
        page: {
          parent_id: Math.abs(parseInt(item.parent_id)),
          page_holder_id: Math.abs(parseInt(item.page_holder_id)),
        },
        parent: {
          currentParent: Math.abs(parseInt(parent.currentParent)),
          newParent: Math.abs(parseInt(parent.newParent)),
          updated: parent.updated
        }
      }
    };

    axios
      .request(config)
      .then(response => {})
      .catch(error => {
        console.log('Sort Error', error);
      })
    ;
  }

  // moves the leaf to the correct folder / page
  function moveLeaf(pagesList, item, reposition = true) {
    if (pagesList.length) {
      pagesList.forEach(p => {
        let found = false;
        let parentId = null;
        let pageId = null;

        if (p.id == item.parent_id && p.type == 'page') {
          found = true;
          parentId = item.parent_id;
          pageId = p.id;
        }

        // need to do the moving if its changing holders
        if (item.parent_id < 0 && p.id == Math.abs(item.parent_id) && p.type == 'holder') {
          found = true;
          parentId = item.parent_id;
          pageId = -p.id;
          findFolder();
        }

        if (found == true && pageId != null && parentId != null) {

          flatPages[pageId].children.push(item);
          flatPages[pageId].show = true;

          if (!reposition) {
            // update the order if by drag drop
            flatPages[pageId].children.sort((a, b) => {
              return naturalSort(a.position, b.position);
            });
          }

          // remove from the current parent
          if (flatPages[parent.currentParent].children.length) {
            flatPages[parent.currentParent].children.forEach((child, index) => {
              if (child.id == item.id) {
                flatPages[parent.currentParent].children.splice(index, 1);
              }
            });
          }

          // save the new position
          if (flatPages[parentId].children.length && reposition) {
            let ids = [];

            flatPages[parentId].children.forEach(child => {
              ids.push(child.id);
            });

            if (ids.length) {
              repositionPages(ids, pageId);
            }
          }

          if (reposition) {
            loadPage(item);
          }

        }


        if (p.children.length) {
          moveLeaf(p.children, item);
        }
      });
    }

  }

  // boot up the sorting for tree branches
  function initSort() {
    if (sortables == null) {
      let elements = document.querySelectorAll('.pages-workspace__tree .tree__trunk--sortable');

      let containers = [];
      if (elements.length) {
        elements.forEach(element => {
          containers.push(element);
        })

        sortables = dragula(containers, {
          direction: 'vertical'
        })
        .on('drop', (e) => {
          let parentEl = document.querySelector('.tree__trunk[data-id="'+e.dataset.parent+'"');
          let newParent = e.closest('.tree__trunk');
          let children = [];
          let parentId = e.dataset.parent;

          // check to see if the parent has updated
          if (parentEl?.dataset.id != newParent?.dataset.id) {
            // we have a different parent, need to update
            children = newParent.querySelectorAll(':scope > .tree__branch');
            parentId = parseInt(newParent.dataset.id);

            // find and update the new leaf
            if (typeof flatPages[e.dataset.id] != 'undefined') {
              flatPages[e.dataset.id].parent_id = parentId;
              // only if the parent is a holder update it
              if (parentId < 0) {
                flatPages[e.dataset.id].page_holder_id = Math.abs(parentId);
                // reset the parent id to 0 if the holder is different
                flatPages[e.dataset.id].parent_id = 0;
              }

              // now update the leaf in the db
              parent.updated = true;
              parent.currentParent = parentEl.dataset.id;
              parent.newParent = newParent.dataset.id;
              updateParentDB(flatPages[e.dataset.id]);
            }

          } else {
            children = parentEl.querySelectorAll(':scope > .tree__branch');
          }

          if (children.length) {
            let ids = [];
            children.forEach(el => {
              ids.push(el.dataset.id);
            });

             repositionPages(ids, parentId);
          }

        })
        ;
      }
    }

  }

  // run the db to reset position items
  function repositionPages(ids, parentArg) {
    if (Array.isArray(ids) && ids.length) {
      axios
        .post(`${window.siteUrl}/refined/pages/position`, {
          positions: ids,
          parent: parentArg,
        })
        .then(response => {})
        .catch(error => {
          console.log('Sort Error', error);
        })
      ;
    }

  }

  // create an array of templates that have forms
  function setFormTemplates() {
    if (templates.value.length) {
      templates.value.forEach(template => {
        if (template.has_forms) {
          formTemplates.value.push(template.id);
        }
      })
    }
  }

  // work out if the forms attribute needs to show or not
  function showForms() {
    if (formTemplates.value.indexOf(page.value.meta.template_id) > -1) {
      return true;
    }

    return false;
  }

  ////////////////////////////////
  //// end page
  ///////////////////////////////


  ////////////////////////////////
  //// page modules
  ///////////////////////////////
  function setupModules() {
    for (let i in props.modules) {
      let mod = props.modules[i];
      let name = i;

      if (typeof mod.name !== 'undefined') {
        name = mod.name;
      }
      let t = {
        tab: _.snakeCase(mod.tab),
        name: name,
        default: false,
        type: mod.type,
        fields: mod.fields
      };

      if (typeof mod.config.active !== 'undefined') {
        t.active = mod.config.active;
      } else {
        t.active = {};
        if (typeof mod.config.home.active !== 'undefined') {
          t.active.home = mod.config.home.active;
        }
        if (typeof mod.config.internal.active !== 'undefined') {
          t.active.internal = mod.config.internal.active;
        }
        if (typeof mod.config.hide_on_pages !== 'undefined') {
          t.active.hide_on_pages = mod.config.hide_on_pages;
        }
        if (typeof mod.config.show_on_pages !== 'undefined') {
          t.active.show_on_pages = mod.config.show_on_pages;
        }
      }
      tabs.value.push(t);
    }
  }

  function resetPageData() {
    tabs.value.forEach(t => {
      let fields = t.fields;
      if (t.type === 'repeatable') {
        if (typeof page.value.data[t.tab] !== 'undefined' && Array.isArray(page.value.data[t.tab])) {
          page.value.data[t.tab].forEach((row, index) => {
            let fieldsInData = [];
            for (let i in row) {
              let configField = getRepeatableConfigField(t.fields, i);
              if (configField) {
                row[i].note = getRepeatableFieldNote(configField);
              }
              fieldsInData.push(i);
              if (typeof row[i].key === 'undefined') {
                row[i].key = getRepeatableFieldIndex({...row[i], field: i}, index);
              }

              row[i].fieldName = `${t.tab}.${index}.${i}.content`

              page.value.data[t.tab][index] = row;
            }

            // if the config defines fields this saved row doesn't have, rebuild
            // the row from the definitions. a length comparison misses the
            // add-one-remove-one case, so check the actual field keys
            if (fields.some(field => !fieldsInData.includes(field.field))) {
              let newData = {};
              fields.forEach((field, index) => {
                let set = false;
                for (let i in row) {
                  if (i === field.field) {
                    newData[i] = row[i];
                    set = true;
                  }
                }
                if (!set) {
                  let d = {
                    content: pageContentAsArray.includes(field.page_content_type_id) ? [] : '',
                    page_content_type_id: field.page_content_type_id,
                    note: field.note || '',
                    key: getRepeatableFieldIndex(field, index),
                  };
                  if (typeof field.options !== 'undefined') {
                    d.options = field.options;
                  }
                  newData[field.field] = d;
                }
              });

              page.value.data[t.tab][index] = newData;
            }
          });
        } else {
          page.value.data[t.tab] = [];
        }
      }
      if (t.type === 'fields') {
        addFieldData();
      }
    });


  }

  function addFieldData() {
    tabs.value.forEach(t => {
      // add the fields if they do not exist
      if (t.type === 'fields' && typeof page.value.data[t.tab] === 'undefined' && t.fields.length) {
        // add the content section to the fields
        let fields = {};
        t.fields.forEach((field, index) => {
          fields[_.snakeCase(field.name)] = {
            id: field.id,
            key: getRepeatableFieldIndex(field, index),
            page_content_type_id: field.page_content_type_id,
            content: typeof field.content !== 'undefined' ? field.content : '',
          }
        });

        // add the fields to the page data
        page.value.data[t.tab] = fields;
      } else {
        page.value.data[t.tab] = [];
      }
    });
  }

  ////////////////////////////////
  //// end page modules
  ///////////////////////////////

  // created
  ui.loading = true;
  setupModules();

  axios
    .get(`${window.siteUrl}/refined/pages/get-tree`)
    .then(r => {
      ui.loading = false;

      if (r.status === 200) {
        pages.value = r.data.tree;
        templates.value = r.data.templates;
        contentTypes.value = r.data.types;
        leaf.value = r.data.leaf;
        forms.value = r.data.forms;

        // setting the initial page
        if (pages.value.length) {
          pages.value.forEach(holder => {
            if (holder.show) {
              if (holder.children.length) {
                loadPage(holder.children[0]);
              }
            }
          });

          resetParents();
        }

        if (templates.value.length) {
          setFormTemplates();
        }
      }
    })
    .catch(e => {
      ui.loading = false;
    })
  ;

  onUpdated(() => {
    nextTick(() => {
      initSort();
    });
  });

  onUnmounted(() => {
    window.removeEventListener('message', onMessage);
    debouncedRender.cancel();
    if (contentSort) contentSort.destroy();
    if (sortables) sortables.destroy();
  });
</script>
