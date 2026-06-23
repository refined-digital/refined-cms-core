import eventBus from '../eventBus';

const dragOver = (e) => {
  if (e.preventDefault) {
    e.preventDefault();
  }

  if (e.target.nodeName === 'LI') {
    e.target.classList.add('tree__branch--on-drag-over');
  }

  if (e.target.nodeName === 'SPAN' || e.target.nodeName === 'I') {
    e.target.closest('li').classList.add('tree__branch--on-drag-over');
  }
  return false;
};

const removeClass = (e) => {
  if (e.target.nodeName === 'LI') {
    e.target.classList.remove('tree__branch--on-drag-over');
  }

  if (e.target.nodeName === 'SPAN' || e.target.nodeName === 'I') {
    e.target.closest('li').classList.remove('tree__branch--on-drag-over');
  }
};

const dragLeave = (e) => {
  if (e.preventDefault) {
    e.preventDefault();
  }

  removeClass(e);

  return false;
};

const drop = (e) => {
  if (e.preventDefault) {
    e.preventDefault();
  }

  removeClass(e);

  const mediaId = e.dataTransfer.getData('media');
  const categoryId = e.target.closest('li.tree__branch').dataset.id;

  // Vue 3 directives can't emit on a host component, so route through the bus;
  // Media.vue listens for this and handles the move.
  eventBus.emit('media-dropped', { mediaId, categoryId });

  return false;
};

export default {
  mounted(el) {
    el.setAttribute('droppable', true);
    el.addEventListener('dragenter', dragOver);
    el.addEventListener('dragover', dragOver);
    el.addEventListener('dragleave', dragLeave);
    el.addEventListener('drop', drop);
  },

  unmounted(el) {
    el.removeEventListener('dragenter', dragOver);
    el.removeEventListener('dragover', dragOver);
    el.removeEventListener('dragleave', dragLeave);
    el.removeEventListener('drop', drop);
  },
};
