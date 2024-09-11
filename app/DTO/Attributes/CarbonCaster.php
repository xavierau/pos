<?php

namespace App\DTO\Attributes;

use Attribute;
use Illuminate\Support\Carbon;


#[Attribute(Attribute::TARGET_PROPERTY)]
class CarbonCaster implements IDTOAttribute
{


    /**
     * @throws \Exception
     */
    public function process($value, bool $isOptional, $defaultValue): Carbon
    {
        return (is_null($value) and $isOptional) ?
            Carbon::parse($defaultValue) :
            Carbon::parse($value);
    }
}
