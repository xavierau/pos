import {DiscountType} from "./DiscountType";
import {randomString} from "../../utils";

export class CartItem {


    constructor(id = null) {
        this.cart_item_id = id || randomString(10);
        this.name = ''
        this.code = null
        this.product_id = null
        this.product_variant_id = null
        this.has_imei = false
        this.is_variant = false
        this.imei = null
        this.quantity = 0
        this.unit_price = 0
        this.sale_unit_id = null
        this.sale_unit = null
        this.discounted_price = null
        this.discount = null
        this.discount_method = null
        this.promotion_id = null
        this.promotion_name = null
        this.promotion_type = null
        this.notes = ''
    }

    getGrossTotal() {
        return this.quantity * this.getActivePrice()
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
        return (this.quantity * (this.getActivePrice() - this.getDiscount())) || 0
    }

    getActivePrice() {// if discounted price is a number, return it
        return Number.isFinite(this.discounted_price) ? this.discounted_price : this.unit_price
    }

    static fromWarehouseProduct(warehouseProduct) {

        console.log("CartItem.fromWarehouseProduct: ", warehouseProduct)

        const instance = new CartItem()
        const properties = Object.getOwnPropertyNames(instance)

        for (const prop of properties) {
            if (warehouseProduct.hasOwnProperty(prop)) {
                instance[prop] = warehouseProduct[prop]
            }
        }
        instance.quantity = 1

        console.log("CartItem.fromWarehouseProduct: ", instance, warehouseProduct)

        return instance
    }

}
