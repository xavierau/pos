<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;

class  PosDetailDTO extends ADTO
{

    public function __construct(
        public readonly ?int    $product_id,
        public readonly int    $quantity,
        public readonly ?float  $unit_price,
        public readonly ?float $discounted_price = null,
        public readonly ?float $discount = null,
        public readonly ?int   $product_variant_id = null,
        public readonly ?int   $promotion_id = null,
    )
    {
    }

    /**
     * the discount is referred to the discount applied to the product's unit price
     * @return float
     */
    public function getSubtotal(): float
    {
        $active_price = ($this->discounted_price ?? $this->unit_price) - ($this->discount ?? 0);
        return $active_price * $this->quantity;
    }

}
