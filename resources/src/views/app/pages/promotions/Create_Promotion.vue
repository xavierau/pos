<template>
    <div class="main-content">
        <breadcumb :page="$t('Promotions:CreatePageTitle')" :folder="$t('Promotions:IndexPageTitle')"/>
        <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>
        <CreatePromotionForm @submit-promotion="submitPromotion"/>
    </div>
</template>

<script>

import helperMixin from "../../../../mixins/helperMethods";
import CreatePromotionForm from "../../../../components/promotions/CreatePromotionForm.vue";

export default {
    name: "CreatePromotion",
    mixins: [helperMixin],
    components: {
        CreatePromotionForm
    },
    metaInfo: {
        title: "Create Promotion"
    },
    data() {
        return {
            focused: false,
            timer: null,
            search_input: '',
            product_filter: [],
            is_loading: true,
            types: [],
            SubmitProcessing: false,
            warehouses: [],
            products: [],
            details: [],
            promotion: {
                id: "",
                notes: "",
                name: "",
                warehouse_id: "",
                max_applications_per_sale: 1,
                max_usage: null,
                x_product_index: "",
                y_product_index: "",
                x_product: "",
                y_product: "",
                x_qty: "",
                y_qty: "",
                start_date: null,
                end_date: null,
                date: new Date().toISOString().slice(0, 10)
            },
            symbol: ""
        };
    },
    watch: {
        "promotion.x_product_index": function (val) {
            console.log('cat change event', val)
            this.promotion.x_product = this.products[val]
        },
        "promotion.y_product_index": function (val) {
            this.promotion.y_product = this.products[val]
        },
    },
    methods: {
        updateXProduct(e) {

            console.log('cat change event')

        },

        handleFocus() {
            this.focused = true
        },

        handleBlur() {
            this.focused = false
        },

        // Search Products
        search() {

            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }

            if (this.search_input.length < 2) {
                return this.product_filter = [];
            }
            if (this.adjustment.warehouse_id != "" && this.adjustment.warehouse_id != null) {
                this.timer = setTimeout(() => {
                    const product_filter = this.products.filter(product => product.code === this.search_input || product.barcode.includes(this.search_input));
                    if (product_filter.length === 1) {
                        this.SearchProduct(product_filter[0])
                    } else {
                        this.product_filter = this.products.filter(product => {
                            return (
                                product.name.toLowerCase().includes(this.search_input.toLowerCase()) ||
                                product.code.toLowerCase().includes(this.search_input.toLowerCase()) ||
                                product.barcode.toLowerCase().includes(this.search_input.toLowerCase())
                            );
                        });
                    }
                }, 800);
            } else {
                this.makeToast(
                    "warning",
                    this.$t("SelectWarehouse"),
                    this.$t("Warning")
                );
            }


        },

        //---------------- Submit Search Product-----------------\\
        SearchProduct(result) {
            this.product = {};
            if (
                this.details.length > 0 &&
                this.details.some(detail => detail.code === result.code)
            ) {
                this.makeToast("warning", this.$t("AlreadyAdd"), this.$t("Warning"));
            } else {
                this.product.code = result.code;
                this.product.current = result.qte;
                if (result.qte < 1) {
                    this.product.quantity = result.qte;
                } else {
                    this.product.quantity = 1;
                }
                this.product.product_variant_id = result.product_variant_id;
                this.getProductDetails(result.id, result.product_variant_id);
            }
            this.search_input = '';
            this.$refs.product_autocomplete.value = "";
            this.product_filter = [];
        },

        //---------------------- Event Get Value Search ------------------------------\\
        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
        },

        //------------- Submit Validation Create Adjustment
        submitPromotion(payload) {

            console.log('create promotion payload', payload)

            this.createPromotion(payload);
        },

        //----------------Event Validation -----------------\\
        getValidationState({dirty, validated, valid = null}) {
            return dirty || validated ? valid : null;
        },

        //------ Toast
        makeToast(variant, msg, title) {
            this.$root.$bvToast.toast(msg, {
                title: title,
                variant: variant,
                solid: true
            });
        },

        //---------------------- Event Select Warehouse ------------------------------\\
        selectPromotionType(value) {
            this.search_input = '';
            this.product_filter = [];
            this.Get_Products_By_Warehouse(value);
        },

        //------------------------------------ Get Products By Warehouse -------------------------\\

        Get_Products_By_Warehouse(id) {
            // Start the progress bar.
            axios
                .get("get_Products_by_warehouse/" + id + "?stock=" + 0 + "&product_service=" + 0)
                .then(response => {
                    this.products = response.data;
                })
                .catch(error => {
                });
        },
        //----------------------------------------- Add Product To list -------------------------\\
        add_product() {
            if (this.details.length > 0) {
                this.detail_order_id();
            } else if (this.details.length === 0) {
                this.product.detail_id = 1;
            }
            this.details.push(this.product);
        },

        //-----------------------------------Verified QTY ------------------------------\\
        Verified_Qty(detail, id) {
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === id) {
                    if (isNaN(detail.quantity)) {
                        this.details[i].quantity = detail.current;
                    }

                    if (detail.type == "sub" && detail.quantity > detail.current) {
                        this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                        this.details[i].quantity = detail.current;
                    } else {
                        this.details[i].quantity = detail.quantity;
                    }
                }
            }
            this.$forceUpdate();
        },

        //----------------------------------- Increment QTY ------------------------------\\
        increment(detail, id) {
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id == id) {
                    if (detail.type == "sub") {
                        if (detail.quantity + 1 > detail.current) {
                            this.makeToast(
                                "warning",
                                this.$t("LowStock"),
                                this.$t("Warning")
                            );
                        } else {
                            this.formatNumber(this.details[i].quantity++, 2);
                        }
                    } else {
                        this.formatNumber(this.details[i].quantity++, 2);
                    }
                }
            }
            this.$forceUpdate();
        },

        //----------------------------------- Decrement QTY ------------------------------\\
        decrement(detail, id) {
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id == id) {
                    if (detail.quantity - 1 > 0) {
                        if (detail.type == "sub" && detail.quantity - 1 > detail.current) {
                            this.makeToast(
                                "warning",
                                this.$t("LowStock"),
                                this.$t("Warning")
                            );
                        } else {
                            this.formatNumber(this.details[i].quantity--, 2);
                        }
                    }
                }
            }
            this.$forceUpdate();
        },

        //------------------------------Formetted Numbers -------------------------\\
        formatNumber(number, dec) {
            const value = (typeof number === "string"
                    ? number
                    : number.toString()
            ).split(".");
            if (dec <= 0) return value[0];
            let formated = value[1] || "";
            if (formated.length > dec)
                return `${value[0]}.${formated.substr(0, dec)}`;
            while (formated.length < dec) formated += "0";
            return `${value[0]}.${formated}`;
        },

        //-----------------------------------Remove the product from the order list ------------------------------\\
        Remove_Product(id) {
            for (var i = 0; i < this.details.length; i++) {
                if (id === this.details[i].detail_id) {
                    this.details.splice(i, 1);
                }
            }
        },

        //--------------------------------- Create New Adjustment -------------------------\\
        createPromotion(data) {

            this.execute(() => {
                this.SubmitProcessing = this.is_loading = true;
                axios.post('/promotions', data)
                    .then(() => {
                        this.makeSuccessToast("Promotion Created Successfully")
                        this.$router.push({name: 'promotions'})
                    })
                    .catch(error => console.error('createPromotion: ', error))
                    .finally(() => {
                        this.SubmitProcessing = this.is_loading = false;
                    })

            })

        },

        //-------------------------------- detail order id -------------------------\\
        detail_order_id() {
            this.product.detail_id = 0;
            var len = this.details.length;
            this.product.detail_id = this.details[len - 1].detail_id + 1;
        },

        //---------------------------------Get Product Details ------------------------\\

        getProductDetails(product_id, variant_id) {
            axios.get("/show_product_data/" + product_id + "/" + variant_id).then(response => {
                this.product.product_id = response.data.id;
                this.product.name = response.data.name;
                this.product.type = "add";
                this.product.unit = response.data.unit;
                this.add_product();
            });
        },

        //---------------------------------------Get Adjustment Elements ------------------------------\\
        loadData() {
            this.execute(() => {
                this.is_loading = true
                axios.get("promotions/create")
                    .then(({data}) => {
                        this.types = data.types;
                        this.products = data.products;
                    })
                    .catch(error => console.error('loadData: ', error))
                    .finally(() => this.is_loading = false)
            })
        }
    },

//----------------------------- Created function-------------------\\

    created() {
        this.loadData();
    }
}

</script>
