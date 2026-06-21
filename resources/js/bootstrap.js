// TODO: remove jquery — only jquery-ui sortable still depends on it
import jQuery from 'jquery';
window.jQuery = window.$ = jQuery;

// TODO: remove this too
import 'jquery-ui/ui/widgets/sortable.js';

import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.baseURL = window.siteUrl || '';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// only wire up Echo/Pusher when a key is provided at build time
if (import.meta.env.VITE_PUSHER_APP_KEY) {
  const { default: Echo } = await import('laravel-echo');
  const { default: Pusher } = await import('pusher-js');

  window.Pusher = Pusher;
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: import.meta.env.VITE_PUSHER_APP_FORCE_TLS,
  });
}
