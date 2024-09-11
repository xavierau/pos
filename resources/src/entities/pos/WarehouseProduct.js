export class WarehouseProduct {


    constructor() {
        this.code = null
        this.discounted_price = null
        this.has_imei = false
        this.image = "no-image.png"
        this.is_service = false
        this.is_variant = false
        this.name = ""
        this.not_selling = false
        this.product_id = null
        this.product_variant_id = null
        this.available_qty = 0
        this.sale_unit = null
        this.sale_unit_id = null
        this.unit_price = null
    }

    static fromObject(obj) {
        const instance = new WarehouseProduct()
        for (const prop of Object.getOwnPropertyNames(instance)) {
            if (obj.hasOwnProperty(prop)) {
                instance[prop] = obj[prop]
            }
        }

        return instance
    }
}
