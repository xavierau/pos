<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Modules\Promotions\Enums\PromotionType;
use Modules\Promotions\Services\Actions\StorePromotion;

class CreatePromotionController extends Controller
{
    public function __invoke(Request $request, StorePromotion $action)
    {
        $types = [];
        foreach (PromotionType::array() as $val => $key) {
            $types[] = [
                "label" => $key,
                "value" => $val
            ];
        }

        $products = Product::active()
            ->with('variants')
            ->orderBy('name')
            ->get()
            ->reduce(function ($carry, $product) {
                if ($product->is_variant) {
                    foreach ($product->variants as $variant) {
                        $carry[] = [
                            'id' => $variant->id,
                            'name' => sprintf("[%s] %s", $product->name, $variant->name)
                        ];
                    }
                } else {
                    $carry[] = [
                        'id' => $product->id,
                        'name' => $product->name
                    ];
                }
                return $carry;
            }, []);
        return response()->json([
            'types' => $types,
            'products' => $products
        ]);
    }
}
