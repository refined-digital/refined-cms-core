<template>
  <div class="form__control--image">
    <input type="hidden" v-model="image" :name="name" :id="id">
    <div class="form__image">
      <figure>
        <div class="form__image-thumb">
          <img
              class="form__image-thumb-img"
              v-if="file.type == 'Image'"
              :src="fileUrl"
          />
          <div class="spinner" v-if="file.type === 'Image' && file.external_id && !file.external_url"></div>
          <span class="form__image-thumb-img form__image-thumb-video" v-if="file.type == 'Video'">
            <i class="fas fa-video"></i>
            <span class="form__image-thumb-name">{{ file.name }}</span>
          </span>
        </div>
        <figcaption>
          <span :title="file.name">{{ file.name }}</span>
        </figcaption>
      </figure>

      <aside>
        <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearFile" class="button button--red button--small">Clear File</a>
      </aside>
    </div><!-- / form image -->

    <div class="form__image--note form__note" v-if="file.note" v-html="file.note"></div>
  </div>

</template>

<script>

    export default {

        props: [
          'name',
          'id',
          'value',
          'width',
          'height'
        ],

        data() {
            return {
              image: '',

              file: {
                link: {
                  thumb: ''
                },
                external_id: '',
                external_url: '',
                note: null,
                type: ''
              },

              default: {
                link: {
                  thumb: ''
                },
                external_id: '',
                external_url: '',
              },

            }
        },

        created() {
          this.loadFile();

          eventBus.$on('selecting-file', this.updateFile);
          eventBus.$on('media-updated', this.mediaUpdated);
        },

        computed: {
          fileUrl() {
            if (this.file.external_url) {
              return this.file.external_url;
            }

            return this.file.link.thumb;
          }

        },

        methods:  {

          clearFile() {
            this.image = null;
            this.file = this.$root.clone(this.default);
            this.emit();
          },

          loadModal() {
            eventBus.$emit('media-set-type', 'Image');
            eventBus.$emit('media-reload');
            this.$root.media.showModal = true;
            this.$root.media.model = this._uid;
          },

          mediaUpdated(data) {
            if (data.external_url) {
              this.file.external_url = data.external_url;
            }
          },

          updateFile(data) {
            if (data.model === this._uid) {
              this.file = data;
              this.image = this.file.id;
              this.emit();
              eventBus.$emit('media-close');
            }
          },

          loadFile() {
            this.image = this.value;

            if (this.value) {
              axios
                .get(`${window.siteUrl}/refined/media/${this.value}`)
                .then(r => {
                  this.$root.loading = false;
                  if (r.status === 200 && r.data.file) {
                    this.file = r.data.file;
                    this.setFileNote();
                    if (typeof this.name !== 'undefined') {
                      this.emit();
                    }
                  }
                })
                .catch(() => {
                  this.$root.loading = false;
                })
              ;
            } else {
              this.file = this.default;
              this.setFileNote();
              if (typeof this.name !== 'undefined') {
                this.emit();
              }
            }

          },

          setFileNote() {

            if (this.width || this.height) {

              const n = [];
              if (this.width) {
                n.push(`${this.width}px wide`)
              }
              if (this.height) {
                n.push(`${this.height}px tall`)
              }

              const dimensions = `<strong>${n.length > 1 ? 'fit within ' : ''}${n.join(' x ')}</strong>`

              this.file.note = `
                Image will be resized to ${dimensions}
                <br>If you are having trouble with images, <a href="https://www.iloveimg.com/photo-editor" target="_blank">visit this page</a> to create your image.</div>
              `;
            }
          },

          emit() {
            this.$emit('input', this.image);
          }


        },

        watch: {
          value() {
            this.loadFile();
          }
        }
    }
</script>
