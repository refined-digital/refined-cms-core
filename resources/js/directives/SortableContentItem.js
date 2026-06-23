import dragula from 'dragula';
import eventBus from '../eventBus';

export default {
  mounted(el) {
    dragula([el], {
      direction: 'vertical',
      moves: (item, container, handle) => handle.classList.contains('fa-sort')
        && handle.parentElement.classList.contains('content-editor__item-sort'),
    }).on('dragend', (item) => {
      const body = item.closest('.content-editor__data').querySelectorAll('.content-editor__item');
      const indexes = [];

      for (const row of body) {
        indexes.push({
          id: row.dataset.id,
          index: parseInt(row.dataset.index, 10),
        });
      }

      eventBus.emit('pages.sortable.content-item.dragend', indexes);
    });
  },
};
