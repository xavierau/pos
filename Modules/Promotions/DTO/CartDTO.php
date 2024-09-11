<?php

namespace Modules\Promotions\DTO;

use App\DTO\ADTO;
use App\DTO\Attributes\ArrayOf;

class CartDTO extends ADTO
{

    public function __construct(
        #[ArrayOf(PosDetailDTO::class)]
        public readonly array $items,
        public readonly float $discount,
        public readonly float $shipping,
        public readonly ?int  $client_id,
    )
    {
    }

    /**
     * @return Array<PosDetailDTO>
     */
    public function getStandardItems(): array
    {
        return array_filter($this->items, fn($d) => !$d->promotion_id);
    }

    /**
     * @return Array<PosDetailDTO>
     */
    public function getPromotionalItems(): array
    {
        return array_filter($this->items, fn($d) => $d->promotion_id);
    }
}
