Vue.directive('draggable-media', {
  bind: function(el) {
    el.setAttribute('draggable', true);

    this.dragStart = (e) => {
      e.target.classList.add('media__file--on-drag-start');
      e.dataTransfer.setData('media', e.target.dataset.id);
      document.getElementById('app').classList.add('media-file-dragging');
      return false;
    };

    this.dragEnd = (e) => {
      e.target.classList.remove('media__file--on-drag-start');
      document.getElementById('app').classList.remove('media-file-dragging');
      return false;
    };

    el.addEventListener('dragstart', this.dragStart);
    el.addEventListener('dragend', this.dragEnd);
  },

  unbind: function(el) {
    el.removeEventListener('dragstart', this.dragStart);
    el.removeEventListener('dragend', this.dragEnd);
  }
});