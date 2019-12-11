import { Node } from 'tiptap'

export default class Iframe extends Node {

  get name() {
    return 'iframe'
  }

  get schema() {
    return {
      attrs: {
        src: {
          default: null,
        },
        class: {
          default: null,
        },
        id: {
          default: null,
        },
        style: {
          default: null,
        },
        width: {
          default: null,
        },
        height: {
          default: null,
        },
      },
      group: 'inline',
      inline: true,
      selectable: false,
      parseDOM: [{
        tag: 'iframe',
        getAttrs: dom => ({
          src: dom.getAttribute('src'),
          class: dom.getAttribute('class'),
          id: dom.getAttribute('id'),
          style: dom.getAttribute('style'),
          width: dom.getAttribute('width'),
          height: dom.getAttribute('height'),
        }),
      }],
      toDOM: node => ['iframe', {
        ...node.attrs,
        frameborder: 0,
        allowfullscreen: 'true',
      }],
    }
  }

  commands({ type }) {
    return attrs => (state, dispatch) => {
      const { selection } = state;
      const position = selection.$cursor ? selection.$cursor.pos : selection.$to.pos;
      const node = type.create(attrs);
      const transaction = state.tr.insert(position, node);
      dispatch(transaction);
    }
  }
}
