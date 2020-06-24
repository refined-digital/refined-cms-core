// todo: remove jquery
try {
  window.$ = window.jQuery = require('jquery');
} catch (e) {}

// todo: remove this too
require('jquery-ui/ui/widgets/sortable.js');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = window.app.siteUrl;

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
