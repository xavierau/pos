<template>
    <div class="main-content">
        <breadcumb :page="$t('Promotions:IndexPageTitle')" :folder="$t('Promotions')"/>
        <div v-if="is_loading" class="loading_page spinner spinner-primary mr-3"></div>
        <div v-else>
            <vue-good-table
                mode="remote"
                :columns="columns"
                :totalRows="totalRows"
                :rows="promotions"
                @on-page-change="onPageChange"
                @on-per-page-change="onPerPageChange"
                @on-sort-change="onSortChange"
                @on-search="onSearch"
                :search-options="{
        enabled: true,
        placeholder: $t('Search_this_table'),
      }"
                :select-options="{
          enabled: true ,
          clearSelectionText: '',
        }"
                @on-selected-rows-change="selectionChanged"
                :pagination-options="{
        enabled: true,
        mode: 'records',
        nextLabel: 'next',
        prevLabel: 'prev',
      }"
                styleClass="table-hover tableOne vgt-table"
            >
                <div slot="selected-row-actions">
                    <button class="btn btn-danger btn-sm" @click="delete_by_selected()">{{ $t('Del') }}</button>
                </div>
                <div slot="table-actions" class="mt-2 mb-3">
                    <b-button variant="outline-info m-1" size="sm" v-b-toggle.sidebar-right>
                        <i class="i-Filter-2"></i>
                        {{ $t("Filter") }}
                    </b-button>
                    <b-button @click="Adjustment_PDF()" size="sm" variant="outline-success m-1">
                        <i class="i-File-Copy"></i> PDF
                    </b-button>
                    <vue-excel-xlsx
                        class="btn btn-sm btn-outline-danger ripple m-1"
                        :data="adjustments"
                        :columns="columns"
                        :file-name="'Adjustments'"
                        :file-type="'xlsx'"
                        :sheet-name="'Adjustments'"
                    >
                        <i class="i-File-Excel"></i> EXCEL
                    </vue-excel-xlsx>
                    <router-link
                        class="btn-sm btn btn-primary btn-icon m-1"
                        v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_add')"
                        :to="{name:'create promotion'}"
                    >
            <span class="ul-btn__icon">
              <i class="i-Add"></i>
            </span>
                        <span class="ul-btn__text ml-1">{{ $t('Add') }}</span>
                    </router-link>
                </div>

                <template slot="table-row" slot-scope="props">
                    <span v-if="props.column.field == 'actions'">
                        <a v-b-tooltip.hover title="View" class="cursor-pointer" @click="showDetails(props.row.id)">
                          <i class="i-Eye text-25 text-info"></i>
                        </a>
                        <router-link
                            v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_edit')"
                            v-b-tooltip.hover
                            title="Edit"
                            :to="{name:'edit promotion', params:{id:props.row.id}}"
                        >
                          <i class="i-Edit text-25 text-success"></i>
                        </router-link>
                        <a
                            v-b-tooltip.hover
                            title="Delete"
                            class="cursor-pointer"
                            v-if="currentUserPermissions"
                            @click="removePromotion(props.row.id)"
                        >
                          <i class="i-Close-Window text-25 text-danger"></i>
                        </a>
                      </span>
                </template>
            </vue-good-table>
        </div>

        <!-- Multiple Filters -->
        <b-sidebar id="sidebar-right" :title="$t('Filters')" bg-variant="white" right shadow>
            <div class="px-3 py-2">
                <b-row>
                    <!-- start date  -->
                    <b-col md="12">
                        <b-form-group :label="$t('Start Date')">
                            <b-form-input type="date" v-model="Filter_start_date"></b-form-input>
                        </b-form-group>
                    </b-col>
                    <!-- end date  -->
                    <b-col md="12">
                        <b-form-group :label="$t('End Date')">
                            <b-form-input type="date" v-model="Filter_end_date"></b-form-input>
                        </b-form-group>
                    </b-col>

                    <b-col md="6" sm="12">
                        <b-button
                            @click="getPromotions(serverParams.page)"
                            variant="primary m-1"
                            size="sm"
                            block>
                            <i class="i-Filter-2"></i>
                            {{ $t("Filter") }}
                        </b-button>
                    </b-col>
                    <b-col md="6" sm="12">
                        <b-button @click="resetFilter()" variant="danger m-1" size="sm" block>
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
import {mapGetters} from "vuex";
import NProgress from "nprogress";
import jsPDF from "jspdf";
import "jspdf-autotable";
import helperMixin from "../../../../mixins/helperMethods";
import {PromotionEvent} from "../../../../utils/FireEvent"

export default {
    mixins: [helperMixin],
    metaInfo: {
        title: "Promotions"
    },
    data() {
        return {
            is_loading: true,
            serverParams: {
                sort: {
                    field: "id",
                    type: "desc"
                },
                page: 1,
                perPage: 10
            },
            selectedIds: [],
            search: "",
            totalRows: "",
            limit: "10",
            Filter_start_date: "",
            Filter_end_date: "",
            Filter_ref: "",
            promotions: [],
            promotion: {}
        };
    },
    computed: {
        ...mapGetters(["currentUserPermissions"]),
        columns() {
            return [
                {
                    label: this.$t("name"),
                    field: "name",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("description"),
                    field: "description",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("start_date"),
                    field: "start_date",
                    tdClass: "text-left",
                    thClass: "text-left"
                },
                {
                    label: this.$t("end_date"),
                    field: "end_date",
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
        //-------------------------------------- Adjustement PDF ------------------------------\\
        Adjustment_PDF() {
            var self = this;

            let pdf = new jsPDF("p", "pt");
            let columns = [
                {title: "Date", dataKey: "date"},
                {title: "Reference", dataKey: "Ref"},
                {title: "Warehouse", dataKey: "warehouse_name"},
                {title: "Total Products", dataKey: "items"}
            ];
            pdf.autoTable(columns, self.adjustments);
            pdf.text("Adjustment List", 40, 25);
            pdf.save("Adjustment_List.pdf");
        },

        //---------------Get Details Adjustement ----------------------\\
        showDetails(id) {
            // Start the progress bar.
            NProgress.start();
            NProgress.set(0.1);
            axios
                .get("adjustments/detail/" + id)
                .then(response => {
                    this.adjustment = response.data.adjustment;
                    this.details = response.data.details;
                    Fire.$emit("Get_Details_Adjust");
                })
                .catch(response => {
                    Fire.$emit("Get_Details_Adjust");
                });
        },

        //------  Update Params Table
        updateParams(newProps) {
            this.serverParams = Object.assign({}, this.serverParams, newProps);
        },

        //---- Event Page Change
        onPageChange({currentPage}) {
            if (this.serverParams.page !== currentPage) {
                this.updateParams({page: currentPage});
                this.getPromotions(currentPage);
            }
        },

        //---- Event Per Page Change
        onPerPageChange({currentPerPage}) {
            if (this.limit !== currentPerPage) {
                this.limit = currentPerPage;
                this.updateParams({page: 1, perPage: currentPerPage});
                this.getPromotions(1);
            }
        },

        //---- Event Select Rows
        selectionChanged({selectedRows}) {
            this.selectedIds = [];
            selectedRows.forEach((row, index) => {
                this.selectedIds.push(row.id);
            });
        },

        //------ Event Sort change
        onSortChange(params) {
            console.log("on sort change", params)
            this.updateParams({
                sort: {
                    type: params[0].type,
                    field: params[0].field
                }
            });
            this.getPromotions(this.serverParams.page);
        },

        //------ Event Search
        onSearch(value) {
            this.search = value.searchTerm;
            this.getPromotions(this.serverParams.page);
        },

        //------ Reset Filter
        resetFilter() {
            this.search = "";
            this.Filter_start_date = "";
            this.Filter_end_date = "";
            this.getPromotions(this.serverParams.page);
        },

        //--------------------------------Get All Adjustements ----------------------\\
        getPromotions(page) {
            this.execute(() => {
                let url = "promotions?page=" + page + "&limit=" + this.limit || 15
                if (this.Filter_start_date) url += "&filter_start_date=" + this.Filter_start_date
                if (this.Filter_end_date) url += "&filter_end_date=" + this.Filter_end_date
                if (this.serverParams.sort.field) url += "&SortField=" + this.serverParams.sort.field
                if (this.serverParams.sort.type) url += "&SortType=" + this.serverParams.sort.type
                if (this.search) url += "&search=" + this.search

                axios.get(url)
                    .then(response => {
                        this.promotions = response.data.promotions;
                        this.totalRows = response.data.totalRows;
                    })
                    .catch(error => console.error(error))
                    .finally(() => this.is_loading = false)


            })

        },

        //---------------------------------- Remove Promotion----------------------\\
        removePromotion(id) {
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
                    this.execute(() => {
                        axios.delete("promotions/" + id)
                            .then(() => {
                                this.$swal(
                                    this.$t("Delete.Deleted"),
                                    this.$t("Delete.AdjustDeleted"),
                                    "success"
                                ).then(() => {
                                    Fire.$emit(PromotionEvent.DeleteItem, id);
                                    this.loadPromotions()
                                });
                            })
                            .catch(() => {
                                this.$swal(
                                    this.$t("Delete.Failed"),
                                    this.$t("Delete.Therewassomethingwronge"),
                                    "warning"
                                );
                            });
                    })

                }
            });
        },

        //---- Delete adjustments by selection
        delete_by_selected() {
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
                    this.execute(() => {
                        axios.post("promotions/delete/by_selection", {
                            selectedIds: this.selectedIds
                        })
                            .then(() => {
                                this.$swal(
                                    this.$t("Delete.Deleted"),
                                    this.$t("Delete.AdjustDeleted"),
                                    "success"
                                )
                                    .then(() => {
                                        Fire.$emit(PromotionEvent.DeleteItem);
                                        this.loadPromotions()
                                    });

                            })
                            .catch(() => {
                                // Complete the animation of theprogress bar.
                                this.$swal(
                                    this.$t("Delete.Failed"),
                                    this.$t("Delete.Therewassomethingwronge"),
                                    "warning"
                                );
                            });
                    })

                }
            });
        },

        loadPromotions() {
            this.is_loading = true
            this.execute(() => {
                axios.get('/promotions')
                    .then(({data}) => this.promotions = data.promotions)
                    .catch(error => {
                        this.makeWarningToast("Promotions:ErrorMessages:LoadPromotions")
                    })
                    .finally(() => this.is_loading = false)
            })
        }
    },
    created() {
        this.loadPromotions()
    }
};
</script>
