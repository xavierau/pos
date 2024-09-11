<script>
import helperMixin from "../../../mixins/helperMethods";
import {convertFeeXOverYAmountInput, convertFeeXOverYAmountRule} from "../../../utils/promotionHelpers";

export default {
    name: "FreeXOverYAmountRule",
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
                x_product_code: "",
                x_qty: null,
                y_amount: null,
            },
        }
    },
    methods: {
        convertedRule() {
            return convertFeeXOverYAmountInput(this.my_rule, this.products)
        }
    },
    created() {
        if (!!this.rule) {
            this.my_rule = convertFeeXOverYAmountRule(this.rule)
        }
    }
}
</script>

<template>
    <div>
        <fieldset>
            <legend><small>X Product</small></legend>
            <b-row>
                <!-- X product  and qty -->
                <b-col lg="6" md="6" sm="12">
                    <validation-provider name="Select X Product" :rules="{ required: true}">
                        <b-form-group
                            slot-scope="{ valid, errors }"
                            :label="$t('Select X Product') + ' ' + '*'"
                        >
                            <v-select
                                :class="{'is-invalid': !!errors.length}"
                                :state="errors[0] ? false : (valid ? true : null)"
                                v-model="my_rule.x_product_code"
                                :reduce="product => [product.product_id, product.product_variant_id].filter(p=>p).join('_')"
                                :placeholder=" $t('select_product')"
                                label="name"
                                :options="products"
                            ></v-select>
                            <b-form-invalid-feedback>{{
                                    errors[0]
                                }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>
                <b-col lg="6" md="6" sm="12">
                    <validation-provider
                        name="X Product Qty"
                        :rules="{required:true, min:0 }"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('X Product Qty') + ' ' + '*'">
                            <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="x_qty-feedback"
                                label="X Product Qty"
                                type="number"
                                min="0"
                                :placeholder="$t('X Product Qty')"
                                v-model.number="my_rule.x_qty"
                            ></b-form-input>
                            <b-form-invalid-feedback id="Name-feedback">{{
                                    validationContext.errors[0]
                                }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </validation-provider>
                </b-col>
            </b-row>
        </fieldset>
        <fieldset>
            <legend><small>Y Amount</small></legend>
            <b-row>
                <!-- Y amount -->
                <b-col lg="6" md="6" sm="12">
                    <validation-provider
                        name="y_amount"
                        :rules="{required:true, min:0 }"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('Y Amount') + ' ' + '*'">
                            <b-form-input
                                :state="getValidationState(validationContext)"
                                aria-describedby="y_amount-feedback"
                                name="y_amount"
                                label="Y Amount"
                                type="number"
                                min="0"
                                :placeholder="$t('Y Amount')"
                                v-model.number="my_rule.y_amount"
                            ></b-form-input>
                            <b-form-invalid-feedback id="y_amount-feedback">{{
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
