<template>
    <div class="pos_page">
        <div class="container-fluid p-0 app-admin-wrap layout-sidebar-large clearfix" id="pos">
            <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>
            <b-row v-if="!is_loading">
                <!-- Card Left Panel Details Sale-->
                <b-col md="5">
                    <b-card no-body class="card-order">
                        <div class="main-header">
                            <div class="logo">
                                <router-link to="/app/dashboard">
                                    <img :src="'/images/'+currentUser.logo" alt width="60" height="60">
                                </router-link>
                            </div>
                            <div class="mx-auto"></div>

                            <div class="header-part-right">
                                <!-- Full screen toggle -->
                                <i class="i-Full-Screen header-icon d-none d-sm-inline-block"
                                   @click="handleFullScreen"
                                ></i>
                                <!-- Grid menu Dropdown -->
                                <LanguageSwitcher/>
                                <!-- User avatar dropdown -->
                                <div class="dropdown">
                                    <b-dropdown
                                        id="dropdown-1"
                                        text="Dropdown Button"
                                        class="m-md-2 user col align-self-end"
                                        toggle-class="text-decoration-none"
                                        no-caret
                                        variant="link"
                                        right
                                    >
                                        <template slot="button-content">
                                            <img
                                                :src="'/images/avatar/'+currentUser.avatar"
                                                id="userDropdown"
                                                alt
                                                data-toggle="dropdown"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                            >
                                        </template>

                                        <div class="dropdown-menu-left" aria-labelledby="userDropdown">
                                            <div class="dropdown-header">
                                                <i class="i-Lock-User mr-1"></i>
                                                <span>{{ currentUser.username }}</span>
                                            </div>
                                            <router-link to="/app/profile" class="dropdown-item">{{ $t('profil') }}
                                            </router-link>
                                            <router-link
                                                v-if="currentUserPermissions && currentUserPermissions.includes('setting_system')"
                                                to="/app/settings/System_settings"
                                                class="dropdown-item"
                                            >{{ $t('Settings') }}
                                            </router-link>
                                            <a class="dropdown-item" href="#"
                                               @click.prevent="logout">{{ $t('logout') }}</a>
                                        </div>
                                    </b-dropdown>
                                </div>
                            </div>
                        </div>
                        <validation-observer ref="create_pos">
                            <b-form @submit.prevent="submitPos">
                                <b-card-body>
                                    <b-row>
                                        <!-- Customer -->
                                        <b-col lg="12" md="12" sm="12">
                                            <validation-provider name="Customer" :rules="{ required: true}">
                                                <b-input-group slot-scope="{ valid, errors }" class="input-customer">
                                                    <v-select
                                                        :class="{'is-invalid': !!errors.length}"
                                                        :state="errors[0] ? false : (valid ? true : null)"
                                                        :value="client_id"
                                                        @input="onClientSelected"
                                                        :reduce="label => label.value"
                                                        :placeholder="$t('Choose_Customer')"
                                                        class="w-100"
                                                        :options="clients.map(clients => ({label: clients.name, value: clients.id}))"
                                                    />
                                                    <b-input-group-append>
                                                        <b-button variant="primary" @click="newClient()">
                              <span>
                                <i class="i-Add-User"></i>
                              </span>
                                                        </b-button>
                                                    </b-input-group-append>
                                                </b-input-group>
                                            </validation-provider>
                                        </b-col>

                                        <!-- warehouse -->
                                        <b-col lg="12" md="12" sm="12">
                                            <validation-provider name="warehouse" :rules="{ required: true}">
                                                <b-form-group slot-scope="{ valid, errors }" class="mt-2">
                                                    <v-select
                                                        :class="{'is-invalid': !!errors.length}"
                                                        :state="errors[0] ? false : (valid ? true : null)"
                                                        :disabled="items.length > 0"
                                                        :value="warehouse_id"
                                                        @input="selectedWarehouse"
                                                        :reduce="label => label.value"
                                                        :placeholder="$t('Choose_Warehouse')"
                                                        :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                                                    />
                                                </b-form-group>
                                            </validation-provider>
                                        </b-col>

                                        <!-- Details Product  -->
                                        <b-col md="12" class="mt-2">
                                            <Cart/>
                                        </b-col>
                                    </b-row>

                                    <!-- Calculate Grand Total -->
                                    <div class="footer_panel">
                                        <b-row>
                                            <b-col md="12">
                                                <div class="grandtotal">
                                                    <span>{{
                                                            $t("Total_Payable")
                                                        }} : {{
                                                            displayCurrency(cartNetTotal, {symbol: currentUser.currency})
                                                        }}</span>
                                                </div>
                                            </b-col>

                                            <!-- Discount -->
                                            <b-col lg="6" md="6" sm="12"
                                                   v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_sale')">
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
                                                                type="number"
                                                                :value="discount"
                                                                @change="setDiscount"
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
                                            <b-col lg="6" md="6" sm="12"
                                                   v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_sale')">
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
                                                                type="number"
                                                                :value="shipping"
                                                                @change="setShipping"
                                                            ></b-form-input>
                                                        </b-input-group>

                                                        <b-form-invalid-feedback
                                                            id="Shipping-feedback"
                                                        >{{ validationContext.errors[0] }}
                                                        </b-form-invalid-feedback>
                                                    </b-form-group>
                                                </validation-provider>
                                            </b-col>
                                        </b-row>
                                    </div>


                                </b-card-body>
                            </b-form>
                        </validation-observer>

                        <!-- Update Detail Product -->
                        <b-modal hide-footer size="lg"
                                 id="POS:Modal:UpdateDetail"
                                 :title="detail.name">
                            <update-detail-form v-if="detail"
                                                :units="units"
                                                :detail="detail"
                                                v-on:update-detail="updateDetail"
                            ></update-detail-form>
                        </b-modal>
                    </b-card>
                </b-col>

                <!-- Card right Of Products -->
                <b-col md="7">
                    <ProductCardContainer
                        :current-page="product_current_page"
                        :per-page="product_per_page"
                    ></ProductCardContainer>
                </b-col>

                <!-- Sidebar Brand -->
                <b-sidebar id="sidebar-brand" :title="$t('ListofBrand')" bg-variant="white" right shadow>
                    <div class="px-3 py-2">
                        <b-row>
                            <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                                <div
                                    @click="productsByBrand()"
                                    :class="{ 'brand-Active' : brand_id === ''}"
                                    class="card o-hidden bd-highlight m-1"
                                >
                                    <div class="list-thumb d-flex">
                                        <img alt :src="'/images/no-image.png'">
                                    </div>
                                    <div class="flex-grow-1 d-bock">
                                        <div
                                            class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                                        >
                                            <div class="item-title">{{ $t('All_Brand') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="card o-hidden bd-highlight m-1"
                                    v-for="brand in paginated_brands"
                                    :key="brand.id"
                                    @click="productsByBrand(brand.id)"
                                    :class="{ 'brand-Active' : brand.id === brand_id}"
                                >
                                    <img alt :src="'/images/brands/'+brand.image">
                                    <div class="flex-grow-1 d-bock">
                                        <div
                                            class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center">
                                            <div class="item-title">{{ brand.name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-row>

                        <b-row>
                            <b-col md="12" class="mt-4">
                                <b-pagination
                                    @change="BrandonPageChanged"
                                    :total-rows="numberOfBrands"
                                    :per-page="brand_per_page"
                                    v-model="brand_current_page"
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
                    </div>
                </b-sidebar>

                <!-- Sidebar category -->
                <b-sidebar
                    id="sidebar-category"
                    :title="$t('ListofCategory')"
                    bg-variant="white"
                    right
                    shadow
                >
                    <div class="px-3 py-2">
                        <b-row>
                            <div class="col-md-12 d-flex flex-row flex-wrap bd-highlight list-item mt-2">
                                <div @click="getProductsByCategory()"
                                     :class="{ 'brand-Active' : category_id == ''}"
                                     class="card o-hidden bd-highlight m-1">
                                    <div class="list-thumb d-flex">
                                        <img alt :src="'/images/no-image.png'">
                                    </div>
                                    <div class="flex-grow-1 d-bock">
                                        <div
                                            class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center"
                                        >
                                            <div class="item-title">{{ $t('All_Category') }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="card o-hidden bd-highlight m-1"
                                    v-for="category in paginated_categories"
                                    :key="category.id"
                                    @click="getProductsByCategory(category.id)"
                                    :class="{ 'brand-Active' : category.id === category_id}">
                                    <img alt :src="'/images/no-image.png'">
                                    <div class="flex-grow-1 d-bock">
                                        <div
                                            class="card-body align-self-center d-flex flex-column justify-content-between align-items-lg-center">
                                            <div class="item-title">{{ category.name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </b-row>

                        <b-row>
                            <b-col md="12" class="mt-4">
                                <b-pagination
                                    @change="categoryOnPageChanged"
                                    :total-rows="numberOfCategories"
                                    :per-page="category_per_page"
                                    v-model="category_current_page"
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
                    </div>
                </b-sidebar>


                <div class="pos-button-actions"
                     style="display: flex;margin-top: 10px;bottom: 0px;margin-left: 29px;width: 100%; flex-wrap: wrap; ">
                    <b-button style="width: auto;margin-bottom: 8px;"
                              @click="reset_Pos()"
                              variant="danger ripple btn-rounded mr-1"
                    >
                        <i class="i-Power-2"></i>
                        {{ $t("Reset") }}
                    </b-button>

                    <b-button style="width: auto;margin-bottom: 8px;"
                              @click="submitPos()" variant="success ripple mr-1 btn-rounded">
                        <i class="i-Checkout"></i>
                        {{ $t("payNow") }}
                    </b-button>

                    <b-button style="width: auto;margin-bottom: 8px;"
                              @click="submitDraft()"
                              :disabled="draft_processing"
                              variant="primary ripple mr-1 btn-rounded">
                        <i class="i-Sand-watch"></i>
                        Draft
                    </b-button>

                    <b-button style="width: auto;margin-bottom: 8px;"
                              @click="showDraftSales()"
                              variant="light ripple mr-1 btn-rounded">
                        <i class="i-Alarm-Clock"></i>
                        Recent Drafts
                    </b-button>
                </div>
            </b-row>
        </div>

        <!-- Modal Show Invoice POS-->
        <b-modal hide-footer size="sm" scrollable id="Show_invoice" :title="$t('invoice_pos')">
            <component ref="receipt" :is="invoice_template" :invoice-data="invoice_pos"></component>
            <button @click="printPos()" class="btn btn-outline-primary">
                <i class="i-Billing"></i>{{ $t('print') }}
            </button>
        </b-modal>

        <!-- Modal Add Payment-->
        <b-modal hide-footer size="lg" id="Add_Payment" :title="$t('AddPayment')">
            <CreatePaymentForm/>
        </b-modal>

        <!-- Modal New Customer-->
        <b-modal hide-footer size="lg" id="New_Customer" :title="$t('Add')">
            <CreateCustomerForm :is-loading="form_status.creating_customer"/>
        </b-modal>

        <!--                <b-modal-->
        <!--                    hide-footer-->
        <!--                    size="lg"-->
        <!--                    id="show_draft_sales"-->
        <!--                    title="Draft Sales"-->
        <!--                >-->
        <!--                    <vue-good-table-->
        <!--                        mode="remote"-->
        <!--                        :columns="draft_sales_columns"-->
        <!--                        :totalRows="number_of_drafted_sales"-->
        <!--                        :rows="draft_sales"-->
        <!--                        @on-page-change="onPageChange"-->
        <!--                        @on-per-page-change="onPerPageChange"-->

        <!--                        :pagination-options="{-->
        <!--            enabled: true,-->
        <!--            mode: 'records',-->
        <!--            nextLabel: 'next',-->
        <!--            prevLabel: 'prev',-->
        <!--          }"-->
        <!--                        styleClass="tableOne table-hover vgt-table"-->
        <!--                    >-->


        <!--                        <template slot="table-row" slot-scope="props">-->
        <!--                            <span v-if="props.column.field == 'actions'">-->
        <!--                                <router-link-->
        <!--                                    v-b-tooltip.hover-->
        <!--                                    title="Edit"-->
        <!--                                    :to="{ name:'pos_draft', params: { id: props.row.id } }"-->
        <!--                                >-->
        <!--                                  <i class="i-Edit text-25 text-success"></i>-->
        <!--                                </router-link>-->
        <!--                                <a @click="removeDraftSale(props.row.id)"-->
        <!--                                   v-b-tooltip.hover-->
        <!--                                   title="Delete"-->
        <!--                                   class="cursor-pointer"-->
        <!--                                >-->
        <!--                                  <i class="i-Close-Window text-25 text-danger"></i>-->
        <!--                                </a>-->
        <!--                              </span>-->
        <!--                        </template>-->
        <!--                    </vue-good-table>-->
        <!--                </b-modal>-->
    </div>
</template>

<script>
import NProgress from "nprogress";
import {mapActions, mapGetters} from "vuex";
import vueEasyPrint from "vue-easy-print";
import VueBarcode from "vue-barcode";
import FlagIcon from "vue-flag-icon";
import Util from "./../../../utils";
import {posClient} from "../../../utils/client";
import Cart from "../../../components/pos/simple_pos/Cart";
import UpdateDetailForm from "../../../components/pos/UpdateDetailForm";
import CreateCustomerForm from "../../../components/pos/CreateCustomerForm";
import CreatePaymentForm from "../../../components/pos/simple_pos/CreatePaymentForm";
import DefaultInvoiceTemplate from "../../../components/pos/invoices/templates/default/index";
import LanguageSwitcher from "../../../components/LanguageSwitcher";
import helperMixin from "../../../mixins/helperMethods";
import {PosEvents} from "../../../utils/FireEvent";
import ProductCardContainer from "../../../components/pos/simple_pos/ProductCardContainer.vue"
import posModule from "../../../store/modules/pos";

export default {
    name: "NewPOS",
    mixins: [helperMixin],
    components: {
        Cart,
        vueEasyPrint,
        barcode: VueBarcode,
        FlagIcon,
        ProductCardContainer,
        UpdateDetailForm,
        DefaultInvoiceTemplate,
        LanguageSwitcher,
        CreateCustomerForm,
        CreatePaymentForm
    },
    metaInfo: {title: "POS"},
    data() {
        return {
            form_status: {
                creating_customer: false
            },
            invoice_template: "DefaultInvoiceTemplate",
            payment_processing: false,
            draft_processing: false,
            saved_payment_methods: [],
            has_saved_payment_method: false,
            use_saved_payment_method: false,
            selected_card: null,
            card_id: '',
            is_new_credit_card: false,
            submit_showing_credit_card: false,
            number_of_drafted_sales: "",
            draft_sales: [],
            limit: 10,
            server_params: {
                sort: {
                    field: "id",
                    type: "desc"
                },
                page: 1,
                perPage: 10
            },
            client_name: '',
            focused: false,
            timer: null,
            search_input: '',
            product_filter: [],
            is_loading: true,
            load_product: true,
            total: 0,
            ref: "",
            clients: [],
            units: [],
            warehouses: [],
            detail: {},
            categories: [],
            brands: [],
            accounts: [],
            paginated_brands: "",
            brand_current_page: 1,
            brand_per_page: 3,
            paginated_categories: "",
            category_current_page: 1,
            category_per_page: 3,
            invoice_pos: null,
            category_id: "",
            brand_id: "",
            sound: "/audio/Beep.wav",
            audio: new Audio("/audio/Beep.wav")
        };
    },
    computed: {
        ...mapGetters([
            "currentUser",
            "currentUserPermissions",
            "supportedPaymentTypes", "shipping", "discount", "supportedLanguages", "items", "warehouse_id", "client_id", "products", "totalNumberOfProducts", "product_current_page", 'payment', "cartNetTotal"]),
        numberOfBrands() {
            return this.brands.length;
        },
        numberOfCategories() {
            return this.categories.length;
        },
        draft_sales_columns() {
            return [
                {
                    label: this.$t("date"),
                    field: "date",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("reference"),
                    field: "ref",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Customer"),
                    field: "client_name",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("warehouse"),
                    field: "warehouse_name",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Total"),
                    field: "grand_total",
                    tdClass: "text-left",
                    thClass: "text-left",
                    sortable: false
                },
                {
                    label: this.$t("Action"),
                    field: "actions",
                    html: true,
                    tdClass: "text-right",
                    thClass: "text-right",
                    sortable: false
                }
            ];
        },
    },
    mounted() {
        this.changeSidebarProperties();
        // this.paginate_products(this.product_per_page, 0);
    },
    methods: {
        ...mapActions([
            "changeSidebarProperties",
            "changeThemeMode",
            "logout",
            "getProducts",
            "addCartItem",
            "restCart",
            "setWarehouseId",
            "setClientId",
            "setDiscount",
            "setShipping"]),
        findProductById(product_id, product_variant_id) {
            return this.products.find(product => {
                if (product_id && product_variant_id) {
                    if (product.id === product_id && product.product_variant_id === product_variant_id) {
                        return product;
                    }
                }
                return product.product_id === product_id
            });
        },
        handleFocus() {
            this.focused = true
        },
        handleBlur() {
            this.focused = false
        },
        handleFullScreen() {
            Util.toggleFullScreen();
        },

        // ------------------------ Paginate Products --------------------\\
        // paginate_products(pageSize = null, pageNumber = 0) {
        //     const ps = pageSize || this.product_per_page;
        //     let itemsToParse = this.products;
        //     this.paginated_products = itemsToParse.slice(
        //         pageNumber * ps,
        //         (pageNumber + 1) * ps
        //     );
        // }
        // ,
        // ------------------------ Paginate Brands --------------------\\
        paginate_Brands(pageSize, pageNumber) {
            let itemsToParse = this.brands;
            this.paginated_brands = itemsToParse.slice(
                pageNumber * pageSize,
                (pageNumber + 1) * pageSize
            );
        },
        BrandonPageChanged(page) {
            this.paginate_Brands(this.brand_per_page, page - 1);
        },

        // ------------------------ Paginate Categories --------------------\\
        paginateCategories(pageNumber, pageSize = null) {
            const ps = pageSize || this.category_per_page;
            let itemsToParse = this.categories;
            this.paginated_categories = itemsToParse.slice(
                pageNumber * ps,
                (pageNumber + 1) * ps
            );
        },
        categoryOnPageChanged(page) {
            this.paginateCategories(page - 1, this.category_per_page);
        },

        //--- Submit Validate Create Sale
        submitPos() {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            this.$refs.create_pos.validate().then(success => {
                if (!success) {
                    NProgress.done();
                    if (this.client_id === "" || this.client_id === null) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Customer"),
                            this.$t("Failed")
                        );
                    } else if (
                        this.warehouse_id === "" ||
                        this.warehouse_id === null
                    ) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Warehouse"),
                            this.$t("Failed")
                        );
                    } else {
                        this.makeToast(
                            "danger",
                            this.$t("Please_fill_the_form_correctly"),
                            this.$t("Failed")
                        );
                    }
                } else {
                    if (this.verifiedForm()) {
                        Fire.$emit("pay_now");
                    } else {
                        NProgress.done();
                    }
                }
            });
        },

        //--- Submit Validate Draft
        submitDraft() {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            this.$refs.create_pos.validate().then(success => {
                if (!success) {
                    NProgress.done();
                    if (this.client_id === "" || this.client_id === null) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Customer"),
                            this.$t("Failed")
                        );
                    } else if (
                        this.warehouse_id === "" ||
                        this.warehouse_id === null
                    ) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Warehouse"),
                            this.$t("Failed")
                        );
                    } else {
                        this.makeToast(
                            "danger",
                            this.$t("Please_fill_the_form_correctly"),
                            this.$t("Failed")
                        );
                    }
                } else {
                    if (this.verifiedForm()) {
                        this.create_draft();
                    } else {
                        NProgress.done();
                    }
                }
            });
        },

        //---------------------------------- Create Draft ------------------------------\\
        create_draft() {
            this.execute(() => {
                this.draft_processing = true;
                posClient.createDraft({
                    client_id: this.client_id,
                    warehouse_id: this.warehouse_id,
                    tax_rate: this.tax_rate ? this.tax_rate : 0,
                    tax_net: this.tax_net ? this.tax_net : 0,
                    discount: this.discount ? this.discount : 0,
                    shipping: this.shipping ? this.shipping : 0,
                    notes: this.notes,
                    details: this.items,
                    grand_total: this.cartNetTotal,
                })
                    .then(response => {
                        if (response.data.success === true) {
                            // Complete the animation of the progress bar.
                            this.makeSuccessToast("Draft_Created_successfully",);
                            this.reset_Pos();
                        }
                    })
                    .catch(error => {
                        // Complete the animation of the progress bar.
                        this.makeDangerToast("InvalidData", "Failed");
                    })
                    .finally(() => {
                        this.draft_processing = false;
                    });

            })

        },
        showDraftSales() {
            this.getDraftSales(1);
            setTimeout(() => {
                this.$bvModal.show("show_draft_sales");
            }, 1000);
        },
        getDraftSales(page) {
            axios.get("get_draft_sales?page=" + page + "&limit=" + this.limit)
                .then(response => {
                    this.draft_sales = response.data.draft_sales;
                    this.number_of_drafted_sales = response.data.totalRows;
                })
                .catch(err => console.error('getDraftSales', err))

        },

        //----------------------------------- Remove draft_sales ------------------------------\\
        removeDraftSale(id) {
            this.$swal({
                title: this.$t("Delete.Title"),
                text: this.$t("Delete.Text"),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                cancelButtonText: this.$t("Delete.cancelButtonText"),
                confirmButtonText: this.$t("Delete.confirmButtonText")
            }).then(result => {
                if (result.value) {
                    // Start the progress bar.
                    NProgress.start();
                    NProgress.set(0.1);
                    axios
                        .delete("remove_draft_sale/" + id)
                        .then(() => {
                            this.$swal(
                                this.$t("Delete.Deleted"),
                                this.$t("Draft_Sale_Deleted"),
                                "success"
                            );

                            Fire.$emit("event_delete_draft_sale");
                        })
                        .catch(() => {
                            // Complete the animation of theprogress bar.
                            setTimeout(() => NProgress.done(), 500);
                            this.$swal(
                                this.$t("Delete.Failed"),
                                this.$t("Delete.Therewassomethingwronge"),
                                "warning"
                            );
                        });
                }
            });
        },

        //---- update Params Table
        updateParams(newProps) {
            this.server_params = Object.assign({}, this.server_params, newProps);
        },

        //---- Event Page Change
        onPageChange({currentPage}) {
            if (this.server_params.page !== currentPage) {
                this.updateParams({page: currentPage});

            }
        },

        //---- Event Per Page Change
        onPerPageChange({currentPerPage}) {
            if (this.limit !== currentPerPage) {
                this.limit = currentPerPage;
                this.updateParams({page: 1, perPage: currentPerPage});
                this.execute(() => this.getDraftSales(currentPage));
            }
        },

        //------ Validate Form submitPayment
        submitPayment() {
            // Start the progress bar.

            this.execute(() => {

                if (this.payment.amount > this.payment.received_amount) {
                    this.makeWarningToast("Paying_amount_is_greater_than_Received_amount",);
                    this.payment.received_amount = 0;
                } else if (this.payment.amount > this.cartNetTotal) {
                    this.makeWarningToast("Paying_amount_is_greater_than_Grand_Total",);
                    this.payment.amount = 0;
                } else {
                    this.createPos();
                }
            })

        },

        //---------------------------------------- Create new Customer -------------------------------\\
        createClient(data) {
            this.execute(() => {
                this.form_status.creating_customer = true
                axios.post("clients", data)
                    .then(response => {
                        console.log('created client', response.data)
                        this.setClientId(response.data.client_id)
                        this.makeSuccessToast("Create.TitleCustomer")
                        this.getAllClients();
                        this.$bvModal.hide("New_Customer");
                    })
                    .catch(error => this.makeDangerToast("InvalidData", "Failed"))
                    .finally(() => this.form_status.creating_customer = false);
            })
        },
        //------------------------------ New Model (create Customer) -------------------------------\\
        newClient() {
            this.$bvModal.show("New_Customer");
        },

        //------------------------------------ Get Clients Without Paginate -------------------------\\
        getAllClients() {
            axios.get("get_clients_without_paginate")
                .then(({data}) => (this.clients = data));
        },

        //---------------------- Event Select Warehouse ------------------------------\\
        selectedWarehouse(value) {
            this.search_input = '';
            this.product_filter = [];
            console.log('selectedWarehouse', value)
            this.setWarehouseId(value)
            this.getProducts({page: 1});
        },
        onClientSelected(selectedClient) {
            console.log('try search client', selectedClient);
            this.client_name = '';
            const client = this.clients.find(client => client.id === selectedClient);
            this.setClientId(client.id);
            this.client_name = client.name;
        },

        //---------------------- get_units ------------------------------\\
        getUnits(value) {
            axios.get("get_units?id=" + value)
                .then(({data}) => (this.units = data));
        },

        //------ Show Modal Update Detail Product
        showUpdateDetailModal(detail) {
            this.detail = {};
            this.getUnits(detail.product_id);
            this.detail.detail_id = detail.detail_id;
            this.detail.sale_unit_id = detail.sale_unit_id;
            this.detail.name = detail.name;
            this.detail.product_type = detail.product_type;
            this.detail.unit_price = detail.unit_price;
            this.detail.fix_price = detail.fix_price;
            this.detail.fix_stock = detail.fix_stock;
            this.detail.current = detail.current;
            this.detail.tax_method = detail.tax_method;
            this.detail.discount_method = detail.discount_method;
            this.detail.discount = detail.discount;
            this.detail.quantity = detail.quantity;
            this.detail.tax_percent = detail.tax_percent;
            this.detail.is_imei = detail.is_imei;
            this.detail.imei_number = detail.imei_number;
            setTimeout(() => {
                this.$bvModal.show("POS:Modal:UpdateDetail");
            }, 100);
        },

        //------ Submit Update Detail Product
        updateDetail(detail) {

            return console.log('updateDetail', detail)

            // for (var i = 0; i < this.items.length; i++) {
            //     if (this.items[i].detail_id === detail.detail_id) {
            //         // this.convert_unit();
            //         for (var k = 0; k < this.units.length; k++) {
            //             if (this.units[k].id == detail.sale_unit_id) {
            //                 if (this.units[k].operator == "/") {
            //                     this.items[i].current =
            //                         detail.fix_stock * this.units[k].operator_value;
            //                     this.items[i].unitSale = this.units[k].short_name;
            //                 } else {
            //                     this.items[i].current =
            //                         detail.fix_stock / this.units[k].operator_value;
            //                     this.items[i].unitSale = this.units[k].short_name;
            //                 }
            //             }
            //         }
            //         if (this.items[i].current < this.items[i].quantity) {
            //             this.items[i].quantity = this.items[i].current;
            //         } else {
            //             this.items[i].quantity = 1;
            //         }
            //         this.items[i].unit_price = detail.unit_price;
            //         this.items[i].tax_percent = detail.tax_percent;
            //         this.items[i].tax_method = detail.tax_method;
            //         this.items[i].discount_method = detail.discount_method;
            //         this.items[i].discount = detail.discount;
            //         this.items[i].sale_unit_id = detail.sale_unit_id;
            //         this.items[i].imei_number = detail.imei_number;
            //         this.items[i].product_type = detail.product_type;
            //
            //         if (this.items[i].discount_method == "2") {
            //             //Fixed
            //             this.items[i].discount_net = this.items[i].discount;
            //         } else {
            //             //Percentage %
            //             this.items[i].discount_net = parseFloat(
            //                 (this.items[i].unit_price * this.items[i].discount) / 100
            //             );
            //         }
            //         if (this.items[i].tax_method == "1") {
            //             //Exclusive
            //             this.items[i].net_price = parseFloat(
            //                 this.items[i].unit_price - this.items[i].discount_net
            //             );
            //             this.items[i].tax = parseFloat(
            //                 (this.items[i].tax_percent *
            //                     (this.items[i].unit_price - this.items[i].discount_net)) /
            //                 100
            //             );
            //             this.items[i].total_price = parseFloat(
            //                 this.items[i].net_price + this.items[i].tax
            //             );
            //         } else {
            //             //Inclusive
            //             this.items[i].net_price = parseFloat(
            //                 (this.items[i].unit_price - this.items[i].discount_net) /
            //                 (this.items[i].tax_percent / 100 + 1)
            //             );
            //             this.items[i].tax = parseFloat(
            //                 this.items[i].unit_price -
            //                 this.items[i].net_price -
            //                 this.items[i].discount_net
            //             );
            //             this.items[i].total_price = parseFloat(
            //                 this.items[i].net_price + this.items[i].tax
            //             );
            //         }
            //     }
            // }

            console.log('complete update')

            this.$nextTick(() => this.$bvModal.hide("POS:Modal:UpdateDetail"))
        },

        //-- check Qty of  details order if Null or zero
        verifiedForm() {
            if (this.items.length <= 0) {
                this.makeWarningToast("AddProductToList");
                return false;
            } else {
                var count = 0;
                for (var i = 0; i < this.items.length; i++) {
                    if (
                        this.items[i].quantity == "" ||
                        this.items[i].quantity === 0 ||
                        this.items[i].quantity > this.items[i].current
                    ) {
                        count += 1;
                        if (this.items[i].quantity > this.items[i].current) {
                            this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                            return false;
                        }
                    }
                }
                if (count > 0) {
                    this.makeToast("warning", this.$t("AddQuantity"), this.$t("Warning"));
                    return false;
                } else {
                    return true;
                }
            }
        },

        //------------------------------ Print -------------------------\\
        printPos() {
            this.$refs.receipt.getPrintInfo()
                .then(({html, css_path}) => {
                    var a = window.open("", "", "height=500, width=500");
                    a.document.write(`<link rel="stylesheet" href="${css_path}"><html>`);
                    a.document.write("<body >");
                    a.document.write(html);
                    a.document.write("</body></html>");
                    a.document.close();
                    a.print();
                })
        },

        //-------------------------------- Invoice POS ------------------------------\\
        invoicePos(id) {
            this.execute(() => {
                axios.get("sales_print_invoice/" + id)
                    .then(response => {
                        this.invoice_pos = response.data;
                        // Complete the animation of the  progress bar.
                        this.$nextTick(() => {
                            this.$bvModal.show("Show_invoice");
                            this.$nextTick(() => {
                                if (response.data.pos_settings.is_printable) {
                                    this.printPos()
                                }
                            })
                        })
                    })
                    .catch((error) => {
                        console.error('invoicePos', error)
                    })
            })

        },
        createPaymentData(token = null) {
            return {
                client_id: this.client_id,
                warehouse_id: this.warehouse_id,
                tax_rate: this.tax_rate || 0,
                tax_net: this.tax_net || 0,
                discount: this.discount || 0,
                shipping: this.shipping || 0,
                notes: this.notes,
                details: this.items,
                grand_total: this.cartNetTotal,
                payment: Object.assign(this.payment, {
                    amount: this.payment.received_amount,
                    change: this.payment.received_amount - this.cartNetTotal
                }),
                account_id: this.payment.account_id,
                amount: parseFloat(this.payment.amount).toFixed(2),
                received_amount: parseFloat(this.payment.received_amount).toFixed(2),
                change: parseFloat(this.payment.received_amount - this.cartNetTotal).toFixed(2),
                token: token?.id,
                is_new_credit_card: this.is_new_credit_card || "",
                selected_card: this.selected_card || "",
                card_id: this.card_id || "",
            }
        },

        //----------------------------------Create POS ------------------------------\\
        createPos() {
            this.execute(() => {
                this.payment_processing = true;
                axios.post("pos/create_pos", this.createPaymentData())
                    .then(response => {
                        if (response.data.success === true) {
                            // Complete the animation of the progress bar.
                            this.invoicePos(response.data.id);
                            this.$bvModal.hide("Add_Payment");
                            this.reset_Pos();
                        }
                    })
                    .catch(error => {
                        // Complete the animation of theprogress bar.
                        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
                    })
                    .finally(() => {
                        this.payment_processing = false;
                    });
            })
        },

        //-------Verified QTY
        verifiedQty(detail) {
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].detail_id === detail.id) {
                    if (isNaN(detail.quantity)) {
                        this.items[i].quantity = detail.current;
                    }
                    if (detail.quantity > detail.current) {
                        this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                        this.items[i].quantity = detail.current;
                    } else {
                        this.items[i].quantity = detail.quantity;
                    }
                }
            }
            this.$forceUpdate();

        },
        incrementDetailBy(field, value) {
            this.items = this.items.map(d => {
                if (d.promotion_id) return d
                if (d[field] === value) {
                    return {...d, qty: d.qty + 1}
                }
            })
        },
        decrementDetailBy(field, value) {
            this.items = this.items.map(d => {
                if (d.promotion_id) return d
                if (d[field] === value) {
                    if (d.qty - 1 > 0) {
                        return {...d, qty: d.qty - 1}
                    } else {
                        this.makeWarningToast("Not enough qty")
                        return d
                    }
                }
            })
        },

        //----------------------------------- Increment QTY ------------------------------\\
        increment(detail) {
            this.incrementDetailBy('code', detail.code)

            this.$forceUpdate();
        },

        //----------------------------------- decrement QTY ------------------------------\\
        decrement(detail) {
            this.decrementDetailBy('code', detail.code)

            this.$forceUpdate();
        },

        //-----------------------------------Delete Detail Product ------------------------------\\
        deleteProductDetail(detail) {
            const index = this.items.findIndex(d => d.id === detail.id);
            if (index > -1) {
                this.items.splice(index, 1);
            }
        },

        //----------Reset Pos
        reset_Pos() {
            this.product = {};
            this.category_id = "";
            this.brand_id = "";
            this.client_name = "";
            this.resetCart()
            this.getProducts({page: 1, warehouse_id: this.warehouse_id});
        },

        //------------------------- get Result Value Search Product
        getResultValue(result) {
            return result.code + " " + "(" + result.name + ")";
        },

        //------------------------- Submit Search Product
        SearchProduct(result) {

            if (this.load_product) {
                this.load_product = false;
                this.product = {};

                if (result.product_type === 'is_service') {
                    this.product.quantity = 1;
                    this.product.code = result.code;

                } else {

                    this.product.code = result.code;
                    this.product.current = result.qty_sale;
                    this.product.fix_stock = result.qty;
                    if (result.qty_sale < 1) {
                        this.product.quantity = result.qty_sale;
                    } else {
                        this.product.quantity = 1;
                    }
                }
                this.product.product_variant_id = result.product_variant_id;
                this.getProductDetails(result.id, result.product_variant_id);

                this.search_input = '';

                this.product_filter = [];

            } else {
                this.makeToast(
                    "warning",
                    this.$t("Please_wait_until_the_product_is_loaded"),
                    this.$t("Warning")
                );
            }
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
            if (this.warehouse_id !== "" && this.warehouse_id != null) {
                this.timer = setTimeout(() => {
                    const product_filter = this.products.filter(product => product.code === this.search_input || product.barcode.includes(this.search_input));
                    if (product_filter.length === 1) {
                        this.checkProductExist(product_filter[0]);
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

        //---------------------------------- Check if Product Exist in Order List ---------------------\\
        checkProductExist(product) {
            console.log('checkProductExist', product)
            this.execute(() => {
                this.search_input = '';
                this.product_filter = [];
                this.audio.play();
                console.log("addProduct: ", product)
                this.addCartItem(product)
                if (product.has_imei) {
                    this.product = product;
                    this.showUpdateDetailModal(this.product);
                }
            })
        },

        //--- Get Products by Category
        getProductsByCategory(id = "") {
            this.category_id = id;
            this.getProducts(1);
        },

        //--- Get Products by Brand
        productsByBrand(id = "") {
            this.brand_id = id;
            this.getProducts(1);
        },

        //---------------------------------------Get Elements ------------------------------\\
        init(serverResponse) {
            this.clients = serverResponse.data.clients;
            this.accounts = serverResponse.data.accounts;
            this.warehouses = serverResponse.data.warehouses;
            this.categories = serverResponse.data.categories;
            this.brands = serverResponse.data.brands;
            this.warehouse_id = serverResponse.data.defaultWarehouse;
            this.client_id = serverResponse.data.defaultClient;
            this.client_name = serverResponse.data.default_client_name;
            this.getProducts();
            if (serverResponse.data.defaultWarehouse !== "") {
                this.getProductsByWarehouse(serverResponse.data.defaultWarehouse);
            }
            this.paginate_Brands(this.brand_per_page, 0);
            this.paginateCategories(this.category_per_page, 0);
        }
    },
//-------------------- Created Function -----\\
    created() {

        this.$store.registerModule('pos', posModule)

        posClient.getPosElements()
            .then(this.init)
            .catch((error) => console.error(error))
            .finally(response => this.is_loading = false);

        Fire.$on("pay_now", () => {
            this.$nextTick(() => {
                this.payment.amount = this.formatNumber(this.grand_total, 2);
                this.payment.received_amount = this.formatNumber(this.grand_total, 2);
                this.payment.type = "cash";
                this.$nextTick(() => this.$bvModal.show("Add_Payment"));
            })
        });
        Fire.$on("event_delete_draft_sale", () => this.execute(() => this.getDraftSales(this.server_params.page)));
        Fire.$on(PosEvents.SelectProduct, this.checkProductExist);
        Fire.$on(PosEvents.UpdateDetail, this.showUpdateDetailModal);
        Fire.$on(PosEvents.IncrementDetail, this.increment);
        Fire.$on(PosEvents.DecrementDetail, this.decrement);
        Fire.$on(PosEvents.VerifyQty, this.verifiedQty);
        Fire.$on(PosEvents.DeleteDetail, this.deleteProductDetail);
        Fire.$on(PosEvents.CreateCustomer, this.createClient);
        Fire.$on(PosEvents.SubmitPayment, this.submitPayment);
    },
    destroyed() {
        this.$store.unregisterModule('pos')
    }
};
</script>

<style>
.total {
    font-weight: bold;
    font-size: 14px;
}

.bg-selected-card {
    background-color: #dcdfe6;
}

/* Media query*/
@media screen and (min-width: 1350px) {
    .pos-button-actions {
        position: fixed;
    }
}

</style>
