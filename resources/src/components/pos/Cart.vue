<script>
import CartItem from './CartItem'

export default {
    name: "Cart",
    components: {
        CartItem
    },
    props: {
        details: {
            type: Array,
            default: []
        },
    }
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
                <tbody v-if="details.length <= 0">
                <tr>
                    <td colspan="5">{{ $t('NodataAvailable') }}</td>
                </tr>
                </tbody>
                <tbody v-else>
                <cart-item v-for="(detail, index) in details" :key="index" :detail="detail"></cart-item>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>

</style>
