import { defineStore } from 'pinia';

// holds runtime configuration injected by the admin blade. the inline <script>
// blocks in master.blade.php set these on `window.app.*` AFTER the bundle loads,
// so the window.app compatibility shim (main.js) writes them through to here.
export const useConfigStore = defineStore('config', {
  state: () => ({
    richEditor: {},
    colourSet: {},
    siteUrl: window.siteUrl || false,
    publicUrl: window.publicUrl || false,
    user: {},
  }),
});
