<?php

namespace App\DTO;

use App\Enums\PaymentType;

class PaymentDTO extends ADTO
{
    public function __construct(
        public readonly PaymentType $type,
        public readonly ?string     $notes = null,
        public readonly ?int        $account_id = null,
    )
    {
    }
}
