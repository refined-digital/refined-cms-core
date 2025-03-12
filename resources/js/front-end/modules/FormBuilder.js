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

class ApiClient {
  constructor() {
    this.defaultHeaders = {
      Accept: 'application/json',
    };
    this.errors = [];
  }

  async request(url, options = {}) {
    this.errors = [];

    const config = {
      method: options.method || 'GET',
      headers: { ...this.defaultHeaders, ...options.headers },
      credentials: 'include',
      body: options.body ?? undefined,
    };
    let response = await fetch(url, config);

    if (response.status === 419 && !options.isRetryRequest) {
      await this.refreshToken();

      // Ensure the CSRF token is included in subsequent requests
      const csrfToken = this.getToken();

      if (csrfToken) {
        if (!options.headers) {
          options.headers = {};
        }

        options.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfToken);
      }

      options.isRetryRequest = true;

      return this.request(url, options);
    }

    if (response.status === 422) {
      const data = await response.json();
      this.errors = [];

      for (let key in data.errors) {
        const error = data.errors[key];
        if (key === 'htime') {
          continue;
        }
        error.forEach(err => this.errors.push(err));
      }

      this.alert();
      return false;
    }

    if (!response.ok) {
      throw new Error(response.statusText);
    }

    return await response.json();
  }

  getToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
  }

  async refreshToken() {
    // Refresh CSRF token and retry request
    await fetch(`/sanctum/csrf-cookie`);
  }

  async checkForToken() {
    const token = this.getToken();
    if (!token) {
      await this.refreshToken();
    }
  }

  alert() {
    if (this.errors.length) {
      let msg = 'The following fields are required: ';
      this.errors.forEach(error => {
        let err = ' field is required.';

        error = error.replace(err, '');

        if (error.startsWith('The')) {
          error = error.substring(4, error.length);
        }

        msg += "\n - "+error;
      });

      alert(msg);
    }
  }
}

if (forms.length) {
  const client = new ApiClient();
  client.checkForToken();

  const sendRequest = async (url, formElement , method = 'POST') => {
    const body = new FormData(formElement);

    const options = {
      method,
    };

    if (body) {
      options.body = body;
    }

    try {
      return await client.request(url, options);
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
    let response;

    try {
      response = await sendRequest(
          formUrl,
          form,
      );

      formIsNotLoading(button);

    } catch (e) {
      console.warn(e);
      alert('There was an error submitting the form, please try again later');
      formIsNotLoading(button);
    }

    if (response) {
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
