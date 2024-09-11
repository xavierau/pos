import Util from "../utils";

import NProgress from "nprogress";
import {mapGetters} from "vuex";

import ToastType from "../utils/toastType";

const helperMixin = {
    created() {
        Object.keys(this.registerEvents || {}).forEach(event => Fire?.$on(event, this.registerEvents[event]))
    },
    data() {
        return {
            registerEvents: {}
        }
    },
    computed: {
        ...mapGetters(["currentUserPermissions", "currentUser"])
    },
    methods: {
        makeToast(variant, msg, title) {
            this.$root.$bvToast.toast(
                this.$t(msg),
                {
                    title: this.$t(title),
                    variant: variant,
                    solid: true
                });
        },
        makeErrorToast(msg, title = "Error") {
            this.makeToast(ToastType.ERROR, msg, title);
        },
        makeSuccessToast(msg, title = "Success") {
            this.makeToast(ToastType.SUCCESS, msg, title);
        },
        makeWarningToast(msg, title = "Warning") {
            this.makeToast(ToastType.WARNING, msg, title);
        },
        makeDangerToast(msg, title = "Danger") {
            this.makeToast(ToastType.DANGER, msg, title);
        },
        getValidationState({dirty, validated, valid = null}) {
            return dirty || validated ? valid : null;
        },
        formatNumber(value, decimals = 2) {
            return Util.formatNumber(value, decimals)
        },
        displayCurrency(number, config = {decimal: 1, symbol: null}) {
            const formattedNumber = this.formatNumber(number ?? "", config.decimal || 1)
            const symbol = config.symbol || this.data.symbol
            return `${symbol} ${formattedNumber}`
        },
        fire(eventName, payload = null) {
            console.log('fire event', eventName, payload)
            Fire?.$emit(eventName, payload);
        },
        async execute(callable) {
            NProgress.start();
            NProgress.set(0.1);
            try {
                // Ensure callable is treated as a Promise
                await Promise.resolve(callable());
            } catch (error) {
                console.error('mixin execute: ', error);
            } finally {
                NProgress.done();
            }

        }
    }
}


export default helperMixin;
