import Vue from 'vue'

Vue.directive('sortable-table', {
  bind: function(el) {
    let element = el.querySelector('tbody');
    dragula([element], {
      direction: 'vertical'
    }).on('drop', function(){
      if (typeof el.dataset.route != 'undefined') {
        let positions = [];

        // get the positions and ids
        let elements = element.querySelectorAll('tr');
        if (elements.length) {
          elements.forEach(ele => {
            positions.push(ele.dataset.id);
          });
        }

        // post to the server
        axios
          .post(el.dataset.route, {
            positions: positions
          })
          .then(response => {})
          .catch(error => {
            console.log('Table Sort Error', error);
          })
        ;
      }

    });
  }
});
