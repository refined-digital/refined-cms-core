const validator = require('email-validator');

export class FormValidate {

  constructor() {
    this.self = this;
    this.regs = {
      pass 		: /^[A-Za-z0-9!@#$%^&*()_]{5,20}$/, // Password supports special characters and here min length 6 max 20 charters.
      blank 	: /^.+[\s]{0,4}/ ,
      name		: /^[a-zA-Z0-9]{6,12}[\s]{0,4}/,
      phone		: /^.+[\s]{0,4}/ ,
      num		: /^[0-9.]+$/,
      date 		: /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/
    }
    this.errors = [];
  }

	validate(form) {
	  this.errors = [];

    // loop the form
    let rows = form.querySelectorAll('[data-required-label]');
    if (rows.length) {
      rows.forEach(row => {
        let label = row.dataset.requiredLabel;
        let field = row.querySelector('.form__control');

        if (field) {
          this.validateStandard(label, row, field);
        } else {
          // single checkbox
          this.validateSingleCheckbox(label, row);

          // multiple checkboxes
          this.validateRadioCheckbox(label, row, 'checkbox');

          // radios
          this.validateRadioCheckbox(label, row, 'radio');
        }

      });
    }

    return this.errors;
	}

	validateStandard(label, row, field) {
      let check = this.regs.blank;
      let error = false;

      switch (field.nodeName) {
        case 'INPUT':
        case 'TEXTAREA':
          switch (field.type) {
            case 'number':
              check = this.regs.num;
              break;
            case 'password':
              check = this.regs.pass;
              break;
            case 'tel':
              check = this.regs.phone;
              break;
            case 'date':
              check = this.regs.date;
              break;
          }

          console.log(field.type, field.value, check, check.test(field.value));

          if (field.type === 'email') {
            error = !validator.validate(field.value);
          } else if (!check.test(field.value)) {
            error = true;
          }
          break;

        case 'SELECT':
          if (field.value == 'Please Select' || field.value == 0) {
            error = true;
          }
          break;
      }

      if (error) {
        this.errors.push(label);
        row.classList.add('form__row--has-error');
      } else {
        row.classList.remove('form__row--has-error');
      }

	}

	validateSingleCheckbox(label, row) {
    let single = row.querySelector('.form__control--single-checkbox-input');
	  if (single) {
      if(!single.checked) {
        this.errors.push(label);
        row.classList.add('form__row--has-error');
      } else {
        row.classList.remove('form__row--has-error');
      }
    }
  }

  validateRadioCheckbox(label, row, type) {

    let fields = row.querySelectorAll('.form__control--'+type+'-input');
    if (fields.length) {
      let err = true;
      fields.forEach(field => {
        if (field.checked) {
          err = false;
          return;
        }
      });

      if (err) {
        this.errors.push(label);
        row.classList.add('form__row--has-error');
      } else {
        row.classList.remove('form__row--has-error');
      }
    }

  }

  alert() {
    if (this.errors.length) {
      let msg = 'The following fields are required: ';
      this.errors.forEach(error => {
        msg += "\n - "+error;
      });
      alert(msg);
    }
  }
}
