"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["System_settings"],{

/***/ "./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js":
/*!****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var vuex__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vuex */ "./node_modules/.pnpm/vuex@3.6.2_vue@2.7.16/node_modules/vuex/dist/vuex.esm.js");
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! nprogress */ "./node_modules/.pnpm/nprogress@0.2.0/node_modules/nprogress/nprogress.js");
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(nprogress__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _regeneratorRuntime() { "use strict"; /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/facebook/regenerator/blob/main/LICENSE */ _regeneratorRuntime = function _regeneratorRuntime() { return e; }; var t, e = {}, r = Object.prototype, n = r.hasOwnProperty, o = Object.defineProperty || function (t, e, r) { t[e] = r.value; }, i = "function" == typeof Symbol ? Symbol : {}, a = i.iterator || "@@iterator", c = i.asyncIterator || "@@asyncIterator", u = i.toStringTag || "@@toStringTag"; function define(t, e, r) { return Object.defineProperty(t, e, { value: r, enumerable: !0, configurable: !0, writable: !0 }), t[e]; } try { define({}, ""); } catch (t) { define = function define(t, e, r) { return t[e] = r; }; } function wrap(t, e, r, n) { var i = e && e.prototype instanceof Generator ? e : Generator, a = Object.create(i.prototype), c = new Context(n || []); return o(a, "_invoke", { value: makeInvokeMethod(t, r, c) }), a; } function tryCatch(t, e, r) { try { return { type: "normal", arg: t.call(e, r) }; } catch (t) { return { type: "throw", arg: t }; } } e.wrap = wrap; var h = "suspendedStart", l = "suspendedYield", f = "executing", s = "completed", y = {}; function Generator() {} function GeneratorFunction() {} function GeneratorFunctionPrototype() {} var p = {}; define(p, a, function () { return this; }); var d = Object.getPrototypeOf, v = d && d(d(values([]))); v && v !== r && n.call(v, a) && (p = v); var g = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(p); function defineIteratorMethods(t) { ["next", "throw", "return"].forEach(function (e) { define(t, e, function (t) { return this._invoke(e, t); }); }); } function AsyncIterator(t, e) { function invoke(r, o, i, a) { var c = tryCatch(t[r], t, o); if ("throw" !== c.type) { var u = c.arg, h = u.value; return h && "object" == _typeof(h) && n.call(h, "__await") ? e.resolve(h.__await).then(function (t) { invoke("next", t, i, a); }, function (t) { invoke("throw", t, i, a); }) : e.resolve(h).then(function (t) { u.value = t, i(u); }, function (t) { return invoke("throw", t, i, a); }); } a(c.arg); } var r; o(this, "_invoke", { value: function value(t, n) { function callInvokeWithMethodAndArg() { return new e(function (e, r) { invoke(t, n, e, r); }); } return r = r ? r.then(callInvokeWithMethodAndArg, callInvokeWithMethodAndArg) : callInvokeWithMethodAndArg(); } }); } function makeInvokeMethod(e, r, n) { var o = h; return function (i, a) { if (o === f) throw Error("Generator is already running"); if (o === s) { if ("throw" === i) throw a; return { value: t, done: !0 }; } for (n.method = i, n.arg = a;;) { var c = n.delegate; if (c) { var u = maybeInvokeDelegate(c, n); if (u) { if (u === y) continue; return u; } } if ("next" === n.method) n.sent = n._sent = n.arg;else if ("throw" === n.method) { if (o === h) throw o = s, n.arg; n.dispatchException(n.arg); } else "return" === n.method && n.abrupt("return", n.arg); o = f; var p = tryCatch(e, r, n); if ("normal" === p.type) { if (o = n.done ? s : l, p.arg === y) continue; return { value: p.arg, done: n.done }; } "throw" === p.type && (o = s, n.method = "throw", n.arg = p.arg); } }; } function maybeInvokeDelegate(e, r) { var n = r.method, o = e.iterator[n]; if (o === t) return r.delegate = null, "throw" === n && e.iterator["return"] && (r.method = "return", r.arg = t, maybeInvokeDelegate(e, r), "throw" === r.method) || "return" !== n && (r.method = "throw", r.arg = new TypeError("The iterator does not provide a '" + n + "' method")), y; var i = tryCatch(o, e.iterator, r.arg); if ("throw" === i.type) return r.method = "throw", r.arg = i.arg, r.delegate = null, y; var a = i.arg; return a ? a.done ? (r[e.resultName] = a.value, r.next = e.nextLoc, "return" !== r.method && (r.method = "next", r.arg = t), r.delegate = null, y) : a : (r.method = "throw", r.arg = new TypeError("iterator result is not an object"), r.delegate = null, y); } function pushTryEntry(t) { var e = { tryLoc: t[0] }; 1 in t && (e.catchLoc = t[1]), 2 in t && (e.finallyLoc = t[2], e.afterLoc = t[3]), this.tryEntries.push(e); } function resetTryEntry(t) { var e = t.completion || {}; e.type = "normal", delete e.arg, t.completion = e; } function Context(t) { this.tryEntries = [{ tryLoc: "root" }], t.forEach(pushTryEntry, this), this.reset(!0); } function values(e) { if (e || "" === e) { var r = e[a]; if (r) return r.call(e); if ("function" == typeof e.next) return e; if (!isNaN(e.length)) { var o = -1, i = function next() { for (; ++o < e.length;) if (n.call(e, o)) return next.value = e[o], next.done = !1, next; return next.value = t, next.done = !0, next; }; return i.next = i; } } throw new TypeError(_typeof(e) + " is not iterable"); } return GeneratorFunction.prototype = GeneratorFunctionPrototype, o(g, "constructor", { value: GeneratorFunctionPrototype, configurable: !0 }), o(GeneratorFunctionPrototype, "constructor", { value: GeneratorFunction, configurable: !0 }), GeneratorFunction.displayName = define(GeneratorFunctionPrototype, u, "GeneratorFunction"), e.isGeneratorFunction = function (t) { var e = "function" == typeof t && t.constructor; return !!e && (e === GeneratorFunction || "GeneratorFunction" === (e.displayName || e.name)); }, e.mark = function (t) { return Object.setPrototypeOf ? Object.setPrototypeOf(t, GeneratorFunctionPrototype) : (t.__proto__ = GeneratorFunctionPrototype, define(t, u, "GeneratorFunction")), t.prototype = Object.create(g), t; }, e.awrap = function (t) { return { __await: t }; }, defineIteratorMethods(AsyncIterator.prototype), define(AsyncIterator.prototype, c, function () { return this; }), e.AsyncIterator = AsyncIterator, e.async = function (t, r, n, o, i) { void 0 === i && (i = Promise); var a = new AsyncIterator(wrap(t, r, n, o), i); return e.isGeneratorFunction(r) ? a : a.next().then(function (t) { return t.done ? t.value : a.next(); }); }, defineIteratorMethods(g), define(g, u, "Generator"), define(g, a, function () { return this; }), define(g, "toString", function () { return "[object Generator]"; }), e.keys = function (t) { var e = Object(t), r = []; for (var n in e) r.push(n); return r.reverse(), function next() { for (; r.length;) { var t = r.pop(); if (t in e) return next.value = t, next.done = !1, next; } return next.done = !0, next; }; }, e.values = values, Context.prototype = { constructor: Context, reset: function reset(e) { if (this.prev = 0, this.next = 0, this.sent = this._sent = t, this.done = !1, this.delegate = null, this.method = "next", this.arg = t, this.tryEntries.forEach(resetTryEntry), !e) for (var r in this) "t" === r.charAt(0) && n.call(this, r) && !isNaN(+r.slice(1)) && (this[r] = t); }, stop: function stop() { this.done = !0; var t = this.tryEntries[0].completion; if ("throw" === t.type) throw t.arg; return this.rval; }, dispatchException: function dispatchException(e) { if (this.done) throw e; var r = this; function handle(n, o) { return a.type = "throw", a.arg = e, r.next = n, o && (r.method = "next", r.arg = t), !!o; } for (var o = this.tryEntries.length - 1; o >= 0; --o) { var i = this.tryEntries[o], a = i.completion; if ("root" === i.tryLoc) return handle("end"); if (i.tryLoc <= this.prev) { var c = n.call(i, "catchLoc"), u = n.call(i, "finallyLoc"); if (c && u) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } else if (c) { if (this.prev < i.catchLoc) return handle(i.catchLoc, !0); } else { if (!u) throw Error("try statement without catch or finally"); if (this.prev < i.finallyLoc) return handle(i.finallyLoc); } } } }, abrupt: function abrupt(t, e) { for (var r = this.tryEntries.length - 1; r >= 0; --r) { var o = this.tryEntries[r]; if (o.tryLoc <= this.prev && n.call(o, "finallyLoc") && this.prev < o.finallyLoc) { var i = o; break; } } i && ("break" === t || "continue" === t) && i.tryLoc <= e && e <= i.finallyLoc && (i = null); var a = i ? i.completion : {}; return a.type = t, a.arg = e, i ? (this.method = "next", this.next = i.finallyLoc, y) : this.complete(a); }, complete: function complete(t, e) { if ("throw" === t.type) throw t.arg; return "break" === t.type || "continue" === t.type ? this.next = t.arg : "return" === t.type ? (this.rval = this.arg = t.arg, this.method = "return", this.next = "end") : "normal" === t.type && e && (this.next = e), y; }, finish: function finish(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.finallyLoc === t) return this.complete(r.completion, r.afterLoc), resetTryEntry(r), y; } }, "catch": function _catch(t) { for (var e = this.tryEntries.length - 1; e >= 0; --e) { var r = this.tryEntries[e]; if (r.tryLoc === t) { var n = r.completion; if ("throw" === n.type) { var o = n.arg; resetTryEntry(r); } return o; } } throw Error("illegal catch attempt"); }, delegateYield: function delegateYield(e, r, n) { return this.delegate = { iterator: values(e), resultName: r, nextLoc: n }, "next" === this.method && (this.arg = t), y; } }, e; }
function asyncGeneratorStep(n, t, e, r, o, a, c) { try { var i = n[a](c), u = i.value; } catch (n) { return void e(n); } i.done ? t(u) : Promise.resolve(u).then(r, o); }
function _asyncToGenerator(n) { return function () { var t = this, e = arguments; return new Promise(function (r, o) { var a = n.apply(t, e); function _next(n) { asyncGeneratorStep(a, r, o, _next, _throw, "next", n); } function _throw(n) { asyncGeneratorStep(a, r, o, _next, _throw, "throw", n); } _next(void 0); }); }; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }


/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  metaInfo: {
    title: "System Settings"
  },
  data: function data() {
    return {
      isLoading: true,
      data: new FormData(),
      settings: [],
      currencies: [],
      clients: [],
      warehouses: [],
      sms_gateway: [],
      zones_array: [],
      setting: {
        client_id: "",
        warehouse_id: "",
        currency_id: "",
        email: "",
        logo: "",
        CompanyName: "",
        CompanyPhone: "",
        CompanyAddress: "",
        footer: "",
        developed_by: "",
        default_language: "",
        sms_gateway: "",
        is_invoice_footer: '',
        invoice_footer: '',
        quotation_with_stock: ''
      }
    };
  },
  methods: _objectSpread(_objectSpread({}, (0,vuex__WEBPACK_IMPORTED_MODULE_1__.mapActions)(["refreshUserPermissions"])), {}, {
    SetLocal: function SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("language/setLanguage", locale);
      Fire.$emit("ChangeLanguage");
    },
    //------------- Submit Validation Setting
    Submit_Setting: function Submit_Setting() {
      var _this = this;
      this.$refs.form_setting.validate().then(function (success) {
        if (!success) {
          _this.makeToast("danger", _this.$t("Please_fill_the_form_correctly"), _this.$t("Failed"));
        } else {
          _this.Update_Settings();
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
    //------------------------------ Event Upload Logo -------------------------------\\
    onFileSelected: function onFileSelected(e) {
      var _this2 = this;
      return _asyncToGenerator(/*#__PURE__*/_regeneratorRuntime().mark(function _callee() {
        var _yield$_this2$$refs$L, valid;
        return _regeneratorRuntime().wrap(function _callee$(_context) {
          while (1) switch (_context.prev = _context.next) {
            case 0:
              _context.next = 2;
              return _this2.$refs.Logo.validate(e);
            case 2:
              _yield$_this2$$refs$L = _context.sent;
              valid = _yield$_this2$$refs$L.valid;
              if (valid) {
                _this2.setting.logo = e.target.files[0];
              } else {
                _this2.setting.logo = "";
              }
            case 5:
            case "end":
              return _context.stop();
          }
        }, _callee);
      }))();
    },
    Selected_Time_Zone: function Selected_Time_Zone(value) {
      if (value === null) {
        this.setting.timezone = "";
      }
    },
    //---------------------------------- Update Settings ----------------\\
    Update_Settings: function Update_Settings() {
      var _this3 = this;
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().start();
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().set(0.1);
      var self = this;
      self.data.append("client", self.setting.client_id);
      self.data.append("warehouse", self.setting.warehouse_id);
      self.data.append("currency", self.setting.currency_id);
      self.data.append("email", self.setting.email);
      self.data.append("logo", self.setting.logo);
      self.data.append("CompanyName", self.setting.CompanyName);
      self.data.append("CompanyPhone", self.setting.CompanyPhone);
      self.data.append("CompanyAddress", self.setting.CompanyAddress);
      self.data.append("footer", self.setting.footer);
      self.data.append("developed_by", self.setting.developed_by);
      self.data.append("default_language", self.setting.default_language);
      self.data.append("sms_gateway", self.setting.sms_gateway);
      self.data.append("is_invoice_footer", self.setting.is_invoice_footer);
      self.data.append("invoice_footer", self.setting.invoice_footer);
      self.data.append("quotation_with_stock", self.setting.quotation_with_stock);
      self.data.append("timezone", self.setting.timezone);
      self.data.append("_method", "put");
      axios.post("settings/" + self.setting.id, self.data).then(function (response) {
        Fire.$emit("Event_Setting");
        _this3.makeToast("success", _this3.$t("Successfully_Updated"), _this3.$t("Success"));
        _this3.refreshUserPermissions();
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
        _this3.SetLocal(self.setting.default_language);
      })["catch"](function (error) {
        _this3.makeToast("danger", _this3.$t("InvalidData"), _this3.$t("Failed"));
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
      });
    },
    //---------------------------------- Clear_Cache ----------------\\
    Clear_Cache: function Clear_Cache() {
      var _this4 = this;
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().start();
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().set(0.1);
      axios.get("clear_cache").then(function (response) {
        _this4.makeToast("success", _this4.$t("Cache_cleared_successfully"), _this4.$t("Success"));
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
      })["catch"](function (error) {
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
        _this4.makeToast("danger", _this4.$t("Failed_to_clear_cache"), _this4.$t("Failed"));
      });
    },
    //---------------------------------- Get SETTINGS ----------------\\
    Get_Settings: function Get_Settings() {
      var _this5 = this;
      axios.get("get_Settings_data").then(function (response) {
        _this5.setting = response.data.settings;
        _this5.currencies = response.data.currencies;
        _this5.clients = response.data.clients;
        _this5.warehouses = response.data.warehouses;
        _this5.sms_gateway = response.data.sms_gateway;
        _this5.zones_array = response.data.zones_array;
        _this5.isLoading = false;
      })["catch"](function (error) {
        setTimeout(function () {
          _this5.isLoading = false;
        }, 500);
      });
    }
  }),
  //end Methods

  //----------------------------- Created function-------------------

  created: function created() {
    var _this6 = this;
    this.Get_Settings();
    Fire.$on("Event_Setting", function () {
      _this6.Get_Settings();
    });
  }
});

/***/ }),

/***/ "./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4 ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
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
      page: _vm.$t("SystemSettings"),
      folder: _vm.$t("Settings")
    }
  }), _vm._v(" "), _vm.isLoading ? _c("div", {
    staticClass: "loading_page spinner spinner-primary mr-3"
  }) : _vm._e(), _vm._v(" "), !_vm.isLoading ? _c("validation-observer", {
    ref: "form_setting"
  }, [_c("b-form", {
    on: {
      submit: function submit($event) {
        $event.preventDefault();
        return _vm.Submit_Setting.apply(null, arguments);
      }
    }
  }, [_c("b-row", [_c("b-col", {
    attrs: {
      lg: "12",
      md: "12",
      sm: "12"
    }
  }, [_c("b-card", {
    attrs: {
      "no-body": "",
      header: _vm.$t("SystemSettings")
    }
  }, [_c("b-card-body", [_c("b-row", [_c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("b-form-group", {
    attrs: {
      label: _vm.$t("DefaultCurrency")
    }
  }, [_c("v-select", {
    attrs: {
      reduce: function reduce(label) {
        return label.value;
      },
      placeholder: _vm.$t("Choose_Currency"),
      options: _vm.currencies.map(function (currencies) {
        return {
          label: currencies.name,
          value: currencies.id
        };
      })
    },
    model: {
      value: _vm.setting.currency_id,
      callback: function callback($$v) {
        _vm.$set(_vm.setting, "currency_id", $$v);
      },
      expression: "setting.currency_id"
    }
  })], 1)], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "Email",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("DefaultEmail") + " " + "*"
          }
        }, [_c("b-form-input", {
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "Email-feedback",
            label: "Email",
            placeholder: _vm.$t("DefaultEmail")
          },
          model: {
            value: _vm.setting.email,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "email", $$v);
            },
            expression: "setting.email"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "Email-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 4279491894)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    ref: "Logo",
    attrs: {
      name: "Logo",
      rules: "mimes:image/*|size:200"
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(_ref) {
        var validate = _ref.validate,
          valid = _ref.valid,
          errors = _ref.errors;
        return _c("b-form-group", {
          attrs: {
            label: _vm.$t("ChangeLogo")
          }
        }, [_c("input", {
          "class": {
            "is-invalid": !!errors.length
          },
          attrs: {
            state: errors[0] ? false : valid ? true : null,
            label: "Choose Logo",
            type: "file"
          },
          on: {
            change: _vm.onFileSelected
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "Logo-feedback"
          }
        }, [_vm._v(_vm._s(errors[0]))])], 1);
      }
    }], null, false, 3189141354)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "Company Name",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("CompanyName") + " " + "*"
          }
        }, [_c("b-form-input", {
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "Company-feedback",
            label: "Company Name",
            placeholder: _vm.$t("CompanyName")
          },
          model: {
            value: _vm.setting.CompanyName,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "CompanyName", $$v);
            },
            expression: "setting.CompanyName"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "Company-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 1317089302)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "Company Phone",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("CompanyPhone") + " " + "*"
          }
        }, [_c("b-form-input", {
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "Phone-feedback",
            label: "Company Phone",
            placeholder: _vm.$t("CompanyPhone")
          },
          model: {
            value: _vm.setting.CompanyPhone,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "CompanyPhone", $$v);
            },
            expression: "setting.CompanyPhone"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "Phone-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 1395357846)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "developed by",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("developed_by") + " " + "*"
          }
        }, [_c("b-form-input", {
          staticClass: "form-control",
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "developed_by-feedback"
          },
          model: {
            value: _vm.setting.developed_by,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "developed_by", $$v);
            },
            expression: "setting.developed_by"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "developed_by-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 4204870398)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "footer",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("footer") + " " + "*"
          }
        }, [_c("b-form-input", {
          staticClass: "form-control",
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "footer-feedback"
          },
          model: {
            value: _vm.setting.footer,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "footer", $$v);
            },
            expression: "setting.footer"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "footer-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 3264557662)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "DefaultLanguage",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(_ref2) {
        var valid = _ref2.valid,
          errors = _ref2.errors;
        return _c("b-form-group", {
          attrs: {
            label: _vm.$t("DefaultLanguage") + " " + "*"
          }
        }, [_c("v-select", {
          "class": {
            "is-invalid": !!errors.length
          },
          attrs: {
            state: errors[0] ? false : valid ? true : null,
            reduce: function reduce(label) {
              return label.value;
            },
            placeholder: _vm.$t("DefaultLanguage"),
            options: [{
              label: "English",
              value: "en"
            }, {
              label: "French",
              value: "fr"
            }, {
              label: "Arabic",
              value: "ar"
            }, {
              label: "Turkish",
              value: "tur"
            }, {
              label: "Simplified Chinese",
              value: "sm_ch"
            }, {
              label: "Thaï",
              value: "thai"
            }, {
              label: "Hindi",
              value: "hn"
            }, {
              label: "German",
              value: "de"
            }, {
              label: "Spanish",
              value: "es"
            }, {
              label: "Italien",
              value: "it"
            }, {
              label: "Indonesian",
              value: "Ind"
            }, {
              label: "Traditional Chinese",
              value: "tr_ch"
            }, {
              label: "Russian",
              value: "ru"
            }, {
              label: "Vietnamese",
              value: "vn"
            }, {
              label: "Korean",
              value: "kr"
            }, {
              label: "Bangla",
              value: "ba"
            }, {
              label: "Portuguese",
              value: "br"
            }, {
              label: "Danish",
              value: "da"
            }]
          },
          model: {
            value: _vm.setting.default_language,
            callback: function callback($$v) {
              _vm.$set(_vm.setting, "default_language", $$v);
            },
            expression: "setting.default_language"
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", [_vm._v(_vm._s(errors[0]))])], 1);
      }
    }], null, false, 3878027193)
  })], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("b-form-group", {
    attrs: {
      label: _vm.$t("DefaultCustomer")
    }
  }, [_c("v-select", {
    attrs: {
      reduce: function reduce(label) {
        return label.value;
      },
      placeholder: _vm.$t("Choose_Customer"),
      options: _vm.clients.map(function (clients) {
        return {
          label: clients.name,
          value: clients.id
        };
      })
    },
    model: {
      value: _vm.setting.client_id,
      callback: function callback($$v) {
        _vm.$set(_vm.setting, "client_id", $$v);
      },
      expression: "setting.client_id"
    }
  })], 1)], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("b-form-group", {
    attrs: {
      label: _vm.$t("DefaultWarehouse")
    }
  }, [_c("v-select", {
    attrs: {
      reduce: function reduce(label) {
        return label.value;
      },
      placeholder: _vm.$t("Choose_Warehouse"),
      options: _vm.warehouses.map(function (warehouses) {
        return {
          label: warehouses.name,
          value: warehouses.id
        };
      })
    },
    model: {
      value: _vm.setting.warehouse_id,
      callback: function callback($$v) {
        _vm.$set(_vm.setting, "warehouse_id", $$v);
      },
      expression: "setting.warehouse_id"
    }
  })], 1)], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("b-form-group", {
    attrs: {
      label: _vm.$t("Default_SMS_Gateway")
    }
  }, [_c("v-select", {
    attrs: {
      reduce: function reduce(label) {
        return label.value;
      },
      placeholder: _vm.$t("Choose_SMS_Gateway"),
      options: _vm.sms_gateway.map(function (sms_gateway) {
        return {
          label: sms_gateway.title,
          value: sms_gateway.id
        };
      })
    },
    model: {
      value: _vm.setting.sms_gateway,
      callback: function callback($$v) {
        _vm.$set(_vm.setting, "sms_gateway", $$v);
      },
      expression: "setting.sms_gateway"
    }
  })], 1)], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "4",
      md: "4",
      sm: "12"
    }
  }, [_c("b-form-group", {
    attrs: {
      label: _vm.$t("Time_Zone")
    }
  }, [_c("v-select", {
    attrs: {
      placeholder: _vm.$t("Time_Zone"),
      reduce: function reduce(label) {
        return label.value;
      },
      options: _vm.zones_array.map(function (zones_array) {
        return {
          label: zones_array.label,
          value: zones_array.zone
        };
      })
    },
    on: {
      input: _vm.Selected_Time_Zone
    },
    model: {
      value: _vm.setting.timezone,
      callback: function callback($$v) {
        _vm.$set(_vm.setting, "timezone", $$v);
      },
      expression: "setting.timezone"
    }
  })], 1)], 1), _vm._v(" "), _c("b-col", {
    attrs: {
      lg: "12",
      md: "12",
      sm: "12"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "Address",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("Address") + " " + "*"
          }
        }, [_c("textarea", {
          directives: [{
            name: "model",
            rawName: "v-model",
            value: _vm.setting.CompanyAddress,
            expression: "setting.CompanyAddress"
          }],
          staticClass: "form-control",
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "Address-feedback",
            placeholder: _vm.$t("Afewwords")
          },
          domProps: {
            value: _vm.setting.CompanyAddress
          },
          on: {
            input: function input($event) {
              if ($event.target.composing) return;
              _vm.$set(_vm.setting, "CompanyAddress", $event.target.value);
            }
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "Address-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 2400471581)
  })], 1), _vm._v(" "), _c("b-col", {
    staticClass: "mt-4 mb-4",
    attrs: {
      md: "2",
      sm: "2"
    }
  }, [_c("label", {
    staticClass: "checkbox checkbox-primary mb-3"
  }, [_c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.setting.is_invoice_footer,
      expression: "setting.is_invoice_footer"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.setting.is_invoice_footer) ? _vm._i(_vm.setting.is_invoice_footer, null) > -1 : _vm.setting.is_invoice_footer
    },
    on: {
      change: function change($event) {
        var $$a = _vm.setting.is_invoice_footer,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.setting, "is_invoice_footer", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.setting, "is_invoice_footer", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.setting, "is_invoice_footer", $$c);
        }
      }
    }
  }), _c("h5", [_vm._v(_vm._s(_vm.$t("invoice_footer")) + " ")]), _c("span", {
    staticClass: "checkmark"
  })])]), _vm._v(" "), _vm.setting.is_invoice_footer ? _c("b-col", {
    staticClass: "mt-4 mb-4",
    attrs: {
      md: "6",
      sm: "6"
    }
  }, [_c("validation-provider", {
    attrs: {
      name: "invoice_footer",
      rules: {
        required: true
      }
    },
    scopedSlots: _vm._u([{
      key: "default",
      fn: function fn(validationContext) {
        return [_c("b-form-group", {
          attrs: {
            label: _vm.$t("invoice_footer") + " " + "*"
          }
        }, [_c("textarea", {
          directives: [{
            name: "model",
            rawName: "v-model",
            value: _vm.setting.invoice_footer,
            expression: "setting.invoice_footer"
          }],
          staticClass: "form-control",
          attrs: {
            state: _vm.getValidationState(validationContext),
            "aria-describedby": "invoice_footer-feedback",
            placeholder: _vm.$t("invoice_footer")
          },
          domProps: {
            value: _vm.setting.invoice_footer
          },
          on: {
            input: function input($event) {
              if ($event.target.composing) return;
              _vm.$set(_vm.setting, "invoice_footer", $event.target.value);
            }
          }
        }), _vm._v(" "), _c("b-form-invalid-feedback", {
          attrs: {
            id: "invoice_footer-feedback"
          }
        }, [_vm._v(_vm._s(validationContext.errors[0]))])], 1)];
      }
    }], null, false, 4052989155)
  })], 1) : _vm._e(), _vm._v(" "), _c("b-col", {
    staticClass: "mt-4 mb-4",
    attrs: {
      md: "4"
    }
  }, [_c("label", {
    staticClass: "checkbox checkbox-primary mb-3"
  }, [_c("input", {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: _vm.setting.quotation_with_stock,
      expression: "setting.quotation_with_stock"
    }],
    attrs: {
      type: "checkbox"
    },
    domProps: {
      checked: Array.isArray(_vm.setting.quotation_with_stock) ? _vm._i(_vm.setting.quotation_with_stock, null) > -1 : _vm.setting.quotation_with_stock
    },
    on: {
      change: function change($event) {
        var $$a = _vm.setting.quotation_with_stock,
          $$el = $event.target,
          $$c = $$el.checked ? true : false;
        if (Array.isArray($$a)) {
          var $$v = null,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && _vm.$set(_vm.setting, "quotation_with_stock", $$a.concat([$$v]));
          } else {
            $$i > -1 && _vm.$set(_vm.setting, "quotation_with_stock", $$a.slice(0, $$i).concat($$a.slice($$i + 1)));
          }
        } else {
          _vm.$set(_vm.setting, "quotation_with_stock", $$c);
        }
      }
    }
  }), _c("h5", [_vm._v(_vm._s(_vm.$t("Create_Quotation_with_Stock")) + " ")]), _c("span", {
    staticClass: "checkmark"
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
  }), _vm._v(" " + _vm._s(_vm.$t("submit")))])], 1)], 1)], 1)], 1)], 1)], 1)], 1)], 1)], 1) : _vm._e(), _vm._v(" "), !_vm.isLoading ? _c("b-form", {
    on: {
      submit: function submit($event) {
        $event.preventDefault();
        return _vm.Clear_Cache.apply(null, arguments);
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
      header: _vm.$t("Clear_Cache")
    }
  }, [_c("b-card-body", [_c("b-row", [_c("b-col", {
    attrs: {
      md: "12"
    }
  }, [_c("b-form-group", [_c("b-button", {
    attrs: {
      variant: "primary"
    },
    on: {
      click: function click($event) {
        return _vm.Clear_Cache();
      }
    }
  }, [_vm._v(_vm._s(_vm.$t("Clear_Cache")))])], 1)], 1)], 1)], 1)], 1)], 1)], 1)], 1) : _vm._e()], 1);
};
var staticRenderFns = [];
render._withStripped = true;


/***/ }),

/***/ "./resources/src/views/app/pages/settings/system_settings.vue":
/*!********************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/system_settings.vue ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./system_settings.vue?vue&type=template&id=7bd1aea4 */ "./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4");
/* harmony import */ var _system_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./system_settings.vue?vue&type=script&lang=js */ "./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _system_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  _system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__.render,
  _system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/src/views/app/pages/settings/system_settings.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js":
/*!********************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_pnpm_babel_loader_8_3_0_babel_core_7_25_2_webpack_5_94_0_webpack_cli_4_10_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_index_js_vue_loader_options_system_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./system_settings.vue?vue&type=script&lang=js */ "./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=script&lang=js");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_pnpm_babel_loader_8_3_0_babel_core_7_25_2_webpack_5_94_0_webpack_cli_4_10_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_index_js_vue_loader_options_system_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4":
/*!**************************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4 ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_pnpm_babel_loader_8_3_0_babel_core_7_25_2_webpack_5_94_0_webpack_cli_4_10_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_index_js_vue_loader_options_system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_pnpm_babel_loader_8_3_0_babel_core_7_25_2_webpack_5_94_0_webpack_cli_4_10_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_index_js_vue_loader_options_system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_pnpm_babel_loader_8_3_0_babel_core_7_25_2_webpack_5_94_0_webpack_cli_4_10_0_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_pnpm_vue_loader_15_11_1_css_loader_5_2_7_webpack_5_94_0_webpack_cli_4_10_0_lodash_4_17_21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe_node_modules_vue_loader_lib_index_js_vue_loader_options_system_settings_vue_vue_type_template_id_7bd1aea4__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./system_settings.vue?vue&type=template&id=7bd1aea4 */ "./node_modules/.pnpm/babel-loader@8.3.0_@babel+core@7.25.2_webpack@5.94.0_webpack-cli@4.10.0_/node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/.pnpm/vue-loader@15.11.1_css-loader@5.2.7_webpack@5.94.0_webpack-cli@4.10.0___lodash@4.17.21_pretti_sctu5ef6vxjbclrgqhn5cvjvhe/node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/system_settings.vue?vue&type=template&id=7bd1aea4");


/***/ })

}]);