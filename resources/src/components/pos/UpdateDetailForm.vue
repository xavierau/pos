<script>
import Util from "../../utils";
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "UpdateDetailForm",
    mixin: [helperMixin],
    props: {
        units: {
            type: Array,
            default: []
        },
        detail: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            _detail: {},
        }
    },
    created() {
        this._detail = Object.assign({}, this.detail)
    },
    methods: {
        submitUpdateDetail() {
            this.$refs.update_detail.validate()
                .then(success => {
                    if (!success) {
                        console.error()
                        return;
                    }
                    this.$emit('update-detail', this._detail);
                })
        },
        getValidationState({dirty, validated, validFailed}) {
            return Util.getValidationState(dirty, validated, validFailed);
        }
    }
}
</script>

<template>
    <validation-observer ref="update_detail">
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
                                v-model="_detail.unit_price"
                                :state="getValidationState(validationContext)"
                                aria-describedby="Price-feedback"
                            ></b-form-input>
                            <b-form-invalid-feedback
                                id="Price-feedback"
                            >{{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                <!--                &lt;!&ndash; Tax Method &ndash;&gt;-->
                <!--                <b-col lg="6" md="6" sm="12">-->
                <!--                    <validation-provider name="Tax Method" :rules="{ required: true}">-->
                <!--                        <b-form-group slot-scope="{ valid, errors }"-->
                <!--                                      :label="$t('TaxMethod') + ' ' + '*'">-->
                <!--                            <v-select-->
                <!--                                :class="{'is-invalid': !!errors.length}"-->
                <!--                                :state="errors[0] ? false : (valid ? true : null)"-->
                <!--                                v-model="_detail.tax_method"-->
                <!--                                :reduce="label => label.value"-->
                <!--                                :placeholder="$t('Choose_Method')"-->
                <!--                                :options="-->
                <!--                           [-->
                <!--                            {label: 'Exclusive', value: '1'},-->
                <!--                            {label: 'Inclusive', value: '2'}-->
                <!--                           ]"-->
                <!--                            ></v-select>-->
                <!--                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>-->
                <!--                        </b-form-group>-->
                <!--                    </validation-provider>-->
                <!--                </b-col>-->

                <!--                &lt;!&ndash; Tax &ndash;&gt;-->
                <!--                <b-col lg="6" md="6" sm="12">-->
                <!--                    <validation-provider-->
                <!--                        name="Tax"-->
                <!--                        :rules="{ required: true , regex: /^\d*\.?\d*$/}"-->
                <!--                        v-slot="validationContext"-->
                <!--                    >-->
                <!--                        <b-form-group :label="$t('Tax') + ' ' + '*'">-->
                <!--                            <b-input-group append="%">-->
                <!--                                <b-form-input-->
                <!--                                    label="Tax"-->
                <!--                                    v-model="_detail.tax_percent"-->
                <!--                                    :state="getValidationState(validationContext)"-->
                <!--                                    aria-describedby="Tax-feedback"-->
                <!--                                ></b-form-input>-->
                <!--                            </b-input-group>-->
                <!--                            <b-form-invalid-feedback-->
                <!--                                id="Tax-feedback"-->
                <!--                            >{{ validationContext.errors[0] }}-->
                <!--                            </b-form-invalid-feedback>-->
                <!--                        </b-form-group>-->
                <!--                    </validation-provider>-->
                <!--                </b-col>-->

                <!-- Discount Method -->
                <b-col lg="6" md="6" sm="12">
                    <validation-provider name="Discount Method" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }"
                                      :label="$t('Discount_Method') + ' ' + '*'">
                            <v-select
                                v-model="_detail.discount_method"
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
                                v-model="_detail.discount"
                                :state="getValidationState(validationContext)"
                                aria-describedby="Discount-feedback"
                            ></b-form-input>
                            <b-form-invalid-feedback
                                id="Discount-feedback"
                            >{{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                <!-- Unit Sale -->
                <b-col lg="6" md="6" sm="12" v-if="_detail.product_type != 'is_service'">
                    <validation-provider name="Unit Sale" :rules="{ required: true}">
                        <b-form-group slot-scope="{ valid, errors }"
                                      :label="$t('UnitSale') + ' ' + '*'">
                            <v-select
                                disabled="disabled"
                                :class="{'is-invalid': !!errors.length}"
                                :state="errors[0] ? false : (valid ? true : null)"
                                v-model="_detail.sale_unit_id"
                                :placeholder="$t('Choose_Unit_Sale')"
                                :reduce="label => label.value"
                                :options="units.map(units => ({label: units.name, value: units.id}))"
                            />
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                <!-- Imei or serial numbers -->
                <b-col lg="12" md="12" sm="12" v-show="_detail.is_imei">
                    <b-form-group :label="$t('Add_product_IMEI_Serial_number')">
                        <b-form-input
                            label="Add_product_IMEI_Serial_number"
                            v-model="_detail.imei_number"
                            :placeholder="$t('Add_product_IMEI_Serial_number')"
                        ></b-form-input>
                    </b-form-group>
                </b-col>

                <b-col md="12">
                    <b-form-group>
                        <b-button variant="primary" type="submit">{{ $t('submit') }}</b-button>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-form>
    </validation-observer>
</template>

<style scoped>

</style>
