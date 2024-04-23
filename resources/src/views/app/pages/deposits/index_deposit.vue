<template>
  <div class="main-content">
    <breadcumb :page="$t('List_Deposit')" :folder="$t('Deposits')"/>

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="deposits"
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
        styleClass="tableOne table-hover vgt-table"
      >
        <div slot="selected-row-actions">
          <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{$t('Del')}}</button>
        </div>
        <div slot="table-actions" class="mt-2 mb-3">
          <b-button variant="outline-info ripple m-1" size="sm" v-b-toggle.sidebar-right>
            <i class="i-Filter-2"></i>
            {{ $t("Filter") }}
          </b-button>
          <b-button @click="Deposit_PDF()" size="sm" variant="outline-success ripple m-1">
            <i class="i-File-Copy"></i> PDF
          </b-button>
           <vue-excel-xlsx
              class="btn btn-sm btn-outline-danger ripple m-1"
              :data="deposits"
              :columns="columns"
              :file-name="'Deposits'"
              :file-type="'xlsx'"
              :sheet-name="'Deposits'"
              >
              <i class="i-File-Excel"></i> EXCEL
          </vue-excel-xlsx>
          <router-link
            class="btn-sm btn btn-primary ripple btn-icon m-1"
            v-if="currentUserPermissions && currentUserPermissions.includes('deposit_add')"
            to="/app/deposits/store"
          >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
            <span class="ul-btn__text ml-1">{{$t('Add')}}</span>
          </router-link>
        </div>

        <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('deposit_edit')"
              title="Edit"
              v-b-tooltip.hover
              :to="'/app/deposits/edit/'+props.row.id"
            >
              <i class="i-Edit text-25 text-success"></i>
            </router-link>
            <a
              title="Delete"
              class="cursor-pointer"
              v-b-tooltip.hover
              v-if="currentUserPermissions && currentUserPermissions.includes('deposit_delete')"
              @click="Remove_Deposit(props.row.id)"
            >
              <i class="i-Close-Window text-25 text-danger"></i>
            </a>
          </span>
        </template>
      </vue-good-table>
    </div>

    <!-- Multiple Filters -->
    <b-sidebar id="sidebar-right" :title="$t('Filter')" bg-variant="white" right shadow>
      <div class="px-3 py-2">
        <b-row>
          <!-- date  -->
          <b-col md="12">
            <b-form-group :label="$t('date')">
              <b-form-input type="date" v-model="Filter_date"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Reference  -->
          <b-col md="12">
            <b-form-group :label="$t('Reference')">
              <b-form-input label="Reference" :placeholder="$t('Reference')" v-model="Filter_Ref"></b-form-input>
            </b-form-group>
          </b-col>

          <!-- Account  -->
          <b-col md="12">
            <b-form-group :label="$t('Account')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Account')"
                v-model="Filter_account"
                :options="accounts.map(accounts => ({label: accounts.account_name, value: accounts.id}))"
              />
            </b-form-group>
          </b-col>

          <!-- deposit_Category  -->
          <b-col md="12">
            <b-form-group :label="$t('deposit_Category')">
              <v-select
                :reduce="label => label.value"
                :placeholder="$t('Choose_Category')"
                v-model="Filter_category"
                :options="deposit_Category.map(deposit_Category => ({label: deposit_Category.title, value: deposit_Category.id}))"
              />
            </b-form-group>
          </b-col>

          <b-col md="6" sm="12">
            <b-button
              @click="Get_Deposits(serverParams.page)"
              variant="primary m-1"
              size="sm"
              block
            >
              <i class="i-Filter-2"></i>
              {{ $t("Filter") }}
            </b-button>
          </b-col>
          <b-col md="6" sm="12">
            <b-button @click="Reset_Filter()" variant="danger m-1" size="sm" block>
              <i class="i-Power-2"></i>
              {{ $t("Reset") }}
            </b-button>
          </b-col>
        </b-row>
      </div>
    </b-sidebar>
  </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";

export default {
  metaInfo: {
    title: "Deposit"
  },
  data() {
    return {
      isLoading: true,
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
      Filter_date: "",
      Filter_Ref: "",
      Filter_account: "",
      Filter_category: "",
      deposits: [],
      accounts: [],
      deposit_Category: []
    };
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),
    columns() {
      return [
        {
          label: this.$t("date"),
          field: "date",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Reference"),
          field: "deposit_ref",
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
          label: this.$t("Categorie"),
          field: "category_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Account"),
          field: "account_name",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Details"),
          field: "description",
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
    //---------------------- Expenses PDF -------------------------------\\
    Deposit_PDF() {
      var self = this;

      let pdf = new jsPDF("p", "pt");
      let columns = [
        { title: "Date", dataKey: "date" },
        { title: "Reference", dataKey: "Ref" },
        { title: "Amount", dataKey: "amount" },
        { title: "Category", dataKey: "category_name" },
        { title: "Account", dataKey: "account_name" }
      ];
      pdf.autoTable(columns, self.deposits);
      pdf.text("Deposit List", 40, 25);
      pdf.save("Deposit_List.pdf");
    },

    //------ update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Deposits(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Deposits(1);
      }
    },

    //---- Event Select Rows
    selectionChanged({ selectedRows }) {
      this.selectedIds = [];
      selectedRows.forEach((row, index) => {
        this.selectedIds.push(row.id);
      });
    },

    //------ Event Sort Change
    onSortChange(params) {
      let field = "";
      if (params[0].field == "account_name") {
        field = "account_id";
      } else if (params[0].field == "category_name") {
        field = "deposit_category_id";
      } else {
        field = params[0].field;
      }
      this.updateParams({
        sort: {
          type: params[0].type,
          field: field
        }
      });
      this.Get_Deposits(this.serverParams.page);
    },

    //------ Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Deposits(this.serverParams.page);
    },

    //------ Reset Filter
    Reset_Filter() {
      this.search = "";
      this.Filter_date = "";
      this.Filter_Ref = "";
      this.Filter_account = "";
      this.Filter_category = "";
      this.Get_Deposits(this.serverParams.page);
    },

    // Simply replaces null values with strings=''
    setToStrings() {
      if (this.Filter_account === null) {
        this.Filter_account = "";
      } else if (this.Filter_category === null) {
        this.Filter_category = "";
      }
    },

    //------------------------------------------------ Get All deposits -------------------------------\\
    Get_Deposits(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      this.setToStrings();
      axios
        .get(
          "deposits?page=" +
            page +
            "&deposit_ref=" +
            this.Filter_Ref +
            "&account_id=" +
            this.Filter_account +
            "&date=" +
            this.Filter_date +
            "&deposit_category_id=" +
            this.Filter_category +
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
          this.deposits = response.data.deposits;
          this.deposit_Category = response.data.Deposits_category;
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

    //------------------------------- Remove deposit -------------------------\\

    Remove_Deposit(id) {
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
          // Start the progress bar.
          NProgress.start();
          NProgress.set(0.1);
          axios
            .delete("deposits/" + id)
            .then(() => {
              this.$swal(
                this.$t("Delete.Deleted"),
                this.$t("Deleted_in_successfully"),
                "success"
              );
              Fire.$emit("event_delete_deposit");
            })
            .catch(() => {
              // Complete the animation of theprogress bar.
              setTimeout(() => NProgress.done(), 500);
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

  //----------------------------- Created function-------------------
  created: function() {
    this.Get_Deposits(1);

    Fire.$on("event_delete_deposit", () => {
      setTimeout(() => {
        // Complete the animation of theprogress bar.
        NProgress.done();
        this.Get_Deposits(this.serverParams.page);
      }, 500);
    });
  }
};
</script>