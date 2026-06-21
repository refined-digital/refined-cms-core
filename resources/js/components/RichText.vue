<template>
  <div class="rich-editor" :class="{ 'rich-editor--focused': focused }">
    <div class="rich-editor__toolbar" v-if="editor">
      <button type="button" class="rich-editor__btn" title="Undo" @click="editor.chain().focus().undo().run()"><i class="fas fa-undo"></i></button>
      <button type="button" class="rich-editor__btn" title="Redo" @click="editor.chain().focus().redo().run()"><i class="fas fa-redo"></i></button>

      <select class="rich-editor__format" :value="currentBlock" @change="applyFormat($event.target.value)">
        <option v-for="f in formats" :key="f.value" :value="f.value">{{ f.label }}</option>
      </select>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('bold') }" title="Bold" @click="editor.chain().focus().toggleBold().run()"><i class="fas fa-bold"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('italic') }" title="Italic" @click="editor.chain().focus().toggleItalic().run()"><i class="fas fa-italic"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('strike') }" title="Strikethrough" @click="editor.chain().focus().toggleStrike().run()"><i class="fas fa-strikethrough"></i></button>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('superscript') }" title="Superscript" @click="editor.chain().focus().toggleSuperscript().run()"><i class="fas fa-superscript"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('subscript') }" title="Subscript" @click="editor.chain().focus().toggleSubscript().run()"><i class="fas fa-subscript"></i></button>

      <select v-if="fontSizes.length" class="rich-editor__format" @change="applyFontSize($event.target.value)">
        <option value="">Size</option>
        <option v-for="size in fontSizes" :key="size" :value="size">{{ size }}</option>
      </select>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('link') }" title="Insert Link" @click="openLinkModal"><i class="fas fa-link"></i></button>
      <button type="button" class="rich-editor__btn" title="Remove Link" @click="editor.chain().focus().unsetLink().run()"><i class="fas fa-unlink"></i></button>
      <button type="button" class="rich-editor__btn" title="Insert Image" @click="openImageModal()"><i class="fas fa-image"></i></button>
      <button type="button" class="rich-editor__btn" title="Embed Video" @click="embedVideo"><i class="fab fa-youtube"></i></button>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'left' }) }" title="Align Left" @click="editor.chain().focus().setTextAlign('left').run()"><i class="fas fa-align-left"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'center' }) }" title="Align Center" @click="editor.chain().focus().setTextAlign('center').run()"><i class="fas fa-align-center"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'right' }) }" title="Align Right" @click="editor.chain().focus().setTextAlign('right').run()"><i class="fas fa-align-right"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive({ textAlign: 'justify' }) }" title="Justify" @click="editor.chain().focus().setTextAlign('justify').run()"><i class="fas fa-align-justify"></i></button>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('bulletList') }" title="Bullet List" @click="editor.chain().focus().toggleBulletList().run()"><i class="fas fa-list-ul"></i></button>
      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('orderedList') }" title="Numbered List" @click="editor.chain().focus().toggleOrderedList().run()"><i class="fas fa-list-ol"></i></button>

      <button type="button" class="rich-editor__btn" :class="{ 'is-active': editor.isActive('code') }" title="Code" @click="editor.chain().focus().toggleCode().run()"><i class="fas fa-code"></i></button>
      <button type="button" class="rich-editor__btn" title="Horizontal Rule" @click="editor.chain().focus().setHorizontalRule().run()"><i class="fas fa-minus"></i></button>
      <button type="button" class="rich-editor__btn" title="Clear Formatting" @click="editor.chain().focus().unsetAllMarks().clearNodes().run()"><i class="fas fa-eraser"></i></button>
    </div>

    <editor-content :editor="editor" class="rich-editor__content form__control" />
    <textarea :name="name" :id="id" class="rich-editor__source" v-model="data" hidden></textarea>

    <!-- Image modal -->
    <div class="rich-editor__modal-overlay" v-if="imageModal.open" @click.self="closeImageModal">
      <div class="rich-editor__modal-box">
        <h3>{{ imageModal.editing ? 'Edit Image' : 'Insert Image' }}</h3>
        <div class="rich-editor__modal">
          <div class="rich-editor__modal-image">
            <div class="rich-editor__modal-thumb" :style="imageModal.src ? `background-image: url(${imageModal.src})` : ''"></div>
          </div>
          <button type="button" class="button button--green button--small" @click="browseImage">Browse Server</button>
        </div>
        <label>URL</label>
        <input type="text" class="form__control" v-model="imageModal.src" :id="`${editorId}-image-src`">
        <label>Alternative Text</label>
        <input type="text" class="form__control" v-model="imageModal.alt">
        <label>Id</label>
        <input type="text" class="form__control" v-model="imageModal.elId">
        <label>Class</label>
        <input type="text" class="form__control" v-model="imageModal.className">
        <footer class="rich-editor__modal-actions">
          <button type="button" class="button button--grey button--small" @click="closeImageModal">Cancel</button>
          <button type="button" class="button button--green button--small" @click="confirmImage">Confirm</button>
        </footer>
      </div>
    </div>

    <!-- Link modal -->
    <div class="rich-editor__modal-overlay" v-if="linkModal.open" @click.self="closeLinkModal">
      <div class="rich-editor__modal-box">
        <h3>Insert Link</h3>
        <label>Link Text</label>
        <input type="text" class="form__control" v-model="linkModal.text">

        <label>Link Type</label>
        <select class="form__control" v-model="linkModal.type">
          <option value="internal">Internal Page</option>
          <option value="external">External Link</option>
          <option value="media">File / Image</option>
          <option value="email">Email</option>
          <option value="phone">Phone</option>
        </select>

        <label>{{ urlLabel }}</label>
        <input type="text" class="form__control" v-model="linkModal.url" :readonly="linkModal.type === 'media'">
        <div class="rich-editor__link-hint" v-if="linkModal.type === 'external'">Must start with <code>http://</code> or <code>https://</code></div>
        <button type="button" class="button button--small button--green" v-if="linkModal.type === 'internal'" @click="browseSitemap">Browse Server</button>
        <button type="button" class="button button--small button--green" v-if="linkModal.type === 'media'" @click="browseMedia">Browse Media</button>

        <template v-if="advancedLinks">
          <label>Title</label>
          <input type="text" class="form__control" v-model="linkModal.title">
          <label>Id</label>
          <input type="text" class="form__control" v-model="linkModal.elId">
          <label>Class</label>
          <input type="text" class="form__control" v-model="linkModal.className">
          <label>Target</label>
          <select class="form__control" v-model="linkModal.target">
            <option value="_self">Same Window</option>
            <option value="_blank">New Window</option>
          </select>
          <label>Rel</label>
          <select class="form__control" v-model="linkModal.rel">
            <option v-for="r in relOptions" :key="r" :value="r">{{ r }}</option>
          </select>
        </template>

        <footer class="rich-editor__modal-actions">
          <button type="button" class="button button--grey button--small" @click="closeLinkModal">Cancel</button>
          <button type="button" class="button button--green button--small" @click="confirmLink">Confirm</button>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount, onMounted, onUnmounted } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import eventBus from '../eventBus';
import { useUiStore } from '../stores/ui';
import { useConfigStore } from '../stores/config';
import { buildExtensions } from '../tiptap/extensions';

const props = defineProps(['name', 'id', 'content']);
const emit = defineEmits(['input', 'blur', 'update:modelValue']);

const ui = useUiStore();
const config = useConfigStore();

// unique per-instance id so the right editor responds to media/sitemap picks
const editorId = `rich-editor-${props.id}-${Date.now()}`;
const richConfig = config.richEditor || {};

const data = ref(props.content || '');
const focused = ref(false);

const fontSizes = computed(() => richConfig.fontSize || []);
const advancedLinks = computed(() => richConfig.link && richConfig.link.type === 'advanced');

const formats = [
  { label: 'Paragraph', value: 'paragraph' },
  { label: 'Heading 1', value: 'h1' },
  { label: 'Heading 2', value: 'h2' },
  { label: 'Heading 3', value: 'h3' },
  { label: 'Heading 4', value: 'h4' },
  { label: 'Heading 5', value: 'h5' },
  { label: 'Heading 6', value: 'h6' },
  { label: 'Quote', value: 'blockquote' },
  { label: 'Code Block', value: 'codeBlock' },
];

const relOptions = [
  'none', 'alternate', 'author', 'bookmark', 'external', 'help', 'license',
  'next', 'nofollow', 'noopener', 'noreferrer', 'prev', 'search', 'tag',
];

const editor = useEditor({
  content: props.content || '',
  extensions: buildExtensions(richConfig),
  editorProps: {
    attributes: { class: 'rich-editor__editable' },
  },
  onUpdate: ({ editor: e }) => {
    data.value = e.getHTML();
    emit('input', data.value);
    emit('update:modelValue', data.value);
  },
  onFocus: () => { focused.value = true; },
  onBlur: () => {
    focused.value = false;
    emit('blur', data.value);
  },
});

const currentBlock = computed(() => {
  if (!editor.value) return 'paragraph';
  for (let level = 1; level <= 6; level += 1) {
    if (editor.value.isActive('heading', { level })) return `h${level}`;
  }
  if (editor.value.isActive('blockquote')) return 'blockquote';
  if (editor.value.isActive('codeBlock')) return 'codeBlock';
  return 'paragraph';
});

function applyFormat(value) {
  const chain = editor.value.chain().focus();
  if (value === 'paragraph') {
    chain.setParagraph().run();
  } else if (value === 'blockquote') {
    chain.toggleBlockquote().run();
  } else if (value === 'codeBlock') {
    chain.toggleCodeBlock().run();
  } else if (/^h[1-6]$/.test(value)) {
    chain.setHeading({ level: parseInt(value.slice(1), 10) }).run();
  }
}

function applyFontSize(value) {
  if (!value) {
    editor.value.chain().focus().unsetFontSize().run();
  } else {
    editor.value.chain().focus().setFontSize(value).run();
  }
}

function embedVideo() {
  const url = window.prompt('Enter a YouTube URL');
  if (url) {
    editor.value.chain().focus().setYoutubeVideo({ src: url }).run();
  }
}

/* ---------------- image modal ---------------- */

const imageModal = reactive({
  open: false,
  editing: false,
  src: '',
  alt: '',
  elId: '',
  className: '',
});

function openImageModal(existing = null) {
  imageModal.editing = !!existing;
  imageModal.src = existing?.src || '';
  imageModal.alt = existing?.alt || '';
  imageModal.elId = existing?.id || '';
  imageModal.className = existing?.class || '';
  imageModal.open = true;
}

function closeImageModal() {
  imageModal.open = false;
}

function browseImage() {
  ui.openMediaModal({ type: 'Image', model: editorId, fieldId: `${editorId}-image` });
  eventBus.emit('media-set-type', 'Image');
  eventBus.emit('media-reload');
}

function confirmImage() {
  if (!imageModal.src) {
    closeImageModal();
    return;
  }
  editor.value.chain().focus().setImage({
    src: imageModal.src,
    alt: imageModal.alt || null,
    id: imageModal.elId || null,
    class: imageModal.className || null,
  }).run();
  closeImageModal();
}

/* ---------------- link modal ---------------- */

const linkModal = reactive({
  open: false,
  text: '',
  type: 'internal',
  url: '',
  title: '',
  elId: '',
  className: '',
  target: '_self',
  rel: 'none',
});

const urlLabel = computed(() => {
  if (linkModal.type === 'email') return 'Email Address';
  if (linkModal.type === 'phone') return 'Phone';
  return 'Url';
});

function getLinkType(href) {
  if (!href) return 'internal';
  if (href.includes('mailto:')) return 'email';
  if (href.includes('tel:')) return 'phone';
  if (href.includes('http://') || href.includes('https://')) return 'external';
  if (href.includes('/storage/uploads')) return 'media';
  return 'internal';
}

function openLinkModal() {
  const { state } = editor.value;
  const { from, to } = state.selection;
  const selectedText = state.doc.textBetween(from, to, ' ');
  const attrs = editor.value.getAttributes('link');

  let url = attrs.href || '';
  linkModal.type = getLinkType(url);
  if (url.startsWith('mailto:')) url = url.replace('mailto:', '');
  if (url.startsWith('tel:')) url = url.replace('tel:', '');

  linkModal.text = selectedText || attrs.text || '';
  linkModal.url = url;
  linkModal.title = attrs.title || '';
  linkModal.elId = attrs.id || '';
  linkModal.className = attrs.class || '';
  linkModal.target = attrs.target || '_self';
  linkModal.rel = attrs.rel || 'none';
  linkModal.open = true;
}

function closeLinkModal() {
  linkModal.open = false;
}

function browseSitemap() {
  ui.openSitemapModal({ model: editorId, fieldId: `${editorId}-link` });
  eventBus.emit('sitemap-reload');
}

function browseMedia() {
  ui.openMediaModal({ type: '*', model: editorId, fieldId: `${editorId}-link` });
  eventBus.emit('media-set-type', '*');
  eventBus.emit('media-reload');
}

function confirmLink() {
  let url = linkModal.url;
  if (!url) {
    closeLinkModal();
    return;
  }

  if (linkModal.type === 'external' && !(url.startsWith('http://') || url.startsWith('https://'))) {
    // mirror the old validation — require a protocol for external links
    return;
  }
  if (linkModal.type === 'email' && !url.startsWith('mailto:')) {
    url = `mailto:${url}`;
  }
  if (linkModal.type === 'phone' && !url.startsWith('tel:')) {
    url = `tel:${url.replace(/\s/g, '')}`;
  }

  let target = null;
  let rel = null;
  if (!advancedLinks.value) {
    if (linkModal.type === 'external' || linkModal.type === 'media') {
      target = '_blank';
    }
    if (linkModal.type === 'external') {
      rel = 'nofollow';
    }
  } else {
    if (linkModal.target === '_blank') target = '_blank';
    if (linkModal.rel && linkModal.rel !== 'none') rel = linkModal.rel;
  }

  const attrs = {
    href: url,
    target,
    rel,
    title: linkModal.title || null,
    id: linkModal.elId || null,
    class: linkModal.className || null,
  };

  const { from, to } = editor.value.state.selection;
  const hasSelection = from !== to;
  const text = linkModal.text || linkModal.url;

  if (hasSelection) {
    // apply the link to the current selection, replacing its text if the user
    // edited the Link Text field
    const selectedText = editor.value.state.doc.textBetween(from, to, ' ');
    if (text && text !== selectedText) {
      editor.value.chain().focus()
        .insertContentAt({ from, to }, text)
        .setTextSelection({ from, to: from + text.length })
        .setLink(attrs)
        .run();
    } else {
      editor.value.chain().focus().extendMarkRange('link').setLink(attrs).run();
    }
  } else {
    // no selection — insert the link text as a linked node
    editor.value.chain().focus()
      .insertContent(`<a href="${url}">${text}</a>`)
      .run();
    // re-apply attributes the inline html may not carry
    editor.value.chain().focus().extendMarkRange('link').setLink(attrs).run();
  }

  closeLinkModal();
}

/* ---------------- media / sitemap pick handlers (scoped by editorId) -------- */

function onSelectingFile(payload) {
  document.querySelector('body')?.classList.remove('body-has-modal');
  if (payload.model !== editorId) return;

  const url = payload.link.original.replace(payload.link.basePath, '/');
  const isImage = (ui.media.type || '').toLowerCase() === 'image';

  if (imageModal.open || isImage) {
    imageModal.src = url;
    if (!imageModal.open) openImageModal({ src: url });
  } else if (linkModal.open) {
    linkModal.url = url;
  }
  eventBus.emit('media-clear');
}

function onSelectingLink(href) {
  document.querySelector('body')?.classList.remove('body-has-modal');
  if (ui.sitemap.model !== editorId) return;
  linkModal.url = href;
  eventBus.emit('sitemap-clear');
}

onMounted(() => {
  eventBus.on('selecting-file', onSelectingFile);
  eventBus.on('selecting-link', onSelectingLink);
});

onUnmounted(() => {
  eventBus.off('selecting-file', onSelectingFile);
  eventBus.off('selecting-link', onSelectingLink);
});

onBeforeUnmount(() => {
  if (editor.value) {
    editor.value.destroy();
  }
});
</script>
