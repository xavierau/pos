<script>
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "ModalUpdateProduct",
    mixins: [helperMixin],
    props: {
        detail: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            Submit_Processing_detail: false,
            units: []
        }
    },
    methods: {
        submitUpdateDetail() {
            this.$emit('updateDetail', this.detail);
        },
    }
}
</script>

<template>
    <b-modal hide-footer size="lg" id="form_Update_Detail" :title="detail.name">
        <validation-observer ref="Update_Detail_quote">
            <b-form @submit.prevent="submitUpdateDetail">
                <b-row>
                    <!-- Unit Price -->
                    <b-col lg="6" md="6" sm="12">
                        <validation-provider
                            name="Product Price"
                            :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                            v-slot="validationContext"
                        >
                            <b-form-group :label="$t('ProductPrice') + ' ' + '*'" id="Price-input">
                                <b-form-input
                                    label="Product Price"
                                    v-model="detail.unit_price"
                                    :state="getValidationState(validationContext)"
                                    aria-describedby="Price-feedback"
                                ></b-form-input>
                                <b-form-invalid-feedback id="Price-feedback">{{
                                        validationContext.errors[0]
                                    }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
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
                           [
                            {label: 'Exclusive', value: '1'},
                            {label: 'Inclusive', value: '2'}
                           ]"
                                ></v-select>
                                <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
                    </b-col>

                    <!-- Tax Rate -->
                    <b-col lg="6" md="6" sm="12">
                        <validation-provider
                            name="Order Tax"
                            :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                            v-slot="validationContext"
                        >
                            <b-form-group :label="$t('OrderTax') + ' ' + '*'">
                                <b-input-group append="%">
                                    <b-form-input
                                        label="Order Tax"
                                        v-model="detail.tax_percent"
                                        :state="getValidationState(validationContext)"
                                        aria-describedby="OrderTax-feedback"
                                    ></b-form-input>
                                </b-input-group>
                                <b-form-invalid-feedback id="OrderTax-feedback">{{
                                        validationContext.errors[0]
                                    }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
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
                        <validation-provider
                            name="Discount Rate"
                            :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                            v-slot="validationContext"
                        >
                            <b-form-group :label="$t('Discount') + ' ' + '*'">
                                <b-form-input
                                    label="Discount"
                                    v-model="detail.discount"
                                    :state="getValidationState(validationContext)"
                                    aria-describedby="Discount-feedback"
                                ></b-form-input>
                                <b-form-invalid-feedback id="Discount-feedback">{{
                                        validationContext.errors[0]
                                    }}
                                </b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
                    </b-col>

                    <!-- Unit Sale -->
                    <b-col lg="6" md="6" sm="12" v-if="detail.product_type != 'is_service'">
                        <validation-provider name="Unit Sale" :rules="{ required: true}">
                            <b-form-group slot-scope="{ valid, errors }" :label="$t('UnitSale') + ' ' + '*'">
                                <v-select
                                    :class="{'is-invalid': !!errors.length}"
                                    :state="errors[0] ? false : (valid ? true : null)"
                                    v-model="detail.sale_unit_id"
                                    :placeholder="$t('Choose_Unit_Sale')"
                                    :reduce="label => label.value"
                                    :options="units.map(units => ({label: units.name, value: units.id}))"
                                />
                                <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                            </b-form-group>
                        </validation-provider>
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
                            <b-button variant="primary" type="submit" :disabled="Submit_Processing_detail"><i
                                class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                            </b-button>
                            <div v-once class="typo__p" v-if="Submit_Processing_detail">
                                <div class="spinner sm spinner-primary mt-3"></div>
                            </div>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-form>
        </validation-observer>
    </b-modal>


</template>

<style scoped>

</style>
