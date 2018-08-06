Vue.directive('droppable-media', {
  bind: function(el, binding, vnode) {

    el.setAttribute('droppable', true);

    this.dragOver = (e) => {
      if(e.preventDefault) {
        e.preventDefault();
      }

      if (e.target.nodeName == 'LI') {
        e.target.classList.add('tree__branch--on-drag-over');
      }

      if ( (e.target.nodeName == 'SPAN' || e.target.nodeName == 'I')) {
        e.target.closest('li').classList.add('tree__branch--on-drag-over');
      }
      return false;
    }

    this.dragLeave = (e) => {
      if(e.preventDefault) {
        e.preventDefault();
      }

      this.removeClass(e);

      return false;
    }

    this.drop = (e) => {
      if(e.preventDefault) {
        e.preventDefault();
      }

      this.removeClass(e);

      let mediaId = e.dataTransfer.getData('media');
      let categoryId = e.target.closest('li.tree__branch').dataset.id;
      vnode.context.$emit('media-dropped', { mediaId: mediaId, categoryId: categoryId });

      return false;
    }


    this.removeClass = (e) => {
      if (e.target.nodeName == 'LI') {
        e.target.classList.remove('tree__branch--on-drag-over');
      }

      if ( (e.target.nodeName == 'SPAN' || e.target.nodeName == 'I')) {
        e.target.closest('li').classList.remove('tree__branch--on-drag-over');
      }
    }

    el.addEventListener('dragenter', this.dragOver);
    el.addEventListener('dragover', this.dragOver);
    el.addEventListener('dragleave', this.dragLeave);
    el.addEventListener('drop', this.drop);
  },

  unbind: function(el) {
    el.removeEventListener('dragenter', this.dragOver);
    el.removeEventListener('dragover', this.dragOver);
    el.removeEventListener('dragleave', this.dragLeave);
    el.removeEventListener('drop', this.drop);
  }
});