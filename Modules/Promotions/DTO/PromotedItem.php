<?php

namespace Modules\Promotions\DTO;

use Modules\Promotions\Enums\PromotedItemType;

class PromotedItem
{
    public function __construct(
        public PromotedItemType $type,
        public int              $promotion_id,
        public string           $promotion_name,
        public ?int             $product_id = null,
        public ?int             $product_variant_id = null,
        public ?int             $qty = null,
        public ?int             $amount = null,
    )
    {
    }

}
