<template>
  <div class="pages">
    <aside class="app__trigger" @click="mobileMenuActive = !mobileMenuActive">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M0 88C0 74.7 10.7 64 24 64l400 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 112C10.7 112 0 101.3 0 88zM0 248c0-13.3 10.7-24 24-24l400 0c13.3 0 24 10.7 24 24s-10.7 24-24 24L24 272c-13.3 0-24-10.7-24-24zM448 408c0 13.3-10.7 24-24 24L24 432c-13.3 0-24-10.7-24-24s10.7-24 24-24l400 0c13.3 0 24 10.7 24 24z"/></svg>
    </aside>
    <aside class="pages__tree" :class="mobileMenuActive ? 'pages__tree--active' : ''">

      <aside class="app__trigger" @click="mobileMenuActive = !mobileMenuActive">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M345 137c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-119 119L73 103c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l119 119L39 375c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l119-119L311 409c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-119-119L345 137z"/></svg>
      </aside>

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
    </aside>
    <section class="pages__content">
      <div class="pages__header">
        <h3>{{ page.name }}</h3>
        <aside>
          <a href="" class="button button--grey button--small" @click.prevent.stop="savePage">Save</a>
          <template v-if="!page.newPage">
            <a href="" class="button button--grey button--small" @click.prevent.stop="viewPage">View</a>
            <span> | </span>
            <a href="" class="button button--grey button--small" @click.prevent.stop="addPage">Add a Page</a>
          </template>
          <template v-if="page.newPage">
            <a href="" class="button button--red button--small" @click.prevent.stop="cancelPage">Cancel</a>
          </template>
          <template v-if="page.id > 1">
            <a href="" class="button button--red button--small" @click.prevent.stop="deletePage">Delete</a>
          </template>
        </aside>
      </div><!-- / header -->

      <div class="pages__tabs">
        <nav>
          <ul>
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == item.tab) }" v-for="item in defaultTabs" v-if="showDefaultTab(item)" @click="switchTab(item.tab)">{{ item.name }}</li>
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == item.tab) }" v-for="item in tabs" v-if="showTab(item)" @click="switchTab(item.tab)">{{ item.name }}</li>
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == 'meta') }" @click="switchTab('meta')">Meta</li>
          </ul>
        </nav>
      </div>

      <div class="pages__info">
        <div class="pages__tab-pane" v-show="tab === 'details'">
        <h3>Page Details</h3>

        <div class="form form__horz">
          <div class="form__row form__row--inline-label">
            <label for="form--menu-text" class="form__label">Menu Text</label>
            <div class="form__horz-group">
              <input type="text" id="form--menu-text" v-model="page.name" required="required" class="form__control" @keyup="updateSlug()">
              <div class="form__note">This is used for the text in the menu.</div>
            </div>
          </div><!-- / form row -->


            <div class="form__row form__row--inline-label" v-if="page.id > 1 || page.newPage">
              <label for="form--active" class="form__label">Show Page</label>
              <div class="form__horz-group">
                <select id="form--active" v-model="page.active" required="required" class="form__control">
                  <option :value="1">Yes</option>
                  <option :value="0">No</option>
                </select>
                <div class="form__note">Do you want the page to be viewed by the public? Set to No for draft mode.</div>
              </div>
            </div><!-- / form row -->

            <div class="form__row form__row--inline-label">
              <label for="form--hide-from-menu" class="form__label">Show in Menus</label>
              <div class="form__horz-group">
                <select id="form--hide-from-menu" v-model="page.hide_from_menu" required="required" class="form__control">
                  <option :value="1">No</option>
                  <option :value="0">Yes</option>
                </select>
                <div class="form__note">Do you want the page to be shown in menus?</div>
              </div>
            </div><!-- / form row -->

          <template v-if="page.id > 1 || page.newPage">

            <div class="form__row form__row--inline-label">
              <label for="form--hide-from-menu" class="form__label">Show in Sitemap.xml</label>
              <div class="form__horz-group">
                <select id="form--hide-from-menu" v-model="page.hide_from_sitemap" required="required" class="form__control">
                  <option :value="1">No</option>
                  <option :value="0">Yes</option>
                </select>
                <div class="form__note">Do you want the page to be shown in the sitemap.xml?</div>
              </div>
            </div><!-- / form row -->

            <div class="form__row form__row--inline-label">
              <label for="form--page-type" class="form__label">Page Type</label>
              <div class="form__horz-group">
                <select id="form--page-type" v-model="page.page_type" required="required" class="form__control">
                  <option :value="1">Page</option>
                  <option :value="0">Holder</option>
                  <option :value="2">Redirect to Home Page</option>
                </select>
                <div class="form__note">
                  Page: A standard page<br/>
                  Holder: A menu item, not a page<br/>
                  Redirect: Sets a session and redirects to the home page</div>
              </div>
            </div><!-- / form row -->

            <div class="form__row form__row--inline-label">
              <label for="form--template" class="form__label">Template</label>
              <div class="form__horz-group">
                <select id="form--template" v-model="page.meta.template_id" required="required" class="form__control">
                  <option :value="item.id" v-for="item in templates">{{ item.name }}</option>
                </select>
                <div class="form__note">Which layout do you need for this page?</div>
              </div>
            </div><!-- / form row -->

          </template>

          <div class="form__row form__row--inline-label" v-if="showForms()">
            <label for="form--form" class="form__label">Form</label>
            <div class="form__horz-group">
              <select id="form--form" v-model="page.form_id" required="required" class="form__control">
                <option :value="item.id" v-for="item in forms">{{ item.name }}</option>
              </select>
              <div class="form__note">Which form do you want to display on this page?</div>
            </div>
          </div><!-- / form row -->
        </div><!-- / form -->

        </div><!-- / details -->
        <div class="pages__tab-pane" v-show="tab === 'content'">
          <header class="pages__tab-pane-header">
            <h3>Page Content</h3>
          </header>

          <div class="pages__content-editor">
            <rd-content-blocks
              :config="config"
              :page="page"
              :key="`page__id--${page.id}`"
              :content="content"
              name="content"
            />
          </div><!-- content editor -->
        </div>
        <div class="pages__tab-pane" v-show="tab === 'meta'">
          <h3>Meta Data</h3>

          <div class="form form__horz">

            <div class="form__row form__row--inline-label">
              <label for="form--slug" class="form__label">Page URL</label>
              <div class="form__horz-group">

                <div class="form__control--url">
                  <span>{{ url }}</span>
                  <input type="text" id="form--slug" v-model="page.meta.uri" readonly required="required" class="form__control">
                  <span class="copy-url" @click="copyUrl"><i class="fas fa-link"></i></span>
                </div>

              </div>
            </div><!-- / form row -->

            <div class="form__row form__row--inline-label">
              <label for="form--title" class="form__label">Page Title</label>
              <div class="form__horz-group">
                <input type="text" id="form--title" v-model="page.meta.title" required="required" class="form__control">
                <div class="form__note">
                  This area appears in the title of the browser<br/>
                  A maximum of 70 characters is allowed<br/>
                  <img src="/vendor/refined/core/img/ui/meta-title.png"/>
                </div>
              </div>
            </div><!-- / form row -->

            <div class="form__row form__row--inline-label">
              <label for="form--desc" class="form__label">Meta Description</label>
              <div class="form__horz-group">
                <textarea id="form--desc" v-model="page.meta.description" required="required" class="form__control"></textarea>
                <div class="form__note">
                  This area is used to describe the business to search engines<br>A maximum of <code>160</code> characters is allowed
                </div>
              </div>
            </div><!-- / form row -->

          </div><!-- / form -->

        </div>
        <div class="pages__tab-pane" v-show="tab === 'settings'">
          <h3>Page Settings</h3>
          <rd-pages-settings :settings="page.settings" :page="page"></rd-pages-settings>
        </div>

        <div class="pages__tab-pane" v-show="tab === item.tab" v-if="!item.default" v-for="item in tabs">
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

                <div class="content-editor__fields form form__horz">
                  <div class="content-editor__field" v-for="(field, index) in item.fields">

                    <div class="form__row form__row--inline-label" v-if="page.data[item.tab]">

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
      </div><!-- / info -->
    </section>
  </div><!-- / pages -->
</template>

<script>
  import swal from 'sweetalert';
  import naturalSort from 'javascript-natural-sort';
  import _ from 'lodash';
  import Vue from 'vue';

  import { PagesImageNoteMixin } from  '../mixins/PagesImageNote.js';
  import { PagesRepeatableMixin } from  '../mixins/PagesRepeatable.js';

  export default {

    name: 'Pages',

    props: [ 'siteUrl', 'publicUrl', 'config', 'modules', 'content' ],

    mixins: [
      PagesImageNoteMixin,
      PagesRepeatableMixin
    ],

    created () {
      // get pages
      this.$root.loading = true;
      this.setupModules();

      axios
        .get(`${window.siteUrl}/refined/pages/get-tree`)
        .then(r => {
          this.$root.loading = false;

          if (r.status === 200) {
            this.pages = r.data.tree;
            this.templates = r.data.templates;
            this.contentTypes = r.data.types;
            this.leaf = r.data.leaf;
            this.forms = r.data.forms;

            // setting the initial page
            if (this.pages.length) {
              this.pages.forEach(holder => {
                if (holder.show) {
                  if (holder.children.length) {
                    this.loadPage(holder.children[0]);
                  }
                }
              });

              this.resetParents();
            }

            if (this.templates.length) {
              this.setFormTemplates();
            }
          }
        })
        .catch(e => {
          this.$root.loading = false;
        })
      ;

    },

    data() {
      return {
        tab: 'details',

        page: {
          id: 0,
          meta: {
            template_id: 0,
          },
          data: {},
          content: []
        },

        leaf: {},
        sortables: null,

        pages: [],
        flatPages: {},
        templates: [],
        forms: [],
        formTemplates: [],
        contentTypes: [],
        parents: [],
        parent: {
          updated: false,
          currentParent: null,
          newParent: null
        },

        editor: {
          showForm: false,
          form: {
            name: null,
            source: null,
            field_type: 1,
            note: null
          }
        },

        contentSort: null,

        url: null,

        defaultTabs: [
          { tab: 'details', name: 'Details' },
          { tab: 'settings', name: 'Settings' },
          { tab: 'content', name: 'Content' },
        ],

        tabs: [],

        mobileMenuActive: false,
      }
    },

    updated() {
      this.$nextTick(() => {
        this.initSort();
      });
    },

    methods: {
      ////////////////////////////////
      //// page
      ///////////////////////////////

      // switch tab
      switchTab(tab) {
        this.tab = tab;
      },

      showDefaultTab(tab) {
        if (tab.name === 'Content' && this.config.content && this.config.content.length < 1) {
          return false;
        }

        if (tab.name === 'Settings') {
          return this.page.settings && this.page.settings.length;
        }

        return true;
      },

      showTab(tab) {
        let show = false;

        if (typeof tab.active === 'object') {
          if (this.page.id === 1 && typeof tab.active.home !== 'undefined' && tab.active.home) {
            show = true;
          }

          if (this.page.id !== 1 && typeof tab.active.internal !== 'undefined' && tab.active.internal) {
            show = true;
          }

          if (typeof tab.active.show_on_pages !== 'undefined') {
            show = false;

            if (tab.active.show_on_pages.indexOf(this.page.id) > -1) {
              show = true;
            }
          }

          if (typeof tab.active.hide_on_pages !== 'undefined' && tab.active.hide_on_pages.indexOf(this.page.id) > -1) {
            show = false;
          }

        } else if (typeof tab.active === 'boolean') {
          show = tab.active;
        }


        return show;
      },

      // show / hide the tree
      toggleSubMenu(item) {
        if (item.children.length || item.type == 'holder') {
          item.show = !item.show;
        }
      },

      // load the page for editing
      loadPage(item) {
        this.turnOffPages(this.pages);
        // use this only if we decide not to use the main model
        //this.page = this.clone(item);

        this.page = item;

        //item.on = true; // use this only if we decide not to use the main model
        this.page.on = true;

        this.tab = 'details';

        if (this.page.data == null) {
          this.page.data = {};
          this.addFieldData();
        } else {
          this.resetPageData();
        }

        // if the page has children, auto show the child menu
        if (item.children.length) {
          this.page.show = true;
          // item.show = true; // use this only if we decide not to use the main model
        }

        // set the url string
        this.setUrl();

        // set current parent
        this.setCurrentParent();

        // reset the parent listing, this is so we can re-enable any page that is hidden as to not add the page to be a parent of its self
        this.resetParents();

        this.contentSort = dragula([document.querySelector('.content-editor__fields')], {
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
                this.page.content.forEach(content => {
                  if (content.id == field.dataset.id) {
                    content.position = index;
                  }
                })
              }
            })
          }

        });

      },

      // this just turns off all on pages
      turnOffPages(pages) {
        if (pages.length) {
          pages.forEach(page => {
            if (page.type == 'page' && page.on) {
              page.on = false;
            }

            if (page.children.length) {
              this.turnOffPages(page.children);
            }
          });
        }
      },

      // updates the parent listing
      resetParents() {
        this.addParents(this.pages, 0);
      },

      // set the parents
      addParents(items, depth) {
        if (items.length) {
          items.forEach(item => {
            // add to the flat listing
            if (item.type == 'holder') {
              this.flatPages['-'+item.id] = item;
            } else {
              this.flatPages[item.id] = item;
            }

            if (item.children.length) {
              this.addParents(item.children, depth + 1);
            }

          });
        }
      },

      // copy url
      copyUrl() {
        let string = this.url + this.page.meta.uri;
        let el = document.createElement('textarea');

        el.value = string;

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');

        document.body.removeChild(el);

      },

      // set the url for the meta section
      setUrl() {
        let urlBits = [window.publicUrl];
        if (this.page.parent_id != 0) {
          // find the parent
          let parentUri = this.findParentUri(this.page.parent_id);
          if (parentUri) {
            urlBits.push(parentUri);
          }
        }
        this.url = urlBits.join('/') + '/';
      },

      // finds the parent page
      findParentUri(id) {
        let parentUri = false;
        this.parents.forEach(parent => {
          if (parent.id == id) {
            parentUri = parent.uri;
            return;
          }
        });

        return parentUri;
      },

      // finds the folder the particular page is in
      findFolder() {
        let key = -this.page.page_holder_id;
        if (typeof this.flatPages[key] != 'undefined' && this.flatPages[key].show == false) {
          this.flatPages[key].show = true;
        }
      },

      // load the add form
      addPage() {
        let newData = this.clone(this.leaf);
        newData.parent_id = this.page.id == 1 ? -1 : this.page.id;
        newData.page_holder_id = this.page.page_holder_id;

        this.loadPage(newData);
      },

      // delete the page
      deletePage() {

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
              .delete(`${window.siteUrl}/refined/pages/${this.page.id}`)
              .then(r => {
                this.$root.loading = false;

                if (r.data.success) {
                  // find the page and splice from parent
                  this.findAndRemove(this.pages, this.page);
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

      // view page
      viewPage() {
        let url = this.url + this.page.meta.uri;
        let i = 0;
        while(url.endsWith('/')) {
          url = url.slice(0, -1);
          i += 1;
          if (i > 5) {
            break;
          }
        }
        window.open(url);
      },

      // save the page
      savePage() {
        this.$root.loading = true;
        let config = {
          url: `${window.siteUrl}/refined/pages`,
          method: 'POST',
          data: {
            page: this.page,
            parent: this.parent
          }
        }

        if (typeof this.page.newPage == 'undefined') {
          config.method = 'PUT';
          config.url += '/'+ this.page.id
        }

        axios
          .request(config)
          .then(r => {
            this.$root.loading = false;
            if (r.data.success) {
              if (typeof this.page.newPage != 'undefined') {
                // we have just added a page, so insert it into the menu
                this.flatPages[r.data.leaf.id] = r.data.leaf;
                if (typeof this.flatPages[this.page.parent_id] != 'undefined') {
                  this.flatPages[this.page.parent_id].children.push(r.data.leaf);
                }

                // load the page
                this.loadPage(this.flatPages[r.data.leaf.id]);
                this.findFolder();
              }
              else {
                // find the page

                // use this only if we decide to no use the main model
                //this.findAndUpdate(this.pages, this.page);
              }

              if (this.parent.updated) {
                this.moveLeaf(this.pages, this.page);
              }

              // reload the parents list
              this.resetParents();

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
            this.$root.loading = false;
            swal({
              title: 'Something went wrong',
              text: e.message,
              icon: 'error'
            });
          })
        ;
      },

      // cancel page
      cancelPage() {
        this.loadParent(this.page);
      },


      // find the page in the parent and remove
      findAndRemove(pages, item) {
        if (pages.length) {
          pages.forEach((page, index) => {
            if (page.type == 'page' && item.id == page.id) {
              pages.splice(index, 1);
              // set the first page in the list to the new active page
              if (pages.length) {
                this.loadPage(pages[0]);
              } else {
                // set the parent
                this.loadParent(item);
              }

            }

            if (page.children.length) {
              this.findAndRemove(page.children, item);
            }
          });
        }

      },

      // finds the correct page within the listing and updates it
      findAndUpdate(pages, item) {
        // we will maybe use this, needs a little tidying up
        if (pages.length) {
          pages.forEach(page => {
            if (page.type == 'page' && item.id == page.id) {
              page.active = item.active;
              page.content = item.content;
              page.form_id = item.form_id;
              page.hide_from_menu = item.hide_from_menu;
              page.name = item.name;
              page.parent_id = item.parent_id;
              page.type = item.type;
              page.meta.description = item.meta.description;
              page.meta.title = item.meta.title;
              page.meta.template_id = item.meta.template_id;
            }

            if (page.children.length) {
              this.findAndUpdate(page.children, item);
            }
          });
        }

      },

      // loads the parent item, used when deleting
      loadParent(item) {
        if (typeof this.flatPages[item.parent_id] != 'undefined') {
          // if we have a page, set the first child of the parent
          if (item.parent_id > -1) {
            this.loadPage(this.flatPages[item.parent_id]);
          } else {
            // the folder is empty, so lets go and load the home page
            this.loadPage(this.flatPages[1]);

            // close the folder
            if (this.flatPages[item.parent_id].children.length < 1) {
              this.flatPages[item.parent_id].show = false;
            }

          }
        }
      },

      // updates the uri slug
      updateSlug() {
        this.page.meta.uri = slugify(this.page.name);
      },

      // sets the current parent data before the update, used for moving
      setCurrentParent() {
        this.parent.updated = false;
        this.parent.currentParent = this.page.parent_id;
        this.parent.newParent = null;
      },

      // sets the parent details when updating, used for moving
      updateParent() {
        this.parent.updated = true;
        this.parent.newParent = this.page.parent_id;
      },

      // updates the parent ids in the db
      updateParentDB(item) {
        let config = {
          url: `${window.siteUrl}/refined/pages/${item.id}/update-parent`,
          method: 'PUT',
          data: {
            page: {
              parent_id: Math.abs(parseInt(item.parent_id)),
              page_holder_id: Math.abs(parseInt(item.page_holder_id)),
            },
            parent: {
              currentParent: Math.abs(parseInt(this.parent.currentParent)),
              newParent: Math.abs(parseInt(this.parent.newParent)),
              updated: this.parent.updated
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
      },

      // moves the leaf to the correct folder / page
      moveLeaf(pages, item, reposition = true) {
        if (pages.length) {
          pages.forEach(page => {
            let found = false;
            let parentId = null;
            let pageId = null;

            if (page.id == item.parent_id && page.type == 'page') {
              found = true;
              parentId = item.parent_id;
              pageId = page.id;
            }

            // need to do the moving if its changing holders
            if (item.parent_id < 0 && page.id == Math.abs(item.parent_id) && page.type == 'holder') {
              found = true;
              parentId = item.parent_id;
              pageId = -page.id;
              this.findFolder();
            }

            if (found == true && pageId != null && parentId != null) {

              this.flatPages[pageId].children.push(item);
              this.flatPages[pageId].show = true;

              if (!reposition) {
                // update the order if by drag drop
                this.flatPages[pageId].children.sort((a, b) => {
                  return naturalSort(a.position, b.position);
                });
              }

              // remove from the current parent
              if (this.flatPages[this.parent.currentParent].children.length) {
                this.flatPages[this.parent.currentParent].children.forEach((child, index) => {
                  if (child.id == item.id) {
                    this.flatPages[this.parent.currentParent].children.splice(index, 1);
                  }
                });
              }

              // save the new position
              if (this.flatPages[parentId].children.length && reposition) {
                let ids = [];

                this.flatPages[parentId].children.forEach(child => {
                  ids.push(child.id);
                });

                if (ids.length) {
                  this.reposition(ids, pageId);
                }
              }

              if (reposition) {
                this.loadPage(item);
              }

            }


            if (page.children.length) {
              this.moveLeaf(page.children, item);
            }
          });
        }

      },

      // boot up the sorting for tree branches
      initSort() {
        if (this.sortables == null) {
          let elements = document.querySelectorAll('.app__body .tree__trunk--sortable');

          let containers = [];
          if (elements.length) {
            elements.forEach(element => {
              containers.push(element);
            })

            this.sortables = dragula(containers, {
              direction: 'vertical'
            })
            .on('drop', (e) => {
              let parent = document.querySelector('.tree__trunk[data-id="'+e.dataset.parent+'"');
              let newParent = e.closest('.tree__trunk');
              let children = [];
              let parentId = e.dataset.parent;

              // check to see if the parent has updated
              if (parent?.dataset.id != newParent?.dataset.id) {
                // we have a different parent, need to update
                children = newParent.querySelectorAll(':scope > .tree__branch');
                parentId = parseInt(newParent.dataset.id);

                // find and update the new leaf
                if (typeof this.flatPages[e.dataset.id] != 'undefined') {
                  this.flatPages[e.dataset.id].parent_id = parentId;
                  // only if the parent is a holder update it
                  if (parentId < 0) {
                    this.flatPages[e.dataset.id].page_holder_id = Math.abs(parentId);
                    // reset the parent id to 0 if the holder is different
                    this.flatPages[e.dataset.id].parent_id = 0;
                  }

                  // now update the leaf in the db
                  this.parent.updated = true;
                  this.parent.currentParent = parent.dataset.id;
                  this.parent.newParent = newParent.dataset.id;
                  this.updateParentDB(this.flatPages[e.dataset.id]);
                  //this.moveLeaf(this.pages, this.flatPages[e.dataset.id], false);
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

      // run the db to reset position items
      reposition(ids, parent) {
        if (Array.isArray(ids) && ids.length) {
          axios
            .post(`${window.siteUrl}/refined/pages/position`, {
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

      // create an array of templates that have forms
      setFormTemplates() {
        if (this.templates.length) {
          this.templates.forEach(template => {
            if (template.has_forms) {
              this.formTemplates.push(template.id);
            }
          })
        }
      },

      // work out if the forms attribute needs to show or not
      showForms() {
        if (this.formTemplates.indexOf(this.page.meta.template_id) > -1) {
          return true;
        }

        return false;
      },

      ////////////////////////////////
      //// end page
      ///////////////////////////////


      ////////////////////////////////
      //// page modules
      ///////////////////////////////
      setupModules() {
        for (let i in this.modules) {
          let mod = this.modules[i];
          let name = i;

          if (typeof mod.name !== 'undefined') {
            name = mod.name;
          }
          let tab = {
            tab: _.snakeCase(mod.tab),
            name: name,
            default: false,
            type: mod.type,
            fields: mod.fields
          };

          if (typeof mod.config.active !== 'undefined') {
            tab.active = mod.config.active;
          } else {
            tab.active = {};
            if (typeof mod.config.home.active !== 'undefined') {
              tab.active.home = mod.config.home.active;
            }
            if (typeof mod.config.internal.active !== 'undefined') {
              tab.active.internal = mod.config.internal.active;
            }
            if (typeof mod.config.hide_on_pages !== 'undefined') {
              tab.active.hide_on_pages = mod.config.hide_on_pages;
            }
            if (typeof mod.config.show_on_pages !== 'undefined') {
              tab.active.show_on_pages = mod.config.show_on_pages;
            }
          }
          this.tabs.push(tab);
        }
      },

      resetPageData() {
        this.tabs.forEach(tab => {
          let fields = tab.fields;
          if (tab.type === 'repeatable') {
            if (typeof this.page.data[tab.tab] !== 'undefined' && Array.isArray(this.page.data[tab.tab])) {
              this.page.data[tab.tab].forEach((row, index) => {
                let fieldsInData = [];
                for (let i in row) {
                  let configField = this.getRepeatableConfigField(tab.fields, i);
                  if (configField) {
                    row[i].note = this.getRepeatableFieldNote(configField);
                  }
                  fieldsInData.push(i);
                  if (typeof row[i].key === 'undefined') {
                    row[i].key = this.getRepeatableFieldIndex({...row[i], field: i}, index);
                  }

                  row[i].fieldName = `${tab.tab}.${index}.${i}.content`

                  Vue.set(this.page.data[tab.tab], index, row)
                }

                // if we have added a new field, add it to the existing data
                if (fieldsInData.length !== fields.length) {
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
                        content: this.pageContentAsArray.includes(field.page_content_type_id) ? [] : '',
                        page_content_type_id: field.page_content_type_id,
                        note: field.note || '',
                        key: this.getRepeatableFieldIndex(field, index),
                      };
                      if (typeof field.options !== 'undefined') {
                        d.options = field.options;
                      }
                      newData[field.field] = d;
                    }
                  });

                  Vue.set(this.page.data[tab.tab], index, newData)
                }
              });
            } else {
              Vue.set(this.page.data, tab.tab, [])
            }
          }
          if (tab.type === 'fields') {
            this.addFieldData();
          }
        });


      },

      addFieldData() {
        this.tabs.forEach(tab => {
          // add the fields if they do not exist
          if (tab.type === 'fields' && typeof this.page.data[tab.tab] === 'undefined' && tab.fields.length) {
            // add the content section to the fields
            let fields = {};
            tab.fields.forEach((field, index) => {
              fields[_.snakeCase(field.name)] = {
                id: field.id,
                key: this.getRepeatableFieldIndex(field, index),
                page_content_type_id: field.page_content_type_id,
                content: typeof field.content !== 'undefined' ? field.content : '',
              }
            });

            // add the fields to the page data
            Vue.set(this.page.data, tab.tab, fields);
          } else {
            Vue.set(this.page.data, tab.tab, []);
          }
        });
      },

      ////////////////////////////////
      //// end page modules
      ///////////////////////////////


      clone(data) {
        return JSON.parse(JSON.stringify(data));
      },

      selectAndCopy(event) {
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

    },
  }
</script>
