<template>
  <div>
    <div class="content-editor__header" v-if="config.content">
      <div class="content-editor__buttons">
        <template v-for="content of config.content">
          <button class="button button--small button--green" :class="{ 'button--has-note' : content.description }" @click.prevent.stop="loadContentBlock(content)">
              <span class="button__text">
                {{ content.name }}
              </span>
              <span class="content-editor__button-note" v-if="content.description">
                <span class="fa fa-question-circle"></span>
                <span class="content-editor__button--content" v-html="content.description"></span>
              </span>
          </button>
        </template>
      </div>
    </div><!-- / content editor controls -->

    <div class="content-editor__data form form__horz" v-sortable-content-item>
      <div
        class="content-editor__item"
        :class="{ 'open' : index === 0}"
        v-for="(content, index) of data"
        :data-index="index"
        :data-id="content.id"
        :key="content.id"
      >
        <div class="content-editor__item-header">
          <header>
            <div class="content-editor__item-toggle" @click="toggleContentBlockContent($event, index)">
              <i class="fa fa-chevron-right"></i>
              <i class="fa fa-chevron-down"></i>
            </div>
            <h5>
              <span @click="toggleContentBlockContent($event, index)">
                {{ content.name }}
              </span>
              <small v-if="canShowAnchors" class="content-editor__anchor">
                Anchor: <span @click="selectAndCopy">#{{ anchorPrefix+index }}</span>
              </small>
            </h5>
          </header>
          <aside class="content-editor__item-sort">
            <i class="fa fa-sort" v-if="page[name] && page[name].length > 1"></i>
            <i class="fa fa-times" @click="removeContentBlock(index)"></i>
          </aside>
        </div>
        <div class="content-editor__item-content" :style="{ display: index === 0 ? 'block' : 'none' }">
          <div class="form form__horz">
            <div
              class="content-editor__form-row form__row form__row--inline-label"
              v-for="field of content.fields"
              v-show="canShow(field, content)"
            >
              <label :for="`form--content-${field.id}`" class="form__label">{{field.name}}</label>
              <rd-content-editor-field :item="field" :key="`${content.id}_${field.id}`"></rd-content-editor-field>
            </div>
          </div>
        </div>
      </div>
    </div>

    <textarea :name="name" :value="JSON.stringify(data)" style="display:none"></textarea>
  </div>
</template>

<script>
import swal from 'sweetalert';
import Vue from 'vue';
import naturalSort from 'javascript-natural-sort';
import _ from 'lodash';

import { PagesImageNoteMixin } from  '../mixins/PagesImageNote.js';
import { PagesRepeatableMixin } from  '../mixins/PagesRepeatable.js';

export default {
  name: 'rd-content-blocks',

  props: [ 'config', 'page', 'name', 'canShowAnchors' ],

  mixins: [
    PagesImageNoteMixin,
    PagesRepeatableMixin
  ],

  data() {
    return {
      data: []
    }
  },

  mounted() {
    this.data = this.page[this.name];
  },

  created() {
    eventBus.$on('pages.sortable.content-item.dragend', data => {
      this.reorderContentBlocks(data);
    })
  },

  methods: {
    loadContentBlock(content) {
      const newContent = _.cloneDeep(content);
      newContent.fields.forEach(field => {
        if (!field.content) {
          field.content = field.page_content_type_id === 9 ? [] : ''
        }

        if (!field.id) {
          field.id = `-${_.kebabCase(field.name)}-id-${Date.now()}`
        }

        if (!field.key) {
          field.key = `-${_.kebabCase(field.name)}-key-${Date.now()}`
        }
      })

      if (!newContent.id) {
        newContent.id = `id-${Date.now()}`
      }

      if (!newContent.key) {
        newContent.key = `key-${Date.now()}`
      }

      this.data.push(newContent);
      Vue.set(this.page, this.name, this.data);
    },

    removeContentBlock(index) {
      swal({
        title: 'Are you sure?',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
      }).then(value => {
        if (value) {
          this.data.splice(index, 1);
          Vue.set(this.page, this.name, this.data);
        }
      });
    },

    // todo: remove the jquery from here
    toggleContentBlockContent(event) {
      const klass = 'content-editor__item';
      const element = $(event.target).closest(`.${klass}`);
      if (element) {
        const block = element.find('.content-editor__item-content');

        if (!element.hasClass('open')) {
          block.slideDown(200, function () {
            element.addClass('open')
          })
        } else {
          block.slideUp(200, function () {
            element.removeClass('open')
          })
        }
      }
    },

    reorderContentBlocks(order) {
      const contentLookup = _.keyBy(this.data, 'id');
      const newOrder = order.map(item => {
        return contentLookup[item.id];
      })

      Vue.set(this.page, this.name, newOrder);
    },

    canShow(field, content) {

      if (!field.showOn) {
        return true;
      }

      const keys = field.showOn.split(':');
      // find the corrosponding field
      const altField = content.fields.find(item => {
        const key = _.snakeCase(item.name)
        return key === keys.at(0)
      })

      if (altField) {
        return altField.content == keys.at(1);
      }

      return true;
    }
  }
}

</script>
