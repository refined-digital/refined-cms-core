import './bootstrap';
import '../sass/main.scss';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { storeToRefs } from 'pinia';
import kebabCase from 'lodash.kebabcase';
import swal from 'sweetalert';
import dragula from 'dragula';

import eventBus from './eventBus';
import { useUiStore } from './stores/ui';
import { useConfigStore } from './stores/config';
import { registerDirectives } from './directives/_directives';

import DateTimePicker from './components/DateTimePicker.vue';
import DatePicker from './components/DatePicker.vue';
import RichText from './components/RichText.vue';
import Tags from './components/Tags.vue';
import Pages from './components/Pages.vue';
import PagesBranch from './components/PagesBranch.vue';
import PagesRepeatable from './components/PagesRepeatable.vue';
import PagesSettings from './components/PagesSettings.vue';
import ContentEditorField from './components/ContentEditorField.vue';
import Settings from './components/Settings.vue';
import Media from './components/Media.vue';
import MediaBranch from './components/MediaBranch.vue';
import MediaFile from './components/MediaFile.vue';
import Image from './components/Image.vue';
import Images from './components/Images.vue';
import File from './components/File.vue';
import Link from './components/Link.vue';
import SiteMap from './components/SiteMap.vue';
import FormOptions from './components/FormOptions.vue';
import FormRepeatable from './components/FormRepeatable.vue';
import Icon from './components/Icon.vue';
import SelectAsTags from './components/SelectAsTags.vue';
import ProductVariations from './components/ProductVariations.vue';
import FormEmail from './components/FormEmail.vue';
import ColourPicker from './components/ColourPicker.vue';
import ColourSet from './components/ColourSet.vue';
import ContentBlocks from './components/ContentBlocks.vue';

window.dragula = dragula;
window.swal = swal;

const slugify = (text) => kebabCase(text);
window.slugify = slugify;

const pinia = createPinia();

// instantiate the stores up-front so the window.app shim and the root component
// share the same reactive state.
const ui = useUiStore(pinia);
const config = useConfigStore(pinia);

// backwards-compatible window.app: the admin blade's inline <script> blocks set
// config (richEditor/colourSet/siteUrl/user) on window.app AFTER this bundle
// loads, and the rich-text editor reads window.app.media/sitemap. proxy those
// onto the pinia stores so existing markup keeps working untouched.
const configKeys = new Set(['richEditor', 'colourSet', 'siteUrl', 'publicUrl', 'user']);
window.app = new Proxy(
  {},
  {
    get(_target, prop) {
      if (configKeys.has(prop)) return config[prop];
      return ui[prop];
    },
    set(_target, prop, value) {
      if (configKeys.has(prop)) {
        config[prop] = value;
      } else {
        ui[prop] = value;
      }
      return true;
    },
  }
);

const app = createApp({
  setup() {
    const uiRefs = storeToRefs(ui);

    // triggers the form to submit from the tabbed buttons
    const submitForm = (type) => {
      document.getElementById('form--submit').value = type;
      document.getElementById('model-form').submit();
    };

    // copies the page meta url to the clipboard
    const copyUrl = () => {
      const string = `${config.publicUrl}/${ui.content.uri}`;
      const el = document.createElement('textarea');
      el.value = string;
      document.body.appendChild(el);
      el.select();
      document.execCommand('copy');
      document.body.removeChild(el);
    };

    // updates the meta slug from the content name
    const updateSlug = () => {
      ui.content.uri = slugify(ui.content.name);
    };

    const clone = (data) => JSON.parse(JSON.stringify(data));

    const handleBulk = (e) => {
      const form = e.target.closest('form');
      if (form && ui.bulkAction) {
        swal({
          title: 'Are you sure?',
          icon: 'warning',
          buttons: true,
          dangerMode: true,
        }).then((value) => {
          if (value) {
            form.submit();
          } else {
            ui.bulkAction = false;
          }
        });
      }
    };

    return {
      ...uiRefs,
      submitForm,
      copyUrl,
      updateSlug,
      clone,
      handleBulk,
    };
  },
});

app.use(pinia);

// only boot echo when a pusher key has been supplied at build time
if (import.meta.env.VITE_PUSHER_APP_KEY && window.Echo) {
  window.Echo.private('refinedCMS.media.updated').listenToAll((e, data) => {
    if (data?.media) {
      eventBus.emit('media-updated', data.media);
    }
  });
}

app.component('rd-date-time-picker', DateTimePicker);
app.component('rd-date-picker', DatePicker);
app.component('rd-rich-text', RichText);
app.component('rd-tags', Tags);
app.component('rd-form-email', FormEmail);
app.component('rd-select-as-tags', SelectAsTags);
app.component('rd-pages', Pages);
app.component('rd-pages-branch', PagesBranch);
app.component('rd-pages-repeatable', PagesRepeatable);
app.component('rd-pages-settings', PagesSettings);
app.component('rd-content-editor-field', ContentEditorField);
app.component('rd-settings', Settings);
app.component('rd-media', Media);
app.component('rd-media-branch', MediaBranch);
app.component('rd-media-file', MediaFile);
app.component('rd-image', Image);
app.component('rd-images', Images);
app.component('rd-file', File);
app.component('rd-link', Link);
app.component('rd-sitemap', SiteMap);
app.component('rd-form-options', FormOptions);
app.component('rd-form-repeatable', FormRepeatable);
app.component('rd-product-variations', ProductVariations);
app.component('rd-colour-picker', ColourPicker);
app.component('rd-colour-set', ColourSet);
app.component('rd-content-blocks', ContentBlocks);
app.component('Icon', Icon);

registerDirectives(app);

app.mount('#app');

window.vueApp = app;
