import { Node, Plugin } from 'tiptap'
import { nodeInputRule, updateMark } from 'tiptap-commands'

/**
 * Matches following attributes in Markdown-typed image: [, alt, src, title]
 *
 * Example:
 * ![Lorem](image.jpg) -> [, "Lorem", "image.jpg"]
 * ![](image.jpg "Ipsum") -> [, "", "image.jpg", "Ipsum"]
 * ![Lorem](image.jpg "Ipsum") -> [, "Lorem", "image.jpg", "Ipsum"]
 */
const IMAGE_INPUT_REGEX = /!\[(.+|:?)\]\((\S+)(?:(?:\s+)["'](\S+)["'])?\)/

export default class Image extends Node {

  get name() {
    return 'image'
  }

  get schema() {
    return {
      inline: true,
      attrs: {
        src: {},
        alt: {
          default: null,
        },
        id: {
          default: null,
        },
        style: {
          default: null,
        },
        class: {
          default: null,
        },
      },
      group: 'inline',
      draggable: true,
      parseDOM: [
        {
          tag: 'img[src]',
          getAttrs: dom => ({
            src: dom.getAttribute('src'),
            alt: dom.getAttribute('alt'),
            id: dom.getAttribute('id'),
            style: dom.getAttribute('style'),
            class: dom.getAttribute('class'),
          }),
        },
      ],
      toDOM: node => ['img', node.attrs],
    }
  }

  commands({ type }) {
    return data => {
      if (data.update) {
        return (state, dispatch) => {
          const newNode = type.create(data.attrs);
          const transaction = state.tr.replaceWith(this.editor.selection.from, this.editor.selection.to, newNode);
          dispatch(transaction);
        }
      } else {
        return (state, dispatch) => {
          const {selection} = state;
          const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos;
          const node = type.create(data.attrs);
          const transaction = state.tr.insert(position, node);
          dispatch(transaction);
        }
      }
    }
  }

  inputRules({ type }) {
    return [
      nodeInputRule(IMAGE_INPUT_REGEX, type, match => {
        const [, alt, src, title] = match;
        return {
          src,
          alt,
          title,
        }
      }),
    ]
  }

  get plugins() {
    return [
      new Plugin({
        props: {
          handleDoubleClick: (view, pos, event) => {
            const attrs = view.state.selection.node.attrs;

            eventBus.$emit('rich-editor.open-image', {
              attrs,
              id: event.target.closest('.rich-editor').id
            });
          },
        },
      }),
    ]
  }

}
