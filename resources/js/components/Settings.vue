<template>
  <div class="pages settings">
    <section class="pages__content">
      <div class="pages__header">
        <h3>Settings</h3>
        <aside>
          <template v-if="$root.user.user_level_id < 2">
            <a href="" class="button button--green button--small" @click.prevent.stop="toggleContentEditorForm">Add a Setting</a>
            <span> | </span>
          </template>
          <a href="" class="button button--blue button--small" @click.prevent.stop="save">Save</a>
        </aside>
      </div><!-- / header -->

      <div class="pages__info">
        <div class="pages__tab-pane">
          <div class="pages__content-editor">

            <div class="content-editor__controls" v-show="editor.showForm">
              <div class="form">
                <div class="form__group form__group--4">

                  <div class="form__row form__row--required">
                    <label for="form--editor-name" class="form__label">Name</label>
                    <input type="text" id="form--editor-name" v-model="editor.form.name" required="required" class="form__control">
                  </div><!-- / form row -->

                  <div class="form__row form__row--required">
                    <label for="form--editor-required" class="form__label">Required</label>
                    <select id="form--editor-required" v-model="editor.form.required" required="required" class="form__control form__control--full-width">
                      <option :value="0">No</option>
                      <option :value="1">Yes</option>
                    </select>
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

                <div class="form__group form__group--1" v-if="editor.form.field_type == 6">
                  <div class="form__row">
                    <label for="form--editor-note" class="form__label">Options</label>
                    <rd-form-options v-model="editor.form.options"></rd-form-options>
                  </div>
                </div>

                <div class="form__group form__group--1">
                  <div class="form__row form__row--buttons form__row--buttons-inline">
                    <a href="#" @click.prevent.stop="addContentField()" class="button button--blue button--small">Add Field</a>
                  </div>
                </div>

              </div><!-- / form -->
            </div><!-- / content editor controls -->

            <div class="content-editor__fields form form__horz">
              <div class="content-editor__field" v-for="(item, index) in content" :data-id="item.id">

                <div class="form__row form__row--inline-label" :class="{ 'form__row--required' : item.required }">

                  <div class="form__label form__label--with-controls">
                    <i class="fa fa-times" @click="removeContent(index)" v-if="$root.user.user_level_id < 2"></i>
                    <i class="fa fa-sort"></i>
                    <label :for="'form--content-'+item.id">{{ item.name }}</label>
                  </div>

                  <rd-content-editor-field :item="item"></rd-content-editor-field>

                </div><!-- / form row -->
              </div><!-- / field -->
            </div><!-- / fields -->
          </div><!-- content editor -->
        </div>

      </div><!-- / info -->
    </section>
  </div><!-- / pages -->
</template>

<script>
  import swal from 'sweetalert';

  export default {

    props: [ 'data', 'model' ],

    created () {
      // get the content types
      this.$root.loading = true;
      axios
        .get('/refined/pages/get-tree')
        .then(r => {
          this.$root.loading = false;
          if (r.status == 200) {
            this.contentTypes = r.data.types;
          }
        })
        .catch(e => {
          this.$root.loading = false;
        })
      ;

      this.content = this.data;
    },

    updated() {
      this.$nextTick(() => {
        this.initSort();
      });
    },

    data() {
      return {

        contentTypes: [],
        contentSort: null,
        content: [],

        editor: {
          showForm: false,
          form: {
            name: null,
            required: 0,
            field_type: 1,
            note: null,
            options: []
          }
        },

        contentSort: null,
      }
    },

    methods: {
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
        if (this.editor.form.field_type == 0) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please select a Field Type';
          validationData.appendChild(child);
        }
        if (this.editor.form.field_type == 6 && this.editor.form.options.length < 1) {
          errors.push(1);
          let child = document.createElement('li');
          child.innerText = 'Please add some options';
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
          let length = this.content.length;
          this.content.push({
            id: 'field_'+length,
            name: this.editor.form.name,
            required: this.editor.form.required,
            page_content_type_id: this.editor.form.field_type,
            note: this.editor.form.note,
            options: this.editor.form.options,
            content: '',
            position: length
          });

          this.resetContentForm();
          this.initSort();
        }

      },

      resetContentForm() {
        this.editor.form.name = null;
        this.editor.form.required = 0;
        this.editor.form.field_type = 1;
        this.editor.form.note = null;
        this.editor.form.options = [];
      },

      removeContent(index) {

        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        }).then(value => {
          if (value) {
            this.content.splice(index, 1);
          }
        });

      },

      initSort() {
        if (this.contentSort == null) {
          this.contentSort = dragula([document.querySelector('.content-editor__fields')], {
            direction: 'vertical'
          })
          .on('drop', () => {
            let fields = document.querySelectorAll('.content-editor__field');

            if (fields.length) {
              fields.forEach((field, index) => {
                if (!field.classList.contains('gu-mirror')) {
                  this.content.forEach(content => {
                    if (content.id == field.dataset.id) {
                      content.position = index;
                    }
                  })
                }
              })
            }

          });
        }
      },

      save() {
        // do some quick validation
        let check   = /^.+[\s]{0,4}/,
            errors  = [],
            validationData = document.createElement('ul')
        ;

        if (this.content.length) {
          this.content.forEach(content => {
            if (content.required && (!check.test(content.content) || content.content == null)) {
              errors.push(1);
              let child = document.createElement('li');
              child.innerText = 'The '+content.name+' field is required.';
              validationData.appendChild(child);
            }
          })

          if (errors.length) {
            swal({
              title: 'You have some errors in your form.',
              content: validationData,
              icon: 'error',
              dangerMode: true,
            });
          } else {
            let config = {
              url: '/refined/settings/'+this.model,
              method: 'POST',
              data: this.content
            }

            axios
              .request(config)
              .then(r => {
                this.$root.loading = false;
                if (r.data.success) {
                  swal({
                    title: 'Success',
                    text: 'Settings have been successfully saved',
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
                this.$root.loading = false;
                swal({
                  title: 'Something went wrong',
                  text: e.message,
                  icon: 'error'
                });
              })
            ;


          }

        }

      }

    }


  }
</script>