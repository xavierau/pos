<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;
use App\DTO\Attributes\CarbonCaster;
use Carbon\Carbon;

class UpdatePromotionDTO extends ADTO
{

    public function __construct(
        public readonly int               $id,
        public readonly string            $name,
        #[CarbonCaster]
        public readonly Carbon            $start_date,
        #[CarbonCaster]
        public readonly Carbon            $end_date,
        public readonly ?PromotionRuleDTO $rule,
        public readonly ?int              $max_usage,
        public readonly ?int              $max_applications_per_sale
    )
    {
    }

}
