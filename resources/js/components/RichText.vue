<template>
  <div class="form__control--rich-text rich-editor" :id="editorId">

    <div class="rich-editor__ckeditor" :class="{'rich-editor__ckeditor--source' : sourceCodeView }"><ckeditor :editor="editor" v-model="data" :config="config" @ready="editorReady"></ckeditor></div>
    <div class="rich-editor__toolbar-cover" v-show="sourceCodeView"></div>
    <textarea class="rich-editor__content" v-show="sourceCodeView" v-model="data" :name="name" :id="id"></textarea>

    <div
      v-if="showModal"
      class="rich-editor__modal-background"
    ></div>

    <div
      v-if="link.active"
      class="rich-editor__modal rich-editor__modal--link"
    >
      <header class="rich-editor__modal-header">
        <h3 class="rich-editor__modal-heading">Link</h3>
        <a @click.prevent="hideLinkModal()" class="rich-editor__modal-close"><i class="fa fa-times"></i></a>
      </header>
      <div class="rich-editor__modal-form">


        <div class="rich-editor__modal-row">
          <label>Link Type</label>
          <select class="rich-editor__modal-control" v-model="link.type">
            <option v-for="type in linkTypes" :value="type.id">{{ type.value }}</option>
          </select>
        </div>

        <div class="rich-editor__modal-row" v-if="link.type === 'internal'">
          <button class="button button--green button--small" @click.prevent="loadSitemapModal()">Browse Server</button>
        </div>

        <div class="rich-editor__modal-row" v-if="link.type === 'external'">
          <div class="rich-editor__modal-note">Must include <code>http://</code> or <code>https://</code></div>
        </div>

        <div class="rich-editor__modal-row" v-if="link.type === 'file'">
          <button class="button button--green button--small" @click.prevent="loadMediaModal()">Browse Media</button>
        </div>

        <div class="rich-editor__modal-row">
          <label>{{ link.type === 'email' ? 'Email Address' : 'Url'}}</label>
          <input class="rich-editor__modal-control" type="text" ref="linkInput" v-model="link.url">
        </div>

        <div class="rich-editor__modal-row">
          <label>title</label>
          <input class="rich-editor__modal-control" type="text" v-model="link.attrs.title">
        </div>

        <div class="rich-editor__modal-row">
          <label>id</label>
          <input class="rich-editor__modal-control" type="text" v-model="link.attrs.id">
        </div>

        <div class="rich-editor__modal-row">
          <label>class</label>
          <input class="rich-editor__modal-control" type="text" v-model="link.attrs.class">
        </div>

        <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
          <button class="button button--small" @click.prevent="saveLink()">Save</button>
        </div>
      </div>
    </div>

    <div
      v-if="image.active"
      class="rich-editor__modal rich-editor__modal--image"
    >
      <header class="rich-editor__modal-header">
        <h3 class="rich-editor__modal-heading">Insert Image</h3>
        <a @click.prevent="hideImageModal()" class="rich-editor__modal-close"><i class="fa fa-times"></i></a>
      </header>
      <div class="rich-editor__modal-form">

        <div class="rich-editor__modal-row">
          <div class="rich-editor__modal-note">
            <input class="rich-editor__modal-control" type="hidden" ref="imageInput" v-model="image.url">
            <div class="rich-editor__modal-image">
              <div class="rich-editor__modal-thumb" ref="imageThumb"></div>
            </div>
            <button class="button button--green button--small" @click.prevent="loadMediaModal('Image')">Browse Server</button>
          </div>
        </div>

        <div class="rich-editor__modal-row">
          <label>alt text</label>
          <input class="rich-editor__modal-control" type="text" v-model="image.attrs.alt">
        </div>

        <div class="rich-editor__modal-row">
          <label>id</label>
          <input class="rich-editor__modal-control" type="text" v-model="image.attrs.id">
        </div>

        <div class="rich-editor__modal-row">
          <label>class</label>
          <input class="rich-editor__modal-control" type="text" v-model="image.attrs.class">
        </div>

        <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
          <button class="button button--small" @click.prevent="saveImage()">Save</button>
        </div>
      </div>
    </div>

  </div>

</template>

<script>
  import CKEditor from '@ckeditor/ckeditor5-vue';
  import ClassicEditor from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';

  import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
  import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
  import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
  import StrikethroughPlugin from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
  import SubscriptPlugin from '@ckeditor/ckeditor5-basic-styles/src/subscript';
  import SuperscriptPlugin from '@ckeditor/ckeditor5-basic-styles/src/superscript';
  import CodePlugin from '@ckeditor/ckeditor5-basic-styles/src/code';
  import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
  import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';
  import MediaEmbed from '@ckeditor/ckeditor5-media-embed/src/mediaembed';
  import Image from '@ckeditor/ckeditor5-image/src/image';
  import ImageResize from '@ckeditor/ckeditor5-image/src/imageresize';
  import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar';
  import HorizontalLine from '@ckeditor/ckeditor5-horizontal-line/src/horizontalline';
  import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
  import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
  import Heading from '@ckeditor/ckeditor5-heading/src/heading';
  import List from '@ckeditor/ckeditor5-list/src/list';

  import RefinedImage from '../plugins/ckeditor/Image';
  import RefinedLink from '../plugins/ckeditor/link/Link';
  import RefinedButtons from '../plugins/ckeditor/buttons/List';

  export default {

    props: ['name', 'id', 'content'],

    data() {
      return {

        editor: ClassicEditor,

        config: {
          plugins: [
            EssentialsPlugin,
            PasteFromOffice,
            BoldPlugin,
            ItalicPlugin,
            StrikethroughPlugin,
            SubscriptPlugin,
            SuperscriptPlugin,
            CodePlugin,
            ParagraphPlugin,
            Image,
            ImageResize,
            ImageToolbar,
            MediaEmbed,
            HorizontalLine,
            Alignment,
            Heading,
            List,
            RefinedImage,
            RefinedLink,
            // LinkPlugin,
          ],
          toolbar: [
            'undo', 'redo', '|',
            'heading', '|',
            'bold', 'italic', 'strikethrough', '|',
            'subscript', 'superscript', '|',
            'alignment:left', 'alignment:right', 'alignment:center', 'alignment:justify', '|',
            'link', 'refined:image', 'mediaEmbed', '|',
            'refined:buttons','bulletedList', 'numberedList', '|',
            'horizontalLine'
          ],
          heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', },
                { model: 'heading1', view: 'h1', title: 'Heading 1', },
                { model: 'heading2', view: 'h2', title: 'Heading 2', },
                { model: 'heading3', view: 'h3', title: 'Heading 3', },
                { model: 'code', view: 'code', title: 'Code', },
                { model: 'pre', view: 'pre', title: 'Pre', },
                { model: 'blockquote', view: 'blockquote', title: 'Block Quote', },
            ]
          },
          image: {
            toolbar: [ 'refined:imageEdit' ]
          },

          link: {
            addTargetToExternalLinks: true,
          }
        },

        showModal: false,

        editorId: null,

        data: '',

        sourceCodeView: false,

        modalType: null,

        link: {
          active: false,
          url: null,
          type: 'internal',

          attrs: {
            id: null,
            class: null,
            title: null
          }
        },

        linkTypes: [
          { id: 'internal', value: 'Internal Page' },
          { id: 'external', value: 'External Page' },
          { id: 'file', value: 'File / Image' },
          { id: 'email', value: 'Email' },
        ],

        linkTargets: [
          { id: null, value: 'None' },
          { id: '_blank', value: 'New Window (_blank)' },
          { id: '_top', value: 'Topmost Window (_top)' },
          { id: '_self', value: 'Same Window (_self)' },
          { id: '_parent', value: 'Parent Window (_parent)' },
        ],

        image: {
          active: false,
          url: '',
          update: false,

          attrs: {
            id: null,
            class: null,
            alt: null,
          }
        },
      }
    },

    components: {
      ckeditor: CKEditor.component
    },

    created() {

      eventBus.$on('selecting-link', link => {
        if (window.app.sitemap.model === this.editorId) {
          this.link.url = link;
        }

        eventBus.$emit('sitemap-close');
      });

      eventBus.$on('selecting-file', data => {
        if (window.app.media.model === this.editorId) {
          const type = this.modalType === 'Image' ? 'image' : 'link';
          this[type].url = data.link.original.replace(data.link.basePath, '/');

          if (type === 'image') {
            this.loadImageThumb(data.link.thumb);
          }
        }

        this.modalType = null;

        eventBus.$emit('media-close');
      });

      eventBus.$on('rich-editor.image.open', data => {
        if (data.id === this.editorId) {
          this.showImageModal(data.attrs, true);
        }
      });

      eventBus.$on('rich-editor.link.open', data => {
        if (data.id === this.editorId) {
          this.showLinkModal(data.attrs, data.attrs ? data.attrs : false);
        }
      });

      eventBus.$on('rich-editor.image.insert', data => {
        if (data.id === this.editorId) {
          this.showImageModal();
        }
      });

      eventBus.$on('rich-editor.view-source.toggle', data => {
        if (data.id === this.editorId) {
          this.sourceCodeView = !this.sourceCodeView;
        }
      });

      if (this.content) {
        this.data = this.content;
      }
    },

    beforeDestroy() {
      // Always destroy your editor instance when it's no longer needed
      // this.editor.destroy()
    },

    watch: {
      data() {
        this.$emit('input', this.data);
      }
    },

    methods: {

      editorReady(editor) {
        this.editorInstance = editor;
        this.editorId = this.editorInstance.id;

      },

      showLinkModal(attrs) {
        if (attrs) {
          let href = attrs.href;
          const type = this.getLinkType(href);
          if (type === 'email') {
            href = href.replace('mailto:', '');
          }

          this.link.url = href;
          this.link.type = type;
        }

        this.setAttrs('link', attrs);

        this.showModal = true;
        this.link.active = true;
      },

      hideLinkModal() {
        this.link.url = null;
        this.link.active = false;
        this.link.type = 'internal';

        this.resetAttrs('link');

        this.showModal = false;
        this.modalType = null;
      },

      saveLink() {
        let url = this.link.url;
        let pass = true;

        if (this.link.type === 'email') {
          url = `mailto:${url}`;
        }

        if (this.link.type === 'external' && !/(http(s?)):\/\//gi.test(url)) {
          pass = false;
          window.swal({
            title: 'Something went wrong',
            text: 'Url must begin with either http:// or https://',
            icon: 'error'
          });
        }

        if (pass) {
          const attrs = {
            ...this.link.attrs
          };
          attrs.href = url;
          eventBus.$emit('rich-editor.link.save', attrs);
          this.hideLinkModal();
        }
      },


      showImageModal(attrs, update = false) {
        this.image.active = true;
        this.showModal = true;
        this.image.update = update;

        if (attrs && attrs.src) {
          this.image.url = attrs.src;
        }

        this.setAttrs('image', attrs);
        if (this.image.url) {
          this.$nextTick(() => {
            this.loadImageThumb(attrs.src);
          })
        }

      },

      hideImageModal() {
        this.image.url = null;
        this.image.active = false;
        this.image.update = false;
        this.loadImageThumb(null);

        this.resetAttrs('image');

        this.showModal = false;
        this.modalType = null;
      },

      saveImage() {
        let pass = true;
        if (!this.image.url) {
          pass = false;
          window.swal({
            title: 'Something went wrong',
            text: 'You must select an image',
            icon: 'error'
          });
        }

        if (pass) {
          const attrs = {
            ...this.image.attrs
          };
          attrs.src = this.image.url;
          eventBus.$emit('rich-editor.image.save', attrs);

          this.hideImageModal();
        }
      },



      getLinkType(href) {
        let type = 'internal';

        if (!href) {
          return type;
        }

        if (href.includes('mailto:')) {
          type = 'email';
        }

        if (href.includes('http://') || href.includes('https://')) {
          type = 'external';
        }

        if (href.includes('/storage/uploads')) {
          type = 'file';
        }

        return type;
      },

      loadSitemapModal() {
        eventBus.$emit('sitemap-reload');
        window.app.sitemap.showModal = true;
        window.app.sitemap.model = this.editorId;
      },

      loadMediaModal(type = '*') {
        eventBus.$emit('media-set-type', type);
        eventBus.$emit('media-reload');
        window.app.media.showModal = true;
        window.app.media.model = this.editorId;

        this.modalType = type;
      },


      setAttrs(type, attrs) {
        for (const attr in attrs) {
          let value = attrs[attr];
          if (attr === 'target' && !value) {
            value = null;
          }
          this[type].attrs[attr] = value;
        }
      },

      resetAttrs(type) {
        for (const attr in this[type].attrs) {
          this[type].attrs[attr] = null;
        }
      },

      loadImageThumb(src) {
        if (src) {
          const img = new Image();
          img.onload = function() {};
          img.src = src;
          this.$refs.imageThumb.style.backgroundImage = `url(${src})`;
        } else {
          this.$refs.imageThumb.style.backgroundImage = null;
        }
      }
    }

  }
</script>
