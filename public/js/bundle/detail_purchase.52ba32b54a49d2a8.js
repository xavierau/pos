/*! For license information please see detail_purchase.52ba32b54a49d2a8.js.LICENSE.txt */
"use strict";(self.webpackChunk=self.webpackChunk||[]).push([[4234],{69128:(t,e,r)=>{r.d(e,{A:()=>d});var n=r(78061),a=r(5947),i=r.n(a),s=r(95353);const o={SUCCESS:"success",ERROR:"error",WARNING:"warning",DANGER:"danger"};function c(t){return c="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},c(t)}function u(){u=function(){return e};var t,e={},r=Object.prototype,n=r.hasOwnProperty,a=Object.defineProperty||function(t,e,r){t[e]=r.value},i="function"==typeof Symbol?Symbol:{},s=i.iterator||"@@iterator",o=i.asyncIterator||"@@asyncIterator",l=i.toStringTag||"@@toStringTag";function h(t,e,r){return Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}),t[e]}try{h({},"")}catch(t){h=function(t,e,r){return t[e]=r}}function v(t,e,r,n){var i=e&&e.prototype instanceof y?e:y,s=Object.create(i.prototype),o=new j(n||[]);return a(s,"_invoke",{value:T(t,r,o)}),s}function d(t,e,r){try{return{type:"normal",arg:t.call(e,r)}}catch(t){return{type:"throw",arg:t}}}e.wrap=v;var f="suspendedStart",p="suspendedYield",_="executing",m="completed",b={};function y(){}function g(){}function w(){}var $={};h($,s,(function(){return this}));var C=Object.getPrototypeOf,x=C&&C(C(D([])));x&&x!==r&&n.call(x,s)&&($=x);var S=w.prototype=y.prototype=Object.create($);function E(t){["next","throw","return"].forEach((function(e){h(t,e,(function(t){return this._invoke(e,t)}))}))}function P(t,e){function r(a,i,s,o){var u=d(t[a],t,i);if("throw"!==u.type){var l=u.arg,h=l.value;return h&&"object"==c(h)&&n.call(h,"__await")?e.resolve(h.__await).then((function(t){r("next",t,s,o)}),(function(t){r("throw",t,s,o)})):e.resolve(h).then((function(t){l.value=t,s(l)}),(function(t){return r("throw",t,s,o)}))}o(u.arg)}var i;a(this,"_invoke",{value:function(t,n){function a(){return new e((function(e,a){r(t,n,e,a)}))}return i=i?i.then(a,a):a()}})}function T(e,r,n){var a=f;return function(i,s){if(a===_)throw Error("Generator is already running");if(a===m){if("throw"===i)throw s;return{value:t,done:!0}}for(n.method=i,n.arg=s;;){var o=n.delegate;if(o){var c=O(o,n);if(c){if(c===b)continue;return c}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(a===f)throw a=m,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);a=_;var u=d(e,r,n);if("normal"===u.type){if(a=n.done?m:p,u.arg===b)continue;return{value:u.arg,done:n.done}}"throw"===u.type&&(a=m,n.method="throw",n.arg=u.arg)}}}function O(e,r){var n=r.method,a=e.iterator[n];if(a===t)return r.delegate=null,"throw"===n&&e.iterator.return&&(r.method="return",r.arg=t,O(e,r),"throw"===r.method)||"return"!==n&&(r.method="throw",r.arg=new TypeError("The iterator does not provide a '"+n+"' method")),b;var i=d(a,e.iterator,r.arg);if("throw"===i.type)return r.method="throw",r.arg=i.arg,r.delegate=null,b;var s=i.arg;return s?s.done?(r[e.resultName]=s.value,r.next=e.nextLoc,"return"!==r.method&&(r.method="next",r.arg=t),r.delegate=null,b):s:(r.method="throw",r.arg=new TypeError("iterator result is not an object"),r.delegate=null,b)}function k(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function N(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function j(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(k,this),this.reset(!0)}function D(e){if(e||""===e){var r=e[s];if(r)return r.call(e);if("function"==typeof e.next)return e;if(!isNaN(e.length)){var a=-1,i=function r(){for(;++a<e.length;)if(n.call(e,a))return r.value=e[a],r.done=!1,r;return r.value=t,r.done=!0,r};return i.next=i}}throw new TypeError(c(e)+" is not iterable")}return g.prototype=w,a(S,"constructor",{value:w,configurable:!0}),a(w,"constructor",{value:g,configurable:!0}),g.displayName=h(w,l,"GeneratorFunction"),e.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===g||"GeneratorFunction"===(e.displayName||e.name))},e.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,w):(t.__proto__=w,h(t,l,"GeneratorFunction")),t.prototype=Object.create(S),t},e.awrap=function(t){return{__await:t}},E(P.prototype),h(P.prototype,o,(function(){return this})),e.AsyncIterator=P,e.async=function(t,r,n,a,i){void 0===i&&(i=Promise);var s=new P(v(t,r,n,a),i);return e.isGeneratorFunction(r)?s:s.next().then((function(t){return t.done?t.value:s.next()}))},E(S),h(S,l,"Generator"),h(S,s,(function(){return this})),h(S,"toString",(function(){return"[object Generator]"})),e.keys=function(t){var e=Object(t),r=[];for(var n in e)r.push(n);return r.reverse(),function t(){for(;r.length;){var n=r.pop();if(n in e)return t.value=n,t.done=!1,t}return t.done=!0,t}},e.values=D,j.prototype={constructor:j,reset:function(e){if(this.prev=0,this.next=0,this.sent=this._sent=t,this.done=!1,this.delegate=null,this.method="next",this.arg=t,this.tryEntries.forEach(N),!e)for(var r in this)"t"===r.charAt(0)&&n.call(this,r)&&!isNaN(+r.slice(1))&&(this[r]=t)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(e){if(this.done)throw e;var r=this;function a(n,a){return o.type="throw",o.arg=e,r.next=n,a&&(r.method="next",r.arg=t),!!a}for(var i=this.tryEntries.length-1;i>=0;--i){var s=this.tryEntries[i],o=s.completion;if("root"===s.tryLoc)return a("end");if(s.tryLoc<=this.prev){var c=n.call(s,"catchLoc"),u=n.call(s,"finallyLoc");if(c&&u){if(this.prev<s.catchLoc)return a(s.catchLoc,!0);if(this.prev<s.finallyLoc)return a(s.finallyLoc)}else if(c){if(this.prev<s.catchLoc)return a(s.catchLoc,!0)}else{if(!u)throw Error("try statement without catch or finally");if(this.prev<s.finallyLoc)return a(s.finallyLoc)}}}},abrupt:function(t,e){for(var r=this.tryEntries.length-1;r>=0;--r){var a=this.tryEntries[r];if(a.tryLoc<=this.prev&&n.call(a,"finallyLoc")&&this.prev<a.finallyLoc){var i=a;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var s=i?i.completion:{};return s.type=t,s.arg=e,i?(this.method="next",this.next=i.finallyLoc,b):this.complete(s)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),b},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.finallyLoc===t)return this.complete(r.completion,r.afterLoc),N(r),b}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var r=this.tryEntries[e];if(r.tryLoc===t){var n=r.completion;if("throw"===n.type){var a=n.arg;N(r)}return a}}throw Error("illegal catch attempt")},delegateYield:function(e,r,n){return this.delegate={iterator:D(e),resultName:r,nextLoc:n},"next"===this.method&&(this.arg=t),b}},e}function l(t,e,r,n,a,i,s){try{var o=t[i](s),c=o.value}catch(t){return void r(t)}o.done?e(c):Promise.resolve(c).then(n,a)}function h(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function v(t,e,r){return(e=function(t){var e=function(t,e){if("object"!=c(t)||!t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!=c(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"==c(e)?e:e+""}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}const d={created:function(){var t=this;Object.keys(this.registerEvents||{}).forEach((function(e){var r;return null===(r=Fire)||void 0===r?void 0:r.$on(e,t.registerEvents[e])}))},data:function(){return{registerEvents:{}}},computed:function(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?h(Object(r),!0).forEach((function(e){v(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):h(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}({},(0,s.L8)(["currentUserPermissions","currentUser"])),methods:{makeToast:function(t,e,r){this.$root.$bvToast.toast(this.$t(e),{title:this.$t(r),variant:t,solid:!0})},makeErrorToast:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"Error";this.makeToast(o.ERROR,t,e)},makeSuccessToast:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"Success";this.makeToast(o.SUCCESS,t,e)},makeWarningToast:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"Warning";this.makeToast(o.WARNING,t,e)},makeDangerToast:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"Danger";this.makeToast(o.DANGER,t,e)},getValidationState:function(t){var e=t.dirty,r=t.validated,n=t.valid;return e||r?void 0===n?null:n:null},formatNumber:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:2;return n.Ay.formatNumber(t,e)},displayCurrency:function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{decimal:1,symbol:null},r=this.formatNumber(null!=t?t:"",e.decimal||1),n=e.symbol||this.data.symbol;return"".concat(n," ").concat(r)},fire:function(t){var e,r=arguments.length>1&&void 0!==arguments[1]?arguments[1]:null;console.log("fire event",t,r),null===(e=Fire)||void 0===e||e.$emit(t,r)},execute:function(t){return(e=u().mark((function e(){return u().wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return i().start(),i().set(.1),e.prev=2,e.next=5,Promise.resolve(t());case 5:e.next=10;break;case 7:e.prev=7,e.t0=e.catch(2),console.error("mixin execute: ",e.t0);case 10:return e.prev=10,i().done(),e.finish(10);case 13:case"end":return e.stop()}}),e,null,[[2,7,10,13]])})),function(){var t=this,r=arguments;return new Promise((function(n,a){var i=e.apply(t,r);function s(t){l(i,n,a,s,o,"next",t)}function o(t){l(i,n,a,s,o,"throw",t)}s(void 0)}))})();var e}}}},78061:(t,e,r)=>{function n(t,e){return function(t){if(Array.isArray(t))return t}(t)||function(t,e){var r=null==t?null:"undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(null!=r){var n,a,i,s,o=[],c=!0,u=!1;try{if(i=(r=r.call(t)).next,0===e){if(Object(r)!==r)return;c=!1}else for(;!(c=(n=i.call(r)).done)&&(o.push(n.value),o.length!==e);c=!0);}catch(t){u=!0,a=t}finally{try{if(!c&&null!=r.return&&(s=r.return(),Object(s)!==s))return}finally{if(u)throw a}}return o}}(t,e)||function(t,e){if(t){if("string"==typeof t)return a(t,e);var r={}.toString.call(t).slice(8,-1);return"Object"===r&&t.constructor&&(r=t.constructor.name),"Map"===r||"Set"===r?Array.from(t):"Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r)?a(t,e):void 0}}(t,e)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function a(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=Array(e);r<e;r++)n[r]=t[r];return n}r.d(e,{Ay:()=>u,DU:()=>c,RP:()=>s,W2:()=>o,ZV:()=>i});var i=function(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:2,r=n(Number(t).toFixed(e).split("."),2),a=r[0],i=r[1];return e<=0?a:"".concat(a,".").concat(i)},s=function(t){return null!=t&&""!==t.trim()&&/\d/.test(t)&&0!==t},o=function(t){if(null==t)return!0;var e=Number(t);if(isNaN(e))return!0;if("string"==typeof t&&""===t.trim())return!0;var r=String(t);return!/^-?\d+(\.\d+)?$/.test(r)},c=function(t){for(var e="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",r="",n=0;n<t;n++)r+=e.charAt(Math.floor(62*Math.random()));return r};const u={toggleFullScreen:function(){var t=window.document,e=t.documentElement,r=e.requestFullscreen||e.mozRequestFullScreen||e.webkitRequestFullScreen||e.msRequestFullscreen,n=t.exitFullscreen||t.mozCancelFullScreen||t.webkitExitFullscreen||t.msExitFullscreen;t.fullscreenElement||t.mozFullScreenElement||t.webkitFullscreenElement||t.msFullscreenElement?n.call(t):r.call(e)},formatNumber:i,getValidationState:function(t){var e=t.dirty,r=t.validated,n=t.valid;return e||r?void 0===n?null:n:null},isNonZeroNumber:s,isNotANumber:o,randomString:c}},96470:(t,e,r)=>{r.r(e),r.d(e,{default:()=>s});var n=r(5947),a=r.n(n);const i={name:"DetailPurchase",mixins:[r(69128).A],metaInfo:{title:"Detail Purchase"},data:function(){return{is_loading:!0,purchase:{},details:[],variants:[],company:{},email:{to:"",subject:"",message:"",supplier_name:"",Purchase_Ref:""}}},methods:{Print_Purchase_PDF:function(){var t=this;a().start(),a().set(.1);var e=this.$route.params.id;axios.get("purchase_pdf/".concat(e),{responseType:"blob",headers:{"Content-Type":"application/json"}}).then((function(e){var r=window.URL.createObjectURL(new Blob([e.data])),n=document.createElement("a");n.href=r,n.setAttribute("download","purchase_"+t.purchase.Ref+".pdf"),document.body.appendChild(n),n.click(),setTimeout((function(){return a().done()}),500)})).catch((function(){setTimeout((function(){return a().done()}),500)}))},print:function(){this.$htmlToPaper("print_Invoice")},Get_Details:function(){var t=this,e=this.$route.params.id;axios.get("purchases/".concat(e)).then((function(e){t.purchase=e.data.purchase,t.details=e.data.details,t.company=e.data.company,t.is_loading=!1})).catch((function(e){setTimeout((function(){t.is_loading=!1}),500)}))},Send_Email:function(){var t=this;a().start(),a().set(.1);var e=this.$route.params.id;axios.post("purchase_send_email",{id:e}).then((function(e){setTimeout((function(){return a().done()}),500),t.makeToast("success",t.$t("Send.TitleEmail"),t.$t("Success"))})).catch((function(e){setTimeout((function(){return a().done()}),500),t.makeToast("danger",t.$t("SMTPIncorrect"),t.$t("Failed"))}))},Purchase_SMS:function(){var t=this;a().start(),a().set(.1);var e=this.$route.params.id;axios.post("purchase_send_sms",{id:e}).then((function(e){setTimeout((function(){return a().done()}),500),t.makeToast("success",t.$t("Send_SMS"),t.$t("Success"))})).catch((function(e){setTimeout((function(){return a().done()}),500),t.makeToast("danger",t.$t("sms_config_invalid"),t.$t("Failed"))}))},makeToast:function(t,e,r){this.$root.$bvToast.toast(e,{title:r,variant:t,solid:!0})},Delete_Purchase:function(){var t=this,e=this.$route.params.id;this.$swal({title:this.$t("Delete.Title"),text:this.$t("Delete.Text"),type:"warning",showCancelButton:!0,confirmButtonColor:"#3085d6",cancelButtonColor:"#d33",cancelButtonText:this.$t("Delete.cancelButtonText"),confirmButtonText:this.$t("Delete.confirmButtonText")}).then((function(r){r.value&&axios.delete("purchases/"+e).then((function(){t.$swal(t.$t("Delete.Deleted"),t.$t("Delete.PurchaseDeleted"),"success"),t.$router.push({name:"index_purchases"})})).catch((function(){t.$swal(t.$t("Delete.Failed"),t.$t("Delete.Therewassomethingwronge"),"warning")}))}))}},created:function(){this.Get_Details()}};const s=(0,r(14486).A)(i,(function(){var t=this,e=t._self._c;return e("div",{staticClass:"main-content"},[e("breadcumb",{attrs:{page:t.$t("PurchaseDetail"),folder:t.$t("ListPurchases")}}),t._v(" "),t.is_loading?e("div",{staticClass:"loading_page spinner spinner-primary mr-3"}):t._e(),t._v(" "),t.isLoading?t._e():e("b-card",[e("b-row",[e("b-col",{staticClass:"mb-2",attrs:{md:"12"}},[t.currentUserPermissions&&t.currentUserPermissions.includes("Purchases_edit")&&"no"==t.purchase.purchase_has_return?e("router-link",{staticClass:"btn btn-success btn-icon ripple btn-sm",attrs:{title:"Edit",to:{name:"edit_purchase",params:{id:t.$route.params.id}}}},[e("i",{staticClass:"i-Edit"}),t._v(" "),e("span",[t._v(t._s(t.$t("EditPurchase")))])]):t._e(),t._v(" "),e("button",{staticClass:"btn btn-info btn-icon ripple btn-sm",on:{click:function(e){return t.Send_Email()}}},[e("i",{staticClass:"i-Envelope-2"}),t._v("\n                    "+t._s(t.$t("Email"))+"\n                ")]),t._v(" "),e("button",{staticClass:"btn btn-secondary btn-icon ripple btn-sm",on:{click:function(e){return t.Purchase_SMS()}}},[e("i",{staticClass:"i-Speach-Bubble"}),t._v("\n                    SMS\n                ")]),t._v(" "),e("button",{staticClass:"btn btn-primary btn-icon ripple btn-sm",on:{click:function(e){return t.Print_Purchase_PDF()}}},[e("i",{staticClass:"i-File-TXT"}),t._v(" PDF\n                ")]),t._v(" "),e("button",{staticClass:"btn btn-warning btn-icon ripple btn-sm",on:{click:function(e){return t.print()}}},[e("i",{staticClass:"i-Billing"}),t._v("\n                    "+t._s(t.$t("print"))+"\n                ")]),t._v(" "),t.currentUserPermissions&&t.currentUserPermissions.includes("Purchases_delete")&&"no"==t.purchase.purchase_has_return?e("button",{staticClass:"btn btn-danger btn-icon ripple btn-sm",on:{click:function(e){return t.Delete_Purchase()}}},[e("i",{staticClass:"i-Close-Window"}),t._v("\n                    "+t._s(t.$t("Del"))+"\n                ")]):t._e()],1)],1),t._v(" "),e("div",{staticClass:"invoice mt-5",attrs:{id:"print_Invoice"}},[e("div",{staticClass:"invoice-print"},[e("b-row",{staticClass:"justify-content-md-center"},[e("h4",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("PurchaseDetail"))+" : "+t._s(t.purchase.Ref))])]),t._v(" "),e("hr"),t._v(" "),e("b-row",{staticClass:"mt-5"},[e("b-col",{staticClass:"mb-4",attrs:{lg:"4",md:"4",sm:"12"}},[e("h5",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Supplier_Info")))]),t._v(" "),e("div",[t._v(t._s(t.purchase.supplier_name))]),t._v(" "),e("div",[t._v(t._s(t.purchase.supplier_email))]),t._v(" "),e("div",[t._v(t._s(t.purchase.supplier_phone))]),t._v(" "),e("div",[t._v(t._s(t.purchase.supplier_adr))])]),t._v(" "),e("b-col",{staticClass:"mb-4",attrs:{lg:"4",md:"4",sm:"12"}},[e("h5",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Company_Info")))]),t._v(" "),e("div",[t._v(t._s(t.company.company_name))]),t._v(" "),e("div",[t._v(t._s(t.company.email))]),t._v(" "),e("div",[t._v(t._s(t.company.company_phone))]),t._v(" "),e("div",[t._v(t._s(t.company.company_address))])]),t._v(" "),e("b-col",{staticClass:"mb-4",attrs:{lg:"4",md:"4",sm:"12"}},[e("h5",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Purchase_Info")))]),t._v(" "),e("div",[t._v(t._s(t.$t("Reference"))+" : "+t._s(t.purchase.ref))]),t._v(" "),e("div",[t._v("\n                            "+t._s(t.$t("Status"))+" :\n                            "),"received"===t.purchase.status?e("span",{staticClass:"badge badge-outline-success"},[t._v(t._s(t.$t("Received")))]):"pending"===t.purchase.status?e("span",{staticClass:"badge badge-outline-info"},[t._v(t._s(t.$t("Pending")))]):e("span",{staticClass:"badge badge-outline-warning"},[t._v(t._s(t.$t("Ordered")))])]),t._v(" "),e("div",[t._v(t._s(t.$t("warehouse"))+" : "+t._s(t.purchase.warehouse))]),t._v(" "),e("div",[t._v("\n                            "+t._s(t.$t("PaymentStatus"))+" :\n                            "),"paid"===t.purchase.payment_status?e("span",{staticClass:"badge badge-outline-success"},[t._v(t._s(t.$t("Paid")))]):"partial"===t.purchase.payment_status?e("span",{staticClass:"badge badge-outline-primary"},[t._v(t._s(t.$t("partial")))]):e("span",{staticClass:"badge badge-outline-warning"},[t._v(t._s(t.$t("Unpaid")))])])])],1),t._v(" "),e("b-row",{staticClass:"mt-3"},[e("b-col",{attrs:{md:"12"}},[e("h5",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Order_Summary")))]),t._v(" "),e("div",{staticClass:"table-responsive"},[e("table",{staticClass:"table table-hover table-md"},[e("thead",{staticClass:"bg-gray-300"},[e("tr",[e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("ProductName")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("Net_Unit_Cost")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("Quantity")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("Unitcost")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("Discount")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("Tax")))]),t._v(" "),e("th",{attrs:{scope:"col"}},[t._v(t._s(t.$t("SubTotal")))])])]),t._v(" "),e("tbody",t._l(t.details,(function(r){return e("tr",[e("td",[e("span",[t._v(t._s(r.code)+" ("+t._s(r.name)+")")]),t._v(" "),e("p",{directives:[{name:"show",rawName:"v-show",value:r.is_imei&&null!==r.imei_number,expression:"detail.is_imei && detail.imei_number !==null "}]},[t._v("\n                                            "+t._s(t.$t("IMEI_SN"))+" : "+t._s(r.imei_number))])]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.formatNumber(r.net_cost,3)))]),t._v(" "),e("td",[t._v(t._s(t.formatNumber(r.quantity,2))+" "+t._s(r.unit_purchase))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.formatNumber(r.cost,2)))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.formatNumber(r.discount_net,2)))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.formatNumber(r.tax,2)))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(r.total.toFixed(2)))])])})),0)])])]),t._v(" "),e("div",{staticClass:"offset-md-9 col-md-3 mt-4"},[e("table",{staticClass:"table table-striped table-sm"},[e("tbody",[e("tr",[e("td",{staticClass:"bold"},[t._v(t._s(t.$t("OrderTax")))]),t._v(" "),e("td",[e("span",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.tax_net.toFixed(2))+" ("+t._s(t.formatNumber(t.purchase.tax_rate,2))+" %)")])])]),t._v(" "),e("tr",[e("td",{staticClass:"bold"},[t._v(t._s(t.$t("Discount")))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.discount.toFixed(2)))])]),t._v(" "),e("tr",[e("td",{staticClass:"bold"},[t._v(t._s(t.$t("Shipping")))]),t._v(" "),e("td",[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.shipping.toFixed(2)))])]),t._v(" "),e("tr",[e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Total")))])]),t._v(" "),e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.GrandTotal))])])]),t._v(" "),e("tr",[e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Paid")))])]),t._v(" "),e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.paid_amount))])])]),t._v(" "),e("tr",[e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.$t("Due")))])]),t._v(" "),e("td",[e("span",{staticClass:"font-weight-bold"},[t._v(t._s(t.currentUser.currency)+" "+t._s(t.purchase.due))])])])])])])],1),t._v(" "),e("hr",{directives:[{name:"show",rawName:"v-show",value:t.purchase.note,expression:"purchase.note"}]}),t._v(" "),e("b-row",{staticClass:"mt-4"},[e("b-col",{attrs:{md:"12"}},[e("p",[t._v(t._s(t.purchase.note))])])],1)],1)])],1)],1)}),[],!1,null,null,null).exports}}]);