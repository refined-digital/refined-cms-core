import { defineStore } from 'pinia';

// holds the shared admin UI / modal state that components previously reached via
// `window.app.*` and `this.$root.*` on the Vue 2 root instance.
export const useUiStore = defineStore('ui', {
  state: () => ({
    tab: window.tab || 'content',
    loading: false,
    content: {
      name: null,
      uri: null,
    },
    media: {
      active: false,
      display: 'thumb',
      showModal: false,
      model: null,
      fieldId: null,
      type: 'image',
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
        showOptionsFor: ['3', '4', '5'],
        showDataFor: ['19'],
      },
    },
    bulk: [],
    bulkAction: false,
    mobileMenuActive: false,
  }),

  actions: {
    // opens the media library modal for an editor field. mirrors the old
    // window.loadMediaModal helper that the rich-text plugins called.
    openMediaModal({ type = 'image', model = null, fieldId = null } = {}) {
      document.querySelector('body')?.classList.add('body-has-modal');
      this.media.showModal = true;
      this.media.model = model;
      this.media.fieldId = fieldId;
      this.media.type = type;
    },

    closeMediaModal() {
      document.querySelector('body')?.classList.remove('body-has-modal');
      this.media.showModal = false;
    },

    // opens the sitemap / internal-page picker modal.
    openSitemapModal({ model = null, fieldId = null } = {}) {
      document.querySelector('body')?.classList.add('body-has-modal');
      this.sitemap.showModal = true;
      this.sitemap.active = true;
      this.sitemap.model = model;
      this.sitemap.fieldId = fieldId;
    },

    closeSitemapModal() {
      document.querySelector('body')?.classList.remove('body-has-modal');
      this.sitemap.showModal = false;
    },
  },
});
