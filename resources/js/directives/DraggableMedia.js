import Vue from 'vue'

const dragStart = (e, el) => {
  e.target.classList.add('media__file--on-drag-start');
  e.dataTransfer.setData('media', e.target.dataset.id);
  const ctr = el.cloneNode(true);
  e.dataTransfer.setDragImage(ctr, 0, 0);
  document.getElementById('app').classList.add('media-file-dragging');
  return false;
};

const dragEnd = (e) => {
  e.target.classList.remove('media__file--on-drag-start');
  document.getElementById('app').classList.remove('media-file-dragging');
  return false;
};


Vue.directive('draggable-media', {
  bind: function(el) {
    el.setAttribute('draggable', true);
    el.addEventListener('dragstart', (e) => dragStart(e, el));
    el.addEventListener('dragend', dragEnd);
  },

  unbind: function(el) {
    el.removeEventListener('dragstart', (e) => dragStart(e, el));
    el.removeEventListener('dragend', dragEnd);
  }
});
