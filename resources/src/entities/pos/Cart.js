import {randomString} from "../../utils";
import {DiscountType} from "./DiscountType";
import {CartItem} from "./CartItem";

class Cart {
    #items = [];
    #promotion_items = [];


    constructor(id = null) {
        this.id = id || randomString(10);
        this.#items = [];
        this.#promotion_items = [];
        this.warehouse_id = null
        this.client_id = null
        this.shipping = 0
        this.discount = 0
        this.discount_method = DiscountType.FIXED
        this.notes = ''
    }

    getItems() {
        console.log("get items: ", this.#items)
        return this.#items
    }

    get promotion_items() {
        return this.#promotion_items
    }


    addItem(warehouseProduct) {
        const cartItem = CartItem.fromWarehouseProduct(warehouseProduct)
        // if item has_imei is true, add each imei as a separate item
        console.log("addItem(warehouseProduct): ", warehouseProduct, cartItem)
        if (cartItem.has_imei) {
            return this.#items.push(cartItem)
        }

        // if item already exists, just increase the quantity
        const existingItem = this.#items.find(i => i.product_id === cartItem.product_id)
        if (existingItem) {
            return existingItem.quantity += cartItem.quantity
        }

        this.#items.push(cartItem)
    }

    incrementItem(cartItem, number = 1) {
        if (cartItem.has_imei || cartItem.is_service) return

        const existingItem = this.items.find(i => i.product_id === cartItem.product_id)
        if (existingItem) existingItem.quantity += number

    }

    decrementItem(cartItem, number = 1) {
        if (cartItem.has_imei || cartItem.is_service) return
        const existingItem = this.#items.find(i => i.product_id === cartItem.product_id)
        if (existingItem && (existingItem.quantity - number) >= 0) existingItem.quantity -= number

    }

    getGrossTotal() {
        return this.#items.reduce((acc, item) => acc + item.getNetTotal(), 0)
    }

    getDiscount() {
        if (this.discount_method !== null && this.discount !== null) {
            if (this.discount_method === DiscountType.PERCENTAGE) {
                return this.getGrossTotal() * this.discount / 100
            } else {
                return this.discount
            }
        }

        return 0
    }

    getNetTotal() {
        return this.getGrossTotal() - this.getDiscount() + this.shipping
    }

    _checkPromotion() {

        axios.post('/promotions/check', this)
            .then(response => {
                console.log(response.data)
            })
            .catch(error => {
                console.log(error)
            })

    }
}

export default Cart;
