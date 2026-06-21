<template>
  <div class="pages settings">
    <section class="pages__content">
      <div class="pages__header">
        <h3>{{ heading }}</h3>
        <aside>
          <template v-if="config.user.user_level_id < 2">
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

                  <div class="form__label form__label--with-controls setting-controls">
                    <div>
                      <i class="fa fa-sort"></i>
                      <label :for="'form--content-'+item.id">{{ item.name }}</label>
                    </div>
                    <i class="fa fa-times" @click="removeContent(index)" v-if="config.user.user_level_id < 2"></i>
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

<script setup>
  import { ref, reactive, onUpdated, nextTick } from 'vue';
  import swal from 'sweetalert';
  import { useUiStore } from '../stores/ui';
  import { useConfigStore } from '../stores/config';

  const props = defineProps(['data', 'model', 'heading']);

  const ui = useUiStore();
  const config = useConfigStore();

  const contentTypes = ref([]);
  let contentSort = null;
  const content = ref([]);

  const editor = reactive({
    showForm: false,
    form: {
      name: null,
      required: 0,
      field_type: 3,
      note: null,
      options: []
    }
  });

  function toggleContentEditorForm() {
    editor.showForm = !editor.showForm;
  }

  function addContentField() {
    let check   = /^.+[\s]{0,4}/,
        errors  = [],
        validationData = document.createElement('ul')
    ;

    // do the validation
    if (!check.test(editor.form.name) || editor.form.name == null) {
      errors.push(1);
      let child = document.createElement('li');
      child.innerText = 'Please enter a Name';
      validationData.appendChild(child);
    }
    if (editor.form.field_type == 0) {
      errors.push(1);
      let child = document.createElement('li');
      child.innerText = 'Please select a Field Type';
      validationData.appendChild(child);
    }
    if (editor.form.field_type == 6 && editor.form.options.length < 1) {
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
      let length = content.value.length;
      content.value.push({
        id: 'field_'+length,
        name: editor.form.name,
        required: editor.form.required,
        page_content_type_id: editor.form.field_type,
        note: editor.form.note,
        options: editor.form.options,
        content: '',
        position: length
      });

      resetContentForm();
      initSort();
    }

  }

  function resetContentForm() {
    editor.form.name = null;
    editor.form.required = 0;
    editor.form.field_type = 3;
    editor.form.note = null;
    editor.form.options = [];
  }

  function removeContent(index) {

    swal({
      title: 'Are you sure?',
      icon: 'warning',
      buttons: true,
      dangerMode: true,
    }).then(value => {
      if (value) {
        content.value.splice(index, 1);
      }
    });

  }

  function initSort() {
    if (contentSort == null) {
      contentSort = dragula([document.querySelector('.content-editor__fields')], {
        direction: 'vertical'
      })
      .on('drop', () => {
        let fields = document.querySelectorAll('.content-editor__field');

        if (fields.length) {
          fields.forEach((field, index) => {
            if (!field.classList.contains('gu-mirror')) {
              content.value.forEach(content => {
                if (content.id == field.dataset.id) {
                  content.position = index;
                }
              })
            }
          })
        }

      });
    }
  }

  function save() {
    // do some quick validation
    let check   = /^.+[\s]{0,4}/,
        errors  = [],
        validationData = document.createElement('ul')
    ;

      content.value.forEach(content => {
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
        let requestConfig = {
          url: `${window.siteUrl}/refined/settings/${props.model}`,
          method: 'POST',
          data: content.value
        }

        axios
          .request(requestConfig)
          .then(r => {
            ui.loading = false;
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

  // created
  ui.loading = true;
  axios
    .get(`${window.siteUrl}/refined/pages/get-tree`)
    .then(r => {
      ui.loading = false;
      if (r.status == 200) {
        contentTypes.value = r.data.types;
      }
    })
    .catch(e => {
      ui.loading = false;
    })
  ;

  content.value = props.data;

  onUpdated(() => {
    nextTick(() => {
      initSort();
    });
  });
</script>

<style>
.setting-controls {
  display: flex;
  justify-content: space-between;
  padding-right: 10px;
}

.setting-controls .fa-times {
  color: #f44336;
  padding-top: 5px;
}
</style>
