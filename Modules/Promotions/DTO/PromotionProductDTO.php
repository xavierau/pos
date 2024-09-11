<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;

class PromotionProductDTO extends ADTO
{

    public function __construct(
        public readonly ?string $product_id,
        public readonly ?int    $product_variant_id = null,
    )
    {
    }

}
