<script>
import {mapGetters} from "vuex";
import helperMixin from "../../mixins/helperMethods";
import OrderProductItem from "./OrderProductItem";

export default {
    name: "OrderProductContainer",
    mixins: [helperMixin],
    components: {
        OrderProductItem
    },
    props: {
        details: {
            type: Array,
            default: []
        },
    },
    computed: {
        ...mapGetters(['currentUserPermissions', 'currentUser']),
    }
}
</script>

<template>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="bg-gray-300">
            <tr>
                <th scope="col">#</th>
                <th scope="col">{{ $t('ProductName') }}</th>
                <th scope="col">{{ $t('Net_Unit_Cost') }}</th>
                <th scope="col">{{ $t('CurrentStock') }}</th>
                <th scope="col">{{ $t('Qty') }}</th>
                <th scope="col">{{ $t('Discount') }}</th>
                <th scope="col">{{ $t('Tax') }}</th>
                <th scope="col">{{ $t('SubTotal') }}</th>
                <th scope="col" class="text-center">
                    <i class="fa fa-trash"></i>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="details.length <=0">
                <td colspan="9">{{ $t('NodataAvailable') }}</td>
            </tr>
            <OrderProductItem v-for="detail in details"
                              :class="{'row_deleted': detail.del === 1 || detail.no_unit === 0}"
                              :key="detail.detail_id"
                              :detail="detail"></OrderProductItem>
            </tbody>
        </table>
    </div>

</template>

<style scoped>

</style>
