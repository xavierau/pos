<script>
import Util from "./../../utils";
import {mapGetters} from "vuex";
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "ProductCard",
    mixins: [helperMixin],
    props: {
        product: {
            type: Object,
            required: true
        },
    },
    computed: {
        ...mapGetters(["currentUser", "currentUserPermissions"]),
    },
    methods: {
        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
        },
        formatNumber(value, decimals) {
            return Util.formatNumber(value, decimals);
        },
        selectProduct() {
            Fire.$emit("POS:Product:Select", this.product);
        }
    }
}
</script>

<template>
    <div class="card o-hidden bd-highlight m-1" @click="selectProduct">
        <div class="list-thumb d-flex">
            <img alt :src="'/images/products/'+product.image">
        </div>
        <div class="flex-grow-1 d-bock">
            <div class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center">
                <div class="w-40 w-sm-100 item-title">{{ product.name }}</div>
                <p class="text-muted text-small w-15 w-sm-100 mb-2">{{ product.code }}</p>

                <span v-if="product.not_selling"
                      class="badge badge-secondary w-15 w-sm-100 mb-2">not selling</span>

                <!-- if there is discounted price, show original price-->
                <span v-if="product.discounted_price"
                      class="badge badge-warning w-15 w-sm-100 mb-2">{{
                        displayCurrency(product.price, {symbol: currentUser.currency})
                    }}</span>
                <span class="badge badge-primary w-15 w-sm-100 mb-2">{{
                        displayCurrency(product.net_price, {symbol: currentUser.currency})
                    }}</span>

                <p v-if="product.product_type != 'is_service'"
                   class="m-0 text-muted text-small w-15 w-sm-100 d-none d-lg-block item-badges">
                    <span class="badge badge-info">{{ formatNumber(product.qte_sale, 2) }} {{ product.unitSale }}</span>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
