import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import { TextStyle } from '@tiptap/extension-text-style';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import TextAlign from '@tiptap/extension-text-align';
import Youtube from '@tiptap/extension-youtube';
import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableHeader } from '@tiptap/extension-table-header';
import { TableCell } from '@tiptap/extension-table-cell';
import { FontSize } from './FontSize';
import { FieldToken } from './FieldToken';

// preserves arbitrary id/class/title attributes on links and images so HTML
// authored in the old Trumbowyg editor round-trips without being stripped.
function keepAttrs(names) {
  const attrs = {};
  names.forEach((name) => {
    attrs[name] = {
      default: null,
      parseHTML: (el) => el.getAttribute(name),
      renderHTML: (attributes) => (attributes[name] ? { [name]: attributes[name] } : {}),
    };
  });
  return attrs;
}

const RefinedLink = Link.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      ...keepAttrs(['id', 'class', 'title']),
    };
  },
}).configure({
  openOnClick: false,
  autolink: false,
  HTMLAttributes: {},
});

const RefinedImage = Image.extend({
  addAttributes() {
    return {
      ...this.parent?.(),
      ...keepAttrs(['id', 'class']),
    };
  },
}).configure({
  inline: false,
  allowBase64: true,
});

export function buildExtensions(config = {}) {
  const headingClasses = config.tagClasses || {};

  return [
    StarterKit.configure({
      // we register our own configured Link below
      link: false,
      heading: {
        levels: [1, 2, 3, 4, 5, 6],
        HTMLAttributes: {},
      },
    }),
    TextStyle,
    FontSize,
    Subscript,
    Superscript,
    TextAlign.configure({
      types: ['heading', 'paragraph'],
    }),
    RefinedLink,
    RefinedImage,
    Youtube.configure({
      controls: true,
      nocookie: true,
    }),
    Table.configure({ resizable: true }),
    TableRow,
    TableHeader,
    TableCell,
    // field-token chips (form-builder notification editor). labels map raw
    // tokens -> friendly field names so chips read e.g. "Name", not [[field:5]].
    FieldToken.configure({ labels: config.tokenLabels || {} }),
  ];
}

// tag classes (e.g. h1 => 'heading') are applied per-render via the editor's
// editorProps in RichText.vue rather than baked into a node, matching the old
// Trumbowyg `tagClasses` behaviour.
export function tagClassesFor(config = {}) {
  return config.tagClasses || {};
}
