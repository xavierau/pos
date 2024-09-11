<?php

namespace App\Services\products;

use App\Models\Unit;

class GetUnitCostAndPrice
{

    public static function getUnitAmount(float $amount, ?Unit $unit = null, $fallback = null)
    {
        if (!$unit) return $fallback ?? 0;

        return $unit->operator == '/' ?
            $amount / $unit->operator_value :
            $amount * $unit->operator_value;
    }
}
