<script>
import ECharts from "vue-echarts/components/ECharts.vue";

export default {
    name: "ChartWrapper",
    components: {
        "v-chart": ECharts
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        is_loading: {
            type: Boolean,
            default: true
        }
    }
}
</script>

<template>
    <div class="chart-wrapper">
        <div v-once class="typo__p text-right" v-if="is_loading">
            <div class="spinner sm spinner-primary mt-3"></div>
        </div>
        <v-chart v-if="!is_loading" :options="options" :autoresize="true"></v-chart>
    </div>

</template>

<style scoped>

</style>
