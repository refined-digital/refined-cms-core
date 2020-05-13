window.FormValidate = require('../plugins/FormValidate');
window.FormValidate = FormValidate.FormValidate;

// todo: update to use addEventListen instead
window.FormBuilder = {
  paymentGatewayChanged(event) {
    const gateways = document.querySelectorAll('.payment-gateway--active');
    if (gateways.length) {
      gateways.forEach(gateway => {
        gateway.classList.remove('payment-gateway--active');
      });
    }

    const target = event.target;
    const parent = target.closest('.payment-gateway');
    if (target.checked) {
      parent.classList.add('payment-gateway--active');
    }

  }
};
