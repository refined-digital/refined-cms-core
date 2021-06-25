import Vue from 'vue'

const dragOver = (e) => {
  if(e.preventDefault) {
    e.preventDefault();
  }

  if (e.target.nodeName === 'LI') {
    e.target.classList.add('tree__branch--on-drag-over');
  }

  if ( (e.target.nodeName === 'SPAN' || e.target.nodeName === 'I')) {
    e.target.closest('li').classList.add('tree__branch--on-drag-over');
  }
  return false;
}

const dragLeave = (e) => {
  if(e.preventDefault) {
    e.preventDefault();
  }

  removeClass(e);

  return false;
}

const drop = (e) => {
  if(e.preventDefault) {
    e.preventDefault();
  }

  removeClass(e);

  let mediaId = e.dataTransfer.getData('media');
  let categoryId = e.target.closest('li.tree__branch').dataset.id;
  vnode.context.$emit('media-dropped', { mediaId: mediaId, categoryId: categoryId });

  return false;
}


const removeClass = (e) => {
  if (e.target.nodeName === 'LI') {
    e.target.classList.remove('tree__branch--on-drag-over');
  }

  if ( (e.target.nodeName === 'SPAN' || e.target.nodeName === 'I')) {
    e.target.closest('li').classList.remove('tree__branch--on-drag-over');
  }
}


Vue.directive('droppable-media', {
  bind: function(el, binding, vnode) {
    el.setAttribute('droppable', true);
    el.addEventListener('dragenter', dragOver);
    el.addEventListener('dragover', dragOver);
    el.addEventListener('dragleave', dragLeave);
    el.addEventListener('drop', drop);
  },

  unbind: function(el) {
    el.removeEventListener('dragenter', dragOver);
    el.removeEventListener('dragover', dragOver);
    el.removeEventListener('dragleave', dragLeave);
    el.removeEventListener('drop', drop);
  }
});
