<template>
  <div class="form__control--image">
    <input type="hidden" v-model="image" :name="name" :id="id">
    <div class="form__image">
      <figure>
        <span class="form__image-thumb">
          <img src="/vendor/refined/core/img/ui/media-thumb.png">
          <span class="form__image-thumb-img" v-if="file.type === 'Image'" :style="{ backgroundImage: 'url('+ file.link.thumb +')' }"></span>
          <span class="form__image-thumb-img form__image-thumb-video" v-if="file.type == 'Video'">
            <i class="fas fa-video"></i>
            <span class="form__image-thumb-name">{{ file.name }}</span>
          </span>
        </span>
        <figcaption>
          <span :title="file.name">{{ file.name }}</span>
        </figcaption>
      </figure>
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
              image: '',

              file: {
                link: {
                  thumb: ''
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
            this.image = null;
            this.file = this.$root.clone(this.default);
            this.$emit('input', this.image);
          },

          loadModal() {
            eventBus.$emit('media-set-type', 'Image');
            eventBus.$emit('media-reload');
            this.$root.media.showModal = true;
            this.$root.media.model = this._uid;
          },

          updateFile(data) {
            if (data.model === this._uid) {
              this.file = data;
              this.image = this.file.id;
              this.$emit('input', this.image);
              eventBus.$emit('media-close');
            }
          },

          loadFile() {
            this.image = this.value;
            if (this.value) {
              axios
                .get('/refined/media/'+this.value)
                .then(r => {
                  this.$root.loading = false;
                  if (r.status === 200) {
                    this.file = r.data.file;
                  }
                })
                .catch(() => {
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
