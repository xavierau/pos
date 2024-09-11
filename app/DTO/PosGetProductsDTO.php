<?php

namespace App\DTO;

class PosGetProductsDTO extends ADTO
{

    public function __construct(

        public readonly int  $warehouse_id,
        public readonly bool $stock,
        public readonly bool $product_service,
        public readonly ?int $category_id,
        public readonly ?int $brand_id,
        public readonly int  $page = 1,
        public readonly int  $perPage = 8,
    )
    {
    }
}
