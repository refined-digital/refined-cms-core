import dragula from 'dragula';
import eventBus from '../eventBus';

export default {
  mounted(el) {
    dragula([el], {
      direction: 'vertical',
      moves: (item, container, handle) => handle.classList.contains('fa-sort')
        || handle.classList.contains('data-table__cell--sort'),
    }).on('dragend', (item) => {
      const tbody = item.closest('tbody');
      const indexes = [];

      for (const row of tbody.children) {
        indexes.push(parseInt(row.dataset.index, 10));
      }

      eventBus.emit('sortable-repeatable-table.dragend', {
        id: tbody.dataset.id,
        indexes,
      });
    });
  },
};
