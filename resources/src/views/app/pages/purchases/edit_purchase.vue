<template>
    <div class="main-content">
        <breadcumb :page="$t('EditPurchase')" :folder="$t('ListPurchases')"/>
        <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>

        <validation-observer ref="edit_purchase" v-if="!is_loading">
            <b-form @submit.prevent="submitPurchase">
                <b-row>
                    <b-col lg="12" md="12" sm="12">
                        <b-card>
                            <b-row>
                                <!-- date  -->
                                <b-col lg="4" md="4" sm="12" class="mb-3">
                                    <validation-provider
                                        name="date"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('date') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="date-feedback"
                                                type="date"
                                                v-model="purchase.date"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="OrderTax-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                                <!-- Supplier -->
                                <b-col lg="4" md="4" sm="12" class="mb-3">
                                    <validation-provider name="Supplier" :rules="{ required: true}">
                                        <b-form-group slot-scope="{ valid, errors }"
                                                      :label="$t('Supplier') + ' ' + '*'">
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                v-model="purchase.supplier_id"
                                                :reduce="label => label.value"
                                                :placeholder="$t('Choose_Supplier')"
                                                :options="suppliers.map(suppliers => ({label: suppliers.name, value: suppliers.id}))"
                                            />
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- warehouse -->
                                <b-col lg="4" md="4" sm="12" class="mb-3">
                                    <validation-provider name="warehouse" :rules="{ required: true}">
                                        <b-form-group slot-scope="{ valid, errors }"
                                                      :label="$t('warehouse') + ' ' + '*'">
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                :disabled="details.length > 0"
                                                @input="Selected_Warehouse"
                                                v-model="purchase.warehouse_id"
                                                :reduce="label => label.value"
                                                :placeholder="$t('Choose_Warehouse')"
                                                :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                                            />
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Product -->
                                <b-col md="12" class="mb-5">
                                    <h6>{{ $t('ProductName') }}</h6>

                                    <div id="autocomplete" class="autocomplete">
                                        <input
                                            :placeholder="$t('Scan_Search_Product_by_Code_Name')"
                                            @input='e => search_input = e.target.value'
                                            @keyup="search(search_input)"
                                            @focus="handleFocus"
                                            @blur="handleBlur"
                                            ref="product_autocomplete"
                                            class="autocomplete-input"/>
                                        <ul class="autocomplete-result-list" v-show="focused">
                                            <li class="autocomplete-result" v-for="product_fil in product_filter"
                                                @mousedown="searchProduct(product_fil)">
                                                {{ getResultValue(product_fil) }}
                                            </li>
                                        </ul>
                                    </div>
                                </b-col>

                                <!-- Order products  -->
                                <b-col md="12">
                                    <h5>{{ $t('order_products') }} *</h5>
                                    <OrderProductContainer :details="details"/>
                                </b-col>

                                <div class="offset-md-9 col-md-3 mt-4">
                                    <table class="table table-striped table-sm">
                                        <tbody>
                                        <tr>
                                            <td class="bold">{{ $t('OrderTax') }}</td>
                                            <td>
                                                <span>{{ currentUser.currency }} {{
                                                        purchase.tax_net.toFixed(2)
                                                    }} ({{ formatNumber(purchase.tax_rate, 2) }} %)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bold">{{ $t('Discount') }}</td>
                                            <td> {{displayCurrency(purchase.discount, {symbol: currentUser.currency}) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bold">{{ $t('Shipping') }}</td>
                                            <td>{{ currentUser.currency }} {{ purchase.shipping.toFixed(2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $t('Total') }}</span>
                                            </td>
                                            <td>
                          <span
                              class="font-weight-bold"
                          >{{ currentUser.currency }} {{ grand_total.toFixed(2) }}</span>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Order Tax  -->
                                <b-col lg="4" md="4" sm="12" class="mb-3"
                                       v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_purchase')">
                                    <validation-provider
                                        name="Order Tax"
                                        :rules="{ regex: /^\d*\.?\d*$/}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('OrderTax')">
                                            <b-input-group append="%">
                                                <b-form-input
                                                    :state="getValidationState(validationContext)"
                                                    aria-describedby="OrderTax-feedback"
                                                    label="Order Tax"
                                                    v-model.number="purchase.tax_rate"
                                                    @change="keyupUpdatePurchase('tax_rate')"
                                                ></b-form-input>
                                            </b-input-group>
                                            <b-form-invalid-feedback
                                                id="OrderTax-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Discount -->
                                <b-col lg="4" md="4" sm="12" class="mb-3"
                                       v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_purchase')">
                                    <validation-provider
                                        name="Discount"
                                        :rules="{ regex: /^\d*\.?\d*$/}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('Discount')">
                                            <b-input-group :append="currentUser.currency">
                                                <b-form-input
                                                    :state="getValidationState(validationContext)"
                                                    aria-describedby="Discount-feedback"
                                                    label="Discount"
                                                    v-model.number="purchase.discount"
                                                    @change="keyupUpdatePurchase('discount')"
                                                ></b-form-input>
                                            </b-input-group>
                                            <b-form-invalid-feedback
                                                id="Discount-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Shipping  -->
                                <b-col lg="4" md="4" sm="12" class="mb-3"
                                       v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_purchase')">
                                    <validation-provider
                                        name="Shipping"
                                        :rules="{ regex: /^\d*\.?\d*$/}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('Shipping')">
                                            <b-input-group :append="currentUser.currency">
                                                <b-form-input
                                                    :state="getValidationState(validationContext)"
                                                    aria-describedby="Shipping-feedback"
                                                    label="Shipping"
                                                    v-model.number="purchase.shipping"
                                                    @change="keyupUpdatePurchase('shipping')"
                                                ></b-form-input>
                                            </b-input-group>
                                            <b-form-invalid-feedback
                                                id="Shipping-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Status  -->
                                <b-col lg="4" md="4" sm="12" class="mb-3">
                                    <validation-provider name="Status" :rules="{ required: true}">
                                        <b-form-group slot-scope="{ valid, errors }" :label="$t('Status') + ' ' + '*'">
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                v-model="purchase.status"
                                                :reduce="label => label.value"
                                                :placeholder="$t('Choose_Status')"
                                                :options="
                            [
                              {label: 'received', value: 'received'},
                              {label: 'pending', value: 'pending'},
                               {label: 'ordered', value: 'ordered'}
                            ]"
                                            ></v-select>
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>


                                <b-col md="12">
                                    <b-form-group :label="$t('Note')">
                    <textarea
                        v-model="purchase.notes"
                        rows="4"
                        class="form-control"
                        :placeholder="$t('Afewwords')"
                    ></textarea>
                                    </b-form-group>
                                </b-col>
                                <b-col md="12">
                                    <b-form-group>
                                        <b-button variant="primary" @click="submitPurchase"
                                                  :disabled="submit_processing"><i
                                            class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                                        </b-button>
                                        <div v-once class="typo__p" v-if="submit_processing">
                                            <div class="spinner sm spinner-primary mt-3"></div>
                                        </div>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                </b-row>
            </b-form>
        </validation-observer>

        <!-- Show Modal Update Detail Product -->
        <validation-observer ref="Update_Detail_Purchase">
            <b-modal hide-footer size="lg" id="form_Update_Detail" :title="detail.name">
                <UpdateProductItemForm v-if="detail"
                                       :detail="detail"
                                       :is_loading="submit_processing_detail"
                                       @submit="submitUpdateDetail"/>
            </b-modal>
        </validation-observer>
    </div>
</template>

<script>
import {mapGetters} from "vuex";
import NProgress from "nprogress";
import OrderProductContainer from "../../../../components/purchase/OrderProductContainer";
import UpdateProductItemForm from "../../../../components/purchase/UpdateProductItemForm.vue";
import helperMixin from "../../../../mixins/helperMethods";
import {PurchaseEvent} from "../../../../utils/FireEvent";
import {isNonZeroNumber, isNotANumber} from "../../../../utils";

export default {
    mixins: [helperMixin],
    components: {
        OrderProductContainer,
        UpdateProductItemForm
    },
    metaInfo: {
        title: "Edit Purchase"
    },
    data() {
        return {
            registerEvents: {
                [PurchaseEvent.IncrementItem]: detail => this.increment(detail, detail.detail_id),
                [PurchaseEvent.DecrementItem]: detail => this.decrement(detail, detail.detail_id),
                [PurchaseEvent.VerifyItemQty]: detail => this.verifyQty(detail, detail.detail_id),
                [PurchaseEvent.DeleteItem]: detail => this.deleteProductDetail(detail.detail_id),
                [PurchaseEvent.EditItem]: detail => this.modalUpdateDetail(detail),
            },
            focused: false,
            timer: null,
            search_input: '',
            product_filter: [],
            is_loading: true,
            submit_processing: false,
            submit_processing_detail: false,
            warehouses: [],
            suppliers: [],
            products: [],
            details: [],
            detail: {},
            purchases: [],
            purchase: {
                id: "",
                status: "",
                date: "",
                notes: "",
                supplier_id: "",
                warehouse_id: "",
                tax_rate: 0,
                tax_net: 0,
                shipping: 0,
                discount: 0
            },
            total: 0,
            grand_total: 0,
            product: {
                id: "",
                code: "",
                stock: "",
                qty: 1,
                discount: "",
                discount_net: "",
                discount_method: "",
                name: "",
                no_unit: "",
                unitPurchase: "",
                purchase_unit_id: "",
                net_cost: "",
                total_cost: "",
                unit_cost: "",
                subtotal: "",
                product_id: "",
                detail_id: "",
                tax: "",
                tax_percent: "",
                tax_method: "",
                product_variant_id: "",
                del: "",
                is_imei: "",
                imei_number: "",
            }
        };
    },
    methods: {
        handleFocus() {
            this.focused = true
        },
        handleBlur() {
            this.focused = false
        },
        //--- Submit Validate Update Purchase
        submitPurchase() {
            this.$refs.edit_purchase.validate().then(success => {
                if (!success) {
                    this.makeDangerToast("Please_fill_the_form_correctly", "Failed");
                } else {
                    this.updatePurchase();
                }
            });
        },
        //---Submit Validation Update Detail
        submitUpdateDetail() {
            this.$refs.Update_Detail_Purchase.validate()
                .then(success => {
                    if (!success) {
                        return;
                    } else {
                        this.Update_Detail();
                    }
                });
        },

        //------  Show Modal Update Detail Product
        modalUpdateDetail(detail) {
            this.execute(() => {
                this.detail = {};
                this.detail.name = detail.name;
                this.detail.detail_id = detail.detail_id;
                this.detail.unit_cost = detail.unit_cost;
                this.detail.tax_method = detail.tax_method;
                this.detail.discount_method = detail.discount_method;
                this.detail.discount = detail.discount;
                this.detail.qty = detail.qty;
                this.detail.tax_percent = detail.tax_percent;
                this.detail.is_imei = detail.is_imei;
                this.detail.imei_number = detail.imei_number;
                this.$nextTick(() => {
                    this.$bvModal.show("form_Update_Detail");
                })
            })

        },

        //------ Submit Detail Product

        Update_Detail() {
            NProgress.start();
            NProgress.set(0.1);
            this.submit_processing_detail = true;
            for (let i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === this.detail.detail_id) {
                    this.details[i].tax_percent = this.detail.tax_percent;
                    this.details[i].unit_cost = this.detail.unit_cost;
                    this.details[i].qty = this.detail.qty;
                    this.details[i].tax_method = this.detail.tax_method;
                    this.details[i].discount_method = this.detail.discount_method;
                    this.details[i].discount = this.detail.discount;
                    this.details[i].imei_number = this.detail.imei_number;

                    if (this.details[i].discount_method == "2") {
                        //Fixed
                        this.details[i].discount_net = this.detail.discount;
                    } else {
                        //Percentage %
                        this.details[i].discount_net = parseFloat(
                            (this.detail.unit_cost * this.details[i].discount) / 100
                        );
                    }

                    if (this.details[i].tax_method == "1") {
                        //Exclusive
                        this.details[i].net_cost = parseFloat(
                            this.detail.unit_cost - this.details[i].discount_net
                        );

                        this.details[i].tax = parseFloat(
                            (this.detail.tax_percent *
                                (this.detail.unit_cost - this.details[i].discount_net)) /
                            100
                        );
                    } else {
                        //Inclusive
                        this.details[i].net_cost = parseFloat(
                            (this.detail.unit_cost - this.details[i].discount_net) /
                            (this.detail.tax_percent / 100 + 1)
                        );

                        this.details[i].tax = parseFloat(this.detail.unit_cost - this.details[i].net_cost - this.details[i].discount_net);
                    }

                    this.$forceUpdate();
                }
            }
            this.calculateTotal();

            this.submit_processing_detail = false;
            this.$nextTick(() => {
                this.$bvModal.hide("form_Update_Detail");
            })

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
            if (isNonZeroNumber(this.purchase.warehouse_id)) {
                this.timer = setTimeout(() => {
                    const product_filter = this.products.filter(product => product.code === this.search_input || product.barcode.includes(this.search_input));
                    if (product_filter.length === 1) {
                        this.searchProduct(product_filter[0])
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
                this.makeWarningToast("SelectWarehouse");
            }
        },

        //------  get Result Value Search Products

        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
        },

        //------  Submit Search Products
        searchProduct(result) {
            this.product = {};
            if (
                this.details.length > 0 &&
                this.details.some(detail => detail.code === result.code)
            ) {
                this.makeWarningToast("AlreadyAdd");
            } else {
                this.product.code = result.code;
                this.product.qty = 1;
                this.product.no_unit = 1;
                this.product.stock = result.qte_purchase;
                this.product.product_variant_id = result.product_variant_id;
                this.getProductDetails(result.id, result.product_variant_id);
            }

            this.search_input = '';
            this.$refs.product_autocomplete.value = "";
            this.product_filter = [];
        },

        //---------------------- Event Select Warehouse ------------------------------\\
        Selected_Warehouse(value) {
            this.search_input = '';
            this.product_filter = [];
            this.getProductsByWarehouse(value);
        },

        //------------------------------------ Get Products By Warehouse -------------------------\\

        getProductsByWarehouse(id) {
            // Start the progress bar.
            this.execute(() => {
                axios.get("get_products_by_warehouse/" + id + "?stock=" + 0 + "&product_service=" + 0)
                    .then(response => this.products = response.data)
                    .catch(error => {
                        console.error(error)
                        this.makeWarningToast("Get product by warehouse failed")
                    });
            })
        },

        //----------------------------------------- Add Product -------------------------\\
        addProduct() {
            if (this.details.length > 0) {
                this.getLastDetailId();
            } else if (this.details.length === 0) {
                this.product.detail_id = 1;
            }
            this.details.push(this.product);

            if (this.product.is_imei) {
                this.modalUpdateDetail(this.product);
            }
        },

        //-----------------------------------Verified QTY ------------------------------\\
        verifyQty(detail, id) {
            for (let i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === id) {
                    if (isNaN(detail.qty)) {
                        this.details[i].qty = 1;
                    }
                    this.calculateTotal();
                    this.$forceUpdate();
                }
            }
        },

        //-----------------------------------increment QTY ------------------------------\\

        increment(detail, id) {
            for (let i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === id) {
                    this.formatNumber(this.details[i].qty++, 2);
                }
            }
            this.$forceUpdate();
            this.calculateTotal();
        },

        //-----------------------------------decrement QTY ------------------------------\\

        decrement(detail, id) {
            for (let i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === id) {
                    if (detail.qty - 1 > 0) {
                        this.formatNumber(this.details[i].qty--, 2);
                    }
                }
            }
            this.$forceUpdate();
            this.calculateTotal();
        },
        keyupUpdatePurchase(field) {
            if (isNotANumber(this.purchase[field])) {
                console.log('update: ', this.purchase[field])
                this.purchase[field] = 0;
            }
            this.calculateTotal();
        },

        //-----------------------------------------Calcul Total ------------------------------\\
        calculateTotal() {
            this.total = 0;
            for (let i = 0; i < this.details.length; i++) {
                let tax = this.details[i].tax * this.details[i].qty;
                this.details[i].subtotal = parseFloat(this.details[i].qty * this.details[i].net_cost + tax);

                console.log('subtotal: ', this.details[i], this.details[i].subtotal, this.details[i].qty, this.details[i].net_cost , this.details[i].tax)

                this.total += parseFloat(this.details[i].subtotal);
            }

            console.log('calculate total: ', this.total, this.purchase.discount)

            const total_without_discount = parseFloat(
                this.total - this.purchase.discount
            );
            this.purchase.tax_net = parseFloat(
                (total_without_discount * this.purchase.tax_rate) / 100
            );
            this.grand_total = parseFloat(
                total_without_discount + this.purchase.tax_net + this.purchase.shipping
            );

            let grand_total = this.grand_total.toFixed(2);
            this.grand_total = parseFloat(grand_total);
        },

        //-----------------------------------Delete Detail Product ------------------------------\\
        deleteProductDetail(id) {
            for (let i = 0; i < this.details.length; i++) {
                if (id === this.details[i].detail_id) {
                    this.details.splice(i, 1);
                }
            }
            this.calculateTotal();
        },

        //-----------------------------------Verified Detail Qty If Null ------------------------------\\

        verifiedForm() {
            if (this.details.length <= 0 || this.hasInvalidDetailQty()) {
                this.makeWarningToast("AddProductToList");
                return false;
            }

            return true;
        },
        hasInvalidDetailQty() {
            return this.details.includes(detail => isNotANumber(detail.qty) || detail.qty <= 0)
        },

        //--------------------------------- Update Purchase -------------------------\\
        updatePurchase() {
            if (this.verifiedForm()) {
                this.execute(() => {
                    this.submit_processing = true;
                    // Start the progress bar.
                    let id = this.$route.params.id;
                    axios.put(`purchases/${id}`, {
                        date: this.purchase.date,
                        supplier_id: this.purchase.supplier_id,
                        warehouse_id: this.purchase.warehouse_id,
                        status: this.purchase.status,
                        notes: this.purchase.notes,
                        tax_rate: this.purchase.tax_rate ? this.purchase.tax_rate : 0,
                        tax_net: this.purchase.tax_net ? this.purchase.tax_net : 0,
                        discount: this.purchase.discount ? this.purchase.discount : 0,
                        shipping: this.purchase.shipping ? this.purchase.shipping : 0,
                        grand_total: this.grand_total,
                        details: this.details
                    })
                        .then(() => {
                            this.makeSuccessToast("Update.TitlePurchase");
                            this.$router.push({name: "index_purchases"});
                        })
                        .catch(error => {
                            console.error("updatePurchase: ", error)
                            this.makeDangerToast("InvalidData", "Failed");
                        })
                        .finally(() => {
                            this.submit_processing = false;
                        })
                })
            }
        },

        //-------------------------------- Get Last Detail Id -------------------------\\
        getLastDetailId() {
            this.product.detail_id = 0;
            let len = this.details.length;
            this.product.detail_id = this.details[len - 1].detail_id + 1;
        },

        //---------------------------------get Product Details ------------------------\\

        getProductDetails(product_id, variant_id) {
            axios.get("/show_product_data/" + product_id + "/" + variant_id).then(response => {
                this.product.del = 0;
                this.product.id = 0;
                this.product.discount = 0;
                this.product.discount_net = 0;
                this.product.discount_method = "2";
                this.product.product_id = response.data.id;
                this.product.name = response.data.name;
                this.product.net_cost = response.data.net_cost;
                this.product.unit_cost = response.data.unit_cost;
                this.product.tax = response.data.tax_cost;
                this.product.tax_method = response.data.tax_method;
                this.product.tax_percent = response.data.tax_percent;
                this.product.unitPurchase = response.data.unitPurchase;
                this.product.purchase_unit_id = response.data.purchase_unit_id;
                this.product.is_imei = response.data.is_imei;
                this.product.imei_number = '';
                this.addProduct();
                this.calculateTotal();
            });
        },

        //---------------------------------------Get Elements Purchase ------------------------------\\
        getElements() {
            let id = this.$route.params.id;
            axios.get(`purchases/${id}/edit`)
                .then(response => {
                    this.purchase = response.data.purchase;
                    this.details = response.data.details;
                    this.suppliers = response.data.suppliers;
                    this.warehouses = response.data.warehouses;
                    this.getProductsByWarehouse(this.purchase.warehouse_id);
                    this.calculateTotal();
                })
                .catch(error => console.log('getElements: ', error))
                .finally(() => this.is_loading = false)
        }
    },

    //----------------------------- Created function-------------------
    created() {
        this.getElements();
    },
};
</script>
