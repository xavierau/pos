<?php

namespace App\DTO;

class  SaleDetailDTO extends ADTO
{
    public function __construct(
        public readonly ?float  $discount,
        public readonly float   $unit_price,
        public readonly int     $product_id,
        public readonly int     $quantity,
        public readonly int     $sale_unit_id,
        public readonly ?int    $promotion_id = null,
        public readonly ?string $discount_method,
        public readonly ?string $imei_number = null,
        public readonly ?int    $product_variant_id = null,
    )
    {
    }
}
