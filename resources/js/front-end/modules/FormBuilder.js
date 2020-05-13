window.FormValidate = require('../plugins/FormValidate');
window.FormValidate = FormValidate.FormValidate;

const gateways = document.querySelectorAll('.payment-gateway input[type="radio"]');
if (gateways.length) {
  gateways.forEach(gateway => {
    gateway.addEventListener('change', e => {
      const activeGateways = document.querySelectorAll('.payment-gateway--active');
      if (activeGateways.length) {
        activeGateways.forEach(active => {
          active.classList.remove('payment-gateway--active');
        });
      }

      const target = event.target;
      const parent = target.closest('.payment-gateway');
      if (target.checked) {
        parent.classList.add('payment-gateway--active');
      }
    })
  })
}
