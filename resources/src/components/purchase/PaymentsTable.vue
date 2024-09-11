<script>
import helperMixin from "../../mixins/helperMethods";
import {PurchasePaymentEvent} from "../../utils/FireEvent";


export default {
    name: "PaymentsTable",
    mixins: [helperMixin],
    props: {
        payments: {
            type: Array,
            default: []
        }
    },
    computed: {
        event() {
            return PurchasePaymentEvent
        }
    },
}
</script>

<template>
    <div class="table-responsive payments-table">
        <table class="table table-hover table-bordered table-md">
            <thead>
            <tr>
                <th scope="col">{{ $t('date') }}</th>
                <th scope="col">{{ $t('Reference') }}</th>
                <th scope="col">{{ $t('Amount') }}</th>
                <th scope="col">{{ $t('PayeBy') }}</th>
                <th scope="col">{{ $t('Action') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="payments.length <= 0">
                <td colspan="5">{{ $t('NodataAvailable') }}</td>
            </tr>
            <tr v-for="payment in payments">
                <td>{{ payment.date }}</td>
                <td>{{ payment.ref }}</td>
                <td>{{ currentUser.currency }} {{ formatNumber((payment.amount), 2) }}</td>
                <td>{{ payment.type }}</td>
                <td>
                    <div role="group" aria-label="Basic example" class="btn-group">
                      <span
                          title="Print"
                          class="btn btn-icon btn-info btn-sm"
                          @click="fire(event.Pdf, payment)"
                      >
                        <i class="i-Billing"></i>
                      </span>
                        <span
                            v-if="currentUserPermissions.includes('payment_purchases_edit')"
                            title="Edit"
                            class="btn btn-icon btn-success btn-sm"
                            @click="fire(event.EditPayment, payment)"
                        >
                        <i class="i-Pen-2"></i>
                      </span>
                        <span
                            title="Email"
                            class="btn btn-icon btn-primary btn-sm"
                            @click="fire(event.SendPaymentEmail, payment)"
                        >
                        <i class="i-Envelope"></i>
                      </span>
                        <span
                            title="SMS"
                            class="btn btn-icon btn-secondary btn-sm"
                            @click="fire(event.SendPaymentSMS,payment)"
                        >
                        <i class="i-Speach-Bubble"></i>
                      </span>
                        <span
                            v-if="currentUserPermissions.includes('payment_purchases_delete')"
                            title="Delete"
                            class="btn btn-icon btn-danger btn-sm"
                            @click="fire(event.RemovePayment, payment)"
                        >
                        <i class="i-Close"></i>
                      </span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</template>

<style scoped>

</style>
