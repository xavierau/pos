import {WarehouseProduct} from "../../entities/pos/WarehouseProduct";
import {posClient} from "../../utils/client";
import {CartItem} from "../../entities/pos/CartItem";

function getPromotions(state) {
    return axios.post('/promotions/check', {
        discount: state.discount,
        shipping: state.shipping,
        client_id: state.client_id,
        items: state.items
    })
        .then(response => {

            console.log("getPromotions: ", response.data)

            response.data.promotions.forEach(promotion => {

                if ((promotion.decrement?.length || 0) > 0) {
                    promotion.decrement.forEach(pro => {
                        // remove 1 of the item in items that has the same promotion_id
                        const item = state.items.find(i => {

                            console.log("current item", i, i.promotion_type, pro.type)

                            return i.promotion_id === pro.promotion_id && i.promotion_type === pro.type

                        })

                        console.log("item: ", item, pro)
                        if (item) {
                            console.log("item: ", item)
                            state.items = state.items.filter(i => i.cart_item_id !== item.cart_item_id)
                        }
                    })
                }

                if ((promotion.increment?.length || 0) > 0) {
                    promotion.increment.forEach(pro => {

                        console.log("promotion.increment: ", pro)

                        if (pro.type === 'free_product') {
                            const promotionItem = state.products.find(p => p.product_id === pro.product_id &&
                                p.product_variant_id === pro.product_variant_id)
                            if (promotionItem) {
                                const cartItem = CartItem.fromWarehouseProduct(promotionItem)
                                console.log("cartItem: ", cartItem)
                                cartItem.discounted_price = 0
                                cartItem.type = pro.type
                                cartItem.quantity = pro.quantity
                                cartItem.promotion_id = pro.promotion_id
                                cartItem.promotion_name = pro.promotion_name
                                state.items.push(cartItem)
                            } else {
                                console.log("promotionItem not found")
                            }
                        } else if (pro.type === 'cart_percentage_discount') {
                            const cartItem = new CartItem()
                            cartItem.promotion_id = pro.promotion_id
                            cartItem.promotion_name = pro.promotion_name
                            cartItem.promotion_type = pro.type
                            cartItem.discount = pro.amount
                            state.items.push(cartItem)
                        } else if (pro.type === 'cart_fixed_discount') {
                            const cartItem = new CartItem()
                            cartItem.promotion_id = pro.promotion_id
                            cartItem.promotion_name = pro.promotion_name
                            cartItem.discounted_price = -pro.amount
                            cartItem.promotion_type = pro.type
                            cartItem.quantity = 1
                            state.items.push(cartItem)
                        }


                    })
                }

            })


        })
}

function getCartGrossTotal(state) {


    const grossTotal = state.items.reduce((total, item) => total + item.getNetTotal(), 0)
    const cart_percentage_discount_items = state.items.filter(i => i.promotion_type === 'cart_percentage_discount')

    const new_gross_total = cart_percentage_discount_items.reduce((total, item) => total * (1 - (item.discount / 100)), grossTotal)
    console.log("calculating gross total: ", grossTotal, cart_percentage_discount_items, new_gross_total)
    return new_gross_total
}


const state = {
    items: [],
    warehouse_id: null,
    client_id: null,
    shipping: 0,
    discount: 0,
    notes: null,
    products: [],
    product_current_page: 1,
    total_number_of_products: 0,
    payment: {
        amount: 0,
        account_id: null,
        received_amount: 0,
        type: null,
        notes: ""
    },
    payment_methods: {
        types: [
            {
                label: 'Credit Card',
                value: 'credit card',
            },
            {
                label: 'Cash',
                value: 'cash',
                default: true
            }
        ],
    },
    accounts: [
        {
            id: 1,
            label: 'Saving',
            value: 'saving',
        },
        {
            id: 2,
            label: 'Current',
            value: 'current',
        },
        {
            id: 3,
            label: 'Cash',
            value: 'cash',
            default: true
        },
    ]
};

const getters = {
    supportedPaymentTypes: (state) => state.payment_methods.types,
    getAccounts: (state) => state.accounts,
    client_id: (state) => state.client_id,
    warehouse_id: (state) => state.warehouse_id,
    payment: (state) => state.payment,
    items: (state) => state.items,
    products: (state) => state.products,
    discount: (state) => state.discount,
    shipping: (state) => state.shipping,
    product_current_page: (state) => state.product_current_page,
    totalNumberOfProducts: (state) => state.total_number_of_products,
    cartNetTotal: (state) => {
        const grossTotal = getCartGrossTotal(state)
        return grossTotal - state.discount + state.shipping
    },

};

const actions = {
    setWarehouseId({state}, warehouseId) {
        state.warehouse_id = warehouseId
    },
    setClientId({state}, clientId) {
        state.client_id = clientId
    },
    setDiscount({state}, discount) {
        state.discount = parseFloat(discount)
    },
    setShipping({state}, shipping) {
        state.shipping = parseFloat(shipping)
    },
    addCartItem({state, commit}, warehouseProduct) {
        const cartItem = CartItem.fromWarehouseProduct(warehouseProduct)
        // if item has_imei is true, add each imei as a separate item
        if (cartItem.has_imei) {
            return commit('addItem', cartItem)
        }

        // if item already exists, just increase the quantity
        const existingItem = state.items.find(i => i.product_id === cartItem.product_id &&
            i.product_variant_id === cartItem.product_variant_id &&
            !i.promotion_id)
        if (existingItem) {
            return commit('incrementItem', {...existingItem, quantity: 1})
        }
        return commit('addItem', cartItem)
    },
    setCartItemQuantity({state, commit}, cartItem) {
        commit('setItemQty', cartItem)
    },
    deleteCartItem({commit}, cartItem) {
        commit('deleteItem', cartItem)
    },
    getProducts({state}, params) {
        return posClient.getProducts(params?.category_id || "", params?.brand_id || "", state.warehouse_id || "", params?.page)
            .then(response => {
                state.products = response.data.products.map(d => WarehouseProduct.fromObject(d));
                state.total_number_of_products = response.data.totalRows;
            });
    },
    restCart({commit, dispatch, state}, warehouseId) {
        commit('resetCart', warehouseId);
        dispatch('getProducts', {page: state.product_current_page});
    }
};

const mutations = {
    setItemQty(state, cartItem) {
        const existingItem = state.items.find(i => i.cart_item_id === cartItem.cart_item_id)
        if (existingItem) {
            existingItem.quantity = cartItem.quantity
            getPromotions(state)
        }
    },
    addItem(state, cartItem) {
        state.items.push(cartItem)
        getPromotions(state)
    },
    incrementItem(state, cartItem) {
        const existingItem = state.items.find((i) => i.cart_item_id === cartItem.cart_item_id)
        if (existingItem) existingItem.quantity += cartItem.quantity

        getPromotions(state)
    },
    decrementItem(state, cartItem) {
        const existingItem = state.items.find(i => i.cart_item_id === cartItem.cart_item_id)
        if (existingItem && (existingItem.quantity - cartItem.quantity) >= 0) existingItem.quantity -= cartItem.quantity
        getPromotions(state)
    },
    deleteItem(state, cartItem) {
        state.items = state.items.filter(i => i.cart_item_id !== cartItem.cart_item_id)
        if (!cartItem.promotion_id) {
            getPromotions(state)
        }
    },
    resetCart(state, warehouseId) {
        state.items = []
        state.warehouse_id = warehouseId
        state.product_current_page = 1;
        state.payment = {
            amount: 0,
            account_id: null,
            received_amount: 0,
            type: null,
            notes: ""
        }
    }
};

export default {
    state,
    getters,
    actions,
    mutations
};
