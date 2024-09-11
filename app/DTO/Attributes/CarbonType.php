<?php

namespace App\DTO\Attributes;

use Attribute;
use Illuminate\Support\Carbon;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CarbonType implements IDTOAttribute
{
    public function process($value, bool $isOptional, $defaultValue): Carbon
    {
        return Carbon::parse($value);
    }
}
