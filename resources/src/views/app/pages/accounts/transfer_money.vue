<template>
  <div class="main-content">
    <breadcumb :page="$t('Transfers_Money')" :folder="$t('Accounting')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="transfers"
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
      
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button
            @click="New_Transfer()"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Edit_transfer_money(props.row)" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success cursor-pointer"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover @click="Remove_transfers_money(props.row.id)">
              <i class="i-Close-Window text-25 text-danger cursor-pointer"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_transfer_money">
      <b-modal hide-footer size="lg" id="New_Transfer" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_transfer_money">
          <b-row>

             <!-- date  -->
             <b-col lg="6" md="6" sm="12">
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
                        v-model="transfer.date"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="OrderTax-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                 <!-- Amount  -->
                 <b-col lg="6" md="6" sm="12">
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
                        v-model.number="transfer.amount"
                      ></b-form-input>
                      <b-form-invalid-feedback
                        id="Amount-feedback"
                      >{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                <!-- From Account -->
                <b-col lg="6" md="6" sm="12" v-if="!editmode">
                  <validation-provider name="From_Account" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('From_Account') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="transfer.from_account_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Account')"
                        :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
                      />
                      <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                    </b-form-group>
                  </validation-provider>
                </b-col>

                  <!-- To Account -->
                  <b-col lg="6" md="6" sm="12" v-if="!editmode">
                  <validation-provider name="To_Account" :rules="{ required: true}">
                    <b-form-group slot-scope="{ valid, errors }" :label="$t('To_Account') + ' ' + '*'">
                      <v-select
                        :class="{'is-invalid': !!errors.length}"
                        :state="errors[0] ? false : (valid ? true : null)"
                        v-model="transfer.to_account_id"
                        :reduce="label => label.value"
                        :placeholder="$t('Choose_Account')"
                        :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
                      />
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
    title: "Transfer Money"
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
      totalRows: "",
      search: "",
      limit: "10",
      transfers: [],
      accounts: [],
      editmode: false,

      transfer: {
        id: "",
        from_account_id: "",
        to_account_id: "",
        amount: "",
        date: new Date().toISOString().slice(0, 10),
      }
    };
  },
  computed: {
    columns() {
      return [
      
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("From_Account"),
          field: "from_account",
          tdClass: "text-left",
          thClass: "text-left"
        },

        {
          label: this.$t("To_Account"),
          field: "to_account",
          tdClass: "text-left",
          thClass: "text-left"
        },

        {
          label: this.$t("Amount"),
          field: "amount",
          type: "decimal",
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
        this.get_transfers_money(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.get_transfers_money(1);
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
      this.get_transfers_money(this.serverParams.page);
    },

    //---- Event on Search

    onSearch(value) {
      this.search = value.searchTerm;
      this.get_transfers_money(this.serverParams.page);
    },

    //---- Validation State Form

    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------------- Submit Validation
    Submit_transfer_money() {
      this.$refs.Create_transfer_money.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_transfer_money();
          } else {
            this.Update_transfers_money();
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

    //------------------------------ Modal  (create transfer) -------------------------------\\
    New_Transfer() {
      this.reset_Form();
      this.editmode = false;
      this.$bvModal.show("New_Transfer");
    },

    //------------------------------ Modal (Update transfer) -------------------------------\\
    Edit_transfer_money(transfer) {
      this.get_transfers_money(this.serverParams.page);
      this.reset_Form();
      this.transfer = transfer;
      this.editmode = true;
      this.$bvModal.show("New_Transfer");
    },

    //--------------------------Get ALL Categories & Sub account ---------------------------\\

    get_transfers_money(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "transfer_money?page=" +
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
          this.transfers = response.data.transfers;
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

    //----------------------------------Create new transfer ----------------\\
    Create_transfer_money() {
      if (this.transfer.from_account_id === this.transfer.to_account_id) {
        this.makeToast("danger", this.$t("Accounts_cannot_be_the_same"), this.$t("Failed"));
        return;
      }

      this.SubmitProcessing = true;
      axios
        .post("transfer_money", {
          from_account_id: this.transfer.from_account_id,
          to_account_id: this.transfer.to_account_id,
          amount: this.transfer.amount,
          date: this.transfer.date,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("event_transfers_money");
          this.makeToast(
            "success",
            this.$t("Created_in_successfully"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", error.error, this.$t("Failed"));
        });
    },

    //---------------------------------- Update transfer ----------------\\
    Update_transfers_money() {
      this.SubmitProcessing = true;
      axios
        .put("transfer_money/" + this.transfer.id, {
          from_account_id: this.transfer.from_account_id,
          to_account_id: this.transfer.to_account_id,
          amount: this.transfer.amount,
          date: this.transfer.date,
        })
        .then(response => {
          this.SubmitProcessing = false;
          Fire.$emit("event_transfers_money");
          this.makeToast(
            "success",
            this.$t("Updated_in_successfully"),
            this.$t("Success")
          );
        })
        .catch(error => {
          this.SubmitProcessing = false;
          this.makeToast("danger", error.error, this.$t("Failed"));
        });
    },

    //--------------------------- reset Form ----------------\\

    reset_Form() {
      this.transfer = {
        id: "",
        from_account_id: "",
        to_account_id: "",
        amount: "",
        date: new Date().toISOString().slice(0, 10),
      };
    },

    //--------------------------- Remove transfer----------------\\
    Remove_transfers_money(id) {
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
            .delete("transfer_money/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );

              Fire.$emit("Event_delete_transfer");
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
    this.get_transfers_money(1);

    Fire.$on("event_transfers_money", () => {
      setTimeout(() => {
        this.get_transfers_money(this.serverParams.page);
        this.$bvModal.hide("New_Transfer");
      }, 500);
    });

    Fire.$on("Event_delete_transfer", () => {
      setTimeout(() => {
        this.get_transfers_money(this.serverParams.page);
      }, 500);
    });
  }
};
</script>