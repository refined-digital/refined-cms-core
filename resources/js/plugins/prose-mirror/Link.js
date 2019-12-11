import { Mark } from 'tiptap'
import { updateMark, removeMark, pasteRule } from 'tiptap-commands'

export default class Link extends Mark {

  get name() {
    return 'link'
  }

  get schema() {
    return {
      attrs: {
        href: {
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
        title: {
          default: null,
        },
        target: {
          default: null,
        },
      },
      inclusive: false,
      parseDOM: [
        {
          tag: 'a[href]',
          getAttrs: dom => ({
            href: dom.getAttribute('href'),
            class: dom.getAttribute('class'),
            id: dom.getAttribute('id'),
            style: dom.getAttribute('style'),
            title: dom.getAttribute('title'),
            target: dom.getAttribute('target'),
          }),
        },
      ],
      toDOM: node => ['a', {
        ...node.attrs,
        rel: 'noopener noreferrer nofollow',
      }, 0],
    }
  }

  commands({ type }) {
    return attrs => {
      if (attrs.href) {
        return updateMark(type, attrs)
      }

      return removeMark(type)
    }
  }

  pasteRules({ type }) {
    return [
      pasteRule(
        /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+.~#?&//=]*)/g,
        type,
        url => ({ href: url }),
      ),
    ]
  }

}
