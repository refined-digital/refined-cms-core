
(function ($) {
  'use strict';

  // Plugin default options
  const defaultOptions = {
    type: 'simple'
  };
  const getFields = function(id) {
    const fields = {
      text: {
        label: 'Link Text',
        type: 'text',
      },
      linkType: {
        label: 'Link Type',
        type: function (field, prefix, lg) {
          const fieldId = `field-${field.name}-${id}`
          return `
            <div class="${prefix}input-row" id="${fieldId}">
              <div class="${prefix}input-infos">
                <label for="${fieldId}-link-type">
                  <span>${lg[field.label] ? lg[field.label] : field.label}</span>
                </label>
              </div>
              <div class="trumbowyg-input-html">
                <select id="${fieldId}-link-type" name="${field.name}" onchange="updateLinkType(event, '${id}')">
                  <option value="internal"${field.value === 'internal' ? ' selected="selected"' : ''}>Internal Page</option>
                  <option value="external"${field.value === 'external' ? ' selected="selected"' : ''}>External Link</option>
                  <option value="media"${field.value === 'media' ? ' selected="selected"' : ''}>File / Image</option>
                  <option value="email"${field.value === 'email' ? ' selected="selected"' : ''}>Email</option>
                </select>
              </div>
            </div>
            <script>
              $('#${fieldId}-link-type').trigger('change');
            </script>
          `
        }
      },
      url: {
        label: 'URL',
        required: true,
        type: function (field, prefix, lg) {
          const fieldId = `field-${field.name}-${id}`
          return `
            <div class="${prefix}input-row" id="${fieldId}">
              <div class="${prefix}input-infos">
                <label for="${fieldId}-url">
                  <span id="${fieldId}-url-label">${lg[field.label] ? lg[field.label] : field.label}</span>
                </label>
              </div>
              <div class="trumbowyg-input-html">
                <div class="rich-editor__link">
                  <div class="rich-editor__link-row rich-editor__link--input">
                    <input type="text" id="${fieldId}-url" name="${field.name}" value="${field.value || ''}"/>
                  </div>
                  <div class="rich-editor__link-row rich-editor__link--external">
                    Must start with <code>http://</code> or <code>https://</code>
                  </div>
                  <div class="rich-editor__link-row rich-editor__link--internal rich-editor__link-button rich-editor__link-button--link">
                    <button class="button button--small button--green" onclick="loadSitemapModal(event, '${id}', '${fieldId}')">Browse Server</button>
                  </div>
                  <div class="rich-editor__link-row rich-editor__link--media rich-editor__link-button rich-editor__link-button--media">
                    <button class="button button--small button--green" onclick="loadMediaModal(event, '*', '${id}', '${fieldId}')">Browse Media</button>
                  </div>
                </div>
              </div>
            </div>
          `
        }
      },
      title: {
        label: 'Title',
        type: 'text',
        required: false,
      },
      id: {
        label: 'Id',
        type: 'text',
        required: false,
      },
      class: {
        label: 'Class',
        type: 'text',
        required: false,
      },
      target: {
        label: 'Target',
        type: function (field, prefix, lg) {
          const fieldId = `field-${field.name}-${id}`
          return `
            <div class="${prefix}input-row" id="${fieldId}">
              <div class="${prefix}input-infos">
                <label>
                  <span>${lg[field.label] ? lg[field.label] : field.label}</span>
                </label>
              </div>
              <div class="trumbowyg-input-html">
                <select id="${fieldId}-link-type" name="${field.name}">
                  <option value="_self"${field.value === '_self' ? ' selected="selected"' : ''}>Same Window</option>
                  <option value="_blank"${field.value === '_blank' ? ' selected="selected"' : ''}>New Window</option>
                </select>
              </div>
            </div>
          `
        }
      },
      rel: {
        label: 'Rel',
        type: function (field, prefix, lg) {
          const fieldId = `field-${field.name}-${id}`
          const options = [
            'none',
            'alternate',
            'author',
            'bookmark',
            'external',
            'help',
            'license',
            'next',
            'nofollow',
            'noopener',
            'noreferrer',
            'prev',
            'search',
            'tag',
          ]
          let html = `
            <div class="${prefix}input-row" id="${fieldId}">
              <div class="${prefix}input-infos">
                <label for="${fieldId}-rel">
                  <span>${lg[field.label] ? lg[field.label] : field.label}</span>
                </label>
              </div>
              <div class="trumbowyg-input-html">
                <select id="${fieldId}-rel" name="${field.name}">
            `
            options.forEach(option => {
                html += `<option value="${option}"${field.value === option ? ' selected="selected"' : ''}>${option}</option>
    `
            })
            html += `
                </select>
              </div>
            </div>
          `

          return html;
        }
      },
    }

    if (defaultOptions.type === 'simple') {
      delete fields.target;
      delete fields.rel;
    }

    return fields;
  }

  const getLinkType = function(href) {
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
      type = 'media';
    }

    return type;

  }


  // If the plugin is a button
  const buildButtonDef = function (trumbowyg) {
    return {
      title: 'Insert Link',
      hasIcon: true,
      fn: function () {
        const id = trumbowyg.o.editorId
        const fields = getFields(id);

        const t = trumbowyg;
        const documentSelection = t.doc.getSelection();
        const selectedRange = documentSelection.getRangeAt(0);
        let node = documentSelection.focusNode;
        let text = new XMLSerializer().serializeToString(selectedRange.cloneContents()) || selectedRange + '';
        let url;
        let title;
        let target;
        let rel;
        let fieldId;
        let klass;

        while (['A', 'DIV'].indexOf(node.nodeName) < 0) {
          node = node.parentNode;
        }

        if (node && node.nodeName === 'A') {
          const $a = $(node);
          text = $a.text();
          url = $a.attr('href');
          title = $a.attr('title');
          target = $a.attr('target') || '_self';
          rel = $a.attr('rel');
          fieldId = $a.attr('id');
          klass = $a.attr('class');

          const range = t.doc.createRange();
          range.selectNode(node);
          documentSelection.removeAllRanges();
          documentSelection.addRange(range);
        }

        fields.linkType.value = getLinkType(url);

        if (text) {
          fields.text.value = text;
        }

        if (url) {
          if (url.startsWith('mailto:')) {
            url = url.replace('mailto:', '');
          }
          fields.url.value = url
        }

        if (fieldId) {
          fields.id.value = fieldId
        }

        if (klass) {
          fields.class.value = klass
        }

        if (title) {
          fields.title.value = title
        }

        if (target && fields.target) {
          fields.target.value = target
        }

        if (rel && fields.rel) {
          fields.rel.value = rel
        }

        t.saveRange();

        trumbowyg.openModalInsert(
          'Insert Link',
          fields,
          function(values) {
            let url = trumbowyg.prependUrlPrefix(values.url);
            if (!url.length) {
              return false;
            }

            if (values.linkType === 'external' && !(url.startsWith('https://') || url.startsWith('http://'))) {
              const form = $('.trumbowyg-modal-box form');
              const field = $(`:input[name="url"]`, form);
              trumbowyg.addErrorOnModalField(field, 'Required');
              return false;
            }

            if (values.linkType === 'email' && !url.startsWith('mailto:')) {
              url = `mailto:${url}`
            }

            const link = $(['<a href="', url, '">', values.text || values.url, '</a>'].join(''));

            if (values.title) {
              link.attr('title', values.title);
            }

            if (values.id) {
              link.attr('id', values.id);
            }

            if (values.class) {
              link.attr('class', values.class);
            }

            // auto add the target to open in new window for external and media files
            if (defaultOptions.type === 'simple') {
              switch(values.linkType) {
                case 'external':
                case 'media':
                  console.log('should be adding me a target');
                  link.attr('target', '_blank');
                  break;
              }
            } else {
              if(values.target && values.target === '_blank') {
                link.attr('target', values.target);
              }
            }


            // add no follow option for any external websites
            if (defaultOptions.type === 'simple') {
              if (values.linkType === 'external') {
                link.attr('rel', 'nofollow');
              }
            } else {
              if (values.rel && values.rel !== 'none') {
                link.attr('rel', values.rel);
              }
            }

            trumbowyg.range.deleteContents();
            trumbowyg.range.insertNode(link[0]);
            trumbowyg.syncCode();
            trumbowyg.$c.trigger('tbwchange');
            return true
          }
        )
      }
    }
  }

  $.extend(true, $.trumbowyg, {
    // Add some translations
    langs: {
      en: {
        refinedLink: 'Insert Link'
      }
    },
    // Register plugin in Trumbowyg
    plugins: {
      refinedLink: {
        // Code called by Trumbowyg core to register the plugin
        init: function (trumbowyg) {
          console.log(trumbowyg.o);
          if (trumbowyg.o.linkType && trumbowyg.o.linkType === 'advanced') {
            defaultOptions.type = 'advanced'
          }
          // Fill current Trumbowyg instance with the plugin default options
          trumbowyg.o.plugins.refinedLink = $.extend(true, {},
            defaultOptions,
            trumbowyg.o.plugins.refinedLink || {}
          );

          // If the plugin is a button
          trumbowyg.addBtnDef('refinedLink', buildButtonDef(trumbowyg));
        },

        // Return a list of button names which are active on current element
        tagHandler: function (element, trumbowyg) {
          return [];
        },

        destroy: function (trumbowyg) {},

        getFields: function(editorId) {
          return getFields(editorId);
        }
      }
    }
  })
})(jQuery);
