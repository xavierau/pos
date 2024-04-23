<template>
  <div class="main-content">
    <breadcumb :page="$t('Edit_Deposit')" :folder="$t('Deposits')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <validation-observer ref="Edit_Deposit" v-if="!isLoading">
      <b-form @submit.prevent="Submit_Deposit">
        <b-row>
          <b-col lg="12" md="12" sm="12">
            <b-card>
              <b-row>
                <!-- date  -->
                <b-col lg="4" md="6" sm="12">
                  <validation-provider
                    name="date"
                    :rules="{ required: true}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('date') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="date-feedback"
                        type="date"
                        v-model="deposit.date"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="OrderTax-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Account -->
                <b-col lg="4" md="6" sm="12">
                  <validation-provider name="Account">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('Account')">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="deposit.account_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Account')"
                        :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Deposit_Category  -->
                <b-col lg="4" md="6" sm="12">
                  <validation-provider name="category" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('deposit_Category') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="deposit.category_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Category')"
                        :options="deposit_category.map(deposit_category => 
                        ({label: deposit_category.title, value: deposit_category.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Amount  -->
                <b-col lg="4" md="4" sm="12">
                  <validation-provider
                    name="Amount"
                    :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                    v-slot="validationContext"
                  >
                    <b-form-group :label="$t('Amount') + ' ' + '*'">
                      <b-form-input
                        :state="getValidationState(validationContext)"
                        aria-describedby="Amount-feedback"
                        label="Amount"
                        type="number"
                        :placeholder="$t('Amount')"
                        v-model="deposit.amount"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="Amount-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- Details -->
                <b-col lg="8" md="8" sm="12">
                  <validation-provider name="Details">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('Details')">
                      <textarea
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="deposit.description"
                        rows="4"
                        class="form-control"
                        :placeholder="$t('Afewwords')"
                      ></textarea>
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <b-col md="12">
                  <b-form-group>
                    <b-button variant="primary" type="submit"  :disabled="SubmitProcessing"><i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}</b-button>
                      <div v-once class="typo__p" v-if="SubmitProcessing">
                        <div class="spinner sm spinner-primary mt-3"></div>
                      </div>
                  </b-form-group>
                </b-col>

              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-form>
    </validation-observer>
  </div>
</template>

<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Edit Deposit"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      accounts: [],
      deposit_category: [],
      deposit: {
        date: "",
        account_id: "",
        category_id: "",
        description: "",
        amount: "",
      }
    };
  },

  methods: {
    //------------- Submit Validation Update Deposit
    Submit_Deposit() {
      this.$refs.Edit_Deposit.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          this.Update_Deposit();
        }
      });
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //------ Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //--------------------------------- Update Deposit -------------------------\\
    Update_Deposit() {
      this.SubmitProcessing = true;
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      let id = this.$route.params.id;
      axios
        .put(`deposits/${id}`, {
          deposit: this.deposit
        })
        .then(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.makeToast(
            "success",
            this.$t("Successfully_Updated"),
            this.$t("Success")
          );
          this.SubmitProcessing = false;
          this.$router.push({ name: "index_deposit" });
        })
        .catch(error => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //---------------------------------------Get Expense Elements ------------------------------\\
    GetElements() {
      let id = this.$route.params.id;
      axios
        .get(`deposits/${id}/edit`)
        .then(response => {
          this.deposit = response.data.deposit;
          this.deposit_category = response.data.deposit_category;
          this.accounts = response.data.accounts;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    }
  },

  //----------------------------- Created function-------------------
  created() {
    this.GetElements();
  }
};
</script>