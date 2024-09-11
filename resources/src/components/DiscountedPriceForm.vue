<script>

export default {
    name: "DiscountedPriceForm",
    props: {
        discountedPrice: {
            type: Object,
        },
        canSubmit: {
            type: Boolean,
            default: true,
        },
    },
    methods: {
        confirm() {
            this.$emit('confirm', this.discountedPrice);
        },
    },
}

</script>

<template>
    <b-form @submit.prevent="confirm">
        <b-row>
            <!-- Start Date -->
            <b-col md="12">
                <validation-provider
                    name="Start Date"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                >
                    <b-form-group :label="$t('Start Date') + ' ' + '*'">
                        <b-form-input
                            type="date"
                            label="Start Date"
                            v-model="discountedPrice.start_date"
                        ></b-form-input>
                        <b-form-invalid-feedback id="start_date-feedback">{{ validationContext.errors[0] }}
                        </b-form-invalid-feedback>
                    </b-form-group>
                </validation-provider>
            </b-col>

            <!-- End Date -->
            <b-col md="12">
                <validation-provider
                    name="End Date"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                >
                    <b-form-group :label="$t('End Date') + ' ' + '*'">
                        <b-form-input
                            type="date"
                            label="End Date"
                            v-model="discountedPrice.end_date"
                        ></b-form-input>
                        <b-form-invalid-feedback id="end_date-feedback">{{
                            validationContext.errors[0]
                            }}
                        </b-form-invalid-feedback>
                    </b-form-group>
                </validation-provider>
            </b-col>

            <!-- Price -->
            <b-col md="12">
                <validation-provider
                    name="discounted_price"
                    :rules="{ required: true }"
                    v-slot="validationContext"
                >
                    <b-form-group :label="$t('Discounted Price') + ' ' + '*'">
                        <b-form-input
                            type="numeric"
                            min="0"
                            label="Discounted Price"
                            v-model="discountedPrice.discounted_price"
                        ></b-form-input>
                        <b-form-invalid-feedback id="discounted_price-feedback">{{
                            validationContext.errors[0]
                            }}
                        </b-form-invalid-feedback>
                    </b-form-group>

                    <b-col md="12">
                        <b-button variant="primary" type="submit" :disabled="!canSubmit"><i
                            class="i-Yes me-2 font-weight-bold"></i> {{ $t('update') }}
                        </b-button>
                    </b-col>

                </validation-provider>
            </b-col>

        </b-row>
    </b-form>
</template>
