/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/email-validator/index.js":
/*!***********************************************!*\
  !*** ./node_modules/email-validator/index.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, exports) => {



var tester = /^[-!#$%&'*+\/0-9=?A-Z^_a-z{|}~](\.?[-!#$%&'*+\/0-9=?A-Z^_a-z`{|}~])*@[a-zA-Z0-9](-*\.?[a-zA-Z0-9])*\.[a-zA-Z](-?[a-zA-Z0-9])+$/;
// Thanks to:
// http://fightingforalostcause.net/misc/2006/compare-email-regex.php
// http://thedailywtf.com/Articles/Validating_Email_Addresses.aspx
// http://stackoverflow.com/questions/201323/what-is-the-best-regular-expression-for-validating-email-addresses/201378#201378
exports.validate = function(email)
{
	if (!email)
		return false;
		
	if(email.length>254)
		return false;

	var valid = tester.test(email);
	if(!valid)
		return false;

	// Further checking of some things regex can't handle
	var parts = email.split("@");
	if(parts[0].length>64)
		return false;

	var domainParts = parts[1].split(".");
	if(domainParts.some(function(part) { return part.length>63; }))
		return false;

	return true;
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!********************************************************!*\
  !*** ./resources/js/front-end/plugins/FormValidate.js ***!
  \********************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "FormValidate": () => (/* binding */ FormValidate)
/* harmony export */ });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

var validator = __webpack_require__(/*! email-validator */ "./node_modules/email-validator/index.js");

var FormValidate = /*#__PURE__*/function () {
  function FormValidate() {
    _classCallCheck(this, FormValidate);

    this.self = this;
    this.regs = {
      pass: /^[A-Za-z0-9!@#$%^&*()_]{5,20}$/,
      // Password supports special characters and here min length 6 max 20 charters.
      blank: /^.+[\s]{0,4}/,
      name: /^[a-zA-Z0-9]{6,12}[\s]{0,4}/,
      phone: /^.+[\s]{0,4}/,
      num: /^[0-9.]+$/,
      date: /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/
    };
    this.errors = [];
  }

  _createClass(FormValidate, [{
    key: "validate",
    value: function validate(form) {
      var _this = this;

      this.errors = []; // loop the form

      var rows = form.querySelectorAll('[data-required-label]');

      if (rows.length) {
        rows.forEach(function (row) {
          var label = row.dataset.requiredLabel;
          var field = row.querySelector('.form__control');

          if (field) {
            _this.validateStandard(label, row, field);
          } else {
            // single checkbox
            _this.validateSingleCheckbox(label, row); // multiple checkboxes


            _this.validateRadioCheckbox(label, row, 'checkbox'); // radios


            _this.validateRadioCheckbox(label, row, 'radio');
          }
        });
      }

      return this.errors;
    }
  }, {
    key: "validateStandard",
    value: function validateStandard(label, row, field) {
      var check = this.regs.blank;
      var error = false;

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
  }, {
    key: "validateSingleCheckbox",
    value: function validateSingleCheckbox(label, row) {
      var single = row.querySelector('.form__control--single-checkbox-input');

      if (single) {
        if (!single.checked) {
          this.errors.push(label);
          row.classList.add('form__row--has-error');
        } else {
          row.classList.remove('form__row--has-error');
        }
      }
    }
  }, {
    key: "validateRadioCheckbox",
    value: function validateRadioCheckbox(label, row, type) {
      var fields = row.querySelectorAll('.form__control--' + type + '-input');

      if (fields.length) {
        var err = true;
        fields.forEach(function (field) {
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
  }, {
    key: "alert",
    value: function (_alert) {
      function alert() {
        return _alert.apply(this, arguments);
      }

      alert.toString = function () {
        return _alert.toString();
      };

      return alert;
    }(function () {
      if (this.errors.length) {
        var msg = 'The following fields are required: ';
        this.errors.forEach(function (error) {
          msg += "\n - " + error;
        });
        alert(msg);
      }
    })
  }]);

  return FormValidate;
}();
})();

/******/ })()
;