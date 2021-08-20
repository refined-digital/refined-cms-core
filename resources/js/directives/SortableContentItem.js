import Vue from 'vue'

Vue.directive('sortable-content-item', {
  bind(el) {
    dragula([el], {
      direction: 'vertical',
      moves: (el, container, handle) => {
        return handle.classList.contains('fa-sort') && handle.parentElement.classList.contains('content-editor__item-sort');
      },
    })
    .on('dragend', el => {
      const body = el.closest('.content-editor__data').querySelectorAll('.content-editor__item');
      const indexes = []

      for (const row of body) {
        indexes.push({
          id: row.dataset.id,
          index: parseInt(row.dataset.index, 10)
        });
      }

      eventBus.$emit('pages.sortable.content-item.dragend', indexes)
    });
  }
});
