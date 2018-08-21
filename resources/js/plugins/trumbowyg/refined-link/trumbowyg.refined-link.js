window.trumbowyg.options.refinedLink = {
  text: {
    label: 'Display Text',
    required: true,
  },
  url: {
    label: 'Link Type',
    type: 'linkType',
    options: [
      { name: 'Internal Page', value: 'internal' },
      { name: 'External Page', value: 'external' },
      { name: 'File / Image', value: 'media' },
      { name: 'Email', value: 'email' },
    ]
  },
  title: {
    label: 'Title'
  },
  id: {
    label: 'Id'
  },
  class: {
    label: 'Class'
  },
  style: {
    label: 'Style'
  },
};

(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        langs: {
            en: {
                refinedLink: 'Insert Link'
            },
        },
        plugins: {
            refinedLink: {
                init: function (trumbowyg) {
                  if (typeof trumbowyg.$ed != 'undefined') {
                    trumbowyg.saveRange();
                  }
                  window.trumbowyg.trumbowyg = trumbowyg;
                  var btnDef = {
                    fn: function () {

                      // get the link details, if any
                      var t = trumbowyg,
                          documentSelection = t.doc.getSelection(),
                          node = documentSelection.focusNode,
                          text = new XMLSerializer().serializeToString(documentSelection.getRangeAt(0).cloneContents()),
                          options = JSON.parse(JSON.stringify(window.trumbowyg.methods.getOptions('refinedLink')));

                      while (['A', 'DIV'].indexOf(node.nodeName) < 0) {
                          node = node.parentNode;
                      }

                      if (node && node.nodeName === 'A') {
                        var $a = $(node);
                        options.text.value = $a.text();

                        if ($a.attr('href')) options.url.value = $a.attr('href').replace('mailto:','');
                        if ($a.attr('title')) options.title.value = $a.attr('title');
                        if ($a.attr('target')) options.target.value = $a.attr('target');
                        if ($a.attr('id')) options.id.value = $a.attr('id');
                        if ($a.attr('style')) options.style.value = $a.attr('style');
                        if ($a.attr('class')) options.class.value = $a.attr('class');

                        var range = t.doc.createRange();
                        range.selectNode(node);
                        documentSelection.removeAllRanges();
                        documentSelection.addRange(range);
                      }

                      if (typeof options.text.value == 'undefined' && text) {
                        options.text.value = text;
                      }

                      t.saveRange();

                      window.trumbowyg.methods.openModalInsert(trumbowyg.lang.refinedLink, options, function (v) {
                        var t = window.trumbowyg.trumbowyg;

                        var url = t.prependUrlPrefix(v.url);
                        if (!url.length) {
                            return false;
                        }

                        // find the type
                        var type = window.trumbowyg.methods.getLinkType(v.url);

                        var link = $(['<a href="', v.url, '">', v.text || v.url, '</a>'].join(''));

                        if (type == 'email') {
                          link.attr('href', 'mailto:' + v.url);
                        }

                        if (v.title) {
                            link.attr('title', v.title);
                        }
                        if (v.target) {
                            link.attr('target', v.target);
                        }
                        if (v.id) {
                          link.attr('id', v.id);
                        }
                        if (v.style) {
                          link.attr('style', v.style);
                        }
                        if (v.class) {
                          link.attr('class', v.class);
                        }

                        t.range.deleteContents();
                        t.range.insertNode(link[0]);
                        t.syncCode();
                        t.$c.trigger('tbwchange');
                        return true;


                      });
                    },
                    ico: 'create-link'
                  };

                  trumbowyg.addBtnDef('refinedLink', btnDef);
                },
            }
        }
    });
})(jQuery);
