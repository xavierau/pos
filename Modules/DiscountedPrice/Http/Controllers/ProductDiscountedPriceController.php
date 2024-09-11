<?php

namespace Modules\DiscountedPrice\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DiscountedPrice\Entities\DiscountedPrice;

class ProductDiscountedPriceController extends Controller
{


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            'discounted_price' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $discountedPrice = $product->discountedPrices()->create($validatedData);

        return response()->json([
            'discounted_price' => $discountedPrice,
            'message' => 'Discounted price created successfully',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function update(Request $request, Product $product, DiscountedPrice $discountedPrice)
    {
        if ($discountedPrice->discountable->isNot($product)) {
            abort(403, "Invalid product for the discounted price");
        }
        $validatedData = $request->validate([
            'discounted_price' => ['required', 'numeric'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $discountedPrice->update($validatedData);

        return response()->json([
            'discounted_price' => $discountedPrice->refresh(),
            'message' => 'Discounted price updated successfully',
        ]);
    }
}
