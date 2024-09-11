<script>
import CartItem from './CartItem'
import {mapGetters} from 'vuex'

export default {
    name: "NewCart",
    components: {
        CartItem
    },
    computed:{
        ...mapGetters(["items"]),
    },

}
</script>

<template>
    <div class="pos-detail">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th scope="col">{{ $t('ProductName') }}</th>
                    <th scope="col">{{ $t('Price') }}</th>
                    <th scope="col" class="text-center">{{ $t('Qty') }}</th>
                    <th scope="col" class="text-center">{{ $t('SubTotal') }}</th>
                    <th scope="col" class="text-center"><i class="fa fa-trash"></i></th>
                </tr>
                </thead>
                <tbody v-if="items.length <= 0"><tr><td colspan="5">{{ $t('NodataAvailable') }}</td></tr></tbody>
                <tbody v-else><cart-item v-for="(item, index) in items" :key="index" :item="item"></cart-item></tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>

</style>
