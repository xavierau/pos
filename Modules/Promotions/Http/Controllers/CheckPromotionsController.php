<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Promotions\DTO\CartDTO;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Factories\PromotionFactory;
use Modules\Promotions\Services\PromotionService;

class CheckPromotionsController extends Controller
{
    public function __invoke(Request $request, PromotionService $service)
    {

        $request->validate([
            'items' => ['required', 'array'],
            'discount' => ['required', "numeric"],
            'shipping' => ['required', "numeric"],
            'client_id' => ['required', Rule::exists('clients', 'id')],
        ]);

        $cartDto = CartDTO::fromRequest($request);

        $activePromotions = Promotion::getActivePromotions();

        $promotions = $activePromotions
            ->reduce(function ($carry, $promotion) use ($cartDto, $service) {
                $rule = $promotion->rules->first();
                if ($rule) {
                    $checker = PromotionFactory::create($rule);
                    $temp = [];
                    $new = $checker->getPromotedItems($cartDto);

                    if (count($new->increment) > 0) {
                        $temp['increment'] = $new->increment;
                    }
                    if (count($new->decrement) > 0) {
                        $temp['decrement'] = $new->decrement;
                    }
                    if (count($temp) > 0) {
                        $carry[] = $temp;
                    }
                }
                return $carry;
            }, []);

        return response()->json(compact('promotions'));
    }
}
