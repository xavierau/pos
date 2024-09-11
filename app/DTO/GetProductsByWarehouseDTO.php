<?php

namespace App\DTO;

class GetProductsByWarehouseDTO extends ADTO
{

    public function __construct(
        public readonly int  $warehouse_id,
        public readonly bool $is_sale,
        public readonly bool $stock,
        public readonly bool $product_service,
        public readonly bool $included_empty_stock = true,
    )
    {
    }
}
