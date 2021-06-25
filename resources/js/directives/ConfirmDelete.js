import Vue from 'vue'

Vue.directive('confirm-delete', {
  bind: function(el) {
    var submitForm = false;
    el.addEventListener('submit', e => {
      if (!submitForm) {
        e.preventDefault();
      }

      swal({
        title: 'Are you sure?',
        icon: 'warning',
        buttons: true,
        dangerMode: true,
      })
      .then((value) => {
        if (value) {
          submitForm = true;
          el.submit();
        }
      })
      ;
    })
  }
});
