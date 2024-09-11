<script>
import helperMixin from "../../mixins/helperMethods";
import {PosEvents} from "../../utils/FireEvent";

export default {
    name: "CreateCustomerForm",
    mixins: [helperMixin],
    props: {
        isLoading: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            client: {
                name: "",
                email: "",
                phone: "",
                country: "",
                city: "",
                address: "",
            },
        };
    },
    methods: {
        submit() {
            this.$refs.Create_Customer.validate()
                .then(success => {
                    if (!success) {
                        this.makeDangerToast("Please_fill_the_form_correctly", "Failed");
                    } else {
                        Fire.$emit(PosEvents.CreateCustomer, this.client);
                    }
                });
        }
    }
}
</script>

<template>
    <validation-observer ref="Create_Customer">
        <b-form @submit.prevent="submit">
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
                    <validation-provider
                        name="Name Customer"
                        :rules="{ required: true}"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('Email')  + ' ' + '*'">
                            <b-form-input
                                label="email"
                                type="email"
                                v-model="client.email"
                                :placeholder="$t('Email')"
                            ></b-form-input>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                <!-- Customer Phone -->
                <b-col md="6" sm="12">
                    <validation-provider
                        name="Name Customer"
                        :rules="{ required: true}"
                        v-slot="validationContext"
                    >
                        <b-form-group :label="$t('Phone') + ' ' + '*'">
                            <b-form-input
                                label="Phone"
                                type="tel"
                                v-model="client.phone"
                                :placeholder="$t('Phone')"
                            ></b-form-input>
                        </b-form-group>
                    </validation-provider>
                </b-col>

                <!--                &lt;!&ndash; Customer Country &ndash;&gt;-->
                <!--                <b-col md="6" sm="12">-->
                <!--                    <b-form-group :label="$t('Country')">-->
                <!--                        <b-form-input-->
                <!--                            label="Country"-->
                <!--                            v-model="client.country"-->
                <!--                            :placeholder="$t('Country')"-->
                <!--                        ></b-form-input>-->
                <!--                    </b-form-group>-->
                <!--                </b-col>-->

                <!--                &lt;!&ndash; Customer City &ndash;&gt;-->
                <!--                <b-col md="6" sm="12">-->
                <!--                    <b-form-group :label="$t('City')">-->
                <!--                        <b-form-input-->
                <!--                            label="City"-->
                <!--                            v-model="client.city"-->
                <!--                            :placeholder="$t('City')"-->
                <!--                        ></b-form-input>-->
                <!--                    </b-form-group>-->
                <!--                </b-col>-->

                <!--                &lt;!&ndash; Customer Address &ndash;&gt;-->
                <!--                <b-col md="12" sm="12">-->
                <!--                    <b-form-group :label="$t('Address')">-->
                <!--                      <textarea-->
                <!--                          label="Address"-->
                <!--                          class="form-control"-->
                <!--                          rows="4"-->
                <!--                          v-model="client.address"-->
                <!--                          :placeholder="$t('Address')"-->
                <!--                      ></textarea>-->
                <!--                    </b-form-group>-->
                <!--                </b-col>-->

                <b-col md="12" class="mt-3">
                    <b-button variant="primary" type="submit" :disable="isLoading"><i
                        class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                    </b-button>
                </b-col>
            </b-row>
        </b-form>
    </validation-observer>
</template>

<style scoped>

</style>
