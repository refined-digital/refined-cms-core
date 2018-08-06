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

    export default {

        props: ['name', 'id', 'content'],

        data() {
            return {
                instance: null,
                data: '',
                config: {
                  btnsDef: {
                    // Create a new dropdown
                    image: {
                      dropdown: ['insertImage', 'noembed'],
                      ico: 'insertImage'
                    }
                  },
                  btns: [

                    ['viewHTML'],
                    ['historyUndo', 'historyRedo'],
                    ['formatting', 'fontsize'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link', 'image'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat'],
                    ['fullscreen']
                  ],

                  removeformatPasted: true
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