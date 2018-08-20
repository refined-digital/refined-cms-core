
try {
  window.$ = window.jQuery = require('jquery');
} catch (e) {}


window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.trumbowyg = {
  trumbowyg: {},
  options: {},
  methods: {
    getOptions: function(set) {
      if (typeof window.trumbowyg.options[set] != 'undefined') {
        return window.trumbowyg.options[set];
      }
      return {}
    },
    openModalInsert: function (title, fields, cmd) {
      var CONFIRM_EVENT = 'tbwconfirm', CANCEL_EVENT = 'tbwcancel';
      var t = window.trumbowyg.trumbowyg,
      prefix = t.o.prefix,
      lg = t.lang,
      html = '';

      $.each(fields, function (fieldName, field) {
        var l = field.label || fieldName,
        n = field.name || fieldName,
        a = field.attributes || {};

        var attr = Object.keys(a).map(function (prop) {
          return prop + '="' + a[prop] + '"';
        }).join(' ');

        switch (field.type) {
          case  'image':
            var id = prefix + Date.now();
            var img = field.value || '';
            html += '<div class="' + prefix + 'image-field" id="image-' + id + '">';
            html += '<input type="hidden" name="' + n + '"' + attr + ( img ? ' value="' + img + '"' : '')+'>';
            html += '<div class="' + prefix + 'image-field-thumb-holder"><div class="' + prefix + 'image-field-thumb-img"' +( img ? ' style="background-image: url(' + img + ')"' : '')+ '></div></div>';
            html += '<div class="button button--small button--green" onclick="loadMediaModal(\'image-'+ id +'\', \'Image\')">Browse Server</div>';
            html += '</div>';
            break;
          case  'linkType':
            var id = prefix + Date.now();
            var type = field.value ? window.trumbowyg.methods.getLinkType(field.value) : null;
            html += '<div class="' + prefix + 'link-field" id="link-' + id + '">';

              html += '<label><select name="' + n + '_type"' + attr + ' onchange="changeEditorLinkType(\'link-'+ id +'\', this.value)">';
                field.options.forEach(opt => {
                  html += '<option value="' + opt.value + '"' + ( type == opt.value ? ' selected' : '') + '>' + opt.name + '</option>';
                });
              html += '</select>';
              html += '<span class="' + prefix + 'input-infos"><span>' + field.label + '</span></span>';
              html += '</label>';

              html += '<div class="' + prefix + 'link-field-url">';
                html += '<label>';
                  html += '<input type="text" name="' + n + '" value="' + (field.value || '').replace(/"/g, '&quot;') + '"' + attr + '>';
                  html += '<span class="' + prefix + 'input-infos"><span class="link-field-label">'+(type == 'email' ? 'Email Address' : 'URL')+'</span></span>';
                html += '</label>';
                html += '<div class="button button--small button--green" data-type="internal" style="display: ' + ( (type == 'internal' || !type) ? 'block' : 'none') + '" onclick="loadSitemapModal(\'link-'+ id +'\')">Browse Sitemap</div>';
                html += '<div class="button button--small button--green" data-type="media" style="display: ' + (type == 'media' ? 'block' : 'none') + '" onclick="loadMediaModal(\'link-'+ id +'\', \'*\')">Browse Media</div>';
              html += '</div>';
              html += '<div class="' + prefix + 'link-field-note" data-type="external" style="display: ' + (type == 'external' ? 'block' : 'none') + '">Must include <code>http://</code> or <code>https://</code></div>';

            html += '</div>';
            break;
          default:
            html += '<label><input type="' + (field.type || 'text') + '" name="' + n + '"' +
            (field.type === 'checkbox' && field.value ? ' checked="checked"' : ' value="' + (field.value || '').replace(/"/g, '&quot;')) +
            '"' + attr + '><span class="' + prefix + 'input-infos"><span>' +
            (lg[l] ? lg[l] : l) +
            '</span></span></label>';
            break;
        }

      });

      return t.openModal(title, html)
        .on(CONFIRM_EVENT, function () {
          var $form = $('form', $(this)),
          valid = true,
          values = {};

          $.each(fields, function (fieldName, field) {
            var n = field.name || fieldName;

            var $field = $('input[name="' + n + '"]', $form),
            inputType = $field.attr('type');

            switch (inputType.toLowerCase()) {
              case 'checkbox':
                values[n] = $field.is(':checked');
                break;
              case 'radio':
                values[n] = $field.filter(':checked').val();
                break;
              default:
                values[n] = $field.val();
                break;
            }
            // Validate value
            if (field.required && values[n] === '') {
              valid = false;
              t.addErrorOnModalField($field, t.lang.required);
            } else if (field.pattern && !field.pattern.test(values[n])) {
              valid = false;
              t.addErrorOnModalField($field, field.patternError);
            }
          });

          if (valid) {
            t.restoreRange();

            if (cmd(values, fields)) {
              t.syncCode();
              t.$c.trigger('tbwchange');
              t.closeModal();
              $(this).off(CONFIRM_EVENT);
            }
          }
        })
        .one(CANCEL_EVENT, function () {
          $(this).off(CONFIRM_EVENT);
          t.closeModal();
        })
      ;
  },
    getLinkType: function (url) {
      let regs = {
        email: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,
        external: /(http(s?))\:\/\//gi
      }

      let type = 'internal';

      if (regs.email.test(url)) {
        type = 'email';
      }

      if (regs.external.test(url)) {
        type = 'external';
      }

      // check if its a file
      if (type == 'internal' && url.slice((url.lastIndexOf('.') - 1 >>> 0) + 2)) {
        type = 'media';
      }

      return type;
    }
  }
}