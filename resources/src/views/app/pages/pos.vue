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
                                                        v-model="sale.client_id"
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
                                                        :disabled="details.length > 0"
                                                        @input="selectedWarehouse"
                                                        v-model="sale.warehouse_id"
                                                        :reduce="label => label.value"
                                                        :placeholder="$t('Choose_Warehouse')"
                                                        :options="warehouses.map(warehouses => ({label: warehouses.name, value: warehouses.id}))"
                                                    />
                                                </b-form-group>
                                            </validation-provider>
                                        </b-col>

                                        <!-- Details Product  -->
                                        <b-col md="12" class="mt-2">
                                            <Cart :details="details"></Cart>
                                        </b-col>
                                    </b-row>

                                    <!-- Calculate Grand Total -->
                                    <div class="footer_panel">
                                        <b-row>
                                            <b-col md="12">
                                                <div class="grandtotal">
                                                    <span>{{ $t("Total_Payable") }} : {{
                                                            currentUser.currency
                                                        }} {{ grand_total.toFixed(2) }}</span>
                                                </div>
                                            </b-col>

                                            <!-- Order Tax  -->
                                            <b-col lg="4" md="4" sm="12"
                                                   v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_sale')">
                                                <validation-provider
                                                    name="Order Tax"
                                                    :rules="{ regex: /^\d*\.?\d*$/}"
                                                    v-slot="validationContext"
                                                >
                                                    <b-form-group :label="$t('Tax')" append="%">
                                                        <b-input-group append="%">
                                                            <b-form-input
                                                                :state="getValidationState(validationContext)"
                                                                aria-describedby="OrderTax-feedback"
                                                                label="Order Tax"
                                                                v-model.number="sale.tax_rate"
                                                                @keyup="keyupUpdateSale('tax_rate')"
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
                                            <b-col lg="4" md="4" sm="12"
                                                   v-if="currentUserPermissions && currentUserPermissions.includes('edit_tax_discount_shipping_sale')">
                                                <validation-provider
                                                    name="Discount"
                                                    :rules="{ regex: /^\d*\.?\d*$/}"
                                                    v-slot="validationContext"
                                                >
                                                    <b-form-group :label="$t('Discount')" append="%">
                                                        <b-input-group :append="currentUser.currency">
                                                            <b-form-input
                                                                :state="getValidationState(validationContext)"
                                                                aria-describedby="Discount-feedback"
                                                                label="Discount"
                                                                v-model.number="sale.discount"
                                                                @keyup="keyupUpdateSale('discount')"
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
                                            <b-col lg="4" md="4" sm="12"
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
                                                                v-model.number="sale.shipping"
                                                                @keyup="keyupUpdateSale('shipping')"
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
                        :total-rows="product_total_rows"
                        :products="products ?? []"
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

                <!-- Modal Show Invoice POS-->
                <b-modal hide-footer size="sm" scrollable id="Show_invoice" :title="$t('invoice_pos')">
                    <component ref="receipt" :is="invoice_template" :invoice-data="invoice_pos"></component>
                    <button @click="printPos()" class="btn btn-outline-primary">
                        <i class="i-Billing"></i>{{ $t('print') }}
                    </button>
                </b-modal>

                <!-- Modal Add Payment-->
                <validation-observer ref="Add_payment">
                    <b-modal hide-footer size="lg" id="Add_Payment" :title="$t('AddPayment')">
                        <b-form @submit.prevent="submitPayment">
                            <h1 class="text-center mt-3 mb-3">{{ client_name }}</h1>
                            <b-row>
                                <b-col md="6">
                                    <b-row>
                                        <!-- Received  Amount  -->
                                        <b-col lg="12" md="12" sm="12">
                                            <validation-provider
                                                name="Received Amount"
                                                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                                                v-slot="validationContext"
                                            >
                                                <b-form-group :label="$t('Received_Amount') + ' ' + '*'">
                                                    <b-form-input
                                                        @keyup="verifiedReceivedAmount(payment.received_amount)"
                                                        label="Received_Amount"
                                                        :placeholder="$t('Received_Amount')"
                                                        v-model.number="payment.received_amount"
                                                        :state="getValidationState(validationContext)"
                                                        aria-describedby="Received_Amount-feedback"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback
                                                        id="Received_Amount-feedback"
                                                    >{{ validationContext.errors[0] }}
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </validation-provider>
                                        </b-col>

                                        <!-- Paying  Amount  -->
                                        <b-col lg="12" md="12" sm="12">
                                            <validation-provider
                                                name="Paying Amount"
                                                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                                                v-slot="validationContext"
                                            >
                                                <b-form-group :label="$t('Paying_Amount') + ' ' + '*'">
                                                    <b-form-input
                                                        label="Paying_Amount"
                                                        @keyup="verifyPaidAmount(payment.amount)"
                                                        :placeholder="$t('Paying_Amount')"
                                                        v-model.number="payment.amount"
                                                        :state="getValidationState(validationContext)"
                                                        aria-describedby="Paying_Amount-feedback"
                                                    ></b-form-input>
                                                    <b-form-invalid-feedback
                                                        id="Paying_Amount-feedback"
                                                    >{{ validationContext.errors[0] }}
                                                    </b-form-invalid-feedback>
                                                </b-form-group>
                                            </validation-provider>
                                        </b-col>

                                        <!-- change  Amount  -->
                                        <b-col lg="12" md="12" sm="12">
                                            <label>{{ $t('Change') }} :</label>
                                            <p
                                                class="change_amount"
                                            >{{ parseFloat(payment.received_amount - payment.amount).toFixed(2) }}</p>
                                        </b-col>


                                    </b-row>
                                </b-col>
                                <b-col md="6">
                                    <b-card>
                                        <b-list-group>
                                            <b-list-group-item
                                                class="d-flex justify-content-between align-items-center">
                                                {{ $t('TotalProducts') }}
                                                <b-badge variant="primary" pill>{{ details.length }}</b-badge>
                                            </b-list-group-item>
                                            <b-list-group-item
                                                class="d-flex justify-content-between align-items-center">
                                                {{ $t('OrderTax') }}
                                                <span
                                                    class="font-weight-bold"
                                                >{{ currentUser.currency }} {{
                                                        sale.tax_net.toFixed(2)
                                                    }} ({{ sale.tax_rate }} %)</span>
                                            </b-list-group-item>
                                            <b-list-group-item
                                                class="d-flex justify-content-between align-items-center">
                                                {{ $t('Discount') }}
                                                <span
                                                    class="font-weight-bold"
                                                >{{ currentUser.currency }} {{ sale.discount.toFixed(2) }}</span>
                                            </b-list-group-item>
                                            <b-list-group-item
                                                class="d-flex justify-content-between align-items-center">
                                                {{ $t('Shipping') }}
                                                <span
                                                    class="font-weight-bold"
                                                >{{ currentUser.currency }} {{ sale.shipping.toFixed(2) }}</span>
                                            </b-list-group-item>
                                            <b-list-group-item
                                                class="d-flex justify-content-between align-items-center">
                                                {{ $t('Total_Payable') }}
                                                <span
                                                    class="font-weight-bold"
                                                >{{ currentUser.currency }} {{ grand_total.toFixed(2) }}</span>
                                            </b-list-group-item>
                                        </b-list-group>
                                    </b-card>
                                </b-col>
                            </b-row>
                            <b-row class="mt-4">

                                <!-- Payment choice -->
                                <b-col lg="6" md="6" sm="12">
                                    <validation-provider name="Payment choice" :rules="{ required: true}">
                                        <b-form-group slot-scope="{ valid, errors }"
                                                      :label="$t('Paymentchoice') + ' ' + '*'">
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                v-model="payment.type"
                                                @input="selectedPaymentMethod"
                                                :reduce="label => label.value"
                                                :placeholder="$t('PleaseSelect')"
                                                :options="supportedPaymentTypes"
                                            ></v-select>
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Account -->
                                <b-col lg="6" md="6" sm="12">
                                    <validation-provider name="Account">
                                        <b-form-group slot-scope="{ valid, errors }" :label="$t('Account')">
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                v-model="payment.account_id"
                                                :reduce="label => label.value"
                                                :placeholder="$t('Choose_Account')"
                                                :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
                                            />
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <b-col md="12">
                                    <b-card v-show="payment.type == 'credit card'">
                                        <div v-once class="typo__p" v-if="submit_showing_credit_card">
                                            <div class="spinner sm spinner-primary mt-3"></div>
                                        </div>
                                        <div v-if="saved_payment_methods && !submit_showing_credit_card">
                                            <div class="mt-3"><span
                                                class="mr-3">Saved Credit Card Info For This Client </span>
                                                <b-button variant="outline-info" @click="showNewCreditCard()">
                              <span>
                                <i class="i-Two-Windows"></i>
                                New Credit Card
                              </span>
                                                </b-button>

                                            </div>
                                            <table class="table table-hover mt-3">
                                                <thead>
                                                <tr>
                                                    <th>Last 4 digits</th>
                                                    <th>Type</th>
                                                    <th>Exp</th>
                                                    <th>Action</th>

                                                </tr>
                                                </thead>

                                                <tbody>
                                                <tr v-for="card in saved_payment_methods"
                                                    :class="{ 'bg-selected-card': isSelectedCard(card) }">
                                                    <td>**** {{ card.last4 }}</td>
                                                    <td>{{ card.type }}</td>
                                                    <td>{{ card.exp }}</td>
                                                    <td>
                                                        <b-button variant="outline-primary" @click="selectCard(card)"
                                                                  v-if="!isSelectedCard(card) && card_id != card.card_id">
                                      <span>
                                        <i class="i-Drag-Up"></i>
                                        Use This
                                      </span>
                                                        </b-button>
                                                        <i v-if="isSelectedCard(card) || card_id == card.card_id"
                                                           class="i-Yes" style=" font-size: 20px; "></i>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div v-if="formNewCard && !submit_showing_credit_card">
                                            <form id="payment-form">
                                                <label for="card-element" class="leading-7 text-sm text-gray-600">
                                                    {{ $t('Credit_Card_Info') }}
                                                    <b-button variant="outline-info" @click="showSavedCreditCard()"
                                                              v-if="saved_payment_methods && saved_payment_methods.length > 0">
                                <span>
                                      <i class="i-Two-Windows"></i>
                                      Use Saved Credit Card
                                    </span>
                                                    </b-button>
                                                </label>
                                                <div id="card-element">
                                                </div>
                                                <div id="card-errors" class="is-invalid" role="alert"></div>
                                            </form>
                                        </div>
                                    </b-card>
                                </b-col>


                                <!-- payment Note -->
                                <b-col lg="6" md="6" sm="12" class="mt-2">
                                    <b-form-group :label="$t('Payment_note')">
                                        <b-form-textarea
                                            id="textarea"
                                            v-model="payment.notes"
                                            rows="3"
                                            max-rows="6"
                                        ></b-form-textarea>
                                    </b-form-group>
                                </b-col>

                                <!-- sale Note -->
                                <b-col lg="6" md="6" sm="12" class="mt-2">
                                    <b-form-group :label="$t('sale_note')">
                                        <b-form-textarea
                                            id="textarea"
                                            v-model="sale.notes"
                                            rows="3"
                                            max-rows="6"
                                        ></b-form-textarea>
                                    </b-form-group>
                                </b-col>


                                <b-col md="12" class="mt-3">
                                    <b-button
                                        variant="primary"
                                        type="submit"
                                        :disabled="paymentProcessing"
                                    ><i class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                                    </b-button>
                                    <div v-once class="typo__p" v-if="paymentProcessing">
                                        <div class="spinner sm spinner-primary mt-3"></div>
                                    </div>
                                </b-col>
                            </b-row>
                        </b-form>
                    </b-modal>
                </validation-observer>

                <validation-observer ref="Create_Customer">
                    <b-modal hide-footer size="lg" id="New_Customer" :title="$t('Add')">
                        <b-form @submit.prevent="submitCustomer">
                            <b-row>
                                <!-- Customer Name -->
                                <b-col md="6" sm="12">
                                    <validation-provider
                                        name="Name Customer"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('CustomerName') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="name-feedback"
                                                label="name"
                                                v-model="client.name"
                                                :placeholder="$t('CustomerName')"
                                            ></b-form-input>
                                            <b-form-invalid-feedback id="name-feedback">{{
                                                    validationContext.errors[0]
                                                }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>

                                <!-- Customer Email -->
                                <b-col md="6" sm="12">
                                    <b-form-group :label="$t('Email')">
                                        <b-form-input
                                            label="email"
                                            v-model="client.email"
                                            :placeholder="$t('Email')"
                                        ></b-form-input>
                                    </b-form-group>
                                </b-col>

                                <!-- Customer Phone -->
                                <b-col md="6" sm="12">
                                    <b-form-group :label="$t('Phone')">
                                        <b-form-input
                                            label="Phone"
                                            v-model="client.phone"
                                            :placeholder="$t('Phone')"
                                        ></b-form-input>
                                    </b-form-group>
                                </b-col>

                                <!-- Customer Country -->
                                <b-col md="6" sm="12">
                                    <b-form-group :label="$t('Country')">
                                        <b-form-input
                                            label="Country"
                                            v-model="client.country"
                                            :placeholder="$t('Country')"
                                        ></b-form-input>
                                    </b-form-group>
                                </b-col>

                                <!-- Customer City -->
                                <b-col md="6" sm="12">
                                    <b-form-group :label="$t('City')">
                                        <b-form-input
                                            label="City"
                                            v-model="client.city"
                                            :placeholder="$t('City')"
                                        ></b-form-input>
                                    </b-form-group>
                                </b-col>

                                <!-- Customer Tax Number -->
                                <b-col md="6" sm="12">
                                    <b-form-group :label="$t('Tax_Number')">
                                        <b-form-input
                                            label="Tax Number"
                                            v-model="client.tax_number"
                                            :placeholder="$t('Tax_Number')"
                                        ></b-form-input>
                                    </b-form-group>
                                </b-col>


                                <!-- Customer Address -->
                                <b-col md="12" sm="12">
                                    <b-form-group :label="$t('Address')">
                      <textarea
                          label="Address"
                          class="form-control"
                          rows="4"
                          v-model="client.address"
                          :placeholder="$t('Address')"
                      ></textarea>
                                    </b-form-group>
                                </b-col>

                                <b-col md="12" class="mt-3">
                                    <b-button variant="primary" type="submit"><i
                                        class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                                    </b-button>
                                </b-col>
                            </b-row>
                        </b-form>
                    </b-modal>
                </validation-observer>

                <b-modal
                    hide-footer
                    size="lg"
                    id="show_draft_sales"
                    title="Draft Sales"
                >
                    <vue-good-table
                        mode="remote"
                        :columns="draft_sales_columns"
                        :totalRows="number_of_drafted_sales"
                        :rows="draft_sales"
                        @on-page-change="onPageChange"
                        @on-per-page-change="onPerPageChange"

                        :pagination-options="{
            enabled: true,
            mode: 'records',
            nextLabel: 'next',
            prevLabel: 'prev',
          }"
                        styleClass="tableOne table-hover vgt-table"
                    >


                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field == 'actions'">
                                <router-link
                                    v-b-tooltip.hover
                                    title="Edit"
                                    :to="{ name:'pos_draft', params: { id: props.row.id } }"
                                >
                                  <i class="i-Edit text-25 text-success"></i>
                                </router-link>
                                <a @click="removeDraftSale(props.row.id)"
                                   v-b-tooltip.hover
                                   title="Delete"
                                   class="cursor-pointer"
                                >
                                  <i class="i-Close-Window text-25 text-danger"></i>
                                </a>
                              </span>
                        </template>
                    </vue-good-table>
                </b-modal>

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
    </div>
</template>

<script>
import NProgress from "nprogress";
import {mapActions, mapGetters} from "vuex";
import vueEasyPrint from "vue-easy-print";
import VueBarcode from "vue-barcode";
import FlagIcon from "vue-flag-icon";
import Util, {randomString} from "./../../../utils";
import {loadStripe} from "@stripe/stripe-js";
import {posClient} from "../../../utils/client";
import ProductCardContainer from "../../../components/pos/ProductCardContainer";
import Cart from "../../../components/pos/Cart";
import UpdateDetailForm from "../../../components/pos/UpdateDetailForm";
import DefaultInvoiceTemplate from "../../../components/pos/invoices/templates/default/index";
import LanguageSwitcher from "../../../components/LanguageSwitcher";
import helperMixin from "../../../mixins/helperMethods";

export default {
    mixins: [helperMixin],
    components: {
        Cart,
        vueEasyPrint,
        barcode: VueBarcode,
        FlagIcon,
        ProductCardContainer,
        UpdateDetailForm,
        DefaultInvoiceTemplate,
        LanguageSwitcher
    },
    metaInfo: {title: "POS"},
    data() {
        return {
            cart_id: null,
            cart_created: false,
            invoice_template: "DefaultInvoiceTemplate",
            stripe: {},
            stripe_key: null,
            card_element: {},
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
            payment: {
                amount: "",
                account_id: "",
                received_amount: "",
                type: "",
                notes: ""
            },
            focused: false,
            timer: null,
            search_input: '',
            product_filter: [],
            is_loading: true,
            load_product: true,
            grand_total: 0,
            total: 0,
            ref: "",
            clients: [],
            units: [],
            warehouses: [],
            products: [],
            products_pos: [],
            details: [],
            detail: {},
            categories: [],
            brands: [],
            accounts: [],
            product_current_page: 1,
            paginated_products: "",
            product_per_page: 8,
            product_total_rows: "",
            paginated_brands: "",
            brand_current_page: 1,
            brand_per_page: 3,
            paginated_categories: "",
            category_current_page: 1,
            category_per_page: 3,
            invoice_pos: null,
            sale: {
                warehouse_id: "",
                client_id: "",
                tax_rate: 0,
                shipping: 0,
                discount: 0,
                tax_net: 0,
                notes: '',
            },
            client: {
                id: "",
                name: "",
                code: "",
                email: "",
                phone: "",
                country: "",
                tax_number: "",
                city: "",
                address: ""
            },
            category_id: "",
            brand_id: "",
            product: {
                id: "",
                code: "",
                product_type: "",
                current: "",
                quantity: "",
                check_qty: "",
                discount: "",
                discount_net: "",
                discount_method: "",
                sale_unit_id: "",
                fix_stock: "",
                fix_price: "",
                name: "",
                unitSale: "",
                net_price: "",
                unit_price: "",
                total_price: "",
                subtotal: "",
                product_id: "",
                detail_id: "",
                tax: "",
                tax_percent: "",
                tax_method: "",
                product_variant_id: "",
                is_imei: "",
                imei_number: "",
            },
            sound: "/audio/Beep.wav",
            audio: new Audio("/audio/Beep.wav")
        };
    },
    computed: {
        ...mapGetters(["currentUser", "currentUserPermissions", "supportedPaymentTypes", "supportedLanguages"]),

        numberOfBrands() {
            return this.brands.length;
        },

        numberOfCategories() {
            return this.categories.length;
        },

        formNewCard() {
            return !this.use_saved_payment_method
        },

        isSelectedCard() {
            return card => this.selected_card === card;
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
        }

    },
    watch: {
        details: {
            handler: async function (val) {
                console.log('details watcher trigger', val)

                if (this.cart_created === false
                    && this.sale.warehouse_id !== "") {
                    await axios.post('/pos/cart', {
                        id: this.cart_id,
                        warehouseId: this.sale.warehouse_id,
                        clientId: this.sale.client_id,
                    })
                        .then(response => {
                            this.cart_created = true
                        })
                }

                if (this.cart_created) {
                    const url = `/promotions/check`
                    axios.post(url, {
                        cartId: this.cart_id,
                        warehouseId: this.sale.warehouse_id,
                        clientId: this.sale.client_id,
                        details: val.map(d => ({
                            product_id: d.product_id,
                            quantity: d.quantity,
                            product_variant_id: d.product_variant_id,
                            promotion_id: d.promotion_id
                        }))
                    })
                        .then(response => {
                            console.log('check promotion: ', response.data)

                            const promotion = response.data.promotion


                            if (promotion.is_increment) {
                                response.data.promotion.rewards.forEach(promotedItem => {
                                    const promoted_product = this.findProductById(promotedItem.product_id, promotedItem.product_variant_id)
                                    console.log("promoted_product:", promoted_product)
                                    this.details.push({
                                        ...promoted_product,
                                        promotion_id: promotedItem.promotion_id,
                                        promotion_type: promotedItem.type,
                                        net_price: 0,
                                        subtotal: 0,
                                    })
                                })
                            }

                        })
                }
            },
            deep: true
        }
    },
    mounted() {
        this.changeSidebarProperties();
        this.paginate_products(this.product_per_page, 0);
    },
    methods: {
        ...mapActions(["changeSidebarProperties", "changeThemeMode", "logout",]),
        findProductById(product_id, product_variant_id) {
            return this.products_pos.find(product => {
                if (product_id && product_variant_id) {
                    if (product.id === product_id && product.product_variant_id === product_variant_id) {
                        return product;
                    }
                }
                return product.id === product_id
            });
        },
        handleFocus() {
            this.focused = true
        },
        handleBlur() {
            this.focused = false
        },
        async selectedPaymentMethod(value) {
            if (value === 'credit card') {
                this.saved_payment_methods = [];
                this.submit_showing_credit_card = true;
                this.selected_card = null
                this.card_id = '';
                // Check if the customer has saved payment methods
                await axios.get(`/retrieve-customer?customerId=${this.sale.client_id}`)
                    .then(response => {
                        // If the customer has saved payment methods, display them
                        this.saved_payment_methods = response.data.data;
                        this.card_id = response.data.customer_default_source;
                        this.has_saved_payment_method = true;
                        this.use_saved_payment_method = true;
                        this.is_new_credit_card = false;
                        this.submit_showing_credit_card = false;
                    })
                    .catch(error => {
                        // If the customer does not have saved payment methods, show the card element for them to enter their payment information
                        this.has_saved_payment_method = false;
                        this.use_saved_payment_method = false;
                        this.is_new_credit_card = true;
                        this.card_id = '';

                        setTimeout(() => {
                            this.loadStripePayment();
                        }, 1000);
                        this.submit_showing_credit_card = false;
                    });


            } else {
                this.has_saved_payment_method = false;
                this.use_saved_payment_method = false;
                this.is_new_credit_card = false;
            }

        },

        showSavedCreditCard() {
            this.has_saved_payment_method = true;
            this.use_saved_payment_method = true;
            this.is_new_credit_card = false;
            this.selectedPaymentMethod('credit card');
        },

        showNewCreditCard() {
            this.selected_card = null;
            this.card_id = '';
            this.use_saved_payment_method = false;
            this.has_saved_payment_method = false;
            this.is_new_credit_card = true;

            setTimeout(() => {
                this.loadStripePayment();
            }, 500);
        },

        selectCard(card) {
            this.selected_card = card;
            this.card_id = card.card_id;
        },

        async loadStripePayment() {
            this.stripe = await loadStripe(`${this.stripe_key || ""}`);
            const elements = this.stripe.elements();
            this.card_element = elements.create("card", {
                classes: {
                    base:
                        "bg-gray-100 rounded border border-gray-300 focus:border-indigo-500 text-base outline-none text-gray-700 p-3 leading-8 transition-colors duration-200 ease-in-out"
                }
            });
            this.card_element.mount("#card-element");
        },

        handleFullScreen() {
            Util.toggleFullScreen();
        },

        // ------------------------ Paginate Products --------------------\\
        paginate_products(pageSize = null, pageNumber = 0) {
            const ps = pageSize || this.product_per_page;
            let itemsToParse = this.products;
            this.paginated_products = itemsToParse.slice(
                pageNumber * ps,
                (pageNumber + 1) * ps
            );
        },
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
                    if (this.sale.client_id === "" || this.sale.client_id === null) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Customer"),
                            this.$t("Failed")
                        );
                    } else if (
                        this.sale.warehouse_id === "" ||
                        this.sale.warehouse_id === null
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
                    if (this.sale.client_id === "" || this.sale.client_id === null) {
                        this.makeToast(
                            "danger",
                            this.$t("Choose_Customer"),
                            this.$t("Failed")
                        );
                    } else if (
                        this.sale.warehouse_id === "" ||
                        this.sale.warehouse_id === null
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
                    client_id: this.sale.client_id,
                    warehouse_id: this.sale.warehouse_id,
                    tax_rate: this.sale.tax_rate ? this.sale.tax_rate : 0,
                    tax_net: this.sale.tax_net ? this.sale.tax_net : 0,
                    discount: this.sale.discount ? this.sale.discount : 0,
                    shipping: this.sale.shipping ? this.sale.shipping : 0,
                    notes: this.sale.notes,
                    details: this.details,
                    grand_total: this.grand_total,
                })
                    .then(response => {
                        if (response.data.success === true) {
                            // Complete the animation of the progress bar.
                            this.makeToast(
                                "success",
                                this.$t("Draft_Created_successfully"),
                                this.$t("Success")
                            );
                            this.reset_Pos();
                        }
                    })
                    .catch(error => {
                        // Complete the animation of the progress bar.
                        this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
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
            NProgress.start();
            NProgress.set(0.1);

            this.execute(() => {
                this.$refs.Add_payment.validate()
                    .then(success => {
                        if (!success) {
                            // Complete the animation of theprogress bar.
                            this.makeToast(
                                "danger",
                                this.$t("Please_fill_the_form_correctly"),
                                this.$t("Failed")
                            );
                        } else {
                            if (this.payment.amount > this.payment.received_amount) {
                                this.makeToast(
                                    "warning",
                                    this.$t("Paying_amount_is_greater_than_Received_amount"),
                                    this.$t("Warning")
                                );
                                this.payment.received_amount = 0;
                            } else if (this.payment.amount > this.grand_total) {
                                this.makeToast(
                                    "warning",
                                    this.$t("Paying_amount_is_greater_than_Grand_Total"),
                                    this.$t("Warning")
                                );
                                this.payment.amount = 0;
                            } else {
                                this.createPos();
                            }

                        }
                    });
            })

        },
        //------------- Submit Validation Create & Edit Customer
        submitCustomer() {
            // Start the progress bar.
            this.$refs.Create_Customer.validate()
                .then(success => {
                    if (!success) {
                        this.makeToast(
                            "danger",
                            this.$t("Please_fill_the_form_correctly"),
                            this.$t("Failed")
                        );
                    } else {
                        this.createClient();
                    }
                });
        },
        //---------------------------------------- Create new Customer -------------------------------\\
        createClient() {
            axios.post("clients", {
                name: this.client.name,
                email: this.client.email,
                phone: this.client.phone,
                tax_number: this.client.tax_number,
                country: this.client.country,
                city: this.client.city,
                address: this.client.address
            })
                .then(response => {
                    this.makeToast(
                        "success",
                        this.$t("Create.TitleCustomer"),
                        this.$t("Success")
                    );
                    this.getAllClients();
                    this.$bvModal.hide("New_Customer");
                })
                .catch(error => {
                    this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
                });
        },
        //------------------------------ New Model (create Customer) -------------------------------\\
        newClient() {
            // this.resetFormClient();
            this.$bvModal.show("New_Customer");
        },
        //-------------------------------- reset Form -------------------------------\\
        resetFormClient() {
            this.client = {
                id: "",
                name: "",
                email: "",
                phone: "",
                tax_number: "",
                country: "",
                city: "",
                Address: ""
            };
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
            this.getProductsByWarehouse(value);
            this.getProducts(1);
        },

        onClientSelected(selectedClient) {

            console.log('try search client', selectedClient);

            this.client_name = '';
            const client = this.clients.find(client => client.id === selectedClient);
            this.client_name = client.name;
        },

        //------------------------------------ Get Products By Warehouse -------------------------\\
        getProductsByWarehouse(id) {
            // Start the progress bar.
            this.execute(() => {
                axios.get("get_products_by_warehouse/" + id + "?stock=" + 1 + "&is_sale=" + 1 + "&product_service=" + 1)
                    .then(response => {
                        this.products_pos = response.data;
                    })
                    .catch(error => {
                        console.error('getProductsByWarehouse', error)
                    })
                    .finally(() => console.log('in callable finally'));


            })
        },
        //----------------------------------------- Add Detail of Sale -------------------------\\
        addProduct(code) {
            this.audio.play();
            if (this.details.some(detail => detail.code === code && !this.product.is_imei)) {
                this.incrementQtyScanner(code);
            } else {
                if (this.details.length > 0) {
                    this.orderDetailId();
                } else if (this.details.length === 0) {
                    this.product.detail_id = 1;
                }
                this.details.push(this.product);
                this.load_product = true;
                if (this.product.is_imei) {
                    this.showUpdateDetailModal(this.product);
                }
            }
        },
        //-------------------------------- order detail id -------------------------\\
        orderDetailId() {
            var len = this.details.length;
            this.product.detail_id = this.details[len - 1].detail_id + 1;
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
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === detail.detail_id) {
                    // this.convert_unit();
                    for (var k = 0; k < this.units.length; k++) {
                        if (this.units[k].id == detail.sale_unit_id) {
                            if (this.units[k].operator == "/") {
                                this.details[i].current =
                                    detail.fix_stock * this.units[k].operator_value;
                                this.details[i].unitSale = this.units[k].short_name;
                            } else {
                                this.details[i].current =
                                    detail.fix_stock / this.units[k].operator_value;
                                this.details[i].unitSale = this.units[k].short_name;
                            }
                        }
                    }
                    if (this.details[i].current < this.details[i].quantity) {
                        this.details[i].quantity = this.details[i].current;
                    } else {
                        this.details[i].quantity = 1;
                    }
                    this.details[i].unit_price = detail.unit_price;
                    this.details[i].tax_percent = detail.tax_percent;
                    this.details[i].tax_method = detail.tax_method;
                    this.details[i].discount_method = detail.discount_method;
                    this.details[i].discount = detail.discount;
                    this.details[i].sale_unit_id = detail.sale_unit_id;
                    this.details[i].imei_number = detail.imei_number;
                    this.details[i].product_type = detail.product_type;

                    if (this.details[i].discount_method == "2") {
                        //Fixed
                        this.details[i].discount_net = this.details[i].discount;
                    } else {
                        //Percentage %
                        this.details[i].discount_net = parseFloat(
                            (this.details[i].unit_price * this.details[i].discount) / 100
                        );
                    }
                    if (this.details[i].tax_method == "1") {
                        //Exclusive
                        this.details[i].net_price = parseFloat(
                            this.details[i].unit_price - this.details[i].discount_net
                        );
                        this.details[i].tax = parseFloat(
                            (this.details[i].tax_percent *
                                (this.details[i].unit_price - this.details[i].discount_net)) /
                            100
                        );
                        this.details[i].total_price = parseFloat(
                            this.details[i].net_price + this.details[i].tax
                        );
                    } else {
                        //Inclusive
                        this.details[i].net_price = parseFloat(
                            (this.details[i].unit_price - this.details[i].discount_net) /
                            (this.details[i].tax_percent / 100 + 1)
                        );
                        this.details[i].tax = parseFloat(
                            this.details[i].unit_price -
                            this.details[i].net_price -
                            this.details[i].discount_net
                        );
                        this.details[i].total_price = parseFloat(
                            this.details[i].net_price + this.details[i].tax
                        );
                    }
                }
            }
            console.log('complete update')
            this.calculateTotal();
            this.$nextTick(() => this.$bvModal.hide("POS:Modal:UpdateDetail"))
        },
        //-- check Qty of  details order if Null or zero
        verifiedForm() {
            if (this.details.length <= 0) {
                this.makeToast(
                    "warning",
                    this.$t("AddProductToList"),
                    this.$t("Warning")
                );
                return false;
            } else {
                var count = 0;
                for (var i = 0; i < this.details.length; i++) {
                    if (
                        this.details[i].quantity == "" ||
                        this.details[i].quantity === 0 ||
                        this.details[i].quantity > this.details[i].current
                    ) {
                        count += 1;
                        if (this.details[i].quantity > this.details[i].current) {
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
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
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
                .finally(() => {
                    // Complete the animation of the  progress bar.
                    setTimeout(() => NProgress.done(), 500);
                });
        },
        //----------------------------------Process Payment ------------------------------\\
        async processPayment() {
            this.payment_processing = true;
            const {token, error} = await this.stripe.createToken(this.card_element);
            if (error) {
                this.payment_processing = false;
                NProgress.done();
                this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
            } else {
                axios.post("pos/create_pos", this.createPaymentData(token.id))
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
                        NProgress.done();
                    })
            }
        },

        createPaymentData(token = null) {
            return {
                client_id: this.sale.client_id,
                warehouse_id: this.sale.warehouse_id,
                tax_rate: this.sale.tax_rate || 0,
                tax_net: this.sale.tax_net || 0,
                discount: this.sale.discount || 0,
                shipping: this.sale.shipping || 0,
                notes: this.notes,
                details: this.details,
                grand_total: this.grand_total,
                payment: this.payment,
                account_id: this.payment.account_id,
                amount: parseFloat(this.payment.amount).toFixed(2),
                received_amount: parseFloat(this.payment.received_amount).toFixed(2),
                change: parseFloat(this.payment.received_amount - this.payment.amount).toFixed(2),
                token: token?.id,
                is_new_credit_card: this.is_new_credit_card,
                selected_card: this.selected_card,
                card_id: this.card_id,
            }
        },

        //----------------------------------Create POS ------------------------------\\
        createPos() {
            this.execute(() => {
                if (this.payment.type === "credit card" && this.is_new_credit_card) {
                    if (this.stripe_key !== "") {
                        this.processPayment();
                    } else {
                        this.makeToast(
                            "danger",
                            this.$t("credit_card_account_not_available"),
                            this.$t("Failed")
                        );
                    }
                } else {
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
                }
            })

        },

        //---------------------------------Get Product Details ------------------------\\
        getProductDetails(product_id, variant_id) {
            this.execute(() => axios.get("/show_product_data/" + product_id + "/" + variant_id)
                .then(({data}) => {
                    this.setProduct(data, variant_id);
                    this.addProduct(data.code);
                    this.calculateTotal();
                }))

        },

        //----------- Set Product --------------------
        setProduct(data, variant_id) {
            this.product.discount = 0;
            this.product.discount_net = 0;
            this.product.discount_method = "2";
            this.product.product_id = data.id;
            this.product.product_type = data.product_type;
            this.product.name = data.name;
            this.product.net_price = data.net_price;
            this.product.total_price = data.total_price;
            this.product.unit_price = data.unit_price;
            this.product.discounted_price = data.discounted_price;
            this.product.tax = data.tax_price;
            this.product.tax_method = data.tax_method;
            this.product.tax_percent = data.tax_percent;
            this.product.unitSale = data.unitSale;
            this.product.product_variant_id = variant_id;
            this.product.code = data.code;
            this.product.fix_price = data.fix_price;
            this.product.sale_unit_id = data.sale_unit_id;
            this.product.is_imei = data.is_imei;
            this.product.imei_number = '';

        },
        //----------- Calculate Total ----------------
        calculateTotal() {
            this.total = 0
            for (const detail of this.details) {
                const tax = detail.tax * detail.quantity;
                detail.subtotal = detail.quantity * detail.net_price + tax;
                this.total += detail.subtotal;
            }
            const totalWithoutDiscount = this.total - this.sale.discount;
            this.sale.tax_net = (totalWithoutDiscount * this.sale.tax_rate) / 100;
            this.grand_total = totalWithoutDiscount + this.sale.tax_net + this.sale.shipping;
            this.grand_total = Number(this.grand_total.toFixed(2));
        },
        //-------Verified QTY
        verifiedQty(detail) {
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id === detail.id) {
                    if (isNaN(detail.quantity)) {
                        this.details[i].quantity = detail.current;
                    }
                    if (detail.quantity > detail.current) {
                        this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                        this.details[i].quantity = detail.current;
                    } else {
                        this.details[i].quantity = detail.quantity;
                    }
                }
            }
            this.$forceUpdate();
            this.calculateTotal();
        },
        //----------------------------------- Increment QTY with barcode scanner ------------------------------\\
        incrementQtyScanner(code) {
            this.incrementDetailBy('code', code)
            this.calculateTotal();
            this.$forceUpdate();
            this.$nextTick(() => this.load_product = true)
        },

        checkDetailQty(detail) {
            return detail.quantity + 1 > detail.current
        },

        incrementDetailBy(field, value) {
            this.details = this.details.map(detail => {
                if (detail[field] === value) {
                    if (this.checkDetailQty(this.detail)) {
                        this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                    } else {
                        return {...detail, quantity: detail.quantity + 1};
                    }
                }

                return detail;
            });
        },
        //----------------------------------- Increment QTY ------------------------------\\
        increment(detail) {
            this.incrementDetailBy('detail_id', detail.detail_id)
            this.calculateTotal();
            this.$forceUpdate();
        },
        //----------------------------------- decrement QTY ------------------------------\\
        decrement(detail) {
            for (var i = 0; i < this.details.length; i++) {
                if (this.details[i].detail_id == detail.detail_id) {
                    if (detail.quantity - 1 > detail.current || detail.quantity - 1 < 1) {
                        this.makeToast("warning", this.$t("LowStock"), this.$t("Warning"));
                    } else {
                        this.details[i].quantity--;
                    }
                }
            }
            this.calculateTotal();
            this.$forceUpdate();
        },

        //---------- keyup update sale entry

        keyupUpdateSale(entry) {
            if (isNaN(this.sale[entry]) ||
                !this.sale[entry] ||
                this.sale[entry] === '') {
                this.sale[entry] = 0;
            }
            this.calculateTotal();
        },

        //---------- keyup paid Amount
        verifyPaidAmount() {
            if (isNaN(this.payment.amount)) {
                this.payment.amount = 0;
            } else {
                if (this.payment.amount > this.payment.received_amount) {
                    this.makeToast(
                        "warning",
                        this.$t("Paying_amount_is_greater_than_Received_amount"),
                        this.$t("Warning")
                    );
                    this.payment.amount = 0;
                } else if (this.payment.amount > this.grand_total) {
                    this.makeToast(
                        "warning",
                        this.$t("Paying_amount_is_greater_than_Grand_Total"),
                        this.$t("Warning")
                    );
                    this.payment.amount = 0;
                }
            }
        },
        //---------- keyup Received Amount
        verifiedReceivedAmount() {
            if (isNaN(this.payment.received_amount)) {
                this.payment.received_amount = 0;
            }
        },
        //-----------------------------------Delete Detail Product ------------------------------\\
        deleteProductDetail(detail) {
            const index = this.details.findIndex(d => d.detail_id === detail.detail_id);
            if (index > -1) {
                this.details.splice(index, 1);
                this.calculateTotal();
            }
        },
        //----------Reset Pos
        reset_Pos() {
            this.details = [];
            this.product = {};
            this.payment = {
                amount: "",
                received_amount: "",
                type: "",
                notes: "",
            };

            this.saved_payment_methods = [],
                this.has_saved_payment_method = false,
                this.use_saved_payment_method = false,
                this.selected_card = null,
                this.card_id = '',
                this.is_new_credit_card = false,
                this.submit_showing_credit_card = false,
                this.sale.tax_rate = 0;
            this.sale.tax_net = 0;
            this.sale.shipping = 0;
            this.sale.discount = 0;
            this.grand_total = 0;
            this.total = 0;
            this.category_id = "";
            this.brand_id = "";
            this.client_name = "";
            this.getProducts(1);
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
                    this.product.current = result.qte_sale;
                    this.product.fix_stock = result.qte;
                    if (result.qte_sale < 1) {
                        this.product.quantity = result.qte_sale;
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
            if (this.sale.warehouse_id !== "" && this.sale.warehouse_id != null) {
                this.timer = setTimeout(() => {
                    const product_filter = this.products_pos.filter(product => product.code === this.search_input || product.barcode.includes(this.search_input));
                    if (product_filter.length === 1) {
                        this.checkProductExist(product_filter[0]);
                    } else {
                        this.product_filter = this.products_pos.filter(product => {
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

        //---------------------------------- Check if Product Exist in Order List ---------------------\\
        checkProductExist(product) {
            console.log('checkProductExist', product)
            this.execute(() => {
                this.load_product = false;
                this.product = {};
                if (product.product_type === 'is_service') {
                    this.product.quantity = 1;
                } else {
                    this.product.current = product.qte_sale;
                    this.product.fix_stock = product.qte;
                    this.product.quantity = product.qte_sale < 1 ? product.qte_sale : 1
                }
                this.getProductDetails(product.id, product.product_variant_id);
                this.search_input = '';
                this.product_filter = [];
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

        //------------------------------- Get Products with Filters ------------------------------\\
        getProducts(page = 1) {
            this.execute(() => {
                posClient.getProducts(this.category_id, this.brand_id, this.sale.warehouse_id, page)
                    .then(response => {
                        this.products = response.data.products;
                        this.product_totalRows = response.data.totalRows;
                        this.paginate_products();
                    })
                    .catch(error => console.error('getProducts', error))
            })

        },
        //---------------------------------------Get Elements ------------------------------\\
        init(serverResponse) {
            this.clients = serverResponse.data.clients;
            this.accounts = serverResponse.data.accounts;
            this.warehouses = serverResponse.data.warehouses;
            this.categories = serverResponse.data.categories;
            this.brands = serverResponse.data.brands;
            this.sale.warehouse_id = serverResponse.data.defaultWarehouse;
            this.sale.client_id = serverResponse.data.defaultClient;
            this.client_name = serverResponse.data.default_client_name;
            this.getProducts();
            if (serverResponse.data.defaultWarehouse !== "") {
                this.getProductsByWarehouse(serverResponse.data.defaultWarehouse);
            }
            this.paginate_Brands(this.brand_per_page, 0);
            this.paginateCategories(this.category_per_page, 0);
            this.stripe_key = serverResponse.data.stripe_key;

        }
    },
//-------------------- Created Function -----\\
    created() {

        this.cart_id = randomString(10);

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

        Fire.$on("event_delete_draft_sale",
            () => this.execute(() => this.getDraftSales(this.server_params.page)));

        Fire.$on("POS:Product:Select", this.checkProductExist);
        Fire.$on("POS:Detail:Update", this.showUpdateDetailModal);
        Fire.$on("POS:Detail:Increment", this.increment);
        Fire.$on("POS:Detail:Decrement", this.decrement);
        Fire.$on("POS:Detail:VerifyQty", this.verifiedQty);
        Fire.$on("POS:Detail:Delete", this.deleteProductDetail);

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
