<?php

namespace Modules\Promotions\Services\Promotions;


use App\Models\Sale;
use App\Models\SaleReturn;
use Modules\Promotions\Contracts\IPromotion;
use Modules\Promotions\DTO\CartDTO;
use Modules\Promotions\DTO\PromotedItemChange;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotedItemType;

class FreeXIfOverYAmountPromotion implements IPromotion
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

        $numberShouldBeApplied = $this->getNumberShouldBeApplied($cart);

        if ($numberShouldBeApplied > 0) {
            $increment = [];
            for ($i = 0; $i < $numberShouldBeApplied; $i++) {
                $increment[] = [
                    'promotion_id' => $this->rule->promotion_id,
                    'product_id' => $this->rule->product_id,
                    'product_variant_id' => $this->rule->product_variant_id,
                    'quantity' => 1,
                    'type' => PromotedItemType::FreeProduct
                ];
            }
            return new PromotedItemChange($increment, []);

        }

        $decrement = [];
        for ($i = 0; $i < abs($numberShouldBeApplied); $i++) {
            $decrement[] = [
                'promotion_id' => $this->rule->promotion_id,
                'product_id' => $this->rule->product_id,
                'product_variant_id' => $this->rule->product_variant_id,
                'quantity' => 1,
                'type' => PromotedItemType::FreeProduct
            ];
        }

        return new PromotedItemChange([], $decrement);
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

        $timesPromotionCanBeApplied = (int)floor($net_total / $this->rule->y);


        $limit = min($stillCanApply, $this->rule->promotion->max_applications_per_sale, $timesPromotionCanBeApplied);

        $appliedInCart = count(array_filter($cart->getPromotionalItems(), function ($item) {
            return $item->promotion_id == $this->rule->promotion_id;
        }));

        return $limit - $appliedInCart;

    }
}
