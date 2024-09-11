<?php

namespace Modules\Promotions\Services\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Promotions\DTO\UpdatePromotionDTO;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Enums\PromotionType;

class UpdatePromotion
{
    public function execute(UpdatePromotionDTO $dto): Promotion
    {
        DB::beginTransaction();

        try {

            $promotion = Promotion::findOrFail($dto->id);

            $promotion->update([
                'name' => $dto->name,
                'start_date' => $dto->start_date,
                'end_date' => $dto->end_date,
            ]);

            $rule = $promotion->rules()->first();

            switch ($rule->type) {
                case PromotionType::BuyXGetY:
                    $rule_data = [
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
                        'x' => $dto->rule->x_qty,
                        'y' => $dto->rule->y_qty,
                        'product_id' => $dto->rule->x_product->product_id,
                        'product_variant_id' => $dto->rule->x_product->product_variant_id,
                    ];
                    break;
                case PromotionType::DiscountXPercentageIfOverYAmount:
                    $rule_data = [
                        'x' => $dto->rule->x_qty,
                        'y' => $dto->rule->y_qty,
                        'amount' => $dto->rule->amount,
                    ];
                    break;
            }

            $rule->update($rule_data);

            DB::commit();

            return $promotion->refresh();

        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }

    }

}
