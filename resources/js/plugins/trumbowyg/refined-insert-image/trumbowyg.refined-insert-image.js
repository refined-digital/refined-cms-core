window.trumbowyg.options.refinedInsertImage = {
  src: {
      label: 'URL',
      required: true,
      type: 'image',
  },
  alt: {
      label: 'Alternative Text',
      required: false
  },
  id: {
      label: 'Id',
      required: false
  },
  class: {
      label: 'Class',
      required: false
  },
  style: {
      label: 'Style',
      required: false
  },
};

(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        langs: {
            en: {
                refinedInsertImage: 'Insert Image'
            },
        },
        plugins: {
            refinedInsertImage: {
                init: function (trumbowyg) {
                  trumbowyg.saveRange();
                  window.trumbowyg.trumbowyg = trumbowyg;
                  var btnDef = {
                    fn: function () {
                      window.trumbowyg.methods.openModalInsert(trumbowyg.lang.refinedInsertImage, window.trumbowyg.methods.getOptions('refinedInsertImage'), function (v) {
                        var t = window.trumbowyg.trumbowyg;
                        t.execCmd('insertImage', v.src);
                        var $img = $('img[src="' + v.src + '"]:not([alt])', t.$box);
                        if (v.alt) {
                          $img.attr('alt', v.alt);
                        }
                        if (v.id) {
                          $img.attr('id', v.id);
                        }
                        if (v.style) {
                          $img.attr('style', v.style);
                        }
                        if (v.class) {
                          $img.attr('class', v.class);
                        }

                        t.syncCode();
                        t.$c.trigger('tbwchange');

                        return true;
                      });
                    },
                    ico: 'insertImage'
                  };

                  trumbowyg.addBtnDef('refinedInsertImage', btnDef);
                },
            }
        }
    });
})(jQuery);
