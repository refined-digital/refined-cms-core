import { Mark } from 'tiptap'
import { toggleMark, markInputRule, markPasteRule } from 'tiptap-commands'

export default class Sub extends Mark {

  get name() {
    return 'sub'
  }

  get schema() {
    return {
      parseDOM: [
        { tag: 'sub' },
      ],
      toDOM: () => ['sub', 0],
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
