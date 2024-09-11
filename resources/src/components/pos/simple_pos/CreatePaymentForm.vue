<script>
import {mapGetters} from "vuex"
import helperMixin from "../../../mixins/helperMethods";
import {PosEvents} from "../../../utils/FireEvent";

export default {
    name: "CreatePaymentForm",
    mixins: [helperMixin],
    computed: {
        ...mapGetters(['supportedPaymentTypes', 'getAccounts', "items", "payment", "cartNetTotal", "discount", "shipping"]),
    },
    methods: {
        submit() {
            this.$refs.Add_payment.validate()
                .then(success => {
                    if (!success) {
                        this.makeDangerToast("Please_fill_the_form_correctly", "Failed");
                    } else {
                        Fire.$emit(PosEvents.SubmitPayment)
                    }
                });
        }
    },
    mounted() {
        this.payment.received_amount = this.cartNetTotal
        this.payment.type = this.supportedPaymentTypes.find(t => t.default)?.value || 'cash'
        this.payment.account_id = this.getAccounts.find(a => a.default)?.value || 1
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
                                        autofocus
                                        required
                                        type="number"
                                        step="0.01"
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
                                        disabled
                                        label="Paying_Amount"
                                        :placeholder="$t('Paying_Amount')"
                                        :value="cartNetTotal"
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
                            >{{ parseFloat(payment.received_amount - cartNetTotal).toFixed(2) }}</p>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col md="6">
                    <b-card>
                        <b-list-group>
                            <b-list-group-item
                                class="d-flex justify-content-between align-items-center">
                                {{ $t('TotalProducts') }}
                                <b-badge variant="primary" pill>{{ items.length }}</b-badge>
                            </b-list-group-item>
                            <b-list-group-item
                                class="d-flex justify-content-between align-items-center">
                                {{ $t('Discount') }}
                                <span
                                    class="font-weight-bold"
                                >{{ currentUser.currency }} {{ discount.toFixed(2) }}</span>
                            </b-list-group-item>
                            <b-list-group-item
                                class="d-flex justify-content-between align-items-center">
                                {{ $t('Shipping') }}
                                <span
                                    class="font-weight-bold"
                                >{{ currentUser.currency }} {{ shipping.toFixed(2) }}</span>
                            </b-list-group-item>
                            <b-list-group-item
                                class="d-flex justify-content-between align-items-center">
                                {{ $t('Total_Payable') }}
                                <span
                                    class="font-weight-bold"
                                >{{ currentUser.currency }} {{ cartNetTotal.toFixed(2) }}</span>
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
                                :options="getAccounts.map(a => ({label: a.label, value: a.id}))"
                            />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
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
                            v-model="notes"
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
