<template>
    <div class="main-content">
        <breadcumb :page="$t('EditPromotion')" :folder="$t('ListPromotion')"/>
        <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>

        <EditPromotionForm ref="edit_promotion" v-if="!is_loading" :promotion="promotion"
                           @submit-promotion="updatePromotion"/>
    </div>
</template>

<script>
import helperMethods from "../../../../mixins/helperMethods";
import EditPromotionForm from "../../../../components/promotions/EditPromotionForm";

export default {
    name: "Edit Promotion",
    mixins: [helperMethods],
    metaInfo: {
        title: "Edit Promotion"
    },
    components: {
        EditPromotionForm
    },
    data() {
        return {
            focused: false,
            timer: null,
            search_input: '',
            product_filter: [],
            is_loading: true,
            SubmitProcessing: false,
            warehouses: [],
            products: [],
            details: [],
            promotion: null,
        };
    },

    methods: {

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

        //---------------------- Event Get Value Search ------------------------------\\
        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
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

        //------------- Event Get Validation state
        getValidationState({dirty, validated, valid = null}) {
            return dirty || validated ? valid : null;
        },


        //------------------------------------ Get Products By Warehouse -------------------------\\

        Get_Products_By_Warehouse(id) {
            // Start the progress bar.
            this.execute(() => {
                axios.get("get_products_by_warehouse/" + id + "?stock=" + 0 + "&product_service=" + 0)
                    .then(response => {
                        this.products = response.data;
                    })
            })

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


        //--------------------------------- Update Adjustment -------------------------\\
        updatePromotion(payload) {
            this.SubmitProcessing = true;
            this.execute(() => {
                let id = this.$route.params.id;
                console.log('before update: ', payload)
                axios.put(`promotions/${id}`, payload)
                    .then(response => {
                        this.$router.push({name: "promotions"});
                        this.makeSuccessToast("Successfully_Updated");
                    })
                    .catch(error => {
                        this.makeDangerToast("InvalidData", "Failed");
                    })
                    .finally(() => this.SubmitProcessing = false)
            })

            // Start the progress bar.

        },

        //-------------------------------- detail order id -------------------------\\
        detail_order_id() {
            this.product.detail_id = 0;
            var len = this.details.length;
            this.product.detail_id = this.details[len - 1].detail_id + 1;
        },

        //---------------------- Event Select Warehouse ------------------------------\\
        Selected_Warehouse(value) {
            this.search_input = '';
            this.product_filter = [];
            this.Get_Products_By_Warehouse(value);
        },

        //---------------------------------Get Product Details ------------------------\\

        getProductDetails(product_id, variant_id) {
            axios.get("/show_product_data/" + product_id + "/" + variant_id).then(response => {
                this.product.del = 0;
                this.product.id = 0;
                this.product.product_id = response.data.id;
                this.product.name = response.data.name;
                this.product.type = "add";
                this.product.unit = response.data.unit;
                this.add_product();
            });
        },

        //---------------------------------------Get Adjustment Elements------------------------------\\
        GetElements() {
            let id = this.$route.params.id;
            axios.get(`promotions/${id}`)
                .then(({data}) => this.promotion = data.promotion)
                .finally(() => this.is_loading = false);
        }
    },

    //----------------------------- Created function-------------------
    created() {
        this.GetElements();
    }
};
</script>
