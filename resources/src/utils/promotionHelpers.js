//--------------------- Buy X Get Y ---------------------//
export function convertByXGetYRule(rule) {
    return {
        x_product_code: [rule.product_id, rule.product_variant_id].filter(i => i).join('_'),
        x_qty: rule.x,
        y_product_code: [rule.product_id, rule.product_variant_id].filter(i => i).join('_'),
        y_qty: rule.y,
    }
}

export function convertByXGetYInput(rule, products) {

    const x_code_array = rule.x_product_code.split('_')
    const y_code_array = rule.y_product_code.split('_')


    return {
        type: rule.type,
        x_qty: rule.x_qty,
        y_qty: rule.y_qty,
        x_product: {
            product_id: x_code_array[0],
            product_variant_id: x_code_array[1],
        },
        y_product: {
            product_id: y_code_array[0],
            product_variant_id: y_code_array[1],
        },
    }
}

//--------------------- Free X If Over Y Amount ---------------------//
export function convertFeeXOverYAmountRule(rule) {

    return {
        x_product_code: [rule.product_id, rule.product_variant_id].filter(i => i).join('_'),
        x_qty: rule.x,
        y_amount: rule.y,
    }
}

export function convertFeeXOverYAmountInput(rule, products) {
    const x_code_array = rule.x_product_code.split('_')

    return {
        type: rule.type,
        x_qty: rule.x_qty,
        y_qty: rule.y_amount,
        x_product: {
            product_id: x_code_array[0],
            product_variant_id: x_code_array[1],
        },
    }
}

//--------------------- Discount X If Over Y Amount ---------------------//
export function convertDiscountXOverYAmountRule(rule) {
    return {
        discount_type: rule.x === 1 ? 'percentage' : 'fixed',
        discount_amount: rule.amount,
        threshold: rule.y,
    }
}

export function convertDiscountXOverYAmountInput(rule) {
    return {
        type: rule.type,
        amount: rule.threshold,
        x_qty: rule.discount_type === 'percentage' ? 1 : 2,
        y_qty: rule.discount_amount
    }
}
