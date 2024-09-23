
export function calculateItemSubtotal(item, tax) {
    const net_unit_price = item.net_price;
    const qty = item.qty;

    const subtotal = parseFloat(net_unit_price * qty) + tax;

    console.log("calculateItemSubtotal", subtotal);

    return subtotal;
}
