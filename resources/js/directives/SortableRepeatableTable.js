Vue.directive('sortable-repeatable-table', {
  bind: function(el) {
    dragula([el], {
      direction: 'vertical',
      moves: (el, container, handle) => {
        return handle.classList.contains('fa-sort')
        || handle.classList.contains('data-table__cell--sort')
        ;
      }
    });
  }
});
