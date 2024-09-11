<?php

namespace Modules\Promotions\Factories;

use Modules\Promotions\Contracts\IPromotion;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotionType;
use Modules\Promotions\Services\Promotions\BuyXGetYPromotion;
use Modules\Promotions\Services\Promotions\DiscountXPercentageIfOverYAmountPromotion;
use Modules\Promotions\Services\Promotions\FreeXIfOverYAmountPromotion;

class PromotionFactory
{
    public static function create(PromotionRule $rule): IPromotion
    {
        return match ($rule->type) {
            PromotionType::BuyXGetY => new BuyXGetYPromotion($rule),
            PromotionType::FreeXIfOverYAmount => new FreeXIfOverYAmountPromotion($rule),
            PromotionType::DiscountXPercentageIfOverYAmount => new DiscountXPercentageIfOverYAmountPromotion($rule),
            default => throw new \Exception("Unknown promotion type: {$rule->type->value}"),
        };
    }
}
