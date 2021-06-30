/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/front-end/plugins/FormValidate.js":
/*!********************************************************!*\
  !*** ./resources/js/front-end/plugins/FormValidate.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "FormValidate": () => (/* binding */ FormValidate)
/* harmony export */ });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var FormValidate = /*#__PURE__*/function () {
  function FormValidate() {
    _classCallCheck(this, FormValidate);

    this.self = this;
    this.regs = {
      email: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,
      pass: /^[A-Za-z0-9!@#$%^&*()_]{5,20}$/,
      // Password supports special characters and here min length 6 max 20 charters.
      blank: /^.+[\s]{0,4}/,
      name: /^[a-zA-Z0-9]{6,12}[\s]{0,4}/,
      phone: /^.+[\s]{0,4}/,
      num: /^[0-9]+$/,
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

            case 'email':
              check = this.regs.email;
              break;

            case 'tel':
              check = this.regs.phone;
              break;

            case 'date':
              check = this.regs.date;
              break;
          }

          if (!check.test(field.value)) {
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
/*!*******************************************************!*\
  !*** ./resources/js/front-end/modules/FormBuilder.js ***!
  \*******************************************************/
window.FormValidate = __webpack_require__(/*! ../plugins/FormValidate */ "./resources/js/front-end/plugins/FormValidate.js");
window.FormValidate = FormValidate.FormValidate;
var gateways = document.querySelectorAll('.payment-gateway input[type="radio"]');

if (gateways.length) {
  gateways.forEach(function (gateway) {
    gateway.addEventListener('change', function (e) {
      var activeGateways = document.querySelectorAll('.payment-gateway--active');

      if (activeGateways.length) {
        activeGateways.forEach(function (active) {
          active.classList.remove('payment-gateway--active');
        });
      }

      var target = event.target;
      var parent = target.closest('.payment-gateway');

      if (target.checked) {
        parent.classList.add('payment-gateway--active');
      }
    });
  });
}

var ccField = document.querySelectorAll('.form__control--cc');

if (ccField.length) {
  ccField.forEach(function (field) {
    field.addEventListener('keypress', function (e) {
      var key = e.which || e.keyCode,
          accepted = Array(0, 8, 9, 27, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57);

      if (!accepted.includes(key)) {
        e.preventDefault();
      }
    });
  });
}
})();

/******/ })()
;