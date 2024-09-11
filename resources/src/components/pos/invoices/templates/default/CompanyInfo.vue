<script>
import helperMixin from "../../../../../mixins/helperMethods";

export default {
    name: "CompanyInfo",
    mixins: [helperMixin],
    props: {
        invoiceData: {
            type: Object,
            required: true
        }
    }
}
</script>

<template>
    <div class="info company-info">
        <div class="invoice_logo text-center mb-2">
            <img :src="'/images/'+invoiceData.setting.logo" alt width="60" height="60">
        </div>
        <p>
            <span class="mb-3">{{ $t('date') }} : {{ invoiceData.sale.date }} </span>
            <span class="mb-3"
                  v-show="invoiceData.setting.show_address">{{ $t('Address') }} : {{
                    invoiceData.setting.company_address
                }} </span>
            <span class="mb-3"
                  v-show="invoiceData.setting.show_email">{{ $t('Email') }} : {{ invoiceData.setting.email }} </span>
            <span class="mb-3"
                  v-show="invoiceData.setting.show_phone">{{
                    $t('Phone')
                }} : {{ invoiceData.setting.CompanyPhone }} </span>
            <span class="mb-3"
                  v-show="invoiceData.setting.show_customer">{{
                    $t('Customer')
                }} : {{ invoiceData.sale.client_name }} </span>
            <span class="mb-3"
                  v-show="invoiceData.setting.show_warehouse">{{
                    $t('warehouse')
                }} : {{ invoiceData.sale.warehouse_name }} </span>
        </p>
    </div>
</template>

<style scoped>

</style>
