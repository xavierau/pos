<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;

class PosProduct extends ADTO
{

    public function __construct(
        public readonly int    $product_id,
        public readonly string $name,
        public readonly string $code,
        public readonly float  $unit_price,
        public readonly string $sale_unit,
        public readonly int    $sale_unit_id,
        public readonly float  $available_qty,
        public readonly string $image,
        public readonly bool   $is_service = false,
        public readonly bool   $is_variant = false,
        public readonly bool   $has_imei = false,
        public readonly bool   $not_selling = false,
        public readonly ?float $discounted_price = null,
        public readonly ?int   $product_variant_id = null,
    )
    {
    }

}
