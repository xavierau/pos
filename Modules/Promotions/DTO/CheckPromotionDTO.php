<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;
use App\DTO\Attributes\ArrayOf;

class CheckPromotionDTO extends ADTO
{

    public function __construct(
        #[ArrayOf(PosDetailDTO::class)]
        public readonly array $details,
    )
    {
    }

}
