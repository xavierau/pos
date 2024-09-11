import store from "./store";

import Vue from "vue";
import router from "./router";
import App from "./App.vue";
import Auth from './auth/index.js';
import {extend, localize, ValidationObserver, ValidationProvider} from 'vee-validate';
import * as rules from "vee-validate/dist/rules";
import StockyKit from "./plugins/stocky.kit";
import VueCookies from 'vue-cookies'
import VueExcelXlsx from "vue-excel-xlsx";
import vSelect from 'vue-select'
import 'vue-select/dist/vue-select.css';
import '@trevoreyre/autocomplete-vue/dist/style.css';
import Breadcumb from "./components/breadcumb";
import {i18n} from "./plugins/i18n";

Date.prototype.addDays = function (days) {
    const date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
}

Date.prototype.addDays = function (days) {
    const date = new Date(this.valueOf());
    date.setDate(date.getDate() + days);
    return date;
}

Date.prototype.toDate = function (days) {
    const date = new Date(this.valueOf());
    return date.toISOString().split('T')[0]
}

window.auth = new Auth();

localize({
    en: {
        messages: {
            required: 'This field is required',
            required_if: 'This field is required',
            regex: 'This field must be a valid',
            mimes: `This field must have a valid file type.`,
            size: (_, {size}) => `This field size must be less than ${size}.`,
            min: 'This field must have no less than {length} characters',
            max: (_, {length}) => `This field must have no more than ${length} characters`
        }
    },
});
// Install VeeValidate rules and localization
Object.keys(rules).forEach(rule => {
    extend(rule, rules[rule]);
});

// Register it globally
Vue.component("ValidationObserver", ValidationObserver);
Vue.component('ValidationProvider', ValidationProvider);

Vue.use(StockyKit);
Vue.use(VueCookies);

var VueCookie = require('vue-cookie');
Vue.use(VueCookie);

Vue.use(VueExcelXlsx);

window.axios = require('axios');
window.axios.defaults.baseURL = '/api/';

window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response && error.response.data) {
        if (error.response.status === 401) {

            console.log('401 error', error)
            // window.location.href = '/login';
        }
        if (error.response.status === 404) {
            router.push({name: 'NotFound'});
        }
        if (error.response.status === 403) {
            router.push({name: 'not_authorize'});
        }
    }
    return Promise.reject(error.response.data ?? error.message);
});

Vue.component('v-select', vSelect)

window.Fire = new Vue();

Vue.component("breadcumb", Breadcumb);

Vue.config.productionTip = true;
Vue.config.silent = true;
Vue.config.devtools = true;

new Vue({
    store,
    router,
    VueCookie,
    i18n,
    render: h => h(App),
}).$mount("#app");




