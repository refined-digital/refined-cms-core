require('./bootstrap');
window.Vue = require('vue');
window.dragula = require('dragula');
window.swal = require('sweetalert');
require('./directives/_directives');

import DateTimePicker from './components/DateTimePicker';
import DatePicker from './components/DatePicker';
import RichText from './components/RichText';
import Tags from './components/Tags';
import Pages from './components/Pages';
import PagesBranch from './components/PagesBranch';
import PagesRepeatable from './components/PagesRepeatable';
import ContentEditorField from './components/ContentEditorField';
import Settings from './components/Settings';
import Media from './components/Media';
import MediaBranch from './components/MediaBranch';
import MediaFile from './components/MediaFile';
import Image from './components/Image';
import File from './components/File';
import Link from './components/Link';
import SiteMap from './components/SiteMap';
import FormOptions from './components/FormOptions';
import FormRepeatable from './components/FormRepeatable';
import Icon from './components/Icon';

Vue.component('rd-date-time-picker', DateTimePicker);
Vue.component('rd-date-picker', DatePicker);
Vue.component('rd-rich-text', RichText);
Vue.component('rd-tags', Tags);
Vue.component('rd-pages', Pages);
Vue.component('rd-pages-branch', PagesBranch);
Vue.component('rd-pages-repeatable', PagesRepeatable);
Vue.component('rd-content-editor-field', ContentEditorField);
Vue.component('rd-settings', Settings);
Vue.component('rd-media', Media);
Vue.component('rd-media-branch', MediaBranch);
Vue.component('rd-media-file', MediaFile);
Vue.component('rd-image', Image);
Vue.component('rd-file', File);
Vue.component('rd-link', Link);
Vue.component('rd-sitemap', SiteMap);
Vue.component('rd-form-options', FormOptions);
Vue.component('rd-form-repeatable', FormRepeatable);
Vue.component('Icon', Icon);

window.eventBus = new Vue({});

window.app = new Vue({
    el: '#app',

    data: {
    	tab: 'content',
    	loading: false,
    	siteUrl: false,
    	user: {},
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
    	sitemap: {
    	  active: false,
    	  showModal: false,
    	  model: 0
    	},
      linkAttributes: {},
    	form: {
        action: 1,
        receipt: 0,
        reply: 0,
        field: {
          type: 0,
          showOptionsFor: [
              '3','4','5'
          ],
          showDataFor: [
              '19'
          ]
        },
    	}
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
};
