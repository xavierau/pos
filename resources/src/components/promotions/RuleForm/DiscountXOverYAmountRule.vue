<script>
import helperMixin from "../../../mixins/helperMethods";
import {convertDiscountXOverYAmountInput, convertDiscountXOverYAmountRule} from "../../../utils/promotionHelpers";

export default {
    name: "DiscountXOverYAmountRule",
    mixins: [helperMixin],
    props: {
        rule: {
            type: Object,
        },
        products: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            my_rule: {
                discount_type: 'percentage',
                discount_amount: 0,
                threshold: 0,
            },
            discount_types: [
                {
                    value: 'percentage',
                    label: 'Percentage'
                },
                {
                    value: 'fixed',
                    label: 'Fixed'
                }
            ]
        }
    },
    methods: {
        convertedRule() {
            return convertDiscountXOverYAmountInput(this.my_rule)
        }
    },
    created() {
        if (!!this.rule) {
            this.my_rule = convertDiscountXOverYAmountRule(this.rule)
        }
    }
}
</script>

<template>
    <div>
        <fieldset>
            <legend><small>Discount</small></legend>
            <b-row>
                <!-- X product  and qty -->
                <b-col lg="6" md="6" sm="12">
                    <validation-provider name="Discount type" :rules="{ required: true}">
                        <b-form-group
                            slot-scope="{ valid, errors }"
                            :label="$t('Discount Type') + ' ' + '*'"
                        >
                            <v-select
                                :class="{'is-invalid': !!errors.length}"
                                :state="errors[0] ? false : (valid ? true : null)"
                                v-model="my_rule.discount_type"
                                :reduce="label => label.value"
                                :placeholder="$t('discount_type')"
                                :options="discount_types.map((product, index) => ({ value: index, label: product.label }))"
                            ></v-select>
                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>
                <b-col lg="6" md="6" sm="12">
                    <validation-provider
                        name="Discount amount"
                        :rules="{required:true, min:0 }"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('Discount Amount') + ' ' + '*'">
                            <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="discount_amount-feedback"
                                label="Discount Amount"
                                type="number"
                                min="0"
                                :placeholder="$t('Discount Amount')"
                                v-model.number="my_rule.discount_amount"
                            ></b-form-input>
                            <b-form-invalid-feedback id="discount_amount-feedback">{{ validationContext.errors[0] }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>
            </b-row>
        </fieldset>
        <fieldset>
            <legend><small>Net total over how much</small></legend>
            <b-row>
                <!-- Y amount -->
                <b-col lg="6" md="6" sm="12">
                    <validation-provider
                        name="threshold"
                        :rules="{required:true, min:0 }"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('threshold') + ' ' + '*'">
                            <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="threshold-feedback"
                                name="threshold"
                                label="Net amount"
                                type="number"
                                min="0"
                                :placeholder="$t('Net amount')"
                                v-model.number="my_rule.threshold"
                            ></b-form-input>
                            <b-form-invalid-feedback id="threshold-feedback">{{
                                    validationContext.errors[0]
                                }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>
            </b-row>
        </fieldset>
    </div>

</template>

<style scoped>

</style>
