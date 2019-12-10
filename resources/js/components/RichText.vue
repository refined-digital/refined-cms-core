<template>
  <div class="form__control--rich-text rich-editor">
    <textarea class="form__control" v-model="data" :name="name" :id="id"></textarea>

    <editor-menu-bar :editor="editor" v-slot="{ commands, isActive, getMarkAttrs }">
      <div>
        <div class="rich-editor__menu">
<!--
          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.underline() }" @click="commands.underline" title="View Source">
            <icon src="editor/view-source.svg"></icon>
          </span>

          <span class="rich-editor__menu-divider"></span>
-->
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
<!--
          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.underline() }" @click="commands.underline" title="Insert Image">
            <icon src="editor/image.svg"></icon>
          </span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.strike() }" @click="commands.strike" title="Embed Video">
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
-->
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
<!--
          <span class="rich-editor__menu-divider"></span>

          <span class="rich-editor__icon todo" :class="{ 'rich-editor__icon--active': isActive.underline() }" @click="commands.underline" title="Remove Formatting">
            <icon src="editor/remove-formatting.svg"></icon>
          </span>
-->
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
              Must include <code>http://</code> or <code>https://</code>
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
              <input class="rich-editor__modal-control" type="text" v-model="link.title">
            </div>

            <div class="rich-editor__modal-row">
              <label>id</label>
              <input class="rich-editor__modal-control" type="text" v-model="link.id">
            </div>

            <div class="rich-editor__modal-row">
              <label>class</label>
              <input class="rich-editor__modal-control" type="text" v-model="link.class">
            </div>

            <div class="rich-editor__modal-row">
              <label>styles</label>
              <input class="rich-editor__modal-control" type="text" v-model="link.style">
            </div>

            <div class="rich-editor__modal-row rich-editor__modal-row--buttons">
              <button class="button button--small" @click.prevent="saveLink(commands.link, link.url)">Save</button>
            </div>
          </div>
        </div>
      </div>

    </editor-menu-bar>
    <editor-content :editor="editor"></editor-content>

  </div>

</template>

<script>
  // iframe https://github.com/scrumpy/tiptap/blob/master/examples/Components/Routes/Embeds/index.vue

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

  export default {

    props: ['name', 'id', 'content'],

    data() {
      return {
        link: {
          active: false,
          url: null,
          type: 'internal',

          id: null,
          style: null,
          class: null,
          title: null,
        },

        linkTypes: [
          { id: 'internal', value: 'Internal Page' },
          { id: 'external', value: 'External Page' },
          { id: 'file', value: 'File / Image' },
          { id: 'email', value: 'Email' },
        ],

        showModal: false,

        editor: new Editor({
          content: this.content,
          extensions: [
            new Blockquote(),
            new BulletList(),
            new Code(),
            new Heading({ levels: [1, 2, 3] }),
            new HorizontalRule(),
            new ListItem(),
            new OrderedList(),
            new Link(),
            new Bold(),
            new Italic(),
            new Strike(),
            new Underline(),
            new History(),
            new HardBreak(),
            new Sub(),
            new Sup(),
            new Align(),
          ],
          onUpdate: ({ getHTML }) => {
            this.data = getHTML();
          }
        }),

        editorId: null,

        data: '',
      }
    },

    components: {
      EditorMenuBar,
      EditorContent,
    },

    created() {
      this.editorId = `rich-editor--${Date.now()}`;
      eventBus.$on('selecting-link', link => {
        this.link.url = link;
      });
      eventBus.$on('selecting-file', data => {
        this.link.url = data.link.original.replace(data.link.basePath, '/');
      });

      if (this.content) {
        this.data = this.content;
      }
    },

    beforeDestroy() {
      // Always destroy your editor instance when it's no longer needed
      this.editor.destroy()
    },

    watch: {
      data() {
        console.log('has data updated?', this.data);
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
        this.link.title = attrs.title;
        this.link.id = attrs.id;
        this.link.style = attrs.style;
        this.link.class = attrs.class;

        this.showModal = true;

        this.$nextTick(() => {
          this.$refs.linkInput.focus()
        })
      },

      hideLinkModal() {
        this.link.url = null;
        this.link.active = false;
        this.link.type = 'internal';
        this.link.title = null;
        this.link.id = null;
        this.link.style = null;
        this.link.class = null;

        this.showModal = false;
      },

      setLink(command, href) {
        const attrs = {
          href,
          title: this.link.title,
          id: this.link.id,
          style: this.link.style,
          class: this.link.class,
        };
        command(attrs)
      },

      saveLink(command, href) {
        let url = href;

        if (this.link.type === 'email') {
          url = `mailto:${href}`;
        }

        this.setLink(command, url);
        this.hideLinkModal();
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
        window.loadSitemapModal(this.editorId);
      },

      loadMediaModal() {
        window.loadMediaModal(this.editorId, '*');
      },
    }

  }
</script>
