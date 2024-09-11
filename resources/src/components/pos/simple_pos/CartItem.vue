<script>
import {mapActions, mapGetters} from "vuex";
import Util from "./../../../utils";
import {PosEvents} from "../../../utils/FireEvent";
import {CartItem} from "../../../entities/pos/CartItem";
import helperMethods from "../../../mixins/helperMethods";

export default {
    name: "NewCartItem",
    mixins: [helperMethods],
    props: {
        item: {
            type: CartItem,
            required: true
        },
    },
    computed: {
        ...mapGetters(["currentUser", "currentUserPermissions"]),
    },
    methods: {
        ...mapActions(["deleteCartItem", "setCartItemQuantity"]),
        formatNumber(value, decimals) {
            return Util.formatNumber(value, decimals);
        },
        update() {
            Fire.$emit(PosEvents.UpdateDetail, this.item);
        },
        changeQty(e) {
            const value = parseInt(e.target.value);
            console.log('change qty', value)
            if (value > 0) {
                const item = {...this.item, quantity: value};
                this.setCartItemQuantity(item);
            } else {
                this.makeWarningToast('Quantity must be greater than 0')
            }
        },
        increment() {
            const item = {...this.item, quantity: 1};
            console.log('increment', item)
            this.$store.commit("incrementItem", item)
        },
        decrement() {
            const item = {...this.item, quantity: 1};
            console.log('decrement', item)
            this.$store.commit("decrementItem", item)
        },
        deleteDetail() {
            this.deleteCartItem(this.item);
        },
    }

}
</script>

<template>
    <tr>
        <!--   Name and code     -->
        <td>
            <span v-if="item.promotion_id">{{ item.promotion_name }}</span>
            <span v-else>{{ item.code }}</span>
            <br>
            <span class="badge badge-success">{{ item.name }}</span>
            <span v-if="item.promotion_id" class="badge badge-info"><small>Promotion Item</small></span>
            <i v-if="!item.promotion_id && (currentUserPermissions && currentUserPermissions.includes('edit_product_sale'))"
               @click="update(item)"
               class="i-Edit text-success cursor-pointer"></i>
        </td>

        <!--   Price     -->
        <td>{{ currentUser.currency }} {{ formatNumber(item.getActivePrice() ?? 0.0, 2) }}
        </td>

        <!--   Qty    -->
        <td>
            <div class="quantity" v-if="!(item.has_imei || item.promotion_id)">
                <b-input-group>
                    <b-input-group-prepend>
                        <span class="btn btn-primary btn-sm"
                              @click="decrement(item)">-</span>
                    </b-input-group-prepend>

                    <input class="form-control" @change="changeQty" :value="item.quantity" type="number"/>

                    <b-input-group-append>
                                        <span class="btn btn-primary btn-sm"
                                              @click="increment(item)">+</span>
                    </b-input-group-append>
                </b-input-group>
            </div>
        </td>

        <!--   Subtotal    -->
        <td class="text-center">{{ currentUser.currency }}
            {{ item.getNetTotal().toFixed(2) }}
        </td>

        <!--   Delete    -->
        <td>
            <a
                @click="deleteDetail(item)"
                title="Delete"
            >
                <i class="i-Close-Window text-25 text-danger cursor-pointer"></i>
            </a>
        </td>
    </tr>
</template>

<style scoped>

</style>
