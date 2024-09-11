<script>
import {mapGetters} from "vuex";
import Util from "./../../utils";

export default {
    name: "CartItem",
    props: {
        detail: {
            type: Object,
            required: true
        },
    },
    computed: {
        ...mapGetters(["currentUser", "currentUserPermissions"]),
    },
    methods: {
        formatNumber(value, decimals) {
            return Util.formatNumber(value, decimals);
        },
        update() {
            Fire.$emit("POS:Detail:Update", this.detail);
        },
        increment() {
            const detail = {...this.detail, quantity: 1};
            console.log('increment', detail)
            this.$store.commit("incrementItem", detail)
        },
        decrement() {
            const detail = {...this.detail, quantity: 1};
            this.$store.commit("decrementItem", detail)
        },
        deleteDetail() {
            Fire.$emit("POS:Detail:Delete", this.detail);
        },
        verifyQty() {
            Fire.$emit("POS:Detail:VerifyQty", this.detail);
        },
    }
}
</script>

<template>
    <tr>
        <!--   Name and code     -->
        <td>
            <span>{{ detail.code }}</span>
            <br>
            <span class="badge badge-success">{{ detail.name }}</span>
            <span v-if="detail.promotion_id" class="badge badge-info"><small>Promotion Item</small></span>
            <i v-if="!detail.promotion_id && (currentUserPermissions && currentUserPermissions.includes('edit_product_sale'))"
               @click="update(detail)"
               class="i-Edit text-success cursor-pointer"></i>
        </td>

        <!--   Price     -->
        <td>{{ currentUser.currency }} {{ formatNumber(detail.net_price ?? 0.0, 2) }}
        </td>

        <!--   Qty    -->
        <td>
            <div class="quantity" v-if="!(detail.is_imei || detail.promotion_id)">
                <b-input-group>
                    <b-input-group-prepend>
                        <span class="btn btn-primary btn-sm"
                              @click="decrement(detail ,detail.detail_id)"
                        >-</span>
                    </b-input-group-prepend>

                    <input
                        class="form-control"
                        @keyup="verifyQty(detail,detail.detail_id)"
                        type="number"
                        :value="detail.quantity"
                    >

                    <b-input-group-append>
                                        <span
                                            class="btn btn-primary btn-sm"
                                            @click="increment()"
                                        >+</span>
                    </b-input-group-append>
                </b-input-group>
            </div>
        </td>

        <!--   Subtotal    -->
        <td class="text-center">{{ currentUser.currency }}
            {{ detail.getActivePrice().toFixed(2) }}
        </td>

        <!--   Delete    -->
        <td>
            <a
                @click="deleteDetail()"
                title="Delete"
            >
                <i class="i-Close-Window text-25 text-danger cursor-pointer"></i>
            </a>
        </td>
    </tr>
</template>

<style scoped>

</style>
