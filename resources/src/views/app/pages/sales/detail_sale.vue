<template>
    <div class="main-content">
        <breadcumb :page="$t('SaleDetail')" :folder="$t('Sales')"/>
        <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>

        <b-card v-if="!is_loading">
            <b-row>
                <b-col md="12" class="mb-5">

                    <router-link
                        v-if="currentUserPermissions && currentUserPermissions.includes('Sales_edit') && sale.sale_has_return == 'no'"
                        title="Edit"
                        class="btn btn-success btn-icon ripple btn-sm"
                        :to="{ name:'edit_sale', params: { id: $route.params.id } }"
                    >
                        <i class="i-Edit"></i>
                        <span>{{ $t('EditSale') }}</span>
                    </router-link>

                    <button @click="sendEmail()" class="btn btn-info btn-icon ripple btn-sm">
                        <i class="i-Envelope-2"></i>
                        {{ $t('Email') }}
                    </button>
                    <button @click="saleSms()" class="btn btn-secondary btn-icon ripple btn-sm">
                        <i class="i-Speach-Bubble"></i>
                        SMS
                    </button>
                    <button @click="salePdf()" class="btn btn-primary btn-icon ripple btn-sm">
                        <i class="i-File-TXT"></i>
                        PDF
                    </button>
                    <button @click="print()" class="btn btn-warning btn-icon ripple btn-sm">
                        <i class="i-Billing"></i>
                        {{ $t('print') }}
                    </button>
                    <button
                        v-if="currentUserPermissions && currentUserPermissions.includes('Sales_delete') && sale.sale_has_return == 'no'"
                        @click="deleteSale()"
                        class="btn btn-danger btn-icon ripple btn-sm"
                    >
                        <i class="i-Close-Window"></i>
                        {{ $t('Del') }}
                    </button>
                </b-col>
            </b-row>
            <div class="invoice" id="print_Invoice">
                <div class="invoice-print">
                    <b-row class="justify-content-md-center">
                        <h4 class="font-weight-bold">{{ $t('SaleDetail') }} : {{ sale.Ref }}</h4>
                    </b-row>
                    <hr>
                    <b-row class="mt-5">
                        <b-col lg="4" md="4" sm="12" class="mb-4">
                            <h5 class="font-weight-bold">{{ $t('Customer_Info') }}</h5>
                            <div>{{ sale.client_name }}</div>
                            <div>{{ sale.client_email }}</div>
                            <div>{{ sale.client_phone }}</div>
                            <div>{{ sale.client_adr }}</div>
                        </b-col>
                        <b-col lg="4" md="4" sm="12" class="mb-4">
                            <h5 class="font-weight-bold">{{ $t('Company_Info') }}</h5>
                            <div>{{ company.CompanyName }}</div>
                            <div>{{ company.email }}</div>
                            <div>{{ company.CompanyPhone }}</div>
                            <div>{{ company.CompanyAddress }}</div>
                        </b-col>
                        <b-col lg="4" md="4" sm="12" class="mb-4">
                            <h5 class="font-weight-bold">{{ $t('Invoice_Info') }}</h5>
                            <div>{{ $t('Reference') }} : {{ sale.Ref }}</div>
                            <div>
                                {{ $t('PaymentStatus') }} :
                                <span
                                    v-if="sale.payment_status == 'paid'"
                                    class="badge badge-outline-success"
                                >{{ $t('Paid') }}</span>
                                <span
                                    v-else-if="sale.payment_status == 'partial'"
                                    class="badge badge-outline-primary"
                                >{{ $t('partial') }}</span>
                                <span v-else class="badge badge-outline-warning">{{ $t('Unpaid') }}</span>
                            </div>
                            <div>{{ $t('warehouse') }} : {{ sale.warehouse }}</div>
                            <div>
                                {{ $t('Status') }} :
                                <span
                                    v-if="sale.status == 'completed'"
                                    class="badge badge-outline-success"
                                >{{ $t('complete') }}</span>
                                <span
                                    v-else-if="sale.status == 'pending'"
                                    class="badge badge-outline-info"
                                >{{ $t('Pending') }}</span>
                                <span v-else class="badge badge-outline-warning">{{ $t('Ordered') }}</span>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row class="mt-3">
                        <b-col md="12">
                            <h5 class="font-weight-bold">{{ $t('Order_Summary') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-md">
                                    <thead class="bg-gray-300">
                                    <tr>
                                        <th scope="col">{{ $t('ProductName') }}</th>
                                        <th scope="col">{{ $t('Net_Unit_Price') }}</th>
                                        <th scope="col">{{ $t('Quantity') }}</th>
                                        <th scope="col">{{ $t('UnitPrice') }}</th>
                                        <th scope="col">{{ $t('Discount') }}</th>
                                        <th scope="col">{{ $t('Tax') }}</th>
                                        <th scope="col">{{ $t('SubTotal') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="detail in details">
                                        <td><span>{{ detail.code }} ({{ detail.name }})</span>
                                            <p v-show="detail.is_imei && detail.imei_number !==null ">
                                                {{ $t('IMEI_SN') }} : {{ detail.imei_number }}</p>
                                        </td>
                                        <td>{{ displayCurrency(detail.net_price, {symbol: currentUser.currency}) }}</td>
                                        <td>{{ formatNumber(detail.quantity, 2) }} {{ detail.unit_sale }}</td>
                                        <td> {{ displayCurrency(detail.price, {symbol: currentUser.currency}) }}</td>
                                        <td>{{
                                                displayCurrency(detail.discount_net, {symbol: currentUser.currency})
                                            }}
                                        </td>
                                        <td>{{ displayCurrency(detail.tax, {symbol: currentUser.currency}) }}</td>
                                        <td>{{ displayCurrency(detail.total, {symbol: currentUser.currency}) }}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </b-col>
                        <div class="offset-md-9 col-md-3 mt-4">
                            <table class="table table-striped table-sm">
                                <tbody>
                                <tr>
                                    <td>{{ $t('OrderTax') }}</td>
                                    <td>
                                        <span>{{
                                                displayCurrency(sale.tax_net, {symbol: currentUser.currency})
                                            }} ({{ formatNumber(sale.tax_rate, 2) }} %)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>{{ $t('Discount') }}</td>
                                    <td>{{ displayCurrency(sale.discount, {symbol: currentUser.currency}) }}</td>
                                </tr>
                                <tr>
                                    <td>{{ $t('Shipping') }}</td>
                                    <td>{{ displayCurrency(sale.shipping, {symbol: currentUser.currency}) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $t('Total') }}</span>
                                    </td>
                                    <td>
                      <span
                          class="font-weight-bold"
                      >{{ currentUser.currency }} {{ sale.GrandTotal }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $t('Paid') }}</span>
                                    </td>
                                    <td>
                      <span
                          class="font-weight-bold"
                      >{{ currentUser.currency }} {{ sale.paid_amount }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{ $t('Due') }}</span>
                                    </td>
                                    <td>
                      <span
                          class="font-weight-bold"
                      >{{ currentUser.currency }} {{ sale.due }}</span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </b-row>
                    <hr v-show="sale.note">
                    <b-row class="mt-4">
                        <b-col md="12">
                            <p>{{ $t('sale_note') }} : {{ sale.note }}</p>
                        </b-col>
                    </b-row>
                </div>
            </div>
        </b-card>
    </div>
</template>

<script>

import {mapGetters} from "vuex";
import NProgress from "nprogress";
import helperMixin from "../../../../mixins/helperMethods";

export default {
    mixins: [helperMixin],
    computed: mapGetters(["currentUserPermissions", "currentUser"]),
    metaInfo: {
        title: "Detail Sale"
    },

    data() {
        return {
            is_loading: true,
            sale: {},
            details: [],
            variants: [],
            company: {},
            email: {
                to: "",
                subject: "",
                message: "",
                client_name: "",
                Sale_Ref: ""
            }
        };
    },

    methods: {

        //----------------------------------- Invoice Sale PDF  -------------------------\\
        salePdf() {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            let id = this.$route.params.id;
            const url = `sale_pdf/${id}`;

            window.open(url, '_blank').focus();


            // axios.get(`sale_pdf/${id}`, {
            //     responseType: "blob",
            // })
            //     .then(response => {
            //         console.log('response', response);
            //         const link = document.createElement("a");
            //         link.href = URL.createObjectURL(new Blob([response.data], {type: "application/pdf"}));
            //         link.setAttribute("download", "Sale_" + this.sale.Ref + ".pdf");
            //         document.body.appendChild(link);
            //         link.click();
            //         // Complete the animation of the  progress bar.
            //         setTimeout(() => NProgress.done(), 500);
            //     })
            //     .catch(() => {
            //         // Complete the animation of the  progress bar.
            //         setTimeout(() => NProgress.done(), 500);
            //     });

            NProgress.done()
        },

        //------ Toast
        makeToast(variant, msg, title) {
            this.$root.$bvToast.toast(msg, {
                title: title,
                variant: variant,
                solid: true
            });
        },

        //------------------------------ Print -------------------------\\
        print() {
            this.$htmlToPaper('print_Invoice');
        },


        sendEmail() {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            let id = this.$route.params.id;
            axios
                .post("sales_send_email", {
                    id: id,
                })
                .then(response => {
                    // Complete the animation of the  progress bar.
                    setTimeout(() => NProgress.done(), 500);
                    this.makeToast(
                        "success",
                        this.$t("Send.TitleEmail"),
                        this.$t("Success")
                    );
                })
                .catch(error => {
                    // Complete the animation of the  progress bar.
                    setTimeout(() => NProgress.done(), 500);
                    this.makeToast("danger", this.$t("SMTPIncorrect"), this.$t("Failed"));
                });
        },

        //---------SMS notification
        saleSms() {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            let id = this.$route.params.id;
            axios
                .post("sales_send_sms", {
                    id: id,
                })
                .then(response => {
                    // Complete the animation of the  progress bar.
                    setTimeout(() => NProgress.done(), 500);
                    this.makeToast(
                        "success",
                        this.$t("Send_SMS"),
                        this.$t("Success")
                    );
                })
                .catch(error => {
                    // Complete the animation of the  progress bar.
                    setTimeout(() => NProgress.done(), 500);
                    this.makeToast("danger", this.$t("sms_config_invalid"), this.$t("Failed"));
                });
        },

        //----------------------------------- Get Details Sale ------------------------------\\
        getDetails() {
            let id = this.$route.params.id;
            axios
                .get(`sales/${id}`)
                .then(response => {
                    this.sale = response.data.sale;
                    this.details = response.data.details;
                    this.company = response.data.company;
                    this.is_loading = false;
                })
                .catch(response => this.is_loading = false);
        },

        //------------------------------------------ DELETE Sale ------------------------------\\
        deleteSale() {
            let id = this.$route.params.id;
            this.$swal({
                title: this.$t("Delete.Title"),
                text: this.$t("Delete.Text"),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: this.$t("Delete.cancelButtonText"),
                confirmButtonText: this.$t("Delete.confirmButtonText")
            }).then(result => {
                if (result.value) {
                    axios
                        .delete("sales/" + id)
                        .then(() => {
                            this.$swal(
                                this.$t("Delete.Deleted"),
                                this.$t("Delete.SaleDeleted"),
                                "success"
                            );
                            this.$router.push({name: "index_sales"});
                        })
                        .catch(() => {
                            this.$swal(
                                this.$t("Delete.Failed"),
                                this.$t("Delete.Therewassomethingwronge"),
                                "warning"
                            );
                        });
                }
            });
        }
    }, //end Methods

    //----------------------------- Created function-------------------

    created: function () {
        this.getDetails();
    }
};
</script>
