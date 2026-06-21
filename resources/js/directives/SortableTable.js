import dragula from 'dragula';

export default {
  mounted(el) {
    const element = el.querySelector('tbody');
    dragula([element], {
      direction: 'vertical',
    }).on('drop', () => {
      if (typeof el.dataset.route !== 'undefined') {
        const positions = [];

        const elements = element.querySelectorAll('tr');
        if (elements.length) {
          elements.forEach((ele) => {
            positions.push(ele.dataset.id);
          });
        }

        axios
          .post(el.dataset.route, { positions })
          .then(() => {})
          .catch((error) => {
            console.log('Table Sort Error', error);
          });
      }
    });
  },
};
