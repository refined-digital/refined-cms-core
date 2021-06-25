<template>
  <div>
    <textarea :name="name" :id="id" ref="editor"></textarea>
  </div>
</template>

<script>

import jQuery from 'jquery'
window.jQuery = window.$ = jQuery
// import the editor
import 'trumbowyg'

// import the editor's css
import 'trumbowyg/dist/ui/trumbowyg.css';

// import additional plugins
import 'trumbowyg/plugins/cleanpaste/trumbowyg.cleanpaste.js';
import 'trumbowyg/plugins/noembed/trumbowyg.noembed.js';
import 'trumbowyg/plugins/table/trumbowyg.table.js';
import 'trumbowyg/plugins/fontsize/trumbowyg.fontsize.js';
import 'trumbowyg/plugins/history/trumbowyg.history.js';
import 'trumbowyg/plugins/pasteembed/trumbowyg.pasteembed.js';
import '../plugins/trumbowyg/refined-insert-image/trumbowyg.refined.insert-image.js';
import '../plugins/trumbowyg/refined-link/trumbowyg.refined.link.js';
import '../plugins/trumbowyg/refined-code/trumbowyg.refined.code.js';

// reset the svg path to the website's url
$.trumbowyg.svgPath = '/vendor/refined/core/svg/editor-icons.svg'

window.loadMediaModal = function(event, type, id, fieldId) {
  event.preventDefault();
  document.querySelector('body').classList.add('body-has-modal');

  eventBus.$emit('media-set-type', type);
  eventBus.$emit('media-reload');
  window.app.media.showModal = true;
  window.app.media.model = id;
  window.app.media.fieldId = fieldId;
  window.app.media.type = type;
}

window.loadSitemapModal = function(event, id, fieldId) {
  event.preventDefault();
  document.querySelector('body').classList.add('body-has-modal');

  eventBus.$emit('sitemap-reload');
  window.app.sitemap.showModal = true;
  window.app.sitemap.active = true;
  window.app.sitemap.model = id;
  window.app.sitemap.fieldId = fieldId;
}

window.updateLinkType = function(event, id) {
  const element = event.target;
  const fieldId = `field-url-${id}`;
  const row = document.getElementById(fieldId)

  const inputRow = row.querySelector('.rich-editor__link--input')
  const inputField = document.getElementById(`${fieldId}-url`)
  const internalField = row.querySelector('.rich-editor__link--internal');
  const externalField = row.querySelector('.rich-editor__link--external');
  const mediaField = row.querySelector('.rich-editor__link--media');
  const urlLabel = document.getElementById(`${fieldId}-url-label`)

  const modal = document.querySelector('.trumbowyg-modal');
  const modalBox = modal.querySelector('.trumbowyg-modal-box');

  internalField.style.display = 'none';
  externalField.style.display = 'none';
  mediaField.style.display = 'none';
  inputField.readOnly = false;
  urlLabel.innerText = 'Url'
  inputRow.classList.remove('rich-editor__link-row--no-padding');

  switch(element.value) {
    case 'internal':
      internalField.style.display = 'block'
      break;
    case 'external':
      externalField.style.display = 'block'
      break;
    case 'media':
      mediaField.style.display = 'block'
      inputField.readOnly = true;
      break;
    case 'email':
      urlLabel.innerText = 'Email Address'
      inputRow.classList.add('rich-editor__link-row--no-padding');
      break;
  }

  // adjust the modal height
  const modalHeight = parseInt(modal.style.height);
  const modalBoxHeight = modalBox.offsetHeight + 10; // the + 10 comes from trumbowyg code
  if (modalHeight && modalBoxHeight !== modalHeight) {
    modal.style.height = `${modalBoxHeight}px`;
  }
}

export default {
  props: ['name', 'id', 'content'],

  data() {
    return {
      data: '',
      editor: {
        el: null,
        eventPrefix: 'tbw',
        svgPath: '/vendor/refined/core/svg/editor-icons.svg',

        config: {
          editorId: null,
          btns: [],
          btnsDef: {
            refinedFormatting: {
              title: 'Formatting',
              // hasIcon: false
            },
            pre: {
              fn: 'formatBlock'
            },
          },
          removeformatPasted: true,
          imageWidthModalEdit: true,
          changeActiveDropdownIcon: true,
          tagsToRemove: ['script', 'link'],
          tagsToKeep: ['hr', 'img', 'embed', 'iframe', 'pre', 'code'],
          linkType: 'simple',
          imgDblClickHandler: (e) => {
            const $img = e.currentTarget;
            let src = $img.src.replace(window.app.siteUrl, '/').replace('//', '/');
            const alt = $img.alt;
            const id = $img.id;
            const klass = $img.className;

            const base64 = '(Base64)';
            if (src.indexOf('data:image') === 0) {
              src = base64;
            }


            const options = $.trumbowyg.plugins.refinedInsertImage.getFields(this.editor.config.editorId);
            if (src && options.src) {
              options.src.value = src;
            }
            if (id && options.id) {
              options.id.value = id;
            }
            if (alt && options.alt) {
              options.alt.value = alt;
            }
            if (klass && options.class) {
              options.class.value = klass;
            }

            this.editor.el.trumbowyg('openModalInsert', {
              title: 'Edit Image',
              fields: options,
              callback: function(values) {
                $img.src = values.src;
                if (values.alt) {
                  $img.alt = values.alt;
                } else {
                  $img.removeAttr('alt');
                }
                if (values.class) {
                  $img.className = values.class;
                } else {
                  $img.removeAttr('class')
                }

                return true
              }
            })

            return false;

          }
        }
      }
    }
  },

  mounted() {
    this.editor.config.editorId = `trumbowyg-${this.id}-${Date.now()}`;

    // Return early if instance is already loaded
    if (this.editor.el) return;

    // Store DOM
    this.editor.el = jQuery(this.$refs.editor);

    // Init editor with config
    this.editor.el.trumbowyg(this.editor.config);
    // Set initial value
    this.editor.el.trumbowyg('html', this.data);

    // Watch for further changes
    this.editor.el.on(`${this.editor.eventPrefix}change`, this.onChange);

    // Blur event for validation libraries
    this.editor.el.on(`${this.editor.eventPrefix}blur`, this.onBlur);

    // Register events
    this.registerEvents();
  },

  created() {
    const config = window.app.richEditor;

    if (config.toolbar) {
      this.editor.config.btns = config.toolbar;
    }

    if (config.formatting) {
      this.editor.config.btnsDef.refinedFormatting = {
        ...this.editor.config.btnsDef.refinedFormatting,
        ...config.formatting
      }
    }

    if (config.fontSize) {
      if (!this.editor.config.plugins) {
        this.editor.config.plugins = {}
      }
      this.editor.config.plugins.fontsize = {
        sizeList: config.fontSize
      }
    }

    if (config.link && config.link.type && config.link.type === 'advanced') {
      this.editor.config.linkType = 'advanced';
    }

    if (config.tagClasses) {
      this.editor.config.tagClasses = config.tagClasses;
    }

    if (this.content) {
      this.data = this.content;
    }


    eventBus.$on('selecting-file', data => {
      document.querySelector('body').classList.remove('body-has-modal');

      if (data.model === this.editor.config.editorId) {
        const type = window.app.media.type === 'Image' ? 'image' : 'link';
        const url = data.link.original.replace(data.link.basePath, '/')

        if (type === 'image') {
          this.loadImageThumb(url, window.app.media.fieldId);
        }

        if (type === 'link') {
          this.loadLink(url, window.app.media.fieldId);
        }
      }

      eventBus.$emit('media-clear');
    });

    eventBus.$on('selecting-link', link => {
      document.querySelector('body').classList.remove('body-has-modal');

      if (window.app.sitemap.model === this.editor.config.editorId) {
        this.loadLink(link, window.app.sitemap.fieldId);
      }

      eventBus.$emit('sitemap-clear');
    });
  },

  methods:  {
    /**
     * Emit input event with current editor value
     * This will update v-model on parent component
     * This method gets called when value gets changed by editor itself
     *
     * @param event
     */
    onChange(event) {
      this.data = event.target.value;
      this.$emit('input', event.target.value);
    },

    /**
     * This event is different from tbw-blur event
     *
     * @param event
     */
    onBlur(event) {
      this.data = event.target.value;
      this.$emit('blur', event.target.value);
    },

    /**
     * Emit all available events
     */
    registerEvents() {
      const events = [
        'init', 'focus', 'blur', 'change', 'resize', 'paste', 'openfullscreen', 'closefullscreen', 'close', 'modalopen', 'modalclose', 'input'
      ];
      events.forEach((name) => {
        this.editor.el.on(`${this.editor.eventPrefix}${name}`, (...args) => {
          this.$emit(`${this.editor.eventPrefix}-${name}`, ...args);
        });
      })
    },

    loadImageThumb(src, fieldId) {
      const element = document.getElementById(fieldId);
      if (element) {
        const image = element.querySelector('.rich-editor__modal-thumb');
        const input = element.querySelector('input')

        if (image) {
          if (src) {
            const img = new Image();
            img.onload = function() {};
            img.src = src;
            image.style.backgroundImage = `url(${src})`;
          } else {
            image.style.backgroundImage = null;
          }
        }

        if (input) {
          input.value = src;
        }

      }
    },

    loadLink(src, fieldId) {
      const element = document.getElementById(`${fieldId}-url`)
      if (element) {
        element.value = src;
      }
    },

  },

  beforeDestroy() {
    if (!this.editor.el) return;

    this.editor.el.trumbowyg('destroy');
    this.editor.el = null;
  },

};
</script>
