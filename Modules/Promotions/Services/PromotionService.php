<?php

// Modules/Promotions/Services/PromotionService.php
namespace Modules\Promotions\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Factories\PromotionFactory;

class PromotionService
{
    public function applyPromotions(Sale $sale)
    {
        $promotions = Promotion::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        foreach ($promotions as $promotion) {
            foreach ($promotion->rules as $rule) {
                $promotionHandler = PromotionFactory::create($rule);
                $promotionHandler->apply($sale);
            }
        }

        return $sale;
    }

    public function adjustPromotionsForReturn(SaleReturn $saleReturn)
    {
        $originalSale = $saleReturn->sale;

        // Get promotions applied to the original sale
        $promotions = $this->getPromotionsFromSale($originalSale);

        foreach ($promotions as $promotion) {
            foreach ($promotion->rules as $rule) {
                $promotionHandler = PromotionFactory::create($rule);
                $promotionHandler->adjustForReturn($saleReturn);
            }
        }

        return $saleReturn;
    }

    protected function getPromotionsFromSale(Sale $sale)
    {
        // Assuming there is a way to track which promotions were applied to a sale
        // This method should retrieve those promotions. For simplicity, let's assume
        // it's stored in a pivot table sale_promotion or similar.

        // Example pseudo-code:
        // return $sale->promotions;

        // Replace this with actual logic as per your database structure
        return Promotion::whereIn('id', $sale->promotion_ids)->get();
    }
}
