<template>
    <div class="main-content">
      <breadcumb :page="$t('module_settings')" :folder="$t('Settings')"/>
      <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>
  
      <b-col md="12"  v-if="!isLoading">
        <validation-observer ref="ref_Upload_Module">
            <b-form @submit.prevent="Submit_Upload_Module" enctype="multipart/form-data">
                <div class="row border rounded p-3 mt-3">
                    <b-col md="12">
                      <validation-provider name="Upload Module" ref="Upload_Module">
                        <b-form-group slot-scope="{validate, valid, errors }" label="Install/Upload Module">
                          <input
                            :state="errors[0] ? false : (valid ? true : null)"
                            :class="{'is-invalid': !!errors.length}"
                            @change="onFileSelected"
                            label="Upload_Module"
                            type="file"
                          >
                          <b-form-invalid-feedback id="Upload_Module-feedback">{{ errors[0] }}</b-form-invalid-feedback>
                        </b-form-group>
                      </validation-provider>
                      <span>The module must be uploaded as zip file</span>
                    </b-col>
  
                    <b-col md="12" class="text-center mt-3">
                      <b-button variant="primary" type="submit"  :disabled="SubmitProcessing">Upload Module</b-button>
                        <div v-once class="typo__p" v-if="SubmitProcessing">
                          <div class="spinner sm spinner-primary mt-3"></div>
                        </div>
                    </b-col>
                </div>
            </b-form>
        </validation-observer>
      </b-col>
  
  
      <div class="mt-5" v-if="!isLoading">
         <b-col md="12">
           <h4 v-show="modules_info.length >0">All Modules Installed</h4>
         </b-col>
       <div class="col-md-6" v-for="module_item in modules_info" :key="module_item">
         <div class="row border rounded p-3 mt-3">
           <div class="col-md-12">
             <h3>{{module_item.module_name}}</h3>
           </div>
           <div class="col-md-12">
             <span><strong>Current Version</strong> : {{module_item.current_version}}</span>
           </div>
          <div class="col-md-12 mt-3">
              <label class="switch switch-primary mr-3">
                  <input @change="update_status_module(module_item)" v-model="module_item.status"  type="checkbox">
                  <span class="slider"></span>
                </label>
           </div>
         </div>
       </div> 
   </div> 
    </div>
  </template>
  
  <script>
  import { mapActions, mapGetters } from "vuex";
  import NProgress from "nprogress";
  
  export default {
    metaInfo: {
      title: "Module Settings"
    },
    data() {
      return {
        
        isLoading: true,
        SubmitProcessing:false,
        modules_info:[],
        module_zip:'',
        data: new FormData(),
       
      };
    },
  
    methods: {
      ...mapActions(["refreshUserPermissions"]),
  
      //------ Toast
      makeToast(variant, msg, title) {
        this.$root.$bvToast.toast(msg, {
          title: title,
          variant: variant,
          solid: true
        });
      },
  
      
      //------------------------------ Event Upload Module -------------------------------\\
      async onFileSelected(e) {
        const { valid } = await this.$refs.Upload_Module.validate(e);
  
        if (valid) {
          this.module_zip = e.target.files[0];
        } else {
          this.module_zip = "";
        }
      },
  
      
      update_status_module(module_info) {
        axios
          .post("update_status_module/", {
            status: module_info.status,
            name: module_info.module_name,
          })
          .then(response => {
           
            this.isLoading = true;
             if (module_info.status) {
                this.makeToast(
                  "success",
                  this.$t("Module_enabled_success"),
                  this.$t("Success")
                );
             }else{
               this.makeToast(
                  "danger",
                  this.$t("Module_Disabled_success"),
                  this.$t("Warning")
                );
             }
             window.location.href = '/app/settings/module_settings'; 
          })
          .catch(error => {
            this.makeToast(
              "warning",
              this.$t("Delete.Therewassomethingwronge"),
              this.$t("Warning")
            );
          });
      },
     
       //---------------------------------- get_sms_config ----------------\\
      get_modules_info() {
        axios
          .get("get_modules_info")
          .then(response => {
            this.modules_info = response.data;
            this.isLoading = false;
          })
          .catch(error => {
            setTimeout(() => {
              this.isLoading = false;
            }, 500);
          });
      },   
  
       //------------- Submit Validation 
      Submit_Upload_Module() {
        this.$refs.ref_Upload_Module.validate().then(success => {
          if (!success) {
            this.makeToast(
              "danger",
              this.$t("Please_Upload_the_Correct_Module"),
              this.$t("Failed")
            );
          } else {
              this.Upload_Module();
          }
        });
      },
  
      //---------------------------------------- Upload_Module -----------------\\
      Upload_Module() {
        var self = this;
        self.SubmitProcessing = true;
        self.data.append("module_zip", self.module_zip);
        axios
          .post("upload_module", self.data)
          .then(response => {
            self.SubmitProcessing = false;
            window.location.reload(); // refresh the page here
  
            this.makeToast(
              "success",
              this.$t("Uploaded_Success"),
              this.$t("Success")
            );
          })
          .catch(error => {
             self.SubmitProcessing = false;
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
          });
      },
  
  
     
    }, //end Methods
  
    //----------------------------- Created function-------------------
  
    created: function() {
      this.get_modules_info();
    }
  };
  </script>