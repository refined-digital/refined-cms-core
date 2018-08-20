<template>
  <div class="form__control--link">
    <input type="text" class="form__control" v-model="link" :name="name" :id="id">
    <a href="" @click.prevent.stop="loadModal" class="button button--green button--small">Browse</a>
  </div>

</template>

<script>

    export default {

        props: ['name', 'id', 'value'],

        data() {
            return {
              link: null,
            }
        },

        created() {
          this.loadLink();
          eventBus.$on('selecting-link', this.updateLink);
        },

        methods:  {

          clearLink() {
            this.link = null;
          },

          loadModal() {
            eventBus.$emit('sitemap-reload');
            this.$root.sitemap.showModal = true;
            this.$root.sitemap.model = this._uid;
          },

          updateLink(data) {
            if (this.$root.sitemap.model == this._uid) {
              this.link = data;
              this.$emit('input', this.link);
              eventBus.$emit('sitemap-close');
            }
          },

          loadLink() {
            this.link = this.value;
          }

        },
    }
</script>