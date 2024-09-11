<script>
import {mapGetters} from "vuex";
import helperMixin from "../../../mixins/helperMethods";

export default {
    name: "SimpleMetricCard",
    mixins:[helperMixin],
    props: {
        url: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        },
        isCurrency: {
            type: Boolean,
            default: false
        },
        metric: {
            type: Number,
        },
        icon: {
            type: String,
        },
        isLoading: {
            type: Boolean,
            default: true
        }
    },
    computed: {
        ...mapGetters(["currentUser"]),
        displayText() {
            console.log(this.isCurrency, this.currentUser.currency, this.metric)
            const formattedMetric = this.formatNumber(this.metric)
            return this.isCurrency ? `${this.currentUser.currency} ${formattedMetric}` : formattedMetric
        }
    }
}
</script>

<template>
    <router-link tag="a" class :to="url">
        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
            <i v-if="icon" :class="icon"></i>
            <div class="content">
                <p class="text-muted mt-2 mb-0">{{ $t(title) }}</p>
                <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
                <p v-else class="text-primary text-24 line-height-1 mb-2">{{ displayText }}</p>
            </div>

        </b-card>
    </router-link>

</template>

<style scoped>
.spinner {
    max-width: 30px;
    max-height: 30px;
}

</style>
