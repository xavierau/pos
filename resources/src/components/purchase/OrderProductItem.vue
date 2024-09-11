<script>
import helperMixin from "../../mixins/helperMethods";
import {mapGetters} from "vuex";
import {PurchaseEvent} from "../../utils/FireEvent"

export default {
    name: "OrderProductItem",
    mixins: [helperMixin],
    props: {
        detail: {
            type: Object,
            required: true
        },
    },
    computed: {
        ...mapGetters(['currentUserPermissions', 'currentUser']),
        events: () => PurchaseEvent,
        discount_amount() {
            if (this.detail.discount_method === '1') {
                return this.formatNumber(this.detail.discount_net * this.detail.quantity, 2)
            }
            return this.formatNumber(this.detail.discount_net)
        },
    }
}
</script>

<template>
    <tr :class="{'row_deleted': detail.del === 1 || detail.no_unit === 0}">
        <td>{{ detail.detail_id }}</td>
        <td>
            <span>{{ detail.code }}</span>
            <br>
            <span class="badge badge-success">{{ detail.name }}</span>
        </td>
        <td>{{ currentUser.currency }} {{ formatNumber(detail.net_cost, 2) }}</td>
        <td>
            <span class="badge badge-outline-warning"
            >{{ detail.stock }} {{ detail.unitPurchase }}</span>
        </td>
        <td>
            <div class="quantity">
                <b-input-group>
                    <b-input-group-prepend>
                                  <span v-show="detail.no_unit !== 0"
                                        class="btn btn-primary btn-sm"
                                        @click="fire(events.DecrementItem,detail)"
                                  >-</span>
                    </b-input-group-prepend>
                    <input
                        class="form-control"
                        @change="fire(events.VerifyItemQty,{...detail, quantity: parseFloat($event.target.value)})"
                        :min="0.00"
                        v-model="detail.quantity"
                        :disabled="detail.del === 1 || detail.no_unit === 0"
                    >
                    <b-input-group-append>
                                  <span v-show="detail.no_unit !== 0"
                                        class="btn btn-primary btn-sm"
                                        @click="fire(events.IncrementItem,detail)"
                                  >+</span>
                    </b-input-group-append>
                </b-input-group>
            </div>
        </td>
        <td>{{ currentUser.currency }} {{ discount_amount }}</td>
        <td>{{ currentUser.currency }} {{ formatNumber(detail.tax * detail.quantity, 2) }}</td>
        <td>{{ currentUser.currency }} {{ detail.subtotal.toFixed(2) }}</td>
        <td v-show="detail.no_unit !== 0">
            <i v-if="currentUserPermissions && currentUserPermissions.includes('edit_product_purchase')"
               @click="fire(events.EditItem,detail)" class="i-Edit text-25 text-success"></i>
            <i @click="fire(events.DeleteItem,detail)" class="i-Close-Window text-25 text-danger"></i>
        </td>
    </tr>
</template>

<style scoped>

</style>
