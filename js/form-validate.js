class FormValidate {

  constructor() {
    this.self = this;
    this.regs = {
      email 		: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,
      pass 		: /^[A-Za-z0-9!@#$%^&*()_]{6,20}$/, // Password supports special characters and here min length 6 max 20 charters.
      blank 		: /^.+[\s]{0,4}/ ,
      name		: /^[a-zA-Z0-9]{6,12}[\s]{0,4}/,
      phone		: /^.+[\s]{0,4}/ ,
      num			: /^[0-9]+$/,
      cc			: /^[0-9]{4}$/,
      date 		: /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/
    }
  }

	validate(form) {
	  let errors = [],
	      msg = ''
    ;

    // loop the form
    let fields = form.querySelectorAll('[data-required-label]');
    console.log(fields);
    console.log(this.self);
    console.log(this.regs);
	}
}