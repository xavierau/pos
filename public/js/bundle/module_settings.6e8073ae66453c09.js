/*! For license information please see module_settings.6e8073ae66453c09.js.LICENSE.txt */
"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[3193],{7835:(t,e,r)=>{r.r(e),r.d(e,{default:()=>f});var n=r(95353);r(5947);function o(t){return o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},o(t)}function i(){i=function(){return e};var t,e={},r=Object.prototype,n=r.hasOwnProperty,a=Object.defineProperty||function(t,e,r){t[e]=r.value},s="function"==typeof Symbol?Symbol:{},u=s.iterator||"@@iterator",c=s.asyncIterator||"@@asyncIterator",l=s.toStringTag||"@@toStringTag";function f(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{f({},"")}catch(t){f=function(t,e,r){return t[e]=r}}function d(t,e,r,n){var o=e&&e.prototype instanceof _?e:_,i=Object.create(o.prototype),s=new M(n||[]);return a(i,"_invoke",{value:j(t,r,s)}),i}function h(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}e.wrap=d;var p="suspendedStart",v="suspendedYield",m="executing",y="completed",g={};function _(){}function b(){}function w(){}var x={};f(x,u,(function(){return this}));var L=Object.getPrototypeOf,O=L&&L(L(T([])));O&&O!==r&&n.call(O,u)&&(x=O);var S=w.prototype=_.prototype=Object.create(x);function k(t){["next","throw","return"].forEach((function(e){f(t,e,(function(t){return this._invoke(e,t)}))}))}function P(t,e){function r(i,a,s,u){var c=h(t[i],t,a);if("throw"!==c.type){var l=c.arg,f=l.value;return f&&"object"==o(f)&&n.call(f,"__await")?e.resolve(f.__await).then((function(t){r("next",t,s,u)}),(function(t){r("throw",t,s,u)})):e.resolve(f).then((function(t){l.value=t,s(l)}),(function(t){return r("throw",t,s,u)}))}u(c.arg)}var i;a(this,"_invoke",{value:function(t,n){function o(){return new e((function(e,o){r(t,n,e,o)}))}return i=i?i.then(o,o):o()}})}function j(e,r,n){var o=p;return function(i,a){if(o===m)throw Error("Generator is already running");if(o===y){if("throw"===i)throw a;return{value:t,done:!0}}for(n.method=i,n.arg=a;;){var s=n.delegate;if(s){var u=E(s,n);if(u){if(u===g)continue;return u}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(o===p)throw o=y,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);o=m;var c=h(e,r,n);if("normal"===c.type){if(o=n.done?y:v,c.arg===g)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(o=y,n.method="throw",n.arg=c.arg)}}}function E(e,r){var n=r.method,o=e.iterator[n];if(o===t)return r.delegate=null,"throw"===n&&e.iterator.return&&(r.method="return",r.arg=t,E(e,r),"throw"===r.method)||"return"!==n&&(r.method="throw",r.arg=new TypeError("The iterator does not provide a '"+n+"' method")),g;var i=h(o,e.iterator,r.arg);if("throw"===i.type)return r.method="throw",r.arg=i.arg,r.delegate=null,g;var a=i.arg;return a?a.done?(r[e.resultName]=a.value,r.next=e.nextLoc,"return"!==r.method&&(r.method="next",r.arg=t),r.delegate=null,g):a:(r.method="throw",r.arg=new TypeError("iterator result is not an object"),r.delegate=null,g)}function $(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function C(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function M(t){this.tryEntries=[{tryLoc:"root"}],t.forEach($,this),this.reset(!0)}function T(e){if(e||""===e){var r=e[u];if(r)return r.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var i=-1,a=function r(){for(;++i<e.length;)if(n.call(e,i))return r.value=e[i],r.done=!1,r;return r.value=t,r.done=!0,r};return a.next=a}}throw new TypeError(o(e)+" is not iterable")}return b.prototype=w,a(S,"constructor",{value:w,configurable:!0}),a(w,"constructor",{value:b,configurable:!0}),b.displayName=f(w,l,"GeneratorFunction"),e.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===b||"GeneratorFunction"===(e.displayName||e.name))},e.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,w):(t.__proto__=w,f(t,l,"GeneratorFunction")),t.prototype=Object.create(S),t},e.awrap=function(t){return{__await:t}},k(P.prototype),f(P.prototype,c,(function(){return this})),e.AsyncIterator=P,e.async=function(t,r,n,o,i){void 0===i&&(i=Promise);var a=new P(d(t,r,n,o),i);return e.isGeneratorFunction(r)?a:a.next().then((function(t){return t.done?t.value:a.next()}))},k(S),f(S,l,"Generator"),f(S,u,(function(){return this})),f(S,"toString",(function(){return"[object Generator]"})),e.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},e.values=T,M.prototype={constructor:M,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(C),!e)for(var r in this)"t"===r.charAt(0)&&n.call(this,r)&&!isNaN(+r.slice(1))&&(this[r]=t)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var r=this;function o(n,o){return s.type="throw",s.arg=e,r.next=n,o&&(r.method="next",r.arg=t),!!o}for(var i=this.tryEntries.length-1;i>=0;--i){var a=this.tryEntries[i],s=a.completion;if("root"===a.tryLoc)return o("end");if(a.tryLoc<=this.prev){var u=n.call(a,"catchLoc"),c=n.call(a,"finallyLoc");if(u&&c){if(this.prev<a.catchLoc)return o(a.catchLoc,!0);if(this.prev<a.finallyLoc)return o(a.finallyLoc)}else if(u){if(this.prev<a.catchLoc)return o(a.catchLoc,!0)}else{if(!c)throw Error("try statement without catch or finally");if(this.prev<a.finallyLoc)return o(a.finallyLoc)}}}},abrupt:function(t,e){for(var r=this.tryEntries.length-1;r>=0;--r){var o=this.tryEntries[r];if(o.tryLoc<=this.prev&&n.call(o,"finallyLoc")&&this.prev<o.finallyLoc){var i=o;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var a=i?i.completion:{};return a.type=t,a.arg=e,i?(this.method="next",this.next=i.finallyLoc,g):this.complete(a)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),g},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),C(r),g}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var o=n.arg;C(r)}return o}}throw Error("illegal catch attempt")},delegateYield:function(e,r,n){return this.delegate={iterator:T(e),resultName:r,nextLoc:n},"next"===this.method&&(this.arg=t),g}},e}function a(t,e,r,n,o,i,a){try{var s=t[i](a),u=s.value}catch(t){return void r(t)}s.done?e(u):Promise.resolve(u).then(n,o)}function s(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function u(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?s(Object(r),!0).forEach((function(e){c(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):s(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}function c(t,e,r){return(e=function(t){var e=function(t,e){if("object"!=o(t)||!t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!=o(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"==o(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}const l={metaInfo:{title:"Module Settings"},data:function(){return{isLoading:!0,SubmitProcessing:!1,modules_info:[],module_zip:"",data:new FormData}},methods:u(u({},(0,n.i0)(["refreshUserPermissions"])),{},{makeToast:function(t,e,r){this.$root.$bvToast.toast(e,{title:r,variant:t,solid:!0})},onFileSelected:function(t){var e,r=this;return(e=i().mark((function e(){var n,o;return i().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,r.$refs.Upload_Module.validate(t);case 2:n=e.sent,o=n.valid,r.module_zip=o?t.target.files[0]:"";case 5:case"end":return e.stop()}}),e)})),function(){var t=this,r=arguments;return new Promise((function(n,o){var i=e.apply(t,r);function s(t){a(i,n,o,s,u,"next",t)}function u(t){a(i,n,o,s,u,"throw",t)}s(void 0)}))})()},update_status_module:function(t){var e=this;axios.post("update_status_module/",{status:t.status,name:t.module_name}).then((function(r){e.isLoading=!0,t.status?e.makeToast("success",e.$t("Module_enabled_success"),e.$t("Success")):e.makeToast("danger",e.$t("Module_Disabled_success"),e.$t("Warning")),window.location.href="/app/settings/module_settings"})).catch((function(t){e.makeToast("warning",e.$t("Delete.Therewassomethingwronge"),e.$t("Warning"))}))},get_modules_info:function(){var t=this;axios.get("get_modules_info").then((function(e){t.modules_info=e.data,t.isLoading=!1})).catch((function(e){setTimeout((function(){t.isLoading=!1}),500)}))},Submit_Upload_Module:function(){var t=this;this.$refs.ref_Upload_Module.validate().then((function(e){e?t.Upload_Module():t.makeToast("danger",t.$t("Please_Upload_the_Correct_Module"),t.$t("Failed"))}))},Upload_Module:function(){var t=this,e=this;e.SubmitProcessing=!0,e.data.append("module_zip",e.module_zip),axios.post("upload_module",e.data).then((function(r){e.SubmitProcessing=!1,window.location.reload(),t.makeToast("success",t.$t("Uploaded_Success"),t.$t("Success"))})).catch((function(r){e.SubmitProcessing=!1,t.makeToast("danger",t.$t("InvalidData"),t.$t("Failed"))}))}}),created:function(){this.get_modules_info()}};const f=(0,r(14486).A)(l,(function(){var t=this,e=t._self._c;return e("div",{staticClass:"main-content"},[e("breadcumb",{attrs:{page:t.$t("module_settings"),folder:t.$t("Settings")}}),t._v(" "),t.isLoading?e("div",{staticClass:"loading_page spinner spinner-primary mr-3"}):t._e(),t._v(" "),t.isLoading?t._e():e("b-col",{attrs:{md:"12"}},[e("validation-observer",{ref:"ref_Upload_Module"},[e("b-form",{attrs:{enctype:"multipart/form-data"},on:{submit:function(e){return e.preventDefault(),t.Submit_Upload_Module.apply(null,arguments)}}},[e("div",{staticClass:"row border rounded p-3 mt-3"},[e("b-col",{attrs:{md:"12"}},[e("validation-provider",{ref:"Upload_Module",attrs:{name:"Upload Module"},scopedSlots:t._u([{key:"default",fn:function(r){r.validate;var n=r.valid,o=r.errors;return e("b-form-group",{attrs:{label:"Install/Upload Module"}},[e("input",{class:{"is-invalid":!!o.length},attrs:{state:!o[0]&&(!!n||null),label:"Upload_Module",type:"file"},on:{change:t.onFileSelected}}),t._v(" "),e("b-form-invalid-feedback",{attrs:{id:"Upload_Module-feedback"}},[t._v(t._s(o[0]))])],1)}}],null,!1,833920476)}),t._v(" "),e("span",[t._v("The module must be uploaded as zip file")])],1),t._v(" "),e("b-col",{staticClass:"text-center mt-3",attrs:{md:"12"}},[e("b-button",{attrs:{variant:"primary",type:"submit",disabled:t.SubmitProcessing}},[t._v("Upload Module")]),t._v(" "),t.SubmitProcessing?t._m(0):t._e()],1)],1)])],1)],1),t._v(" "),t.isLoading?t._e():e("div",{staticClass:"mt-5"},[e("b-col",{attrs:{md:"12"}},[e("h4",{directives:[{name:"show",rawName:"v-show",value:t.modules_info.length>0,expression:"modules_info.length >0"}]},[t._v("All Modules Installed")])]),t._v(" "),t._l(t.modules_info,(function(r){return e("div",{key:r,staticClass:"col-md-6"},[e("div",{staticClass:"row border rounded p-3 mt-3"},[e("div",{staticClass:"col-md-12"},[e("h3",[t._v(t._s(r.module_name))])]),t._v(" "),e("div",{staticClass:"col-md-12"},[e("span",[e("strong",[t._v("Current Version")]),t._v(" : "+t._s(r.current_version))])]),t._v(" "),e("div",{staticClass:"col-md-12 mt-3"},[e("label",{staticClass:"switch switch-primary mr-3"},[e("input",{directives:[{name:"model",rawName:"v-model",value:r.status,expression:"module_item.status"}],attrs:{type:"checkbox"},domProps:{checked:Array.isArray(r.status)?t._i(r.status,null)>-1:r.status},on:{change:[function(e){var n=r.status,o=e.target,i=!!o.checked;if(Array.isArray(n)){var a=t._i(n,null);o.checked?a<0&&t.$set(r,"status",n.concat([null])):a>-1&&t.$set(r,"status",n.slice(0,a).concat(n.slice(a+1)))}else t.$set(r,"status",i)},function(e){return t.update_status_module(r)}]}}),t._v(" "),e("span",{staticClass:"slider"})])])])])}))],2)],1)}),[function(){var t=this._self._c;return t("div",{staticClass:"typo__p"},[t("div",{staticClass:"spinner sm spinner-primary mt-3"})])}],!1,null,null,null).exports}}]);