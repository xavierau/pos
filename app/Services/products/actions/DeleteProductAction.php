<?php

namespace App\Services\products\actions;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeleteProductAction
{
    public function execute(int $product_id)
    {
        DB::transaction(function () use ($product_id) {

            $Product = Product::findOrFail($product_id);
            $Product->deleted_at = Carbon::now();
            $Product->save();

            foreach (explode(',', $Product->image) as $img) {
                $pathIMG = public_path() . '/images/products/' . $img;
                if (file_exists($pathIMG)) {
                    if ($img != 'no-image.png') {
                        @unlink($pathIMG);
                    }
                }
            }

            ProductWarehouse::where('product_id', $product_id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            ProductVariant::where('product_id', $product_id)->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);
    }

}
