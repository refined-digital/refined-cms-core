<template>
  <div class="form__control--rich-text">
    <textarea v-model="data" :name="name" :id="id"></textarea>
    <trumbowyg v-model="data" :config="config" class="form-control" :name="name" :id="id" @input="updateInput()"></trumbowyg>
  </div>

</template>

<script>
    // Import this component
    import trumbowyg from 'vue-trumbowyg';

    // Import editor css
    import 'trumbowyg/dist/ui/trumbowyg.css';

    // import additional plugins
    import 'trumbowyg/plugins/cleanpaste/trumbowyg.cleanpaste.js';
    import 'trumbowyg/plugins/noembed/trumbowyg.noembed.js';
    import 'trumbowyg/plugins/table/trumbowyg.table.js';
    import 'trumbowyg/plugins/fontsize/trumbowyg.fontsize.js';
    import 'trumbowyg/plugins/history/trumbowyg.history.js';
    import 'trumbowyg/plugins/pasteembed/trumbowyg.pasteembed.js';
    import 'trumbowyg/plugins/preformatted/trumbowyg.preformatted.js';
    import 'trumbowyg/plugins/insertaudio/trumbowyg.insertaudio.js';
    import '../plugins/trumbowyg/refined-insert-image/trumbowyg.refined-insert-image.js';
    import '../plugins/trumbowyg/refined-link/trumbowyg.refined-link.js';

    export default {

        props: ['name', 'id', 'content'],

        data() {
            return {
                instance: null,
                data: '',
                config: {
                  btns: [

                    ['viewHTML'],
                    ['historyUndo', 'historyRedo'],
                    ['formatting', 'fontsize'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link', 'refinedLink', 'unlink', 'refinedInsertImage', 'noembed'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                  ],

                  removeformatPasted: true,

                  imgDblClickHandler: function () {
                      var $img = $(this),
                        src = $img.attr('src'),
                        alt = $img.attr('alt'),
                        id = $img.attr('id'),
                        klass = $img.attr('class'),
                        style = $img.attr('style'),
                        base64 = '(Base64)';

                      if (src.indexOf('data:image') === 0) {
                        src = base64;
                      }

                      var options = JSON.parse(JSON.stringify(window.trumbowyg.methods.getOptions('refinedInsertImage')));
                      if (src) {
                        options.src.value = src;
                      }
                      if (alt) {
                        options.alt.value = alt;
                      }
                      if (id) {
                        options.id.value = id;
                      }
                      if (klass) {
                        options.class.value = klass;
                      }
                      if (style) {
                        options.style.value = style;
                      }

                      window.trumbowyg.methods.openModalInsert('Insert Image', options, function(v) {
                        if (v.src) {
                          $img.attr('src', v.src);
                        }
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

                        return true;
                      });

                      return false;
                  }
                }
            }
        },
        components: {
          trumbowyg
        },

        mounted() {
            var el = this.$el.firstChild,
                self = this
            ;

            if(self.content) {
                self.data = self.content;
            }
        },

        methods:  {
          updateInput() {
            this.$emit('input', this.data);
          }
        },

        watch: {
          content(data) {
            this.data = data;
          }
        }
    }
</script>