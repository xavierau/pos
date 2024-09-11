<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Enums\PromotionType;

class GetPromotionTypesController extends Controller
{
    public function __invoke(Request $request, Promotion $promotion)
    {
        $types = [];
        foreach (PromotionType::array() as $val => $key) {
            $types[] = [
                "label" => $key,
                "value" => $val
            ];
        }

        return response()->json(compact('types'));
    }
}
