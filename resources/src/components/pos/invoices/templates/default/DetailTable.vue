<script>
import helperMixin from "../../../../../mixins/helperMethods";

export default  {
    name: "DetailTable",
    mixins: [helperMixin],
    props: {
        invoiceData: {
            type: Object,
            required: true
        }
    },
    computed: {
        show_due() {
            return this.invoiceData.sale.paid_amount < this.invoiceData.sale.grand_total
        },
    },
}
</script>

<template>
    <table class="table_data detail-table">
        <tbody>
        <tr v-for="detail in invoiceData.details">
            <td colspan="3">
                {{ detail.name }}
                <span
                    class="mt-3"
                    v-if="detail.is_imei && detail.imei_number !==null ">{{ $t('IMEI_SN') }} : {{
                        detail.imei_number
                    }}</span><br>
                <span class="mt-3">{{ formatNumber(detail.quantity, 1) }} {{
                        detail.unit_sale
                    }} x {{ displayCurrency(detail.total / detail.quantity, {symbol: invoiceData.symbol}) }}</span>
            </td>
            <td style="text-align:right;vertical-align:bottom">{{ formatNumber(detail.total, 1) }}</td>
        </tr>

        <tr style="margin-top:10px" v-if="invoiceData.setting.show_discount">
            <td colspan="3" class="total">{{ $t('OrderTax') }}</td>
            <td style="text-align:right;" class="total">
                {{ displayCurrency(invoiceData.sale.tax, {symbol: invoiceData.symbol}) }}
                ({{ formatNumber(invoiceData.sale.tax_rate, 2) }} %)
            </td>
        </tr>

        <tr style="margin-top:10px" v-if="invoiceData.setting.show_discount">
            <td colspan="3" class="total">{{ $t('Discount') }}</td>
            <td style="text-align:right;" class="total">
                {{ displayCurrency(invoiceData.sale.discount, {symbol: invoiceData.symbol}) }}
            </td>
        </tr>

        <tr style="margin-top:10px" v-if="invoiceData.setting.show_discount">
            <td colspan="3" class="total">{{ $t('Shipping') }}</td>
            <td style="text-align:right;" class="total">
                {{ displayCurrency(invoiceData.sale.shipping, {symbol: invoiceData.symbol}) }}
            </td>
        </tr>

        <tr style="margin-top:10px">
            <td colspan="3" class="total">{{ $t('Total') }}</td>
            <td
                style="text-align:right;"
                class="total"
            >{{ displayCurrency(invoiceData.sale.grand_total, {symbol: invoiceData.symbol}) }}
            </td>
        </tr>

        <tr v-if="show_due">
            <td colspan="3" class="total">{{ $t('Paid') }}</td>
            <td style="text-align:right;"
                class="total"
            >{{ displayCurrency(invoiceData.sale.paid_amount, {symbol: invoiceData.symbol}) }}
            </td>
        </tr>

        <tr v-if="show_due">
            <td colspan="3" class="total">{{ $t('Due') }}</td>
            <td style="text-align:right;" class="total">{{
                    displayCurrency(invoiceData.sale.grand_total, {symbol: invoiceData.symbol} -
                        invoiceData.sale.paid_amount)
                }}
            </td>
        </tr>
        </tbody>
    </table>

</template>

<style scoped>

</style>
