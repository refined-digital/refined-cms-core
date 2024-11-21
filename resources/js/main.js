require('./bootstrap');
window.Vue = require('vue');
window.dragula = require('dragula');
window.swal = require('sweetalert');
require('./directives/_directives');

const { default: Echo } = require('laravel-echo');

import Vue from 'vue'
import kebabCase from 'lodash.kebabcase';

import DateTimePicker from './components/DateTimePicker';
import DatePicker from './components/DatePicker';
import RichText from './components/RichText';
import Tags from './components/Tags';
import Pages from './components/Pages';
import PagesBranch from './components/PagesBranch';
import PagesRepeatable from './components/PagesRepeatable';
import PagesSettings from './components/PagesSettings';
import ContentEditorField from './components/ContentEditorField';
import Settings from './components/Settings';
import Media from './components/Media';
import MediaBranch from './components/MediaBranch';
import MediaFile from './components/MediaFile';
import Image from './components/Image';
import Images from './components/Images';
import File from './components/File';
import Link from './components/Link';
import SiteMap from './components/SiteMap';
import FormOptions from './components/FormOptions';
import FormRepeatable from './components/FormRepeatable';
import Icon from './components/Icon';
import SelectAsTags from './components/SelectAsTags';
import ProductVariations from './components/ProductVariations';
import FormEmail from './components/FormEmail';
import ColourPicker from './components/ColourPicker';
import ContentBlocks from './components/ContentBlocks';

window.eventBus = new Vue({});

const echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: process.env.MIX_PUSHER_APP_FORCE_TLS,
});

echo
    .private('refinedCMS.media.updated')
    .listenToAll((e, data) => {
        if (data.media) {
            window.eventBus.$emit('media-updated', data.media);
        }
    })

Vue.component('rd-date-time-picker', DateTimePicker);
Vue.component('rd-date-picker', DatePicker);
Vue.component('rd-rich-text', RichText);
Vue.component('rd-tags', Tags);
Vue.component('rd-form-email', FormEmail);
Vue.component('rd-select-as-tags', SelectAsTags);
Vue.component('rd-pages', Pages);
Vue.component('rd-pages-branch', PagesBranch);
Vue.component('rd-pages-repeatable', PagesRepeatable);
Vue.component('rd-pages-settings', PagesSettings);
Vue.component('rd-content-editor-field', ContentEditorField);
Vue.component('rd-settings', Settings);
Vue.component('rd-media', Media);
Vue.component('rd-media-branch', MediaBranch);
Vue.component('rd-media-file', MediaFile);
Vue.component('rd-image', Image);
Vue.component('rd-images', Images);
Vue.component('rd-file', File);
Vue.component('rd-link', Link);
Vue.component('rd-sitemap', SiteMap);
Vue.component('rd-form-options', FormOptions);
Vue.component('rd-form-repeatable', FormRepeatable);
Vue.component('rd-product-variations', ProductVariations);
Vue.component('rd-colour-picker', ColourPicker);
Vue.component('rd-content-blocks', ContentBlocks);
Vue.component('Icon', Icon);


window.app = new Vue({
    el: '#app',

    data: {
    	tab: 'content',
    	loading: false,
    	siteUrl: false,
    	publicUrl: false,
      richEditor: {},
    	user: {},
    	content: {
    	  name: null,
    	  uri: null
    	},
    	media: {
    	  active: false,
    	  display: 'thumb',
    	  showModal: false,
    	  model: null,
        fieldId: null,
        type: 'image'
    	},
    	sitemap: {
    	  active: false,
    	  showModal: false,
    	  model: null,
        fieldId: null,
    	},
      link: {
        active: false,
      },
      linkAttributes: {},
    	form: {
        action: 1,
        typeId: 1,
        receipt: 0,
        reply: 0,
        labelPosition: 1,
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
        let string = this.publicUrl + '/' + this.content.uri;
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
  return kebabCase(text);
};
