import {toggleMark} from 'tiptap-commands'
import {Mark}       from 'tiptap'

export default class Align extends Mark {

  get name() {
    return 'align'
  }

  get defaultOptions() {
    return {
      textAlign: ['left', 'center', 'right', 'justify'],
    }
  }

  get schema() {
    return {
      attrs    : {
        textAlign: {
          default: 'left',
        },
      },
      content  : 'inline*',
      group    : 'block',
      defining : true,
      draggable: false,
      parseDOM : this.options.textAlign.map(align => ({
        tag  : 'div[style="text-align:'+align+'"]',
        attrs: { textAlign: align },
      })),
      toDOM    :
        node => {
          return ['div', {
            style       : `text-align:${node.attrs.textAlign}`
          }, 0]
        }
    }
  }

  commands({ type }) {
    return (attrs) => toggleMark(type, attrs)
  }

}