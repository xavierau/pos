<script>
import ProductCard from "./ProductCard.vue"
import {PosEvents} from "../../../utils/FireEvent";
import * as Util from "../../../utils";
import {mapGetters} from "vuex"

export default {
    name: "NewProductCardContainer",
    components: {
        ProductCard
    },
    props: {
        perPage: {
            type: Number,
            required: true
        },

    },
    data: {
        currentPage: 1,
        focused: false
    },
    computed: {
        ...mapGetters(["products", "totalNumberOfProducts"])
    },
    mounted() {
        Fire.$on(PosEvents.SelectProduct, () => this.$refs.product_autocomplete.value = "");
    },
    methods: {
        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
        },
        formatNumber(value, decimals) {
            return Util.formatNumber(value, decimals);
        },
        handleFocus() {
            this.focused = true
        },
        handleBlur() {
            this.focused = false
        },
        search() {
            Fire.$emit(PosEvents.SearchProduct, this.search_input);
        }
    }
}
</script>

<template>
    <b-card class="list-grid">
        <b-row>
            <b-col md="6">
                <button v-b-toggle.sidebar-category class="btn btn-outline-info mt-1 btn-block">
                    <i class="i-Two-Windows"></i>
                    {{ $t('ListofCategory') }}
                </button>
            </b-col>
            <b-col md="6">
                <button v-b-toggle.sidebar-brand class="btn btn-outline-info mt-1 btn-block">
                    <i class="i-Library"></i>
                    {{ $t('ListofBrand') }}
                </button>
            </b-col>
            <!-- Product -->
            <b-col md="12" class="mt-2 mb-2">
                <div id="autocomplete" class="autocomplete">
                    <input
                        :placeholder="$t('Scan_Search_Product_by_Code_Name')"
                        @input='e => search_input = e.target.value'
                        @keyup="search"
                        @focus="handleFocus"
                        @blur="handleBlur"
                        v-model="search_input"
                        ref="product_autocomplete"
                        class="autocomplete-input"/>
                    <ul class="autocomplete-result-list" v-show="focused">
                        <li class="autocomplete-result" v-for="product_fil in product_filter"
                            @mousedown="$emit('searchMouseDown', product_fil)">{{ getResultValue(product_fil) }}
                        </li>
                    </ul>
                </div>
            </b-col>

            <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                <product-card v-for="product in products"
                              :product="product"
                              :key="product.id"
                              @click="$emit('productSelected', product)"></product-card>
            </div>
        </b-row>
        <b-row class="mb-3">
            <b-col md="12" class="mt-4">
                <b-pagination
                    @change="$emit('pageChanged')"
                    :total-rows="totalRows"
                    :per-page="perPage"
                    v-model="currentPage"
                    class="my-0 gull-pagination align-items-center"
                    align="center"
                    first-text
                    last-text
                >
                    <p class="list-arrow m-0" slot="prev-text">
                        <i class="i-Arrow-Left text-40"></i>
                    </p>
                    <p class="list-arrow m-0" slot="next-text">
                        <i class="i-Arrow-Right text-40"></i>
                    </p>
                </b-pagination>
            </b-col>
        </b-row>
    </b-card>

</template>

<style scoped>

</style>
