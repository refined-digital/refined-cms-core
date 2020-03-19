<template>
  <div class="form__control--rich-text rich-editor" :id="editorId">

    <editor-menu-bar :editor="editor" v-slot="{ commands, isActive, getMarkAttrs }">
      <div>
        <div class="rich-editor__menu" :class="{ 'rich-editor__menu--source-code': sourceCodeView }">

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': sourceCodeView }" @click="toggleSourceCodeView()" title="View Source">
            <icon src="editor/view-source.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" @click="commands.undo" title="Undo">
            <icon src="editor/undo.svg"></icon>
          </span>

          <span class="rich-editor__icon" @click="commands.redo" title="redo">
            <icon src="editor/redo.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon rich-editor__icon--with-menu">
            <icon src="editor/formatting.svg"></icon>
            <ul class="rich-editor__list">
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.paragraph() }" @click="commands.paragraph" title="Paragraph">
                <icon src="editor/paragraph.svg"></icon>
                <span class="rich-editor__icon-title">Paragraph</span>
              </li>
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.heading({ level: 1 }) }" @click="commands.heading({ level: 1 })" title="H1">
                <icon src="editor/h1.svg"></icon>
                <span class="rich-editor__icon-title">Heading 1</span>
              </li>
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.heading({ level: 2 }) }" @click="commands.heading({ level: 2 })" title="H2">
                <icon src="editor/h2.svg"></icon>
                <span class="rich-editor__icon-title">Heading 2</span>
              </li>
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.heading({ level: 3 }) }" @click="commands.heading({ level: 3 })" title="H3">
                <icon src="editor/h3.svg"></icon>
                <span class="rich-editor__icon-title">Heading 3</span>
              </li>
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.blockquote() }" @click="commands.blockquote" title="Block Quote">
                <icon src="editor/quote-left.svg"></icon>
                <span class="rich-editor__icon-title">Quote</span>
              </li>
              <li class="rich-editor__list-item rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.code() }" @click="commands.code" title="Code">
                <icon src="editor/code.svg"></icon>
                <span class="rich-editor__icon-title">Code</span>
              </li>
            </ul>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.bold() }" @click="commands.bold" title="Bold">
            <icon src="editor/bold.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.italic() }" @click="commands.italic" title="Italic">
            <icon src="editor/italic.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.underline() }" @click="commands.underline" title="Underline">
            <icon src="editor/underline.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.strike() }" @click="commands.strike" title="Strike Through">
            <icon src="editor/strike-through.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.sup() }" @click="commands.sup" title="Superscript">
            <icon src="editor/superscript.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.sub() }" @click="commands.sub" title="Subscript">
            <icon src="editor/subscript.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.link() }" @click="showLinkModal(getMarkAttrs('link'))" title="Insert Link">
            <icon src="editor/link.svg"></icon>
          </span>

          <span class="rich-editor__icon" @click="setLink(commands.link, null)" title="Remove Link">
            <icon src="editor/unlink.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.image() }" @click="showImageModal(null)" title="Insert Image">
            <icon src="editor/image.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.iframe() }" @click="showEmbedModal(getMarkAttrs('iframe'))" title="Embed Video">
            <icon src="editor/video.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.align({ textAlign: 'left' }) }" @click="commands.align({ textAlign: 'left' })" title="Align Left">
            <icon src="editor/align-left.svg"></icon>
          </span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.align({ textAlign: 'center' }) }" @click="commands.align({ textAlign: 'center' })" title="Align Center">
            <icon src="editor/align-center.svg"></icon>
          </span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.align({ textAlign: 'right' }) }" @click="commands.align({ textAlign: 'right' })" title="Align Right">
            <icon src="editor/align-right.svg"></icon>
          </span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.align({ textAlign: 'justify' }) }" @click="commands.align({ textAlign: 'justify' })" title="Align Justify">
            <icon src="editor/align-justify.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.bullet_list() }" @click="commands.bullet_list" title="Unordered List">
            <icon src="editor/list-ul.svg"></icon>
          </span>

          <span class="rich-editor__icon" :class="{ 'rich-editor__icon--active': isActive.ordered_list() }" @click="commands.ordered_list" title="Ordered List">
            <icon src="editor/list-ol.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon" @click="commands.horizontal_rule" title="Horizontal Rule">
            <icon src="editor/horizontal-rule.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.underline() }" @click="commands.underline" title="Remove Formatting">
            <icon src="editor/remove-formatting.svg"></icon>
          </span>

        </div>

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

            <div class="rich-editor__modal-row">
              <label>styles</label>
              <input class="rich-editor__modal-control" type="text" v-model="link.attrs.style">
            </div>

            <div class="rich-editor__modal-row">
              <label>target</label>
              <select class="rich-editor__modal-control" v-model="link.attrs.target">
                <option v-for="target in linkTargets" :value="target.id">{{ target.value }}</option>
              </select>
            </div>

            <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
              <button class="button button--small" @click.prevent="saveLink(commands.link)">Save</button>
            </div>
          </div>
        </div>

        <div
          v-if="embed.active"
          class="rich-editor__modal rich-editor__modal--embed"
        >
          <header class="rich-editor__modal-header">
            <h3 class="rich-editor__modal-heading">Embed Video</h3>
            <a @click.prevent="hideEmbedModal()" class="rich-editor__modal-close"><i class="fa fa-times"></i></a>
          </header>
          <div class="rich-editor__modal-form">

            <div class="rich-editor__modal-row">
              <label>Url</label>
              <input class="rich-editor__modal-control" type="text" ref="embedInput" v-model="embed.url">
            </div>

            <div class="rich-editor__modal-row">
              <label>id</label>
              <input class="rich-editor__modal-control" type="text" v-model="embed.attrs.id">
            </div>

            <div class="rich-editor__modal-row">
              <label>class</label>
              <input class="rich-editor__modal-control" type="text" v-model="embed.attrs.class">
            </div>

            <div class="rich-editor__modal-row">
              <label>styles</label>
              <input class="rich-editor__modal-control" type="text" v-model="embed.attrs.style">
            </div>

            <div class="rich-editor__modal-row">
              <label>width</label>
              <input class="rich-editor__modal-control" type="text" v-model="embed.attrs.width">
            </div>

            <div class="rich-editor__modal-row">
              <label>height</label>
              <input class="rich-editor__modal-control" type="text" v-model="embed.attrs.height">
            </div>

            <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
              <button class="button button--small" @click.prevent="saveEmbed(commands.iframe)">Save</button>
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

            <div class="rich-editor__modal-row">
              <label>styles</label>
              <input class="rich-editor__modal-control" type="text" v-model="image.attrs.style">
            </div>

            <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
              <button class="button button--small" @click.prevent="saveImage(commands.image)">Save</button>
            </div>
          </div>
        </div>


      </div>

    </editor-menu-bar>
    <editor-content :editor="editor" v-if="!sourceCodeView"></editor-content>
    <textarea class="rich-editor__content" v-show="sourceCodeView" v-model="data" :name="name" :id="id"></textarea>

  </div>

</template>

<script>
  // todo: finish other buttons

  // Import the basic building blocks
  import { Editor, EditorContent, EditorMenuBar } from 'tiptap';
  import {
    Blockquote,
    HorizontalRule,
    OrderedList,
    BulletList,
    ListItem,
    Heading,
    Bold,
    Italic,
    Strike,
    Underline,
    History,
    HardBreak,
    Code,
  } from 'tiptap-extensions';
  import Sub from '../plugins/prose-mirror/Sub';
  import Sup from '../plugins/prose-mirror/Sup';
  import Align from '../plugins/prose-mirror/Align';
  import Link from '../plugins/prose-mirror/Link';
  import Iframe from '../plugins/prose-mirror/Iframe';
  import Image from '../plugins/prose-mirror/Image';

  export default {

    props: ['name', 'id', 'content'],

    data() {
      return {

        editor: new Editor({
          content: this.data,
          extensions: [
            new Blockquote(),
            new BulletList(),
            new Code(),
            new Heading({ levels: [1, 2, 3] }),
            new HorizontalRule(),
            new ListItem(),
            new OrderedList(),
            new Bold(),
            new Italic(),
            new Strike(),
            new Underline(),
            new History(),
            new HardBreak(),
            new Sub(),
            new Sup(),
            new Align(),
            new Link(),
            new Iframe(),
            new Image(),
          ],
          onUpdate: ({ getHTML }) => {
            this.data = getHTML();
          }
        }),

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
            style: null,
            class: null,
            title: null,
            target: null,
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

        embed: {
          active: false,
          url: '',

          attrs: {
            id: null,
            style: null,
            class: null,
            width: null,
            height: null,
          }
        },

        image: {
          active: false,
          url: '',
          update: false,

          attrs: {
            id: null,
            style: null,
            class: null,
            alt: null,
          }
        },
      }
    },

    components: {
      EditorMenuBar,
      EditorContent,
    },

    created() {
      this.editorId = `rich-editor--${Date.now()}`;

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

      eventBus.$on('rich-editor.open-image', data => {
        if (data.id === this.editorId) {
          this.showImageModal(data.attrs, true);
        }
      });

      if (this.content) {
        this.data = this.content;
        this.editor.setContent(this.data);
      }
    },

    beforeDestroy() {
      // Always destroy your editor instance when it's no longer needed
      this.editor.destroy()
    },

    watch: {
      data() {
        this.$emit('input', this.data);
      }
    },

    methods: {
      clearContent() {
        this.editor.clearContent(true);
        this.editor.focus();
      },

      showLinkModal(attrs) {
        let href = attrs.href;
        const type = this.getLinkType(href);
        if (type === 'email') {
          href = href.replace('mailto:', '');
        }

        this.link.url = href;
        this.link.active = true;
        this.link.type = type;

        this.setAttrs('link', attrs);

        this.showModal = true;
        this.$nextTick(() => {
          this.$refs.linkInput.focus()
        })
      },

      hideLinkModal() {
        this.link.url = null;
        this.link.active = false;
        this.link.type = 'internal';

        this.resetAttrs('link');

        this.showModal = false;
        this.modalType = null;
      },

      setLink(command, href) {
        const attrs = {
          href,
          ...this.link.attrs
        };

        if (!href) {
          command({ href })
        } else {
          command(attrs);
        }
      },

      saveLink(command) {
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
          this.setLink(command, url);
          this.hideLinkModal();
        }
      },


      showEmbedModal() {
        this.embed.active = true;
        this.showModal = true;
        this.$nextTick(() => {
          this.$refs.embedInput.focus()
        })
      },

      hideEmbedModal() {
        this.embed.active = false;
        this.showModal = false;
        this.embed.url = null;

        this.resetAttrs('embed');

      },

      saveEmbed(command) {
        let pass = true;
        if (!this.embed.url) {
          pass = false;
          window.swal({
            title: 'Something went wrong',
            text: 'You must enter a Url',
            icon: 'error'
          });
        }

        if (pass) {
          const attrs = {
            src: this.embed.url,
            ...this.embed.attrs
          };
          command(attrs);
          this.hideEmbedModal();
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

      saveImage(command) {
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
          command({ update: this.image.update, attrs });
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

      toggleSourceCodeView() {
        if (this.sourceCodeView) {
          this.hideSourceView();
        } else {
          this.showSourceView();
        }
      },

      showSourceView() {
        this.sourceCodeView = true;
      },

      hideSourceView() {
        this.sourceCodeView = false;
        this.editor.setContent(this.data);
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
