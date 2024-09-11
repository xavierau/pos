<script>
import BuyXGetYRule from "./RuleForm/BuyXGetYRule.vue";
import FreeXOverYAmountRule from "./RuleForm/FreeXOverYAmountRule.vue";
import DiscountXOverYAmountRule from "./RuleForm/DiscountXOverYAmountRule.vue";
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "CreatePromotionForm",
    mixins: [helperMixin],
    components: {
        BuyXGetYRule,
        FreeXOverYAmountRule,
        DiscountXOverYAmountRule
    },
    data() {
        return {
            types: [],
            products: [],
            promotion: {
                name: '',
                type: null,
                start_date: null,
                end_date: null,
                max_applications_per_sale: 1,
                max_usage: null
            }
        }
    },
    computed: {
        rule_form() {
            if (this.promotion.type === 'buy_x_get_y') {
                return "BuyXGetYRule"
            } else if (this.promotion.type === "free_x_if_over_y_amount") {
                return "FreeXOverYAmountRule"
            } else if (this.promotion.type === "discount_x_percentage_if_over_y_amount") {
                return "DiscountXOverYAmountRule"
            }
            return null
        }
    },
    methods: {
        submitPromotion() {
            this.$refs.create_promotion.validate().then(success => {
                if (!success) {
                    this.makeDangerToast("Please_fill_the_form_correctly", 'Failed')
                } else {
                    const data = {
                        ...this.promotion,
                        rule: {...this.$refs.promotion_rule.convertedRule(), type: this.promotion.type}
                    }

                    this.$emit('submit-promotion', data);
                }
            });

        }
    },
    created() {
        axios.get('/promotions/types')
            .then(response => {
                console.log(response.data)

                this.types = response.data.types
            })

        axios.get('/promotions/products')
            .then(response => {
                console.log(response.data)
                this.products = response.data.products
            })
    }
}
</script>

<template>
    <validation-observer ref="create_promotion">
        <b-form @submit.prevent="submitPromotion">
            <b-row>
                <b-col lg="12" md="12" sm="12">
                    <b-card>
                        <fieldset>
                            <legend>Basic Promotion Info</legend>
                            <b-row>
                                <!-- promotion name -->
                                <b-col md="6" class="mb-3">
                                    <validation-provider
                                        name="Promotion Name"
                                        :rules="{required:true , min:3 , max:55}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('Promotions:Name') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="promotion_name-feedback"
                                                label="Promotion Name"
                                                :placeholder="$t('Promotions:Name')"
                                                v-model="promotion.name"
                                            ></b-form-input>
                                            <b-form-invalid-feedback id="promotion_name-feedback">{{
                                                    validationContext.errors[0]
                                                }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                                <!-- promotion types -->
                                <b-col md="6" class="mb-3">
                                    <validation-provider name="promotion type" :rules="{ required: true}">
                                        <b-form-group
                                            slot-scope="{ valid, errors }"
                                            :label="$t('Promotions:ChooseType') + ' ' + '*'"
                                        >
                                            <v-select
                                                :class="{'is-invalid': !!errors.length}"
                                                :state="errors[0] ? false : (valid ? true : null)"
                                                v-model="promotion.type"
                                                :reduce="label => label.value"
                                                :placeholder="$t('Promotions:ChooseType')"
                                                :options="types"
                                            ></v-select>
                                            <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                            </b-row>
                            <b-row>
                                <!-- start date  -->
                                <b-col lg="6" md="6" sm="12" class="mb-3">
                                    <validation-provider
                                        name="start_date"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('start_date') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="start_date-feedback"
                                                type="date"
                                                v-model="promotion.start_date"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="start_date-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                                <!-- end date  -->
                                <b-col lg="6" md="6" sm="12" class="mb-3">
                                    <validation-provider
                                        name="end_date"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('end_date') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="end_date-feedback"
                                                type="date"
                                                v-model="promotion.end_date"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="end_date-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                            </b-row>
                            <b-row>
                                <!-- max usage per order  -->
                                <b-col lg="6" md="6" sm="12" class="mb-3">
                                    <validation-provider
                                        name="max_applications_per_sale"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('Max Usage Per Order') + ' ' + '*'">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="max_applications_per_sale-feedback"
                                                type="number"
                                                min="1"
                                                step="1"
                                                v-model.number="promotion.max_applications_per_sale"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="start_date-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                                <!-- max usage  -->
                                <b-col lg="6" md="6" sm="12" class="mb-3">
                                    <validation-provider
                                        name="max_usage"
                                        :rules="{ }"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('Maximum Total Usage')">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="max_usage-feedback"
                                                type="number"
                                                v-model.number="promotion.max_usage"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="end_date-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>
                                </b-col>
                            </b-row>
                        </fieldset>

                        <fieldset>
                            <legend>Rule Setting</legend>
                            <component :is="rule_form" ref="promotion_rule" :products="products"></component>
                        </fieldset>
                        <b-row>
                            <b-col md="12" class="mt-3">
                                <b-button variant="primary" type="submit" :disabled="SubmitProcessing"><i
                                    class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                                </b-button>
                                <div v-once class="typo__p" v-if="SubmitProcessing">
                                    <div class="spinner sm spinner-primary mt-3"></div>
                                </div>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
        </b-form>
    </validation-observer>

</template>

<style scoped>

</style>
