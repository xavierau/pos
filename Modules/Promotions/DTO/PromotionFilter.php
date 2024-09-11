<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;
use App\DTO\Attributes\CarbonCaster;
use Carbon\Carbon;

class PromotionFilter extends ADTO
{

    public function __construct(
        #[CarbonCaster]
        public readonly ?Carbon $filter_start_date,
        #[CarbonCaster]
        public readonly ?Carbon $filter_end_date
    )
    {
    }

}
