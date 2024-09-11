<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;
use Modules\Promotions\Enums\PromotionType;

class PromotionRuleDTO extends ADTO
{

    public function __construct(
        public readonly PromotionType        $type,
        public readonly ?PromotionProductDTO $x_product,
        public readonly ?PromotionProductDTO $y_product,
        public readonly ?int                 $x_qty,
        public readonly ?int                 $y_qty,
        public readonly ?float               $amount = null,

    )
    {
    }

}
