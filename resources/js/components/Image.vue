<template>
  <div class="form__control--image">
    <input type="hidden" v-model="imageAsString" :name="name" :id="id">
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
      <div class="form__image-alt-text">
        <label class="form__label" :for="`alt-text-id-${altId}`">Alt Text</label>
        <input type="text" v-model="alt" :id="`alt-text-id-${altId}`" class="form__control" @keyup="emit"/>
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

        props: [
          'name',
          'id',
          'value',
          'model',
          'fieldName' // used only for pages / repeatables
        ],

        data() {
            return {
              image: {
                id: '',
                fileAlt: '',
              },

              imageAsString: '',

              alt: '',

              file: {
                link: {
                  thumb: ''
                }
              },

              default: {
                link: {
                  thumb: ''
                }
              },

              altId: Date.now()
            }
        },

        created() {
          this.loadFile();

          eventBus.$on('selecting-file', this.updateFile);
        },

        methods:  {

          clearFile() {
            this.image.id = null;
            this.file = this.$root.clone(this.default);
            this.emit();
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
              this.image.id = this.file.id;
              this.setAltText();
              this.emit();
              eventBus.$emit('media-close');
            }
          },

          loadFile() {
            if (this.value && typeof this.value !== 'number') {
              this.image.id = this.value.id;
            } else {
              this.image.id = this.value;
            }

            if (this.image.id) {
              axios
                .get('/refined/media/'+this.image.id)
                .then(r => {
                  this.$root.loading = false;
                  if (r.status === 200) {
                    this.file = r.data.file;
                    this.setAltText();
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
              if (typeof this.name !== 'undefined') {
                this.emit();
              }
            }

          },

          setAltText() {
            let alt = this.file && this.file.alt ? this.file.alt : '';
            console.log(this.file, this.model, this.image);

            if (this.model.alts.length) {
              console.log('has some model alts');
              const found = this.model.alts.find(al => {
                return (al.field_name === this.name)
              })

              if (found) {
                alt = found.alt;
              }
            }

            this.image.alt = alt;
            if (this.file && this.file.alt) {
              console.log('has a file alt');
              this.image.fileAlt = this.file.alt;
            }

            if (this.model && this.model.oldAlt) {
              console.log('there is an old');
              alt = this.model.oldAlt;
            }

            if (typeof this.fieldName !== 'undefined' && this.file.alt_texts.length) {
              console.log('there are file alts');
              const found = this.file.alt_texts.find(al => {
                return (al.field_name === this.fieldName && al.type_id === this.model.id)
              })

              console.log('found', found);

              if (found) {
                alt = found.alt;
              }
            }

            // if alt is already set, use it
            if (this.alt) {
              console.log('this there an alt already?', this.alt);
              alt = this.alt;
            }

            this.alt = alt;
          },

          emit() {
            let data = null;
            if (this.image.id) {
              const image = {
                id: this.image.id,
                fileAlt: this.file.alt,
                alt: this.alt,
                model: this.model
              };
              data = typeof this.name !== 'undefined' ? JSON.stringify(image) : image;
              this.imageAsString = data;
            }

            this.$emit('input', data);
          }


        },

        watch: {
          value() {
            this.loadFile();
          }
        }
    }
</script>
