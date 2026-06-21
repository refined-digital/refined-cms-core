const dragStart = (e, el) => {
  e.target.classList.add('media__file--on-drag-start');
  e.dataTransfer.setData('media', e.target.closest('.media__file').dataset.id);
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

export default {
  mounted(el) {
    el.setAttribute('draggable', true);
    el._onDragStart = (e) => dragStart(e, el);
    el.addEventListener('dragstart', el._onDragStart);
    el.addEventListener('dragend', dragEnd);
  },

  unmounted(el) {
    el.removeEventListener('dragstart', el._onDragStart);
    el.removeEventListener('dragend', dragEnd);
    delete el._onDragStart;
  },
};
