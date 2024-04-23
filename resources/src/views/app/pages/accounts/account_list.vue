<template>
  <div class="main-content">
    <breadcumb :page="$t('List_accounts')" :folder="$t('Accounting')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="accounts"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
        @on-sort-change="onSortChange"
        @on-search="onSearch"
        :search-options="{
          enabled: true,
          placeholder: $t('Search_this_table'),  
        }"
      :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
        styleClass="table-hover tableOne vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button
            @click="New_Account()"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_Account(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success cursor-pointer"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Remove_Account(props.row.id)">
              <i class="i-Close-Window text-25 text-danger cursor-pointer"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Account">
      <b-modal hide-footer size="md" id="New_Account" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Account">
          <b-row>
            <!-- account_num -->
            <b-col md="12">
              <validation-provider
                name="account_num"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('account_num') + ' ' + '*'">
                  <b-form-input
                    :placeholder="$t('Enter_account_num')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="account_num-feedback"
                    label="account_num"
                    v-model="account.account_num"
                  ></b-form-input>
                  <b-form-invalid-feedback id="account_num-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- Name account -->
            <b-col md="12">
              <validation-provider
                name="Name account"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('account_name') + ' ' + '*'">
                  <b-form-input
                    :placeholder="$t('Enter_account_name')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="account_name-feedback"
                    label="account_name"
                    v-model="account.account_name"
                  ></b-form-input>
                  <b-form-invalid-feedback id="account_name-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

            <!-- initial_balance -->
            <b-col md="12" v-if="!editmode">
              <validation-provider
                name="initial_balance"
                :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('initial_balance') + ' ' + '*'">
                  <b-form-input
                    type="number"
                    :placeholder="$t('Enter_initial_balance')"
                    :state="getValidationState(validationContext)"
                    aria-describedby="initial_balance-feedback"
                    label="initial_balance"
                    v-model="account.initial_balance"
                  ></b-form-input>
                  <b-form-invalid-feedback id="initial_balance-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>

             <!-- Details -->
             <b-col lg="12" md="12" sm="12">
                <validation-provider name="Details">
                  <b-form-group slot-scope="{ valid, errors }" :label="$t('Details')">
                    <textarea
                      :class="{'is-invalid': !!errors.length}"
                      :state="errors[0] ? false : (valid ? true : null)"
                      v-model="account.note"
                      rows="4"
                      class="form-control"
                      :placeholder="$t('Afewwords')"
                    ></textarea>
                    <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

             <b-col md="12" class="mt-3">
                <b-button variant="primary" type="submit"  :disabled="SubmitProcessing"><i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}</b-button>
                  <div v-once class="typo__p" v-if="SubmitProcessing">
                    <div class="spinner sm spinner-primary mt-3"></div>
                  </div>
            </b-col>

          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>
  </div>
</template>


<script>
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Account"
  },
  data() {
    return {
      isLoading: true,
      SubmitProcessing:false,
      serverParams: {
        columnFilters: {},
        sort: {
          field: "id",
          type: "desc"
        },
        page: 1,
        perPage: 10
      },
      selectedIds: [],
      totalRows: "",
      search: "",
      limit: "10",
      accounts: [],
      editmode: false,

      account: {
        id: "",
        account_num: "",
        account_name: "",
        initial_balance: 0,
        note: ""
      }
    };
  },
  computed: {
    columns() {
      return [
        {
          label: this.$t("account_num"),
          field: "account_num",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("account_name"),
          field: "account_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("balance"),
          field: "balance",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("notes"),
          field: "note",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Action"),
          field: "actions",
          html: true,
          tdClass: "text-right",
          thClass: "text-right",
          sortable: false
        }
      ];
    }
  },

  methods: {
    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Accounts(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Accounts(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //---- Event on Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Accounts(this.serverParams.page);
    },

    //---- Event on Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Accounts(this.serverParams.page);
    },

    //---- Validation State Form

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation Create & Edit account
    Submit_Account() {
      this.$refs.Create_Account.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Account();
          } else {
            this.Update_Category();
          }
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

    //------------------------------ Modal  (create account) -------------------------------\\
    New_Account() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Account");
    },

    //------------------------------ Modal (Update account) -------------------------------\\
    Edit_Account(account) {
      this.Get_Accounts(this.serverParams.page);
      this.reset_Form();
      this.account = account;
      this.editmode = true;
      this.$bvModal.show("New_Account");
    },

    //--------------------------Get ALL Categories & Sub account ---------------------------\\

    Get_Accounts(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "accounts?page=" +
            page +
            "&SortField=" +
            this.serverParams.sort.field +
            "&SortType=" +
            this.serverParams.sort.type +
            "&search=" +
            this.search +
            "&limit=" +
            this.limit
        )
        .then(response => {
          this.accounts = response.data.accounts;
          this.totalRows = response.data.totalRows;

          // Complete the animation of theprogress bar.
          NProgress.done();
          this.isLoading = false;
        })
        .catch(response => {
          // Complete the animation of theprogress bar.
          NProgress.done();
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },

    //----------------------------------Create new account ----------------\\
    Create_Account() {
      this.SubmitProcessing = true;
      axios
        .post("accounts", {
          account_num: this.account.account_num,
          account_name: this.account.account_name,
          initial_balance: this.account.initial_balance,
          note: this.account.note,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Account");
          this.makeToast(
            "success",
            this.$t("Created_in_successfully"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //---------------------------------- Update account ----------------\\
    Update_Category() {
      this.SubmitProcessing = true;
      axios
        .put("accounts/" + this.account.id, {
          account_num: this.account.account_num,
          account_name: this.account.account_name,
          note: this.account.note,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("Event_Account");
          this.makeToast(
            "success",
            this.$t("Updated_in_successfully"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
        });
    },

    //--------------------------- reset Form ----------------\\

    reset_Form() {
      this.account = {
        id: "",
        account_num: "",
        account_name: "",
        initial_balance: 0,
        note: "",
      };
    },

    //--------------------------- Remove account----------------\\
    Remove_Account(id) {
      this.$swal({
        title: this.$t("Delete.Title"),
        text: this.$t("Delete.Text"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: this.$t("Delete.cancelButtonText"),
        confirmButtonText: this.$t("Delete.confirmButtonText")
      }).then(result => {
        if (result.value) {
          axios
            .delete("accounts/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );

              Fire.$emit("Event_delete_Account");
            })
            .catch(() => {
              this.$swal(
                this.$t("Delete.Failed"),
                this.$t("Delete.Therewassomethingwronge"),
                "warning"
              );
            });
        }
      });
    },

  
  }, //end Methods

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Accounts(1);

    Fire.$on("Event_Account", () => {
      setTimeout(() => {
        this.Get_Accounts(this.serverParams.page);
        this.$bvModal.hide("New_Account");
      }, 500);
    });

    Fire.$on("Event_delete_Account", () => {
      setTimeout(() => {
        this.Get_Accounts(this.serverParams.page);
      }, 500);
    });
  }
};
</script>