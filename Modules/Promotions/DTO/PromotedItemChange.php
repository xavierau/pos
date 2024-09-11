<?php

namespace Modules\Promotions\DTO;

readonly class PromotedItemChange
{
    /**
     * @param Array<PromotedItem> $increment
     * @param Array<PromotedItem> $decrement
     */
    public function __construct(
        public array $increment,
        public array $decrement,
    )
    {
    }

}
