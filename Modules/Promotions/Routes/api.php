<?php

use Illuminate\Support\Facades\Route;
use Modules\Promotions\Http\Controllers\CheckPromotionsController;
use Modules\Promotions\Http\Controllers\CreatePromotionController;
use Modules\Promotions\Http\Controllers\CreatePromotionsController;
use Modules\Promotions\Http\Controllers\DeletePromotionController;
use Modules\Promotions\Http\Controllers\GetProductsController;
use Modules\Promotions\Http\Controllers\GetPromotionTypesController;
use Modules\Promotions\Http\Controllers\ListPromotionsBackendController;
use Modules\Promotions\Http\Controllers\ShowPromotionController;
use Modules\Promotions\Http\Controllers\UpdatePromotionController;

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

Route::middleware('auth:api')->group(callback: function () {
    Route::get('/promotions', ListPromotionsBackendController::class);
    Route::post('/promotions', CreatePromotionsController::class);
    Route::get('/promotions/create', CreatePromotionController::class);
    Route::get('/promotions/types', GetPromotionTypesController::class);
    Route::get('/promotions/products', GetProductsController::class);
    Route::post('/promotions/check', CheckPromotionsController::class);

    Route::get('/promotions/{promotion}', ShowPromotionController::class);
    Route::delete('/promotions/{promotion}', DeletePromotionController::class);
    Route::put('/promotions/{promotion}', UpdatePromotionController::class);

});
