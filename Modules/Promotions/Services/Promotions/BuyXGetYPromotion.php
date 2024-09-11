<?php

// Modules/Promotions/Handlers/BuyXGetYPromotion.php

namespace Modules\Promotions\Services\Promotions;

use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Promotions\Contracts\IPromotion;
use Modules\Promotions\DTO\CartDTO;
use Modules\Promotions\DTO\PromotedItem;
use Modules\Promotions\DTO\PromotedItemChange;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotedItemType;

class BuyXGetYPromotion implements IPromotion
{

    private $numberOfApplications = null;

    public function __construct(
        protected PromotionRule $rule
    )
    {
    }

    public function getPromotedItems(CartDTO $cart): PromotedItemChange
    {
        $number = $this->getNumberShouldBeApplied(collect($cart->getStandardItems()), collect($cart->getStandardItems()));

        $increment = [];
        $decrement = [];

        if ($number > 0) {
            for ($i = 0; $i < $number; $i++) {
                $increment[] = $this->getPromotedItem();
            }
        } else {
            for ($i = 0; $i < abs($number); $i++) {
                $decrement[] = $this->getPromotedItem();
            }
        }

        return new PromotedItemChange($increment, $decrement);
    }

    private function getPromotedItem(): PromotedItem
    {
        return new PromotedItem(
            PromotedItemType::FreeProduct,
            $this->rule->promotion_id,
            $this->rule->promotion->name,
            $this->rule->y_product_id,
            $this->rule->y_product_variant_id,
            $this->rule->y,
        );
    }


    public function apply(Sale $sale)
    {
        // update sale is promotion is applicable
        // also remove the promotion if it was applied before and now it is not applicable

        if ($this->isEligibleForPromotion($sale)) {
            $this->applyPromotion($sale);
        } else {
            $this->removePromotion($sale);
        }

        $sale->refresh();

        return $sale;
    }

    protected function getEligibleItems($items): int
    {
        return $items->reduce(function ($carry, $detail) {
            if ($this->rule->product_id && $detail->product_id === $this->rule->product_id) {
                return $carry + $detail->quantity;
            }
            if ($this->rule->product_variant_id && $detail->product_variant_id === $this->rule->product_variant_id) {
                return $carry + $detail->quantity;
            }
            if ($this->rule->product_category_id) {
                if ($detail->product_variant_id && $detail->productVariant->product->category_id === $this->rule->product_category_id) {
                    return $carry + $detail->quantity;
                }
                if ($detail->product_id && $detail->product->category_id === $this->rule->product_category_id) {
                    return $carry + $detail->quantity;
                }
            }

            return $carry;
        }, 0);
    }

    public function adjustForReturn(SaleReturn $saleReturn)
    {
        foreach ($saleReturn->details as $detail) {
            if ($this->wasPromotionApplied($detail)) {
                $this->reverseDiscount($detail);
            }
        }
    }

    private function getCurrentApplied(Collection $rewards = null)
    {

        if ($rewards === null and $this->numberOfApplications === null) {
            $this->numberOfApplications = DB::table('promotion_sale')
                ->where('promotion_id', $this->rule->promotion_id)
                ->count();
            return $this->numberOfApplications;
        }

        return $rewards->filter(function ($item) {
            return $this->rule->promotion_id === $item->promotion_id ?? null;
        })->count();

    }

    private function getMaxRemainingUsage()
    {
        return $this->rule->promotion->max_usage ?
            $this->rule->promotion->max_usage - $this->getCurrentApplied() :
            PHP_INT_MAX;

    }

    /**
     * @param \App\Models\Sale $sale
     * @return bool
     */
    public function isEligibleForPromotion(Sale $sale): bool
    {

        if ($this->rule->max_usage) {
            $totalPromotionUsage = $this->getCurrentApplied();

            if ($totalPromotionUsage >= $this->rule->max_usage) {
                return false;
            }
        }


        // the eligible items should be greater than or equal to x,
        // if the rule can be applied multiple times, then the eligible items should be greater than or equal to x * n
        // the sale items update and less than before, then the promotion should be removed
        $eligibleItems = $this->getEligibleItems($sale->details);

        $maxApplications = $this->rule->promotion->max_applications_per_sale ?? PHP_INT_MAX;

        $applications = $sale->promotions->where('id', $this->rule->promotion_id)->count();

        $timesPromotionCanBeApplied = floor($eligibleItems / $this->rule->x);

        return $timesPromotionCanBeApplied > $applications && $applications < $maxApplications;

    }

    protected function wasPromotionApplied($detail)
    {
        return $detail->discount_method == 'buy_x_get_y';
    }

    protected function applyPromotion(Sale $sale)
    {

        $numberShouldBeApplied = $this->getNumberShouldBeApplied($sale);

        for ($counter = 0; $counter < $numberShouldBeApplied; $counter++) {
            DB::transaction(function () use ($sale) {
                // create a new sales detail
                $sale->details()->create([
                    'product_id' => $this->rule->y,
                    'date' => now(),
                    'price' => 0,
                    'quantity' => 1,
                    'discount' => 0,
                    'discount_method' => 'buy_x_get_y',
                ]);

                $sale->promotions()->attach($this->rule->promotion_id);
            });
        }


    }

    protected function removePromotion(Sale $sale)
    {
        if ($sale->promotions->contains($this->rule->promotion_id)) {
            $this->reverseDiscount($sale);
        }

    }

    protected function reverseDiscount(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            // create a new sales detail
            $sale->details()
                ->where('discount_method', 'buy_x_get_y')
                ->where('product_id', $this->rule->y)
                ->delete();

            $sale->promotions()->detach($this->rule->promotion_id);
        });

    }

    /**
     * @param \App\Models\Sale $sale
     * @return int|mixed
     */
    private function getNumberShouldBeApplied($details, $rewards = null): mixed
    {
        $maxApplications = $this->rule->promotion->max_applications_per_sale ?? PHP_INT_MAX;

        $eligibleItems = $this->getEligibleItems($details);

        $timesPromotionCanBeApplied = floor($eligibleItems / $this->rule->x);

        $currentApplied = $this->getCurrentApplied($rewards);

        $numberShouldBeApplied = min($this->getMaxRemainingUsage(), $maxApplications, $timesPromotionCanBeApplied) - $currentApplied;

        return $numberShouldBeApplied;
    }
}
