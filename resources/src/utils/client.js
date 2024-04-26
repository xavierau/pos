export const posClient = {
    getProducts(category_id, brand_id, warehouse_id, page = 1) {
        return axios
            .get(
                "pos/get_products_pos?page=" +
                page +
                "&category_id=" +
                category_id +
                "&brand_id=" +
                brand_id +
                "&warehouse_id=" +
                warehouse_id +
                "&stock=" + 1 +
                "&product_service=" + 1
            )
    },
    getPosElements() {
        return axios
            .get("pos/get_pos_elements")
    }
}
