
(function ($) {
  'use strict';

  // Plugin default options
  const defaultOptions = {  };
  const getFields = function(id) {
    return {
      src: {
        label: 'URL',
        required: true,
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
                <input type="hidden" name="${field.name}" value="${field.value || ''}"/>
                <div class="rich-editor__modal">
                  <div class="rich-editor__modal-image">
                    <div class="rich-editor__modal-thumb"${field.value ? ` style="background-image: url(${field.value})"` : ''}></div>
                  </div> 
                  <button class="button button--green button--small" onclick="loadMediaModal(event, 'Image', '${id}', '${fieldId}')">Browse Server</button>
                </div>
              </div>
            </div>
          `
        }
      },
      alt: {
        label: 'Alternative Text',
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
      }
    };
  }


  // If the plugin is a button
  const buildButtonDef = function (trumbowyg) {
    return {
      title: 'Insert Image',
      hasIcon: true,
      fn: function () {
        const id = trumbowyg.o.editorId
        const fields = getFields(id);

        trumbowyg.openModalInsert(
          'Insert Image',
          fields,
          function(values) {
            trumbowyg.execCmd('insertImage', values.src, false, true);
            const $img = $('img[src="' + values.src + '"]:not([alt])', trumbowyg.$box);
            $img.attr('alt', values.alt);

            if (values.alt) {
              $img.attr('alt', values.alt);
            }
            if (values.id) {
              $img.attr('id', values.id);
            }
            if (values.class) {
              $img.attr('class', values.class);
            }

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
        refinedInsertImage: 'Insert Image'
      }
    },
    // Register plugin in Trumbowyg
    plugins: {
      refinedInsertImage: {
        // Code called by Trumbowyg core to register the plugin
        init: function (trumbowyg) {
          // Fill current Trumbowyg instance with the plugin default options
          trumbowyg.o.plugins.refinedInsertImage = $.extend(true, {},
            defaultOptions,
            trumbowyg.o.plugins.refinedInsertImage || {}
          );

          // If the plugin is a button
          trumbowyg.addBtnDef('refinedInsertImage', buildButtonDef(trumbowyg));
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
