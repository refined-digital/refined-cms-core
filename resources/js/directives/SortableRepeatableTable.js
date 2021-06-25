import Vue from 'vue'

Vue.directive('sortable-repeatable-table', {
  bind(el) {
    dragula([el], {
      direction: 'vertical',
      moves: (el, container, handle) => {
        return handle.classList.contains('fa-sort')
        || handle.classList.contains('data-table__cell--sort')
        ;
      },
    })
    .on('dragend', el => {
      const tbody = el.closest('tbody');
      const indexes = []

      for (const row of tbody.children) {
        indexes.push(parseInt(row.dataset.index, 10));
      }

      eventBus.$emit('sortable-repeatable-table.dragend', {
        id: tbody.dataset.id,
        indexes,
      })
    });
  }
});
