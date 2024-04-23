<template>
  <div class="main-content">
    <breadcumb :page="$t('Payroll')" :folder="$t('hrm')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <b-card class="wrapper" v-if="!isLoading">
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="payrolls"
        @on-page-change="onPageChange"
        @on-per-page-change="onPerPageChange"
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
            @click="Function_New_Payroll()"
            class="btn-rounded"
            variant="btn btn-primary btn-icon m-1"
          >
            <i class="i-Add"></i>
            {{$t('Add')}}
          </b-button>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a @click="Function_Edit_Payroll(props.row)" class="cursor-pointer" title="Edit" v-b-tooltip.hover>
              <i class="i-Edit text-25 text-success"></i>
            </a>
            <a title="Delete" v-b-tooltip.hover class="cursor-pointer" @click="Remove_Payroll(props.row.id)">
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
          <div v-else-if="props.column.field == 'payment_status'">
            <span
              v-if="props.row.payment_status == 'paid'"
              class="badge badge-outline-success"
            >{{$t('Paid')}}</span>
          </div>
        </template>
      </vue-good-table>
    </b-card>

    <validation-observer ref="Create_Payroll">
      <b-modal hide-footer size="md" id="Modal_New_Payroll" :title="editmode?$t('Edit'):$t('Add')">
        <b-form @submit.prevent="Submit_Payroll">
          <b-row>

              <!-- date -->
              <b-col md="12">
                <validation-provider
                  name="date"
                  :rules="{ required: true}"
                  v-slot="validationContext"
                >
                    <b-form-group :label="$t('date') + ' ' + '*'">
                        <Datepicker id="date" name="date" :placeholder="$t('date')" v-model="payroll.date"
                            input-class="form-control back_important" format="yyyy-MM-dd"  @closed="payroll.date=formatDate(payroll.date)">
                        </Datepicker>
                        <b-form-invalid-feedback id="date-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                     </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Employee -->
              <b-col md="12">
                <validation-provider name="Employee" :rules="{ required: true}">
                  <b-form-group slot-scope="{ valid, errors }" :label="$t('Employee') + ' ' + '*'">
                    <v-select
                      :class="{'is-invalid': !!errors.length}"
                      :state="errors[0] ? false : (valid ? true : null)"
                      v-model="payroll.employee_id"
                      class="required"
                      required
                      @input="Selected_Employee"
                      :placeholder="$t('Choose_Employee')"
                      :reduce="label => label.value"
                      :options="employees.map(employees => ({label: employees.username, value: employees.id}))"
                    />
                    <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Account -->
              <b-col md="12">
                <validation-provider name="Account">
                  <b-form-group slot-scope="{ valid, errors }" :label="$t('Account')">
                    <v-select
                      :class="{'is-invalid': !!errors.length}"
                      :state="errors[0] ? false : (valid ? true : null)"
                      v-model="payroll.account_id"
                      @input="Selected_Account"
                      :reduce="label => label.value"
                      :placeholder="$t('Choose_Account')"
                      :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
                    />
                    <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Paying Amount  -->
              <b-col md="12">
                <validation-provider
                  name="Amount"
                  :rules="{ required: true , regex: /^\d*\.?\d*$/}"
                  v-slot="validationContext"
                >
                  <b-form-group :label="$t('Amount') + ' ' + '*'">
                    <b-form-input
                      @keyup="Verified_paidAmount(facture.montant)"
                      label="Amount"
                      type="number"
                      :placeholder="$t('Paying_Amount')"
                      v-model.number="payroll.amount"
                      :state="getValidationState(validationContext)"
                      aria-describedby="Amount-feedback"
                    ></b-form-input>
                    <b-form-invalid-feedback id="Amount-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Payment choice -->
              <b-col md="12">
              <validation-provider name="Payment choice" :rules="{ required: true}">
                <b-form-group slot-scope="{ valid, errors }" :label="$t('Paymentchoice') + ' ' + '*'">
                  <v-select
                    :class="{'is-invalid': !!errors.length}"
                    :state="errors[0] ? false : (valid ? true : null)"
                    v-model="payroll.payment_method"
                    :reduce="label => label.value"
                    :placeholder="$t('PleaseSelect')"
                    :options="
                          [
                          {label: 'Cash', value: 'Cash'},
                          {label: 'credit card', value: 'credit card'},
                          {label: 'TPE', value: 'tpe'},
                          {label: 'cheque', value: 'cheque'},
                          {label: 'Western Union', value: 'Western Union'},
                          {label: 'bank transfer', value: 'bank transfer'},
                          {label: 'other', value: 'other'},
                          ]"
                  ></v-select>
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
import Datepicker from 'vuejs-datepicker';

export default {
  metaInfo: {
    title: "Payroll"
  },
   components: {
    Datepicker
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
      editmode: false,
      employees:[],
      accounrs:[],
      payrolls: {},
      payroll: {
        date: new Date().toISOString().slice(0, 10),
        employee_id:"",
        account_id:"",
        amount:"",
        payment_method:"",
      },
    };
  },

  computed: {
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Reference"),
          field: "Ref",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Employee"),
          field: "employee_name",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Account"),
          field: "account_name",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("Amount"),
          field: "amount",
          type: "decimal",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("ModePayment"),
          field: "payment_method",
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
        },
        {
          label: this.$t("PaymentStatus"),
          field: "payment_status",
          html: true,
          tdClass: "text-left",
          thClass: "text-left",
          sortable: false
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


    //---------- keyup Received Amount

    Verified_paidAmount() {
      if (isNaN(this.payroll.amount)) {
        this.payroll.amount = 0;
      }
    },

    //---- update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Payrolls(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Payrolls(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },



    //---- Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Payrolls(this.serverParams.page);
    },

    //---- Validation State Form
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    formatDate(d){
        var m1 = d.getMonth()+1;
        var m2 = m1 < 10 ? '0' + m1 : m1;
        var d1 = d.getDate();
        var d2 = d1 < 10 ? '0' + d1 : d1;
        return [d.getFullYear(), m2, d2].join('-');
    },


    //------------- Submit Validation
    Submit_Payroll() {
      this.$refs.Create_Payroll.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
          if (!this.editmode) {
            this.Create_Payroll();
          } else {
            this.Update_Payroll();
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

   //------------------------------ Show Modal (Create Payroll) -------------------------------\\
    Function_New_Payroll() {
        this.reset_Form();
        this.editmode = false;
        this.$bvModal.show("Modal_New_Payroll");
    },

    //------------------------------ Show Modal (Update Payroll) -------------------------------\\
    Function_Edit_Payroll(payroll) {
        this.editmode = true;
        this.reset_Form();
        this.payroll = payroll;
        this.$bvModal.show("Modal_New_Payroll");
    },


    Selected_Account(value) {
        if (value === null) {
            this.payroll.account_id = "";
        }
    },

    Selected_Employee(value) {
        if (value === null) {
            this.payroll.employee_id = "";
        }
    },



    //--------------------------Get ALL payrolls ---------------------------\\

    Get_Payrolls(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "payroll?page=" +
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
          this.totalRows = response.data.totalRows;
          this.payrolls = response.data.payrolls;
          this.accounts = response.data.accounts;
          this.employees = response.data.employees;

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

    //------------------------------- Create payroll ------------------------\\
    Create_Payroll() {

        var self = this;
        self.SubmitProcessing = true;
        axios.post("/payroll", {
          date: self.payroll.date,
          employee_id: self.payroll.employee_id,
          account_id: self.payroll.account_id,
          amount: self.payroll.amount,
          payment_method: self.payroll.payment_method,
        }).then(response => {
            this.SubmitProcessing = false;
            Fire.$emit("Event_Payroll");
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

    //------------------------------- Update payroll ------------------------\\
    Update_Payroll() {

      var self = this;
      self.SubmitProcessing = true;
      axios.put("/payroll/" + self.payroll.id, {
          date: self.payroll.date,
          employee_id: self.payroll.employee_id,
          account_id: self.payroll.account_id,
          amount: self.payroll.amount,
          payment_method: self.payroll.payment_method,
      }).then(response => {
            this.SubmitProcessing = false;
            Fire.$emit("Event_Payroll");

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

    //------------------------------- reset Form ------------------------\\
    reset_Form() {
     this.payroll = {
        id: "",
        date: new Date().toISOString().slice(0, 10),
        employee_id:"",
        account_id:"",
        amount:"",
        payment_method:"",
    };
    },

    //------------------------------- Delete payroll ------------------------\\
    Remove_Payroll(id) {
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
            .delete("payroll/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );

              Fire.$emit("Event_delete_Payroll");
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


  },

  //----------------------------- Created function-------------------\\

  created: function() {
    this.Get_Payrolls(1);

    Fire.$on("Event_Payroll", () => {
      setTimeout(() => {
        this.Get_Payrolls(this.serverParams.page);
        this.$bvModal.hide("Modal_New_Payroll");
      }, 500);
    });

    Fire.$on("Event_delete_Payroll", () => {
      setTimeout(() => {
        this.Get_Payrolls(this.serverParams.page);
      }, 500);
    });
  }
};
</script>
