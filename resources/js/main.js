require('./bootstrap');
window.Vue = require('vue');
window.dragula = require('dragula');
window.swal = require('sweetalert');
require('./directives/_directives');


Vue.component('rd-date-time-picker', require('./components/DateTimePicker.vue'));
Vue.component('rd-rich-text', require('./components/RichText.vue'));
Vue.component('rd-tags', require('./components/Tags.vue'));
Vue.component('rd-pages', require('./components/Pages.vue'));
Vue.component('rd-pages-branch', require('./components/PagesBranch.vue'));
Vue.component('rd-content-editor-field', require('./components/ContentEditorField.vue'));
Vue.component('rd-settings', require('./components/Settings.vue'));
Vue.component('rd-media', require('./components/Media.vue'));
Vue.component('rd-media-branch', require('./components/MediaBranch.vue'));
Vue.component('rd-media-file', require('./components/MediaFile.vue'));
Vue.component('rd-image', require('./components/Image.vue'));
Vue.component('rd-file', require('./components/File.vue'));

window.eventBus = new Vue({});

window.app = new Vue({
    el: '#app',

    data: {
    	tab: 'content',
    	loading: false,
    	siteUrl: false,
    	content: {
    	  name: null,
    	  uri: null
    	},
    	media: {
    	  active: false,
    	  display: 'thumb',
    	  showModal: false,
    	  model: 0
    	},
    },

    methods: {

      // triggers the form to submit from the tabbed buttons
      submitForm(type) {
        document.getElementById('form--submit').value = type;
        document.getElementById('model-form').submit();
      },

      // copies the page meta
      copyUrl() {
        let string = this.siteUrl + '/' + this.content.uri;
        let el = document.createElement('textarea');

        el.value = string;

        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');

        document.body.removeChild(el);

      },

      // updates the meta slug
      updateSlug() {
        this.content.uri = slugify(this.content.name);
      },

      // clones an element
      clone(data) {
        return JSON.parse(JSON.stringify(data));
      },
    }
});

window.slugify = function (text) {
  return text.toString().toLowerCase()
    .replace(/\s+/g, '-')           // Replace spaces with -
    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
    .replace(/\-\-+/g, '-')         // Replace multiple - with single -
    .replace(/^-+/, '')             // Trim - from start of text
    .replace(/-+$/, '');            // Trim - from end of text
}