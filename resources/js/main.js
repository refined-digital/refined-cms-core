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
Vue.component('rd-pages-repeatable', require('./components/PagesRepeatable.vue'));
Vue.component('rd-content-editor-field', require('./components/ContentEditorField.vue'));
Vue.component('rd-settings', require('./components/Settings.vue'));
Vue.component('rd-media', require('./components/Media.vue'));
Vue.component('rd-media-branch', require('./components/MediaBranch.vue'));
Vue.component('rd-media-file', require('./components/MediaFile.vue'));
Vue.component('rd-image', require('./components/Image.vue'));
Vue.component('rd-file', require('./components/File.vue'));
Vue.component('rd-link', require('./components/Link.vue'));
Vue.component('rd-sitemap', require('./components/SiteMap.vue'));
Vue.component('rd-form-options', require('./components/FormOptions.vue'));

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
              '13', '20'
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
}

window.loadMediaModal = function (fieldId, type) {
  eventBus.$emit('media-set-type', type);
  eventBus.$emit('media-reload');
  window.app.media.showModal = true;
  window.app.media.model = fieldId;
  eventBus.$on('selecting-file', function(data) {
    if (window.app.media.model == fieldId) {
      let block = $('#'+fieldId),
          field = block.find('input')
      ;
      field.val(data.link.original.replace(data.link.basePath, '/'));

      if (type == 'Image') {
        let image = block.find('div').children('div')

        let img = new Image;
        img.onload = function() {
          image.css('backgroundImage', 'url(' + this.src + ')');
        };
        img.src = data.link.thumb;
      }

      eventBus.$emit('media-close');
    }

    return false;
  });
}

window.loadSitemapModal = function (fieldId) {
  eventBus.$emit('sitemap-reload');
  window.app.sitemap.showModal = true;
  window.app.sitemap.model = fieldId;
  eventBus.$on('selecting-link', function(data) {
    if (window.app.sitemap.model == fieldId) {
      let block = $('#'+fieldId),
          field = block.find('input')
      ;
      field.val(data);

      eventBus.$emit('sitemap-close');
    }

    return false;
  });
}

window.changeEditorLinkType = function(fieldId, value) {
  let field = document.getElementById(fieldId),
    types = field.querySelectorAll('*[data-type]'),
    type = field.querySelectorAll('*[data-type="' + value + '"]'),
    label = field.querySelector('.link-field-label')
  ;

  // hide all field types
  if (types.length) {
    types.forEach(t => {
      t.style.display = 'none';
    });
  }

  if (type && type.length) {
    type.forEach(t => {
      console.log(t);
      t.style.display = 'block';
    });
  }

  if (label) {
    label.textContent = value == 'email' ? 'Email Address' : 'URL';
  }
}