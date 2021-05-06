<template>
  <div class="form__control--file">
    <input type="hidden" v-model="item" :name="name" :id="id">
    <div class="form__file">
      <div class="form__file-name">
        <strong class="form__file--title" v-if="file.name">{{ file.name }}</strong> <span v-if="file.name">/</span>
        <span class="form__file--size" v-if="file.size">{{ file.size }} / </span>
        <a :href="file.link.original" v-if="file.link.original" class="form__file--link">View File</a>
      </div>
      <aside>
        <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearFile" class="button button--red button--small">Clear File</a>
      </aside>
    </div><!-- / form image -->
  </div>

</template>

<script>

    export default {

        props: ['name', 'id', 'value'],

        data() {
            return {
              item: '',

              file: {
                name: '',
                size: '',
                link: {
                  original: ''
                }
              },

              default: {
                link: {
                  thumb: ''
                }
              }
            }
        },

        created() {
          this.loadFile();

          eventBus.$on('selecting-file', this.updateFile);
        },

        methods:  {
          clearFile() {
            this.item = null;
            this.file = this.$root.clone(this.default);
            this.$emit('input', this.item);
          },

          loadModal() {
            eventBus.$emit('media-set-type', 'File');
            this.$root.media.showModal = true;
            this.$root.media.model = this._uid;
          },

          updateFile(data) {
            if (data.model === this._uid) {
              this.file = data;
              this.item = this.file.id;
              this.$emit('input', this.item);
              eventBus.$emit('media-close');
            }
          },

          loadFile() {

            this.item = this.value;
            if (this.value) {
              axios
                .get('/refined/media/'+this.value)
                .then(r => {
                  this.$root.loading = false;
                  if (r.status == 200) {
                    this.file = r.data.file;
                  }
                })
                .catch(e => {
                  this.$root.loading = false;
                })
              ;
            } else {
              this.file = this.default;
            }
          },


        },

        watch: {
          value() {
            this.loadFile();
          }
        }
    }
</script>
