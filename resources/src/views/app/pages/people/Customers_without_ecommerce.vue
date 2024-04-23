<template>
  <div class="main-content">
    <breadcumb page="Customers Unregistered in Online Store" :folder="$t('Customers')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
    <div v-else>
      <div class="mb-5">
        <span class="alert alert-danger" v-show="clients_without_ecommerce > 0">
          There are <strong>{{ clients_without_ecommerce}}</strong> 
          customers not having an account in the online store.
        </span>
      </div>
      <vue-good-table
        mode="remote"
        :columns="columns"
        :totalRows="totalRows"
        :rows="clients"
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
       :styleClass="showDropdown?'tableOne table-hover vgt-table full-height':'tableOne table-hover vgt-table non-height'"
      >

      <template slot="table-row" slot-scope="props">
          <span v-if="props.column.field == 'actions'">
            <a class="btn btn-primary"  @click="Edit_Client(props.row)">
              <span class="text-white"><i class="i-Yes me-2 font-weight-bold"></i> Register Account</span>
            </a>
           
          </span>
        </template>

      </vue-good-table>
    </div>


    <!-- Modal Create store account for Customer -->
    <validation-observer ref="Create_Customer">
      <b-modal hide-footer size="md" id="New_Customer" title="Register Account">
        <b-form @submit.prevent="Submit_Customer">
          <b-row>

            <!-- Customer email -->
            <b-col md="12" sm="12">
              <validation-provider
                name="email Customer"
                :rules="{ required: true}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('Email') + ' ' + '*'">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="email-feedback"
                    label="email"
                    :placeholder="$t('Email')"
                    v-model="client.email"
                  ></b-form-input>
                  <b-form-invalid-feedback id="email-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                  <b-alert
                    show
                    variant="danger"
                    class="error mt-1"
                    v-if="email_exist !=''"
                  >{{email_exist}}</b-alert>
                </b-form-group>
              </validation-provider>
            </b-col>

             <!-- password -->
             <b-col md="12" sm="12">
              <validation-provider
                name="password"
                :rules="{ required: true , min:6 , max:14}"
                v-slot="validationContext"
              >
                <b-form-group :label="$t('password') + ' ' + '*'">
                  <b-form-input
                    :state="getValidationState(validationContext)"
                    aria-describedby="password-feedback"
                    label="password"
                    type="password"
                    v-model="client.password"
                    :placeholder="$t('password')"
                  ></b-form-input>
                  <b-form-invalid-feedback id="password-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
                </b-form-group>
              </validation-provider>
            </b-col>
          

            <b-col md="12" class="mt-3">
                <b-button variant="primary" type="submit"  :disabled="SubmitProcessing">{{$t('submit')}}</b-button>
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
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "Customer Without Ecommerce"
  },
  data() {
    return {

      clients_without_ecommerce:'',
      email_exist : "",
      
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
      showDropdown: false,
     
      totalRows: "",
      search: "",
      limit: "10",
      clients: [],
      client: {
        id: "",
        email: "",
        password: "",
      }
    };
  },

   mounted() {
    this.$root.$on("bv::dropdown::show", bvEvent => {
      this.showDropdown = true;
    });
    this.$root.$on("bv::dropdown::hide", bvEvent => {
      this.showDropdown = false;
    });
  },

  computed: {
    ...mapGetters(["currentUserPermissions", "currentUser"]),


    columns() {
      return [
        {
          label: this.$t("Code"),
          field: "code",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Name"),
          field: "name",
          tdClass: "text-left",
          thClass: "text-left"
        },

        {
          label: this.$t("Phone"),
          field: "phone",
          tdClass: "text-left",
          thClass: "text-left"
        },
        {
          label: this.$t("Email"),
          field: "email",
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

    //------------- Submit Validation Create & Edit Customer
    Submit_Customer() {
      this.$refs.Create_Customer.validate().then(success => {
        if (!success) {
          this.makeToast(
            "danger",
            this.$t("Please_fill_the_form_correctly"),
            this.$t("Failed")
          );
        } else {
            this.Create_Client();
        }
      });
    },

    //------ update Params Table
    updateParams(newProps) {
      this.serverParams = Object.assign({}, this.serverParams, newProps);
    },

    //---- Event Page Change
    onPageChange({ currentPage }) {
      if (this.serverParams.page !== currentPage) {
        this.updateParams({ page: currentPage });
        this.Get_Clients(currentPage);
      }
    },

    //---- Event Per Page Change
    onPerPageChange({ currentPerPage }) {
      if (this.limit !== currentPerPage) {
        this.limit = currentPerPage;
        this.updateParams({ page: 1, perPage: currentPerPage });
        this.Get_Clients(1);
      }
    },


    //------ Event Sort Change
    onSortChange(params) {
      this.updateParams({
        sort: {
          type: params[0].type,
          field: params[0].field
        }
      });
      this.Get_Clients(this.serverParams.page);
    },

    //------ Event Search
    onSearch(value) {
      this.search = value.searchTerm;
      this.Get_Clients(this.serverParams.page);
    },

    //------ Event Validation State
    getValidationState({ dirty, validated, valid = null }) {
      return dirty || validated ? valid : null;
    },

    //------ Toast
    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, {
        title: title,
        variant: variant,
        solid: true
      });
    },

    //--------------------------------------- Get All Clients -------------------------------\\
    Get_Clients(page) {
      // Start the progress bar.
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get(
          "clients_without_ecommerce?page=" +
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
          this.clients = response.data.clients;
          this.totalRows = response.data.totalRows;
          this.clients_without_ecommerce = response.data.clients_without_ecommerce;

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


    //------------------------------ Show Modal (Edit Client) -------------------------------\\
    Edit_Client(client) {
      this.Get_Clients(this.serverParams.page);
      this.reset_Form();
      this.client = client;
      this.$bvModal.show("New_Customer");
    },

    //---------------------------------------- Create new Client -------------------------------\\
    Create_Client() {
      this.SubmitProcessing = true;
      axios
        .post("clients_without_ecommerce", {
          client_id: this.client.id,
          email: this.client.email,
          password: this.client.password,
        
        })
        .then(response => {
          Fire.$emit("Event_Customer");

          this.makeToast(
            "success",
            "Account Registred Successfully",
            this.$t("Success")
          );
          this.SubmitProcessing = false;
        })
        .catch(error => {

          if (error.errors.email.length > 0) {
            this.email_exist = error.errors.email[0];
          }
          
          this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          this.SubmitProcessing = false;
        });
    },

    //-------------------------------- Reset Form -------------------------------\\
    reset_Form() {
      this.client = {
        id: "",
        email: "",
        password: "",
      };
      this.email_exist= "";
    },


  }, // END METHODS

  //----------------------------- Created function-------------------

  created: function() {
    this.Get_Clients(1);

    Fire.$on("Event_Customer", () => {
      setTimeout(() => {
        this.Get_Clients(this.serverParams.page);
        this.$bvModal.hide("New_Customer");
      }, 500);
    });

  }
};
</script>
