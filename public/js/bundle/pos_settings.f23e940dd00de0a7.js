"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["pos_settings"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js":
/*!*********************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js ***!
  \*********************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vuex */ "./node_modules/vuex/dist/vuex.esm.js");
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! nprogress */ "./node_modules/nprogress/nprogress.js");
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(nprogress__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  metaInfo: {
    title: "POS Settings"
  },
  data: function data() {
    return {
      isLoading: true,
      pos_settings: {
        note_customer: "",
        show_note: "",
        show_barcode: "",
        show_discount: "",
        show_phone: "",
        show_email: "",
        show_address: "",
        show_customer: "",
        show_Warehouse: "",
        is_printable: ''
      }
    };
  },
  methods: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_1__.mapActions)(["refreshUserPermissions"])), {}, {
    //------------- Submit Validation Pos Setting
    Submit_Pos_Settings: function Submit_Pos_Settings() {
      var _this = this;
      this.$refs.Submit_Pos_Settings.validate().then(function (success) {
        if (!success) {
          _this.makeToast("danger", _this.$t("Please_fill_the_form_correctly"), _this.$t("Failed"));
        } else {
          _this.Update_Pos_Settings();
        }
      });
    },
    //------ Toast
    makeToast: function makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },
    getValidationState: function getValidationState(_ref) {
      var dirty = _ref.dirty,
        validated = _ref.validated,
        _ref$valid = _ref.valid,
        valid = _ref$valid === void 0 ? null : _ref$valid;
      return dirty || validated ? valid : null;
    },
    //---------------------------------- Update_Pos_Settings ----------------\\
    Update_Pos_Settings: function Update_Pos_Settings() {
      var _this2 = this;
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().start();
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().set(0.1);
      axios.put("pos_settings/" + this.pos_settings.id, {
        note_customer: this.pos_settings.note_customer,
        show_note: this.pos_settings.show_note,
        show_barcode: this.pos_settings.show_barcode,
        show_discount: this.pos_settings.show_discount,
        show_phone: this.pos_settings.show_phone,
        show_email: this.pos_settings.show_email,
        show_address: this.pos_settings.show_address,
        show_customer: this.pos_settings.show_customer,
        show_Warehouse: this.pos_settings.show_Warehouse,
        is_printable: this.pos_settings.is_printable
      }).then(function (response) {
        Fire.$emit("Event_Pos_Settings");
        _this2.makeToast("success", _this2.$t("Successfully_Updated"), _this2.$t("Success"));
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
      })["catch"](function (error) {
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
        _this2.makeToast("danger", _this2.$t("InvalidData"), _this2.$t("Failed"));
      });
    },
    //---------------------------------- Get_pos_Settings ----------------\\ 
    get_pos_Settings: function get_pos_Settings() {
      var _this3 = this;
      axios.get("get_pos_Settings").then(function (response) {
        _this3.pos_settings = response.data.pos_settings;
        _this3.isLoading = false;
      })["catch"](function (error) {
        _this3.isLoading = false;
      });
    }
  }),
  //end Methods

  //----------------------------- Created function-------------------

  created: function created() {
    var _this4 = this;
    this.get_pos_Settings();
    Fire.$on("Event_Pos_Settings", function () {
      _this4.get_pos_Settings();
    });
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* binding */ render),
/* harmony export */   staticRenderFns: () => (/* binding */ staticRenderFns)
/* harmony export */ });
var render = function render() {
  var _vm = this,
    _c = _vm._self._c;
  return _c("div", {
    staticClass: "main-content"
  }, [_c("breadcumb", {
    attrs: {
      page: _vm.$t("pos_settings"),
      folder: _vm.$t("Settings")
    }
  }), _vm._v(" "), _vm.isLoading ? _c("div", {
    staticClass: "loading_page spinner spinner-primary mr-3"
  }) : _vm._e(), _vm._v(" "), !_vm.isLoading ? _c("validation-observer", {
    ref: "Submit_Pos_Settings"
  }, [_c("b-form", {
    on: {
      submit: function submit($event) {
        $event.preventDefault();
        return _vm.Submit_Pos_Settings.apply(null, arguments);
      }
    }
  }, [_c("b-row", {
    staticClass: "mt-5"
  }, [_c("b-col", {
    attrs: {
      lg: "12",
      md: "12",
      sm: "12"
    }
  }, [_c("b-card", {
    attrs: {
      "no-body": "",
      header: _vm.$t("Pos_Settings")
    }
  }, [_c("b-card-body", [_c("b-row", [_c("b-col", {
    attrs: {
      lg: "12",
      md: "12",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "note",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("Note_to_customer") + " " + "*"
          }
        }, [_c("b-form-input", {
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "note-feedback",
            label: "Note to customer",
            placeholder: _vm.$t("Note_to_customer")
          },
          model: {
            value: _vm.pos_settings.note_customer,
            callback: function callback($$v) {
              _vm.$set(_vm.pos_settings, "note_customer", $$v);
            },
            expression: "pos_settings.note_customer"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "note-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 1637698482)
  })], 1), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Phone")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_phone,
      expression: "pos_settings.show_phone"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_phone) ? _vm._i(_vm.pos_settings.show_phone, null) > -1 : _vm.pos_settings.show_phone
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_phone,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_phone", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_phone", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_phone", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Address")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_address,
      expression: "pos_settings.show_address"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_address) ? _vm._i(_vm.pos_settings.show_address, null) > -1 : _vm.pos_settings.show_address
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_address,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_address", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_address", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_address", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Email")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_email,
      expression: "pos_settings.show_email"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_email) ? _vm._i(_vm.pos_settings.show_email, null) > -1 : _vm.pos_settings.show_email
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_email,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_email", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_email", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_email", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Customer")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_customer,
      expression: "pos_settings.show_customer"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_customer) ? _vm._i(_vm.pos_settings.show_customer, null) > -1 : _vm.pos_settings.show_customer
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_customer,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_customer", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_customer", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_customer", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Warehouse")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_Warehouse,
      expression: "pos_settings.show_Warehouse"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_Warehouse) ? _vm._i(_vm.pos_settings.show_Warehouse, null) > -1 : _vm.pos_settings.show_Warehouse
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_Warehouse,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_Warehouse", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_Warehouse", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_Warehouse", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Tax_and_Discount")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_discount,
      expression: "pos_settings.show_discount"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_discount) ? _vm._i(_vm.pos_settings.show_discount, null) > -1 : _vm.pos_settings.show_discount
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_discount,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_discount", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_discount", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_discount", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_barcode")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_barcode,
      expression: "pos_settings.show_barcode"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_barcode) ? _vm._i(_vm.pos_settings.show_barcode, null) > -1 : _vm.pos_settings.show_barcode
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_barcode,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_barcode", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_barcode", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_barcode", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Show_Note_to_customer")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.show_note,
      expression: "pos_settings.show_note"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.show_note) ? _vm._i(_vm.pos_settings.show_note, null) > -1 : _vm.pos_settings.show_note
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.show_note,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "show_note", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "show_note", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "show_note", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    staticClass: "mt-3 mb-3",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "switch switch-primary mr-3"
  }, [_vm._v("\n                       " + _vm._s(_vm.$t("Enable_Print_Invoice")) + "\n                        "), _c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.pos_settings.is_printable,
      expression: "pos_settings.is_printable"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.pos_settings.is_printable) ? _vm._i(_vm.pos_settings.is_printable, null) > -1 : _vm.pos_settings.is_printable
    },
    on: {
      change: function change($event) {
        var $$a = _vm.pos_settings.is_printable,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.pos_settings, "is_printable", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.pos_settings, "is_printable", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.pos_settings, "is_printable", $$c);
        }
      }
    }
  }), _vm._v(" "), _c("span", {
    staticClass: "slider"
  })])]), _vm._v(" "), _c("b-col", {
    attrs: {
      md: "12"
    }
  }, [_c("b-form-group", [_c("b-button", {
    attrs: {
      variant: "primary",
      type: "submit"
    }
  }, [_c("i", {
    staticClass: "i-Yes me-2 font-weight-bold"
  }), _vm._v(" " + _vm._s(_vm.$t("submit")))])], 1)], 1)], 1)], 1)], 1)], 1)], 1)], 1)], 1) : _vm._e()], 1);
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./resources/src/views/app/pages/settings/pos_settings.vue":
/*!*****************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/pos_settings.vue ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./pos_settings.vue?vue&type=template&id=5e3e9e8d */ "./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d");
/* harmony import */ var _pos_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./pos_settings.vue?vue&type=script&lang=js */ "./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _pos_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  _pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__.render,
  _pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/src/views/app/pages/settings/pos_settings.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js":
/*!*****************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js ***!
  \*****************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_pos_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./pos_settings.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=script&lang=js");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_pos_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d":
/*!***********************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d ***!
  \***********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_pos_settings_vue_vue_type_template_id_5e3e9e8d__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./pos_settings.vue?vue&type=template&id=5e3e9e8d */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/pos_settings.vue?vue&type=template&id=5e3e9e8d");


/***/ })

}]);