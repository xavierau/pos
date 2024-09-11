<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\DiscountedPrice\Http\Controllers\ProductDiscountedPriceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/discountedprice', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::post('products/{product}/discounted_prices', [ProductDiscountedPriceController::class, 'store']);
    Route::put('products/{product}/discounted_prices/{discounted_price}', [ProductDiscountedPriceController::class, 'update']);

    Route::post('productVariants/{productVariant}/discounted_prices', [\Modules\DiscountedPrice\Http\Controllers\ProductVariantDiscountedPriceController::class, 'store']);
    Route::put('productVariants/{productVariant}/discounted_prices/{discounted_price}', [\Modules\DiscountedPrice\Http\Controllers\ProductVariantDiscountedPriceController::class, 'update']);
});

