<script>
import ChartWrapper from "./ChartWrapper"

export default {
    name: "ChartSalesPurchase",
    components: {
        ChartWrapper
    },
    props: {
        warehouseId: {
            type: Number,
            default: null
        },
        startDate: {
            type: Date,
            default: (new Date()).addDays(-6)
        },
        endDate: {
            type: Date,
            default: new Date()
        }
    },
    data() {
        return {
            data: null,
            is_loading: true,
            dark_heading: "#c2c6dc"
        }
    },
    watch: {
        warehouseId() {
            this.loadData()
        }
    },
    computed: {
        options() {
            return {
                legend: {
                    borderRadius: 0,
                    orient: "horizontal",
                    x: "right",
                    data: [this.$t("Sales"), this.$t("Purchases")]
                },
                grid: {
                    left: "8px",
                    right: "8px",
                    bottom: "0",
                    containLabel: true
                },
                tooltip: {
                    show: true,
                    backgroundColor: "rgba(0, 0, 0, .8)"
                },
                xAxis: [
                    {
                        type: "category",
                        data: this.data?.sales?.x || [],
                        axisTick: {
                            alignWithLabel: true
                        },
                        splitLine: {
                            show: false
                        },
                        axisLabel: {
                            color: this.dark_heading,
                            interval: 0,
                            rotate: 30
                        },
                        axisLine: {
                            show: true,
                            color: this.dark_heading,

                            lineStyle: {
                                color: this.dark_heading
                            }
                        }
                    }
                ],
                yAxis: [
                    {
                        type: "value",
                        axisLabel: {
                            color: this.dark_heading
                            // formatter: "${value}"
                        },
                        axisLine: {
                            show: false,
                            color: this.dark_heading,

                            lineStyle: {
                                color: this.dark_heading
                            }
                        },
                        min: 0,
                        splitLine: {
                            show: true,
                            interval: "auto"
                        }
                    }
                ],
                series: [
                    {
                        name: this.$t("Sales"),
                        data: this.data?.sales?.y || [],
                        label: {show: false, color: "#8B5CF6"},
                        type: "bar",
                        color: "#A78BFA",
                        smooth: true,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowOffsetY: -2,
                                shadowColor: "rgba(0, 0, 0, 0.3)"
                            }
                        }
                    },
                    {
                        name: this.$t("Purchases"),
                        data: this.data?.purchase?.y || [],
                        label: {show: false, color: "#0168c1"},
                        type: "bar",
                        barGap: 0,
                        color: "#DDD6FE",
                        smooth: true,
                        itemStyle: {
                            emphasis: {
                                shadowBlur: 10,
                                shadowOffsetX: 0,
                                shadowOffsetY: -2,
                                shadowColor: "rgba(0, 0, 0, 0.3)"
                            }
                        }
                    }
                ]
            }
        }
    },
    methods: {
        constructQueryString() {
            const queries = new URLSearchParams()
            queries.append('warehouse_id', this.warehouseId || '')
            queries.append('start_date', this.startDate?.toDate() || '')
            queries.append('end_date', this.endDate?.toDate() || '')
            return queries.toString()
        },
        loadData() {
            this.is_loading = true
            axios.get('/dashboard/charts/sales_purchases?' + this.constructQueryString())
                .then(({data}) => this.data = data)
                .finally(() => this.is_loading = false)
        }
    },
    created() {
        this.loadData()
    }
}
</script>

<template>
    <ChartWrapper :options="options" :is_loading="is_loading"/>
</template>
