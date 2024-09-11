<template>
    <div class="auth-layout-wrap">
        <div class="auth-content">
            <div class="card o-hidden">
                <div class="row">
                    <div class="col-md-12">
                        <div class="p-4">
                            <div class="auth-logo text-center mb-30">
                                <img src="/images/logo.jpeg">
                            </div>
                            <h1 class="mb-3 text-18">{{ $t('SignIn') }}</h1>
                            <validation-observer ref="submit_login">
                                <b-form @submit.prevent="Submit_Login">
                                    <validation-provider
                                        name="employee_code"
                                        :rules="{ required: true}"
                                        v-slot="validationContext"
                                    >
                                        <b-form-group :label="$t('employee_code')" class="text-12">
                                            <b-form-input
                                                :state="getValidationState(validationContext)"
                                                aria-describedby="employee_code-feedback"
                                                class="form-control-rounded"
                                                v-model="employee_code"
                                            ></b-form-input>
                                            <b-form-invalid-feedback
                                                id="employee_code-feedback"
                                            >{{ validationContext.errors[0] }}
                                            </b-form-invalid-feedback>
                                        </b-form-group>
                                    </validation-provider>

                                    <b-button
                                        type="submit"
                                        tag="button"
                                        class="btn-rounded btn-block mt-2"
                                        variant="primary mt-2"
                                        :disabled="loading"
                                    >{{ $t('SignIn') }}
                                    </b-button>
                                    <div v-once class="typo__p" v-if="loading">
                                        <div class="spinner sm spinner-primary mt-3"></div>
                                    </div>
                                </b-form>
                            </validation-observer>

                            <div class="mt-3 text-center">
                                <a href="/password/reset" class="text-muted">
                                    <u>{{ $t('Forgot_Password') }}</u>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {mapGetters} from "vuex";
import NProgress from "nprogress";

export default {
    name: "SimpleSingIn",
    metaInfo: {
        title: "SignIn"
    },
    data() {
        return {
            employee_code: "",
            userId: "",
            loading: false
        };
    },
    computed: {
        ...mapGetters(["isAuthenticated", "error"])
    },
    methods: {
        //------------- Submit Form login
        Submit_Login() {
            this.$refs.submit_login.validate().then(success => {
                if (!success) {
                    this.makeToast(
                        "danger",
                        this.$t("Please_fill_the_form_correctly"),
                        this.$t("Failed")
                    );
                } else {
                    this.Login();
                }
            });
        },

        getValidationState({dirty, validated, valid = null}) {
            return dirty || validated ? valid : null;
        },

        Login() {
            let self = this;
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            self.loading = true;
            axios
                .post("/simple_login", {
                        employee_code: self.employee_code,
                    },
                    {
                        baseURL: '',
                    })
                .then(response => {
                    this.makeToast(
                        "success",
                        this.$t("Successfully_Logged_In"),
                        this.$t("Success")
                    );

                    console.log('response', response.data)
                    console.log('response', response)
                    window.location = '/app/simple_pos';
                })
                .catch(error => {
                    this.makeToast(
                        "danger",
                        this.$t("Incorrect_Login"),
                        this.$t("Failed")
                    );
                })
                .finally(() => {
                    NProgress.done();
                    this.loading = false;
                });
        },

        //------ Toast
        makeToast(variant, msg, title) {
            this.$root.$bvToast.toast(msg, {
                title: title,
                variant: variant,
                solid: true
            });
        }
    }
};
</script>
