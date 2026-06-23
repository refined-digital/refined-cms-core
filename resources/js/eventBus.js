import mitt from 'mitt';

// replaces the Vue 2 `new Vue({})` event bus. components must pair every
// `eventBus.on(...)` with `eventBus.off(...)` in onUnmounted — mitt does not
// auto-clean listeners the way a destroyed Vue instance did.
const eventBus = mitt();

window.eventBus = eventBus;

export default eventBus;
