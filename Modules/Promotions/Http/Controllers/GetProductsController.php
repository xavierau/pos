<?php

namespace Modules\Promotions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Modules\Promotions\Services\Actions\StorePromotion;

class GetProductsController extends Controller
{
    public function __invoke(Request $request, StorePromotion $action)
    {
        $products = Product::active()
            ->with('variants')
            ->orderBy('name')
            ->get()
            ->reduce(function ($carry, $product) {
                if ($product->is_variant) {
                    foreach ($product->variants as $variant) {
                        $carry[] = [
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'name' => sprintf("[%s] %s", $product->name, $variant->name)
                        ];
                    }
                } else {
                    $carry[] = [
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'name' => $product->name
                    ];
                }
                return $carry;
            }, []);
        return response()->json(['products' => $products]);
    }
}
