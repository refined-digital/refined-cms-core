import { Mark } from 'tiptap'
import { toggleMark, markInputRule, markPasteRule } from 'tiptap-commands'

export default class Sup extends Mark {

  get name() {
    return 'sup'
  }

  get schema() {
    return {
      parseDOM: [
        { tag: 'sup' },
      ],
      toDOM: () => ['sup', 0],
    }
  }

  commands({ type }) {
    return () => toggleMark(type)
  }

  inputRules({ type }) {
    return [
      markInputRule(/(?:`)([^`]+)(?:`)$/, type),
    ]
  }

  pasteRules({ type }) {
    return [
      markPasteRule(/(?:`)([^`]+)(?:`)/g, type),
    ]
  }

}
