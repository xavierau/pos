<template>
    <!-- ============ Body content start ============= -->
    <div class="main-content">
        <div v-if="loading" class="loading_page spinner spinner-primary mr-3"></div>
        <div v-else-if="!loading && currentUserPermissions && currentUserPermissions.includes('dashboard')">
            <!-- warehouse -->
            <b-row>
                <b-col lg="4" md="4" sm="12">
                    <b-form-group :label="$t('Filter_by_warehouse')">
                        <v-select
                            @input="Selected_Warehouse"
                            v-model="warehouse_id"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Warehouse')"
                            :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                        />
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row>
                <!-- ICON BG -->

                <b-col lg="3" md="6" sm="12">
                    <TodaySalesMetric :warehouse-id="warehouse_id"/>
                </b-col>

                <b-col lg="3" md="6" sm="12">
                    <TodayPurchasesMetric :warehouse-id="warehouse_id"></TodayPurchasesMetric>
                </b-col>

                <b-col lg="3" md="6" sm="12">
                    <router-link tag="a" class to="/app/sale_return/list">
                        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
                            <i class="i-Right-4"></i>
                            <div class="content">
                                <p class="text-muted mt-2 mb-0">{{ $t('SalesReturn') }}</p>
                                <p
                                    class="text-primary text-24 line-height-1 mb-2"
                                >{{ currentUser.currency }}
                                    {{ report_today.return_sales ? report_today.return_sales : 0 }}</p>
                            </div>
                        </b-card>
                    </router-link>
                </b-col>

                <b-col lg="3" md="6" sm="12">
                    <router-link tag="a" class to="/app/purchase_return/list">
                        <b-card class="card-icon-bg card-icon-bg-primary o-hidden mb-30 text-center">
                            <i class="i-Left-4"></i>
                            <div class="content">
                                <p class="text-muted mt-2 mb-0">{{ $t('PurchasesReturn') }}</p>
                                <p
                                    class="text-primary text-24 line-height-1 mb-2"
                                >{{ currentUser.currency }}
                                    {{ report_today.return_purchases ? report_today.return_purchases : 0 }}</p>
                            </div>
                        </b-card>
                    </router-link>
                </b-col>

            </b-row>

            <b-row>
                <b-col lg="8" md="12" sm="12">
                    <b-card class="mb-30">
                        <h4 class="card-title m-0">{{ $t('This_Week_Sales_Purchases') }}</h4>
                        <ChartSalesPurchase :warehouse-id="warehouse_id"/>
                    </b-card>
                </b-col>
                <b-col col lg="4" md="12" sm="12">
                    <b-card class="mb-30">
                        <h4 class="card-title m-0">{{ $t('Top_Selling_Products') }}
                            ({{ new Date().getFullYear() }})</h4>
                        <ChartWrapper :options="echart_product" :is_loading="loading"/>
                    </b-card>
                </b-col>
            </b-row>

            <b-row>
                <!-- Stock Alert -->
                <div class="col-md-8">
                    <div class="card mb-30">
                        <div class="card-body p-2">
                            <h5 class="card-title border-bottom p-3 mb-2">{{ $t('StockAlert') }}</h5>

                            <vue-good-table
                                :columns="columns_stock"
                                styleClass="order-table vgt-table mb-3"
                                row-style-class="text-left"
                                :rows="stock_alerts"
                            >
                                <template slot="table-row" slot-scope="props">
                                    <div v-if="props.column.field == 'stock_alert'">
                                        <span class="badge badge-outline-danger">{{ props.row.stock_alert }}</span>
                                    </div>
                                </template>
                            </vue-good-table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-30">
                        <div class="card-body p-3">
                            <h5
                                class="card-title border-bottom p-3 mb-2"
                            >{{ $t('Top_Selling_Products') }} ({{ current_month }})</h5>

                            <vue-good-table
                                :columns="columns_products"
                                styleClass="order-table vgt-table"
                                row-style-class="text-left"
                                :rows="products"
                            >
                                <template slot="table-row" slot-scope="props">
                                    <div v-if="props.column.field == 'total'">
                                        <span>{{ currentUser.currency }} {{ formatNumber(props.row.total, 2) }}</span>
                                    </div>
                                </template>
                            </vue-good-table>
                        </div>
                    </div>
                </div>
            </b-row>

            <b-row>
                <b-col lg="8" md="12" sm="12">
                    <b-card class="mb-30">
                        <h4 class="card-title m-0">{{ $t('Payment_Sent_Received') }}</h4>
                        <ChartWrapper :options="echart_payment" :is_loading="loading"/>
                    </b-card>
                </b-col>
                <b-col col lg="4" md="12" sm="12">
                    <b-card class="mb-30">
                        <h4 class="card-title m-0">{{ $t('TopCustomers') }} ({{ current_month }})</h4>
                        <ChartWrapper :options="echart_customer" :is_loading="loading"/>
                    </b-card>
                </b-col>
            </b-row>

            <!-- Last Sales -->
            <b-row>
                <div class="col-md-12">
                    <div class="card mb-30">
                        <div class="card-body p-0">
                            <h5 class="card-title border-bottom p-3 mb-2">{{ $t('Recent_Sales') }}</h5>

                            <vue-good-table
                                v-if="!loading"
                                :columns="columns_sales"
                                styleClass="order-table vgt-table"
                                row-style-class="text-left"
                                :rows="sales"
                            >
                                <template slot="table-row" slot-scope="props">
                                    <div v-if="props.column.field == 'status'">
                    <span
                        v-if="props.row.status == 'completed'"
                        class="badge badge-outline-success"
                    >{{ $t('complete') }}</span>
                                        <span
                                            v-else-if="props.row.status == 'pending'"
                                            class="badge badge-outline-info"
                                        >{{ $t('Pending') }}</span>
                                        <span v-else class="badge badge-outline-warning">{{ $t('Ordered') }}</span>
                                    </div>

                                    <div v-else-if="props.column.field == 'payment_status'">
                    <span
                        v-if="props.row.payment_status == 'paid'"
                        class="badge badge-outline-success"
                    >{{ $t('Paid') }}</span>
                                        <span
                                            v-else-if="props.row.payment_status == 'partial'"
                                            class="badge badge-outline-primary"
                                        >{{ $t('partial') }}</span>
                                        <span v-else class="badge badge-outline-warning">{{ $t('Unpaid') }}</span>
                                    </div>
                                </template>
                            </vue-good-table>
                        </div>
                    </div>
                </div>
            </b-row>
        </div>

        <div v-else>
            <h4>{{ $t('Welcome_to_your_Dashboard') }}</h4>
        </div>

    </div>

    <!-- ============ Body content End ============= -->
</template>
<script>
import {mapGetters} from "vuex";

import ECharts from "vue-echarts/components/ECharts.vue";

// import ECharts modules manually to reduce bundle size
import "echarts/lib/chart/pie";
import "echarts/lib/chart/bar";
import "echarts/lib/chart/line";
import "echarts/lib/component/tooltip";
import "echarts/lib/component/legend";
import ChartWrapper from "../../../components/common/charts/ChartWrapper";
import ChartSalesPurchase from "../../../components/common/charts/ChartSalesPurchase";
import TodaySalesMetric from "../../../components/dashboard/metrics/TodaySalesMetric.vue";
import TodayPurchasesMetric from "../../../components/dashboard/metrics/TodayPurchasesMetric.vue";

export default {
    components: {
        ChartWrapper,
        TodaySalesMetric,
        TodayPurchasesMetric,
        ChartSalesPurchase,
        "v-chart": ECharts
    },
    metaInfo: {
        // if no subcomponents specify a metaInfo.title, this title will be used
        title: "Dashboard"
    },
    data() {
        return {
            sales: [],
            warehouses: [],
            warehouse_id: null,
            stock_alerts: [],
            report_today: {
                revenue: 0,
                today_purchases: 0,
                today_sales: 0,
                return_sales: 0,
                return_purchases: 0
            },
            products: [],
            current_month: "",
            loading: true,
            echart_sales: {},
            echart_product: {},
            echart_customer: {},
            echart_payment: {}
        };
    },
    computed: {
        ...mapGetters(["currentUserPermissions", "currentUser"]),
        columns_sales() {
            return [
                {
                    label: this.$t("Reference"),
                    field: "Ref",
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Customer"),
                    field: "client_name",
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("warehouse"),
                    field: "warehouse_name",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("Status"),
                    field: "status",
                    html: true,
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Total"),
                    field: "GrandTotal",
                    type: "decimal",
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Paid"),
                    field: "paid_amount",
                    type: "decimal",
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Due"),
                    field: "due",
                    type: "decimal",
                    tdClass: "gull-border-none text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("PaymentStatus"),
                    field: "payment_status",
                    html: true,
                    sortable: false,
                    tdClass: "text-left gull-border-none",
                    thClass: "text-left"
                }
            ];
        },
        columns_stock() {
            return [
                {
                    label: this.$t("ProductCode"),
                    field: "code",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("ProductName"),
                    field: "name",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("warehouse"),
                    field: "warehouse",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Quantity"),
                    field: "quantity",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("AlertQuantity"),
                    field: "stock_alert",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                }
            ];
        },
        columns_products() {
            return [
                {
                    label: this.$t("ProductName"),
                    field: "name",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("TotalSales"),
                    field: "total_sales",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("TotalAmount"),
                    field: "total",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                }
            ];
        }
    },
    methods: {

        //---------------------- Event Select Warehouse ------------------------------\\
        Selected_Warehouse(value) {
            if (value === null) {
                this.warehouse_id = "";
            }
            this.getDashboardData();
        },

        //---------------------------------- Report Dashboard With Echart
        getDashboardData() {
            axios
                .get(
                    "/dashboard_data?warehouse_id=" + this.warehouse_id)
                .then(response => {
                    const responseData = response.data;
                    this.report_today = response.data.report_dashboard.original.report;
                    this.warehouses = response.data.warehouses;
                    this.stock_alerts = response.data.report_dashboard.original.stock_alert;
                    this.products = response.data.report_dashboard.original.products;
                    this.sales = response.data.report_dashboard.original.last_sales;


                    this.echart_customer = {
                        color: ["#6D28D9", "#8B5CF6", "#A78BFA", "#C4B5FD", "#7C3AED"],
                        tooltip: {
                            show: true,
                            backgroundColor: "rgba(0, 0, 0, .8)"
                        },
                        formatter: function (params) {
                            return `${params.name}: (${params.data.value} sales) (${
                                params.percent
                            }%)`;
                        },
                        series: [
                            {
                                name: "Top Customers",
                                type: "pie",
                                radius: "50%",
                                center: "50%",

                                data: responseData.customers.original,
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: "rgba(0, 0, 0, 0.5)"
                                    }
                                }
                            }
                        ]
                    };
                    this.echart_payment = {
                        tooltip: {
                            trigger: "axis"
                        },
                        legend: {
                            data: ["Payment sent", "Payment received"]
                        },
                        grid: {
                            left: "3%",
                            right: "4%",
                            bottom: "3%",
                            containLabel: true
                        },
                        toolbox: {
                            feature: {
                                saveAsImage: {}
                            }
                        },
                        xAxis: {
                            type: "category",
                            boundaryGap: false,
                            data: responseData.payments.original.days
                        },
                        yAxis: {
                            type: "value"
                        },
                        series: [
                            {
                                name: "Payment sent",
                                type: "line",
                                data: responseData.payments.original.payment_sent
                            },
                            {
                                name: "Payment received",
                                type: "line",
                                data: responseData.payments.original.payment_received
                            }
                        ]
                    };
                    this.echart_product = {
                        color: ["#6D28D9", "#8B5CF6", "#A78BFA", "#C4B5FD", "#7C3AED"],
                        tooltip: {
                            show: true,
                            backgroundColor: "rgba(0, 0, 0, .8)"
                        },
                        formatter: function (params) {
                            return `${params.name}: (${params.value}sales)`;
                        },
                        series: [
                            {
                                name: "Top Selling Products",
                                type: "pie",
                                radius: "50%",
                                center: "50%",

                                data: responseData.product_report.original,
                                itemStyle: {
                                    emphasis: {
                                        shadowBlur: 10,
                                        shadowOffsetX: 0,
                                        shadowColor: "rgba(0, 0, 0, 0.5)"
                                    }
                                }
                            }
                        ]
                    };
                    this.loading = false;
                })
                .catch(response => {
                });
        },

        //------------------------------Get Month -------------------------\\
        getMonth() {
            let months = [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ];
            let now = new Date();
            this.current_month = months[now.getMonth()];
        },

        //------------------------------Formetted Numbers -------------------------\\
        formatNumber(number, dec) {
            const value = (typeof number === "string"
                    ? number
                    : number.toString()
            ).split(".");
            if (dec <= 0) return value[0];
            let formated = value[1] || "";
            if (formated.length > dec)
                return `${value[0]}.${formated.substr(0, dec)}`;
            while (formated.length < dec) formated += "0";
            return `${value[0]}.${formated}`;
        }
    },
    async mounted() {
        await this.getDashboardData();
        this.getMonth();
    },
    created() {
        axios.get('/dashboard/charts/sales_purchases_payments')
            .then(response => console.log(response))
    },

};
</script>
