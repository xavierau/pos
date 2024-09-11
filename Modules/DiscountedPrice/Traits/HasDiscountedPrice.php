<?php

namespace Modules\DiscountedPrice\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\DiscountedPrice\Entities\DiscountedPrice;

trait HasDiscountedPrice
{
    public function discountedPrices(): MorphMany
    {
        return $this->morphMany(DiscountedPrice::class, 'discountable');
    }

    public function activeDiscountedPrices(): MorphMany
    {
        return $this->morphMany(DiscountedPrice::class, 'discountable')
            ->where('discounted_prices.start_date', '<=', now())
            ->where('discounted_prices.end_date', '>=', now());
    }
}
