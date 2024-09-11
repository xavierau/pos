<script>
import DetailTable from "./DetailTable.vue";
import CompanyInfo from "./CompanyInfo.vue";
import helperMixin from "../../../../../mixins/helperMethods";
import VueBarcode from "vue-barcode";

export default {
    name: "POS:Invoice:Template:Default",
    mixins: [helperMixin],
    components: {
        DetailTable,
        CompanyInfo,
        barcode: VueBarcode,
    },
    props: {
        invoiceData: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            css_path: '/css/pos_print.css',
            barcode_format: "CODE128",
        }
    },
    methods: {
        getPrintInfo() {
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    console.log(this.$refs, document.getElementById('invoice-POS'));
                    resolve({
                        html: this.$refs.template.innerHTML,
                        css_path: this.css_path,
                    });
                }, 1000);

            });
        }
    },
    mounted() {
        console.log('mounted', this.invoiceData);
    }
}
</script>

<template>
    <div ref="template" id="invoice-POS">
        <div v-if="invoiceData" style="max-width:400px;margin:0px auto">
            <CompanyInfo :invoice-data="invoiceData"/>
            <DetailTable :invoice-data="invoiceData"/>
            <table class="change mt-3"
                   style=" font-size: 10px;"
                   v-show="invoiceData.sale.paid_amount > 0">
                <thead>
                <tr style="background: #eee; ">
                    <th style="text-align: left;" colspan="1">{{ $t('PayBy') }}:</th>
                    <th style="text-align: center;" colspan="2">{{ $t('Amount') }}:</th>
                    <th style="text-align: right;" colspan="1">{{ $t('Change') }}:</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="payment in invoiceData.payments">
                    <td style="text-align: left;" colspan="1">{{ payment.type }}</td>
                    <td
                        style="text-align: center;"
                        colspan="2"
                    >{{ formatNumber(payment.amount, 1) }}
                    </td>
                    <td
                        style="text-align: right;"
                        colspan="1"
                    >{{ formatNumber(payment.change, 1) }}
                    </td>
                </tr>
                </tbody>
            </table>

            <div id="legalcopy" class="ml-2">
                <p class="legal" v-show="invoiceData.pos_settings.show_note">
                    <strong>{{ invoiceData.pos_settings.note_customer }}</strong>
                </p>
                <div id="bar" v-show="invoiceData.pos_settings.show_barcode">
                    <barcode
                        class="barcode"
                        :format="barcode_format"
                        :value="invoiceData.sale.ref"
                        textmargin="0"
                        fontoptions="bold"
                        fontSize="15"
                        height="25"
                        width="1"
                    ></barcode>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
