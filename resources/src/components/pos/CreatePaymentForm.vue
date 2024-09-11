<script>
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "CreatePaymentForm",
    mixins: [helperMixin],
    props: {
        order: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            paymentMethods: [],
            accounts: [],
            notes: {
                payment: '',
                sale: ''
            }
        }
    },
    methods: {
        submit() {
        }
    }
}
</script>

<template>
    <validation-observer ref="Add_payment">
        <b-form @submit.prevent="submit">
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
    </validation-observer>

</template>

<style scoped>

</style>
