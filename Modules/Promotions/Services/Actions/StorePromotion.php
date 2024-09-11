<?php

namespace Modules\Promotions\Services\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Promotions\DTO\StorePromotionDTO;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Enums\PromotionType;

class StorePromotion
{
    public function execute(StorePromotionDTO $dto): Promotion
    {
        DB::beginTransaction();

        try {

            $promotion = Promotion::create([
                'name' => $dto->name,
                'start_date' => $dto->start_date,
                'end_date' => $dto->end_date,
                'max_usage' => $dto->max_usage,
                'max_applications_per_sale' => $dto->max_applications_per_sale,
            ]);

            switch ($dto->rule->type) {
                case PromotionType::BuyXGetY:
                    $rule_data = [
                        'type' => $dto->rule->type,
                        'x' => $dto->rule->x_qty,
                        'y' => $dto->rule->y_qty,
                        'product_id' => $dto->rule->x_product->product_id,
                        'product_variant_id' => $dto->rule->x_product->product_variant_id,
                        'y_product_id' => $dto->rule->y_product->product_id,
                        'y_product_variant_id' => $dto->rule->y_product->product_variant_id,
                    ];
                    break;
                case PromotionType::FreeXIfOverYAmount:
                    $rule_data = [
                        'type' => $dto->rule->type,
                        'x' => $dto->rule->x_qty,
                        'y' => $dto->rule->y_qty,
                        'product_id' => $dto->rule->x_product->product_id,
                        'product_variant_id' => $dto->rule->x_product->product_variant_id,
                    ];
                    break;
                case PromotionType::DiscountXPercentageIfOverYAmount:
                    $rule_data = [
                        'type' => $dto->rule->type,
                        'x' => $dto->rule->x_qty,
                        'y' => $dto->rule->y_qty,
                        'amount' => $dto->rule->amount,
                    ];
                    break;
            }

            $promotion->rules()->create($rule_data);

            DB::commit();

            return $promotion;

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }

    }

}
