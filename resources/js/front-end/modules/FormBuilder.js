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

const forms = Array.from(document.querySelectorAll('form')).filter(form =>
    form.className.match(/\bform--\d+\b/)
);
if (forms.length) {
  const sendRequest = async (url, formElement , method = 'POST') => {
    const headers = {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    const body = new FormData(formElement);

    const options = {
      method,
      headers,
      credentials: 'same-origin'
    };

    if (body) {
      options.body = body;
    }

    try {
      const response = await fetch(url, options);
      return await response.json();

    } catch (error) {
      console.error('Fetch error:', error);
      throw error;
    }
  }

  const formIsLoading = (button = null) => {
    if (button) {
      button.disabled = true;
      button.classList.add('button--loading');
    }
  }
  const formIsNotLoading = (button = null) => {
    if (button) {
      button.disabled = false;
      button.classList.remove('button--loading');
    }
  }

  const submit = async (form, button) => {
    const formUrl = form.getAttribute('action');

    try {
      const response = await sendRequest(
          formUrl,
          form,
      );

      formIsNotLoading(button);

      if (response.url) {
        window.location.href = response.url;
      }

      if (response.confirmation) {
        let element = form;

        if (form.dataset.replacement) {
          element = document.querySelector(`.${form.dataset.replacement}`);
        }

        if (element) {
          if (element.tagName === 'FORM') {
            element.insertAdjacentHTML('beforebegin', response.confirmation);
            element.remove();
          } else {
            element.innerHTML = response.confirmation;
          }
        }

      }


    } catch (e) {
      console.warn(e);
      alert('There was an error submitting the form, please try again later');
      formIsNotLoading(button);
    }
  }

  forms.forEach(form => {
    const validate = new window.FormValidate();
    const tokenField = form.querySelector('input[name="_captcha"]');

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const submitButton = form.querySelector('.form__row--buttons .button');
      const errors = validate.validate(this);

      formIsLoading(submitButton);

      if (errors.length) {
        validate.alert();

        formIsNotLoading(submitButton);
      } else {
        if (!tokenField) {
          await submit(form, submitButton);
        } else {
          grecaptcha.ready(() => {
            try {
              grecaptcha
                  .execute(form.dataset.red, { action: 'submit' })
                  .then(async (token) => {
                    tokenField.value = token;

                    await submit(form, submitButton);
                  })
              ;
            } catch (e) {
              console.warn(e.message);
              alert('An error has occurred, please try later.')
              if (submitButton) {
                formIsNotLoading(submitButton);
              }
            }
          });
        }
      }
    });
  });
}
