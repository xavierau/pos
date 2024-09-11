<script>
import {mapGetters} from 'vuex'
import {PurchaseEvent} from "../../utils/FireEvent";
import ValidationInput from "../common/ValidationInput.vue";
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "UpdateProductItemForm",
    mixins: [helperMixin],
    components: {
        ValidationInput
    },
    props: {
        detail: {
            type: Object,
            required: true
        },
        is_loading: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        ...mapGetters(['currentUserPermissions', 'currentUser']),
        events: () => PurchaseEvent
    }
}
</script>

<template>
    <b-form @submit.prevent="$emit('submit')">
        <b-row>
            <!-- Unit Cost -->
            <b-col lg="6" md="6" sm="12">
                <ValidationInput name="ProductCost"
                                 :rules="{ required: true , regex: /^\d*\.?\d*$/}">
                    <template #default="{ validationContext }">
                        <b-form-input
                            label="Product Cost"
                            v-model.number="detail.unit_cost"
                            :state="getValidationState(validationContext)"
                            aria-describedby="cost-feedback"
                        ></b-form-input>
                    </template>
                </ValidationInput>
            </b-col>

            <!-- Tax Method -->
            <b-col lg="6" md="6" sm="12">
                <validation-provider name="Tax Method" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('TaxMethod') + ' ' + '*'">
                        <v-select
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            v-model="detail.tax_method"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Method')"
                            :options="
                                           [{label: 'Exclusive', value: '1'},
                                            {label: 'Inclusive', value: '2'}]"
                        ></v-select>
                        <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                </validation-provider>
            </b-col>

            <!-- Tax Rate -->
            <b-col lg="6" md="6" sm="12">
                <ValidationInput name="OrderTax"
                                 :rules="{ required: true , regex: /^\d*\.?\d*$/}">
                    <template #default="{ validationContext }">
                        <b-input-group append="%">
                            <b-form-input
                                label="Order Tax"
                                v-model.number="detail.tax_percent"
                                :state="getValidationState(validationContext)"
                                aria-describedby="OrderTax-feedback"
                            ></b-form-input>
                        </b-input-group>
                    </template>
                </ValidationInput>
            </b-col>

            <!-- Discount Method -->
            <b-col lg="6" md="6" sm="12">
                <validation-provider name="Discount Method" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('Discount_Method') + ' ' + '*'">
                        <v-select
                            v-model="detail.discount_method"
                            :reduce="label => label.value"
                            :placeholder="$t('Choose_Method')"
                            :class="{'is-invalid': !!errors.length}"
                            :state="errors[0] ? false : (valid ? true : null)"
                            :options="
                           [
                            {label: 'Percent %', value: '1'},
                            {label: 'Fixed', value: '2'}
                           ]"
                        ></v-select>
                        <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                </validation-provider>
            </b-col>

            <!-- Discount Rate -->
            <b-col lg="6" md="6" sm="12">
                <ValidationInput name="Discount"
                                 :rules="{ required: true , regex: /^\d*\.?\d*$/}">
                    <template #default="{ validationContext }">
                        <b-input-group v-if="detail.discount_method === '1'" append="%" >
                            <b-form-input
                                label="Discount"
                                v-model.number="detail.discount"
                                :state="getValidationState(validationContext)"
                                aria-describedby="Discount-feedback"
                            ></b-form-input>
                        </b-input-group>

                        <b-input-group v-else prepend="$">
                            <b-form-input
                                label="Discount"
                                v-model.number="detail.discount"
                                :state="getValidationState(validationContext)"
                                aria-describedby="Discount-feedback"
                            ></b-form-input>
                        </b-input-group>
                    </template>
                </ValidationInput>
            </b-col>

            <!-- Imei or serial numbers -->
            <b-col lg="12" md="12" sm="12" v-show="detail.is_imei">
                <b-form-group :label="$t('Add_product_IMEI_Serial_number')">
                    <b-form-input
                        label="Add_product_IMEI_Serial_number"
                        v-model="detail.imei_number"
                        :placeholder="$t('Add_product_IMEI_Serial_number')"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col md="12">
                <b-form-group>
                    <b-button variant="primary" type="submit" :disabled="is_loading"><i
                        class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                    </b-button>
                    <div v-once class="typo__p" v-if="is_loading">
                        <div class="spinner sm spinner-primary mt-3"></div>
                    </div>
                </b-form-group>
            </b-col>
        </b-row>
    </b-form>
</template>

<style scoped>

</style>
