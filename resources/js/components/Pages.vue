<template>
  <div class="pages">
    <aside class="pages__tree">
      <nav class="tree">
        <ul class="tree__trunk">
          <li class="tree__branch tree__branch--master"
            :class="{ 'tree__branch--has-children' : holder.children.length, 'tree__branch--active' : holder.show }"
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
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == item.tab) }" v-for="item in defaultTabs" @click="switchTab(item.tab)">{{ item.name }}</li>
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == item.tab) }" v-for="item in tabs" v-if="showTab(item)" @click="switchTab(item.tab)">{{ item.name }}</li>
            <li class="pages__tab" :class="{ ' pages__tab--active' : (tab == 'meta') }" @click="switchTab('meta')">Meta</li>
          </ul>
        </nav>
      </div>

      <div class="pages__info">
        <div class="pages__tab-pane" v-show="tab == 'details'">
          <h3>Page Details</h3>

          <div class="form form__horz">
            <div class="form__row form__row--inline-label">
              <label for="form--menu-text" class="form__label">Menu Text</label>
              <div class="form__horz-group">
                <input type="text" id="form--menu-text" v-model="page.name" required="required" class="form__control" @keyup="updateSlug()">
                <div class="form__note">This is used for the text in the menu.</div>
              </div>
            </div><!-- / form row -->

            <template v-if="page.id > 1 || page.newPage">

              <div class="form__row form__row--inline-label">
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

              <div class="form__row form__row--inline-label">
                <label for="form--page-type" class="form__label">Page Type</label>
                <div class="form__horz-group">
                  <select id="form--page-type" v-model="page.page_type" required="required" class="form__control">
                    <option :value="1">Page</option>
                    <option :value="0">Holder</option>
                  </select>
                  <div class="form__note">Is this a page, or just a menu item?</div>
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

            <div class="form__row form__row--inline-label" v-if="showBanner()">
              <label for="form--menu-text" class="form__label">Banner Image</label>
              <div class="form__horz-group">
                <rd-image v-model="page.banner"></rd-image>
                <div class="form__note">
                  Banner will be resized to <strong>fit within
                  {{ page.id == 1 ? config.banner.home.width : config.banner.internal.width }}px wide x
                  {{ page.id == 1 ? config.banner.home.height : config.banner.internal.height }}px tall</strong>
                  <br/>
                  If you are having trouble with images, <a href="http://www.picresize.com/" target="_blank">visit this page</a> to create your image.
                </div>
              </div>
            </div><!-- / form row -->


          </div><!-- / form -->
        </div><!-- / details -->
        <div class="pages__tab-pane" v-show="tab == 'content'">
          <header class="pages__tab-pane-header">
            <h3>Page Content</h3>
            <aside v-if="$root.user.user_level_id < 2">
              <a href="#" @click.prevent.stop="toggleContentEditorForm()" class="button button--green button--small"><i class="fa fa-plus"></i></a>
            </aside>
          </header>

          <div class="pages__content-editor">

            <div class="content-editor__controls" v-show="editor.showForm">
              <div class="form">
                <div class="form__group form__group--4">

                  <div class="form__row form__row--required">
                    <label for="form--editor-name" class="form__label">Name</label>
                    <input type="text" id="form--editor-name" v-model="editor.form.name" required="required" class="form__control">
                  </div><!-- / form row -->

                  <div class="form__row form__row--required">
                    <label for="form--editor-source" class="form__label">Source</label>
                    <input type="text" id="form--editor-source" v-model="editor.form.source" required="required" class="form__control">
                  </div><!-- / form row -->

                  <div class="form__row form__row--required">
                    <label for="form--editor-field-type" class="form__label">Field Type</label>
                    <select id="form--editor-field-type" v-model="editor.form.field_type" required="required" class="form__control form__control--full-width">
                      <option :value="item.id" v-for="item in contentTypes">{{ item.name }}</option>
                    </select>
                  </div><!-- / form row -->

                  <div class="form__row">
                    <label for="form--editor-note" class="form__label">Note</label>
                    <input type="text" id="form--editor-note" v-model="editor.form.note" required="required" class="form__control">
                  </div><!-- / form row -->

                </div><!-- / form group -->

                <div class="form__group form__group--1">
                  <div class="form__row form__row--buttons form__row--buttons-inline">
                    <a href="#" @click.prevent.stop="addContentField()" class="button button--blue button--small">Add Field</a>
                  </div>
                </div>

              </div><!-- / form -->

            </div><!-- / content editor controls -->

            <div class="content-editor__fields form form__horz">
              <div class="content-editor__field" v-for="(item, index) in page.content" :data-id="item.id">

                <div class="form__row form__row--inline-label">

                  <div class="form__label form__label--with-controls">
                    <i class="fa fa-times" @click="removeContent(index)" v-if="$root.user.user_level_id < 2"></i>
                    <i class="fa fa-sort" v-if="page.content && page.content.length > 1"></i>
                    <label :for="'form--content-'+item.id">{{ item.name }}</label>
                  </div>

                  <rd-content-editor-field :item="item"></rd-content-editor-field>

                </div><!-- / form row -->
              </div><!-- / field -->
            </div><!-- / fields -->
          </div><!-- content editor -->
        </div>
        <div class="pages__tab-pane" v-show="tab == 'meta'">
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
                  <img src="/vendor/refinedcms/img/ui/meta-title.png"/>
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

        <div class="pages__tab-pane" v-show="tab == item.tab" v-if="!item.default" v-for="item in tabs">
          <h3>{{ item.name }}</h3>
          <rd-pages-repeatable :item="item" :page="page"></rd-pages-repeatable>
        </div>

      </div><!-- / info -->
    </section>
  </div><!-- / pages -->
</template>

<script>
  import swal from 'sweetalert';
  import naturalSort from 'javascript-natural-sort';

  export default {

    props: [ 'siteUrl', 'config', 'modules' ],

    created () {
      // get pages
      this.$root.loading = true;
      this.setupModules();

      axios
        .get('/refined/pages/get-tree')
        .then(r => {
          this.$root.loading = false;
          if (r.statusText == 'OK') {
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
          data: {}
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
          { tab: 'content', name: 'Content' },
        ],

        tabs: []
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

      showTab(tab) {
        let show = false;

        if (this.page.id == 1 && tab.active.home) {
          show = true;
        }

        if (this.page.id != 1 && tab.active.internal) {
          show = true;
        }

        return show;
      },

      // show / hide the tree
      toggleSubMenu(item) {
        if (item.children.length) {
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
        } else {
          this.resetRepeatableData();
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
          direction: 'vertical'
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

        let urlBits = [this.siteUrl];
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
              .delete('/refined/pages/'+this.page.id)
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
        window.open(this.url + this.page.meta.uri);
      },

      // save the page
      savePage() {
        this.$root.loading = true;
        let config = {
          url: '/refined/pages',
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
          url: '/refined/pages/'+item.id+'/update-parent',
          method: 'PUT',
          data: {
            page: {
              parent_id: item.parent_id,
              page_holder_id: item.page_holder_id,
            },
            parent: this.parent
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
          let elements = document.querySelectorAll('.tree__trunk--sortable');

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
              if (parent.dataset.id != newParent.dataset.id) {
                // we have a different parent, need to update
                children = newParent.querySelectorAll(':scope > .tree__branch');
                parentId = parseInt(newParent.dataset.id);

                // find and update the new leaf
                if (typeof this.flatPages[e.dataset.id] != 'undefined') {
                  this.flatPages[e.dataset.id].parent_id = parentId;
                  // only if the parent is a holder update it
                  if (parentId < 0) {
                    this.flatPages[e.dataset.id].page_holder_id = Math.abs(parentId);
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
            .post('/refined/pages/position', {
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
      //// content editor
      ///////////////////////////////
      toggleContentEditorForm() {
        this.editor.showForm = !this.editor.showForm;
      },

      addContentField() {
        let check   = /^.+[\s]{0,4}/,
            errors  = [],
            validationData = document.createElement('ul')
        ;

        // do the validation
        if (!check.test(this.editor.form.name) || this.editor.form.name == null) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please enter a Name';
          validationData.appendChild(child);
        }
        if (!check.test(this.editor.form.source) || this.editor.form.source == null) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please enter a Source';
          validationData.appendChild(child);
        }
        if (this.editor.form.field_type == 0) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please select a Field Type';
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
          // now push into the page content area
          let length = this.page.content.length;
          this.page.content.push({
            id: 'field_'+length,
            name: this.editor.form.name,
            source: this.editor.form.source,
            page_content_type_id: this.editor.form.field_type,
            note: this.editor.form.note,
            content: '',
            position: length
          });

          this.resetContentForm();
        }

      },

      resetContentForm() {
        this.editor.form.name = null;
        this.editor.form.source = null;
        this.editor.form.field_type = 1;
        this.editor.form.note = null;
      },

      removeContent(index) {

        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        }).then(value => {
          if (value) {
            this.page.content.splice(index, 1);
          }
        });

      },

      ////////////////////////////////
      //// end content editor
      ///////////////////////////////

      showBanner() {
        let show = false;

        if (this.page.id == 1 && this.config.banner.home.active) {
          show = true;
        }

        if (this.page.id != 1 && this.config.banner.internal.active) {
          show = true;
        }

        return show;
      },

      ////////////////////////////////
      //// page modules
      ///////////////////////////////
      setupModules() {
        for (let i in this.modules) {
          let module = this.modules[i];
          let tab = {
            tab: module.tab,
            name: i,
            default: false,
            type: module.type,
            fields: module.fields,
            active: {
              home: module.config.home.active,
              internal: module.config.internal.active
            }
          };
          this.tabs.push(tab);
        }
      },

      addRepeatable(tab) {
        if (typeof this.page.data[tab.tab] == 'undefined') {
          Vue.set(this.page.data, tab.tab, []); // updates the reactivity, allows for correct looping
        }

        let row = {};
        if (tab.fields.length) {
          tab.fields.forEach(field => {
            let note = this.getRepeatableFieldNote(field);

            row[field.field] = {
              page_content_type_id: field.page_content_type_id,
              content: '',
              note: note
            };
          });
        }

        this.page.data[tab.tab].push(row)
      },

      removeRepeatable(tab, index) {
        if (typeof this.page.data[tab.tab] != 'undefined') {
          swal({
            title: 'Are you sure?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
          })
          .then((value) => {
            if (value) {
              this.page.data[tab.tab].splice(index, 1);
            }
          });
        }
      },

      getRepeatableFieldNote(field) {
        let note = '';
        if (field.field == 'image' && field.config) {
          note = 'Banner will be resized to <strong>fit within ';
              note += (this.page.id == 1 ? field.config.home.width : field.config.internal.width) + 'px wide x ';
              note += (this.page.id == 1 ? field.config.home.height : field.config.internal.height) + 'px tall</strong>';
          note += '<br/>If you are having trouble with images, <a href="http://www.picresize.com/" target="_blank">visit this page</a> to create your image.';
        }

        return note;
      },

      getRepeatableConfigField(fields, index) {
        let configField = null;

        if (Array.isArray(fields)) {
          fields.forEach(field => {
            if (field.field == index) {
              configField = field;
            }
          });
        }

        return configField;
      },

      resetRepeatableData() {
        this.tabs.forEach(tab => {
          if (tab.type == 'repeatable') {
            if (typeof this.page.data[tab.tab] != 'undefined' && Array.isArray(this.page.data[tab.tab])) {
              this.page.data[tab.tab].forEach(row => {
                for (let i in row) {
                  let configField = this.getRepeatableConfigField(tab.fields, i);
                  if (configField) {
                    row[i].note = this.getRepeatableFieldNote(configField);
                  }
                }
              });
            }
          }
        });


      },

      ////////////////////////////////
      //// end page modules
      ///////////////////////////////


      clone(data) {
        return JSON.parse(JSON.stringify(data));
      },
    }


  }
</script>