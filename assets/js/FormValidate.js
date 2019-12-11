/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 361);
/******/ })
/************************************************************************/
/******/ ({

/***/ 32:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "FormValidate", function() { return FormValidate; });
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var FormValidate = function () {
  function FormValidate() {
    _classCallCheck(this, FormValidate);

    this.self = this;
    this.regs = {
      email: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,
      pass: /^[A-Za-z0-9!@#$%^&*()_]{5,20}$/, // Password supports special characters and here min length 6 max 20 charters.
      blank: /^.+[\s]{0,4}/,
      name: /^[a-zA-Z0-9]{6,12}[\s]{0,4}/,
      phone: /^.+[\s]{0,4}/,
      num: /^[0-9]+$/,
      date: /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/
    };
    this.errors = [];
  }

  _createClass(FormValidate, [{
    key: 'validate',
    value: function validate(form) {
      var _this = this;

      this.errors = [];

      // loop the form
      var rows = form.querySelectorAll('[data-required-label]');
      if (rows.length) {
        rows.forEach(function (row) {
          var label = row.dataset.requiredLabel;
          var field = row.querySelector('.form__control');

          if (field) {
            _this.validateStandard(label, row, field);
          } else {
            // single checkbox
            _this.validateSingleCheckbox(label, row);

            // multiple checkboxes
            _this.validateRadioCheckbox(label, row, 'checkbox');

            // radios
            _this.validateRadioCheckbox(label, row, 'radio');
          }
        });
      }

      return this.errors;
    }
  }, {
    key: 'validateStandard',
    value: function validateStandard(label, row, field) {
      var check = this.regs.blank;
      var error = false;

      switch (field.nodeName) {
        case 'INPUT':
        case 'TEXTAREA':
          switch (field.type) {
            case 'number':
              check = this.regs.num;break;
            case 'password':
              check = this.regs.pass;break;
            case 'email':
              check = this.regs.email;break;
            case 'tel':
              check = this.regs.phone;break;
            case 'date':
              check = this.regs.date;break;
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
    key: 'validateSingleCheckbox',
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
    key: 'validateRadioCheckbox',
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
    key: 'alert',
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

/***/ }),

/***/ 361:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(32);


/***/ })

/******/ });