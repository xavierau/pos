<script>
import SimpleMetricCard from "../../common/metrics/SimpleMetricCard.vue";

export default {
    name: "TodayPurchasesMetric",
    components: {SimpleMetricCard},
    props: {
        warehouseId: {
            type: Number,
        }
    },
    data() {
        return {
            metric: null,
            isLoading: true
        }
    },
    watch: {
        warehouseId() {
            this.loadData()
        }
    },
    methods: {
        loadData() {
            this.isLoading = true
            axios.get('/dashboard/metrics/today_purchases?' + this.constructQueryString())
                .then(response => this.metric = response.data.data)
                .catch(error => console.log(error))
                .finally(() => this.isLoading = false)
        },
        constructQueryString() {
            const queries = new URLSearchParams()
            queries.append('warehouse_id', this.warehouseId || "")

            return queries.toString()

        }
    },
    created() {
        this.loadData()
    }

}
</script>

<template>
    <SimpleMetricCard url="/app/purchases/list"
                      title="Purchases"
                      :is-currency="true"
                      :is-loading="isLoading"
                      :metric="metric"
                      icon="i-Add-Cart"/>

</template>

<style scoped>

</style>
