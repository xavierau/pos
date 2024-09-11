"use strict";
(self["webpackChunk"] = self["webpackChunk"] || []).push([["update_settings"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js":
/*!************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js ***!
  \************************************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! nprogress */ "./node_modules/nprogress/nprogress.js");
/* harmony import */ var nprogress__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(nprogress__WEBPACK_IMPORTED_MODULE_0__);

/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  metaInfo: {
    title: "Update Settings"
  },
  data: function data() {
    return {
      isLoading: true,
      SubmitProcessing: false,
      version: ""
    };
  },
  methods: {
    //------------------------ Update ---------------------------\\
    Update_system: function Update_system() {
      var _this = this;
      var self = this;
      self.SubmitProcessing = true;
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().start();
      nprogress__WEBPACK_IMPORTED_MODULE_0___default().set(0.1);
      axios.get("/").then(function (response) {
        self.SubmitProcessing = false;
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
        _this.makeToast("success", _this.$t("Successfully_Updated"), _this.$t("Success"));
        Fire.$emit("Event_update");
      })["catch"](function (error) {
        self.SubmitProcessing = false;
        nprogress__WEBPACK_IMPORTED_MODULE_0___default().done();
        if (error.response.status == 400) {
          _this.makeToast("danger", error.response.data.msg, _this.$t("Failed"));
        } else {
          _this.makeToast("danger", _this.$t("InvalidData"), _this.$t("Failed"));
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
    //---------------------------------- get_version_info ----------------\\
    get_version_info: function get_version_info() {
      var self = this;
      axios.get("get_version_info").then(function (response) {
        self.version = response.data;
        self.isLoading = false;
      })["catch"](function (error) {
        setTimeout(function () {
          self.isLoading = false;
        }, 500);
      });
    }
  },
  //end Methods

  //----------------------------- Created function-------------------

  created: function created() {
    var _this2 = this;
    this.get_version_info();
    Fire.$on("Event_update", function () {
      _this2.get_version_info();
    });
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c":
/*!***********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c ***!
  \***********************************************************************************************************************************************************************************************************************************************************************************************************/
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
      page: _vm.$t("update_settings"),
      folder: _vm.$t("Settings")
    }
  }), _vm._v(" "), _vm.isLoading ? _c("div", {
    staticClass: "loading_page spinner spinner-primary mr-3"
  }) : _vm._e(), _vm._v(" "), !_vm.isLoading ? _c("div", {
    staticClass: "col-md-12"
  }, [_c("div", {
    staticClass: "card"
  }, [_c("div", {
    staticClass: "card-header"
  }, [_c("span", [_vm._v(_vm._s(_vm.$t("Update_Log")))])]), _vm._v(" "), _c("div", {
    staticClass: "card-body"
  }, [_c("div", {
    staticClass: "alert alert-danger"
  }, [_vm._v(_vm._s(_vm.$t("Note_update")))]), _vm._v(" "), _vm.version != "" ? _c("div", {
    staticClass: "alert alert-info"
  }, [_c("strong", [_vm._v(_vm._s(_vm.$t("Update_Available")) + "\n                          "), _c("span", {
    staticClass: "badge badge-pill badge-info"
  }, [_vm._v("\n                              " + _vm._s(_vm.version) + "\n                          ")])])]) : _c("div", {
    staticClass: "alert alert-info"
  }, [_c("strong", [_vm._v(_vm._s(_vm.$t("You_already_have_the_latest_version")) + " "), _c("span", {
    staticClass: "badge badge-pill badge-info"
  })])]), _vm._v(" "), _vm._m(0), _vm._v(" "), _vm._m(1)])])]) : _vm._e()], 1);
};
var staticRenderFns = [function () {
  var _vm = this,
    _c = _vm._self._c;
  return _c("div", {
    staticClass: "col-md-12 mt-3"
  }, [_c("h5", [_vm._v("Please follow these steps, To Update your application")]), _vm._v(" "), _c("div", {
    staticClass: "allert alert-danger"
  }, [_vm._v("Note 1: If you have made any changes in the code manually then your changes will be lost.")]), _vm._v(" "), _c("div", {
    staticClass: "allert alert-danger"
  }, [_vm._v('Note 2: only admin or user who has permission "system_setting" he can upgrade the system')]), _vm._v(" "), _c("ul", [_c("li", [_c("strong", [_vm._v("Step 1 : ")]), _vm._v("Take back up of your database,  Go to "), _c("a", {
    attrs: {
      href: "/app/settings/Backup"
    }
  }, [_vm._v("Backup")]), _vm._v(" Click on Generate Backup ,\n                         You will find it in "), _c("strong", [_vm._v("/storage/app/public/backup")]), _vm._v("  and save it to your pc To restore it if there is an error , \n                         or Go to your PhpMyAdmin and export your database then and save it to your pc To restore it if there is an error\n                      ")]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 2 : ")]), _vm._v(" Take back up of your files before updating.\n                       ")]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 3 : ")]), _vm._v("  Download the latest version from your codecanyon and Extract it .\n                       ")]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 4 : ")]), _vm._v(" Replace all the files and folders "), _c("strong", [_vm._v("except")]), _vm._v(" the following :\n                          "), _c("ul", [_c("li", [_vm._v("file   : "), _c("strong", [_vm._v(".env")])]), _vm._v(" "), _c("li", [_vm._v("Folder : "), _c("strong", [_vm._v("storage")])]), _vm._v(" "), _c("li", [_vm._v("Folder : "), _c("strong", [_vm._v("Modules")]), _vm._v(" (If you have any Module installed)")]), _vm._v(" "), _c("li", [_vm._v("file   : "), _c("strong", [_vm._v("modules_statuses.json")]), _vm._v(" (If you have any Module installed)")]), _vm._v(" "), _c("li", [_vm._v("Folder : "), _c("strong", [_vm._v("images folder in public : /public/images")])])])]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 5 : ")]), _vm._v("Visit  http://your_app/update to update your database\n                       ")]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 6 : ")]), _vm._v(" Hard Clear your cache browser\n                       ")]), _vm._v(" "), _c("li", [_c("strong", [_vm._v("Step 7 : ")]), _vm._v(" You are done! Enjoy the updated application\n                       ")])]), _vm._v(" "), _c("div", {
    staticClass: "allert alert-danger"
  }, [_vm._v("Note: If any pages are not loading or blank, make sure you cleared your browser cache.")])]);
}, function () {
  var _vm = this,
    _c = _vm._self._c;
  return _c("div", {
    staticClass: "col-md-12 mt-3"
  }, [_c("h4", {
    staticClass: "mb-3"
  }, [_vm._v("This video showing you the steps on how to upgrade stocky")]), _vm._v(" "), _c("iframe", {
    attrs: {
      width: "853",
      height: "480",
      src: "https://www.youtube.com/embed/VwfRtMkxS9U",
      title: "how to update Stocky step by step",
      frameborder: "0",
      allow: "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture",
      allowfullscreen: ""
    }
  })]);
}];
render._withStripped = true;


/***/ }),

/***/ "./resources/src/views/app/pages/settings/update_settings.vue":
/*!********************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/update_settings.vue ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./update_settings.vue?vue&type=template&id=29aa1b6c */ "./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c");
/* harmony import */ var _update_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./update_settings.vue?vue&type=script&lang=js */ "./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! !../../../../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */
;
var component = (0,_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _update_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_1__["default"],
  _update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__.render,
  _update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns,
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/src/views/app/pages/settings/update_settings.vue"
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (component.exports);

/***/ }),

/***/ "./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js":
/*!********************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js ***!
  \********************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_update_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./update_settings.vue?vue&type=script&lang=js */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=script&lang=js");
 /* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_index_js_vue_loader_options_update_settings_vue_vue_type_script_lang_js__WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c":
/*!**************************************************************************************************!*\
  !*** ./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c ***!
  \**************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   render: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__.render),
/* harmony export */   staticRenderFns: () => (/* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__.staticRenderFns)
/* harmony export */ });
/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_5_use_0_node_modules_vue_loader_lib_loaders_templateLoader_js_ruleSet_1_rules_2_node_modules_vue_loader_lib_index_js_vue_loader_options_update_settings_vue_vue_type_template_id_29aa1b6c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../../../../node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!../../../../../../node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!../../../../../../node_modules/vue-loader/lib/index.js??vue-loader-options!./update_settings.vue?vue&type=template&id=29aa1b6c */ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-5.use[0]!./node_modules/vue-loader/lib/loaders/templateLoader.js??ruleSet[1].rules[2]!./node_modules/vue-loader/lib/index.js??vue-loader-options!./resources/src/views/app/pages/settings/update_settings.vue?vue&type=template&id=29aa1b6c");


/***/ })

}]);