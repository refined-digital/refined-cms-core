import { Node, mergeAttributes } from '@tiptap/core';

// Inline atom node that renders a token (e.g. [[field:5]], [[fields]], [Form Name])
// as a removable chip showing a friendly NAME, while serialising back to the raw
// token in the stored HTML. The label map is supplied via `labels` config so chips
// read e.g. "Message" / "Form Name", not the raw token.
//
// In the DOM a token is <span data-fb-token="[[field:5]]">Name</span>; that data
// attribute is the source of truth, so stored HTML containing raw token text is
// upgraded to chips on load (see RichText tokensToChips).
export const FieldToken = Node.create({
  name: 'fieldToken',
  group: 'inline',
  inline: true,
  atom: true,
  selectable: true,

  addOptions() {
    return { labels: {} };
  },

  addAttributes() {
    return {
      token: {
        default: null,
        parseHTML: (el) => el.getAttribute('data-fb-token'),
        renderHTML: (attrs) => (attrs.token ? { 'data-fb-token': attrs.token } : {}),
      },
    };
  },

  parseHTML() {
    return [{ tag: 'span[data-fb-token]' }];
  },

  // serialised HTML (what gets stored before chipsToTokens strips it back to the
  // raw token): a labelled span carrying the token.
  renderHTML({ HTMLAttributes, node }) {
    const label = this.options.labels[node.attrs.token] || node.attrs.token;
    return ['span', mergeAttributes(HTMLAttributes, { class: 'fb-token' }), label];
  },

  // interactive chip in the editor, with a remove (×) button
  addNodeView() {
    return ({ node, editor, getPos }) => {
      const dom = document.createElement('span');
      dom.className = 'fb-token fb-token--removable';
      dom.contentEditable = 'false';
      dom.setAttribute('data-fb-token', node.attrs.token);

      const label = document.createElement('span');
      label.className = 'fb-token__label';
      label.textContent = this.options.labels[node.attrs.token] || node.attrs.token;
      dom.appendChild(label);

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'fb-token__remove';
      remove.textContent = '×';
      remove.addEventListener('mousedown', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (typeof getPos === 'function') {
          const pos = getPos();
          editor
            .chain()
            .focus()
            .deleteRange({ from: pos, to: pos + node.nodeSize })
            .run();
        }
      });
      dom.appendChild(remove);

      return { dom };
    };
  },
});
