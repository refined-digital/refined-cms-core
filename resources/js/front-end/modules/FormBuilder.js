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


const ccField = document.querySelectorAll('.form__control--cc');
if (ccField.length) {
  ccField.forEach(field => {
    field.addEventListener('keypress', e => {
      const key = e.which || e.keyCode,
        accepted = Array(0,8,9,27,46,48,49,50,51,52,53,54,55,56,57)
      ;

      if(!accepted.includes(key)) {
        e.preventDefault();
      }
    });
  })
}
