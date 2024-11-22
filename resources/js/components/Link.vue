<template>
  <div class="form__control--link">
    <input type="hidden" class="form__control" v-model="link" :name="name" :id="elementId"/>


    <div class="link__type link__type--with-button">
      <div class="link__file form__file-name">
        <strong class="form__file--title" v-if="linkModel && linkModel.text">{{ linkModel.text }}</strong>
        <span v-if="linkModel && linkModel.text"> / </span>
        <a :href="linkModel && linkModel.type === 'file' ? linkModel.file.url : linkModel.url" target="_blank" class="form__file--link" v-if="linkModel && linkModel.url">View Link</a>
      </div>

      <aside>
        <a href="" @click.prevent.stop="openModal" class="button button--green button--small">Browse</a>
        <a href="" @click.prevent.stop="clearLink" class="button button--red button--small">Clear Link</a>
      </aside>
    </div>

    <div class="rd-link form__horz sitemap" :class="active ? 'sitemap--active' : ''">
      <div class="sitemap__inner">
        <div class="form__group">
          <div class="form__row">
            <label class="form__label" :for="`form--link-text-${elementId}`">Link Text</label>
            <input type="text" class="form__control" name="link_text" :id="`form--link-text-${elementId}`" v-model="modal.text"/>
          </div>

          <div class="form__row">
            <label class="form__label" :for="`form--link-type-${elementId}`">Link Type</label>
            <select class="form__control" name="link_type" :id="`form--link-type-${elementId}`" v-model="modal.type">
              <option value="internal">Internal Page</option>
              <option value="external">External Link</option>
              <option value="file">File / Image</option>
              <option value="email">Email</option>
              <option value="phone">Phone</option>
              <option value="anchor">Anchor</option>
            </select>
          </div>

          <div class="form__row">
            <label class="form__label" :for="`form--link-url-${elementId}`">{{ urlLabel }}</label>
            <div class="link__type" :class="classList">
              <input :type="modal.type === 'file' ? 'hidden' : 'text'" class="form__control" name="link_url" :id="`form--link-url-${elementId}`" v-model="modal.url"/>


              <div class="link__file form__file-name" v-if="modal.type === 'file'">
                <strong class="form__file--title" v-if="modal.file.name">{{ modal.file.name }}</strong>
                <span v-if="modal.file.name"> / </span>
                <a :href="modal.file.url" v-if="modal.file.url" target="_blank" class="form__file--link">View File</a>
              </div>

              <aside v-if="hasButton.includes(modal.type)">
                <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
                <a href="" @click.prevent.stop="clearFile" class="button button--red button--small" v-if="modal.type === 'file'">Clear File</a>
              </aside>

              <div class="form__note" v-if="modal.type === 'external'">Must start with <code>http://</code> or <code>https://</code></div>
            </div>
          </div>

          <div class="form__row">
            <label class="form__label" :for="`form--link-title-${elementId}`">Element Title</label>
            <input type="text" class="form__control" name="link_title" :id="`form--link-title-${elementId}`" v-model="modal.title"/>
          </div>

          <div class="form__row">
            <label class="form__label" :for="`form--link-id-${elementId}`">Element ID</label>
            <input type="text" class="form__control" name="link_id" :id="`form--link-id-${elementId}`" v-model="modal.id"/>
          </div>

          <div class="form__row">
            <label class="form__label" :for="`form--link-classes-${elementId}`">Element Classes</label>
            <input type="text" class="form__control" name="link_classes" :id="`form--link-classes-${elementId}`" v-model="modal.classes"/>
          </div>
        </div>

        <footer class="sitemap__footer">
          <button class="button button--blue button--small" @click.prevent.stop="save">Save</button>
          <button class="button button--red button--small" @click.prevent.stop="closeModal">Close</button>
        </footer>
      </div>
    </div>
  </div>

</template>

<script>
    export default {

        props: ['name', 'id', 'value'],

        data() {
            return {
              link: null,
              linkModel: null,
              modal: {
                text: null,
                type: 'internal',
                url: null,
                title: null,
                id: null,
                classes: null,
                file: {
                  name: null,
                  url: null
                },
              },
              hasButton: [
                'internal',
                'file'
              ],
              elementId: null,
              active: false
            }
        },

        created() {
          this.loadLink();
          eventBus.$on('selecting-link', this.updateLink);
          eventBus.$on('selecting-file', this.updateLink);

          if (this.id) {
            this.elementId = this.id;
          } else {
            this.elementId = Date.now();
          }

        },

        computed: {
          urlLabel() {
            if (this.modal.type === 'email') {
              return 'Email Address';
            }

            if (this.modal.type === 'phone') {
              return 'Phone';
            }

            if (this.modal.type === 'anchor') {
              return 'Element ID';
            }

            return 'URL'
          },

          classList() {
            if (this.hasButton.includes(this.modal.type)) {
              return 'link__type--with-button';
            }

            return '';
          },
        },

        methods:  {

          clearLink() {
            this.link = null;
            this.linkModel.text = null;
            this.linkModel.type = 'internal';
            this.linkModel.url = null;
            this.linkModel.id = null;
            this.linkModel.title = null;
            this.linkModel.classes = null;
            this.linkModel.file.name = null;
            this.linkModel.file.url = null;
          },

          clearFile() {
            this.modal.url = null;
            this.modal.file.name = null;
            this.modal.file.url = null;
          },

          openModal() {
            if (this.linkModel) {
              this.modal = _.cloneDeep(this.linkModel);
            }

            this.active = true;
            this.$root.link.active = true;
          },

          loadModal() {
            if (this.modal.type === 'internal') {
              eventBus.$emit('sitemap-reload');
              this.$root.sitemap.showModal = true;
              this.$root.sitemap.model = this._uid;
            }

            if (this.modal.type === 'file') {
              eventBus.$emit('media-set-type', '*');
              eventBus.$emit('media-reload');
              this.$root.media.showModal = true;
              this.$root.media.model = this._uid;
            }
          },

          updateLink(data) {
            if (this.$root.sitemap.model == this._uid) {
              this.modal.url = data;
              eventBus.$emit('sitemap-close');
            }

            if (this.$root.media.model == this._uid) {
              this.modal.url = data.id;
              this.modal.file.name = data.file;
              const url = data.link.original.replace(data.link.basePath, '/');
              this.modal.file.url = url;
              eventBus.$emit('media-close');
            }
          },

          loadLink() {
            let value = this.value;
            if (typeof value === 'string' && value.length) {
              value = JSON.parse(value);
            }

            if (typeof value !== 'object' || !value) {
              this.modal = _.cloneDeep(this.modal);
            } else {
              this.modal = value;
            }

            this.link = JSON.stringify(value);
            this.linkModel = value;
          },

          save() {
            const check = this.modal.url && this.modal.text;
            if (!check) {

              let msg = 'The following fields are required: ';
              if (!this.modal.text) msg += "\n - Link Text";
              if (!this.modal.url) msg += "\n - URL";
              alert(msg);

              return;
            }

            this.linkModel = _.cloneDeep(this.modal);
            this.link = JSON.stringify(this.linkModel);
            this.$emit('input', this.linkModel);
            this.closeModal();
          },

          closeModal() {
            this.active = false;
            this.modal.text = null;
            this.modal.type = 'internal';
            this.modal.url = null;
            this.modal.id = null;
            this.modal.title = null;
            this.modal.classes = null;
            this.modal.file.name = null;
            this.modal.file.url = null;
            this.$root.link.active = false;
          }

        },
    }
</script>

<style scoped>
.link__type {
  flex: 1;
}

.link__type--with-button {
  display: flex;
  gap: 20px;
  align-items: center;
}

.link__file {
  flex: 1;
}

.rd-link .sitemap__inner {
  max-height: 492px;
}

.rd-link .form__row {
  flex: 0 0 100%;
}

</style>
