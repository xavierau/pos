<?php

namespace Modules\Promotions\Services\Promotions;

use App\Models\Sale;
use App\Models\SaleReturn;
use Modules\Promotions\Contracts\IPromotion;
use Modules\Promotions\DTO\CartDTO;
use Modules\Promotions\DTO\PromotedItem;
use Modules\Promotions\DTO\PromotedItemChange;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotedItemType;

class DiscountXPercentageIfOverYAmountPromotion implements IPromotion
{

    public function __construct(
        protected PromotionRule $rule
    )
    {
    }


    public function apply(Sale $sale)
    {
        // TODO: Implement apply() method.
    }

    public function adjustForReturn(SaleReturn $saleReturn)
    {
        // TODO: Implement adjustForReturn() method.
    }

    public function isEligibleForPromotion(Sale $sale): bool
    {
        return true;
    }

    public function getPromotedItems(CartDTO $cart): PromotedItemChange
    {
        $number = $this->getNumberShouldBeApplied($cart);

        if ($number > 0) {
            for ($i = 0; $i < $number; $i++) {
                $increment[] = new PromotedItem(
                    type: $this->rule->x === 1 ? PromotedItemType::CartPercentageDiscount : PromotedItemType::CartFixedDiscount,
                    promotion_id: $this->rule->promotion_id,
                    promotion_name: $this->rule->promotion->name,
                    qty: 1,
                    amount: $this->rule->y,
                );
            }

            return new PromotedItemChange($increment ?? [], []);
        } else {

            for ($i = 0; $i < abs($number); $i++) {
                $decrement[] = new PromotedItem(
                    type: $this->rule->x === 1 ? PromotedItemType::CartPercentageDiscount : PromotedItemType::CartFixedDiscount,
                    promotion_id: $this->rule->promotion_id,
                    promotion_name: $this->rule->promotion->name,
                    qty: 1,
                    amount: $this->rule->y,
                );
            }

            return new PromotedItemChange([], $decrement ?? []);
        }
    }

    /**
     * This promotion not include any shipping and discount
     * @param CartDTO $cart
     * @return mixed
     */
    private function getNumberShouldBeApplied($cart)
    {

        $maxApplications = $this->rule->promotion->max_applications_per_sale ?? PHP_INT_MAX;

        $alreadyApplied = $this->rule->promotion->salesDetails?->count() ?? 0;

        $stillCanApply = $maxApplications - $alreadyApplied;

        $items_subtotal = collect($cart->items)->reduce(fn($carry, $item) => $carry + $item->getSubtotal(), 0);

        $net_total = $items_subtotal - $cart->discount;

        $timesPromotionCanBeApplied = (int)floor($net_total / (float)$this->rule->amount);

        $limit = min($stillCanApply, $this->rule->promotion->max_applications_per_sale, $timesPromotionCanBeApplied);

        $appliedInCart = count(array_filter($cart->getPromotionalItems(), function ($item) {
            return $item->promotion_id == $this->rule->promotion_id;
        }));

        return $limit - $appliedInCart;

    }
}
