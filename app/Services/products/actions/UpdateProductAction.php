<?php

namespace App\Services\products\actions;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Carbon\Carbon;
use Gumlet\ImageResizeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UpdateProductAction
{

    public function execute(Product $product, array $data)
    {
        DB::transaction(function () use ($product, $data) {

            $product = $this->updateProductBasicInfo($product, $data);

            switch ($data['type']) {
                case 'is_single';
                    $product = $this->updateSingleTypeProduct($product, $data);
                    break;
                case 'is_variant';
                    $product = $this->updateVariantTypeProduct($product, $data);
                    break;
                default:
                    $product = $this->updateProduct($product, $data);
            }


            $filename = $this->updateProductImages($product, $data);

            $product->image = $filename;
            $product->save();

        });


        return $product->refresh();

    }

    private function updateProductBasicInfo(Product $product, array $data): Product
    {
        //-- Update Product
        $product->type = $data['type'];
        $product->name = $data['name'];
        $product->code = $data['code'];
        $product->type_barcode = $data['type_barcode'];
        $product->category_id = $data['category_id'];
        $product->brand_id = $data['brand_id'] == 'null' ? Null : $data['brand_id'];
        $product->tax_net = $data['tax_net'];
        $product->tax_method = $data['tax_method'];
        $product->note = $data['note'];

        $product->is_imei = $data['is_imei'] == 'true' ? 1 : 0;
        $product->not_selling = $data['not_selling'] == 'true' ? 1 : 0;

        return $product;

    }

    private function getProductVariantAndWarehouses(Product $product): array
    {

        $productVariants = ProductVariant::where('product_id', $product->id)
            ->whereNull('deleted_at')
            ->get();

        $warehouses = Warehouse::whereNull('deleted_at')
            ->pluck('id')
            ->toArray();

        return [$productVariants, $warehouses];

    }

    private function updateSingleTypeProduct(Product $product, array $data): Product
    {
        $product->price = $data['price'];
        $product->cost = $data['cost'];

        $product->unit_id = $data['unit_id'];
        $product->unit_sale_id = $data['unit_sale_id'] ? $data['unit_sale_id'] : $data['unit_id'];
        $product->unit_purchase_id = $data['unit_purchase_id'] ? $data['unit_purchase_id'] : $data['unit_id'];

        $product->stock_alert = $data['stock_alert'] ? $data['stock_alert'] : 0;
        $product->is_variant = 0;

        $manage_stock = 1;

        $this->updateOldVariants($product, $data, $manage_stock);

        $this->updateDiscountedPrice($product, $data['discounted_price']);

        return $product;
    }

    private function updateOldVariants(Product $product, array $data, int $manage_stock): void
    {
        list($oldVariants, $warehouses) = $this->getProductVariantAndWarehouses($product);

        if ($oldVariants->isNotEmpty()) {
            foreach ($oldVariants as $old_var) {
                $var_old = ProductVariant::where('product_id', $old_var['product_id'])
                    ->where('deleted_at', null)
                    ->first();
                $var_old->deleted_at = Carbon::now();
                $var_old->save();

                ProductWarehouse::where('product_variant_id', $old_var['id'])
                    ->update([
                        'deleted_at' => Carbon::now(),
                    ]);
            }

            if ($warehouses) {
                foreach ($warehouses as $warehouse) {

                    $product_warehouse[] = [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse,
                        'product_variant_id' => null,
                        'manage_stock' => $manage_stock,
                    ];

                }
                ProductWarehouse::insert($product_warehouse);
            }
        }
    }

    private function updateVariantTypeProduct(Product $product, array $data): Product
    {
        $product->price = 0;
        $product->cost = 0;

        $product->unit_id = $data['unit_id'];
        $product->unit_sale_id = $data['unit_sale_id'] ? $data['unit_sale_id'] : $data['unit_id'];
        $product->unit_purchase_id = $data['unit_purchase_id'] ? $data['unit_purchase_id'] : $data['unit_id'];

        $product->stock_alert = $data['stock_alert'] ? $data['stock_alert'] : 0;
        $product->is_variant = 1;
        $manage_stock = 1;

        list($oldVariants, $warehouses) = $this->getProductVariantAndWarehouses($product);

        if ($oldVariants->isNotEmpty()) {
            $new_variants_id = [];
            $var = 'id';

            foreach ($data['variants'] as $new_id) {
                if (array_key_exists($var, $new_id)) {
                    $new_variants_id[] = $new_id['id'];
                } else {
                    $new_variants_id[] = 0;
                }
            }

            foreach ($oldVariants as $key => $value) {
                $old_variants_id[] = $value->id;

                // Delete Variant
                if (!in_array($old_variants_id[$key], $new_variants_id)) {
                    $productVariant = ProductVariant::findOrFail($value->id);
                    $productVariant->deleted_at = Carbon::now();
                    $productVariant->save();

                    $productWarehouse = ProductWarehouse::where('product_variant_id', $value->id)
                        ->update(['deleted_at' => Carbon::now()]);
                }
            }

            foreach ($data['variants'] as $key => $variant) {
                if (array_key_exists($var, $variant)) {

                    $productVariantDT = new ProductVariant;
                    //-- Field Required
                    $productVariantDT->product_id = $variant['product_id'];
                    $productVariantDT->name = $variant['text'];
                    $productVariantDT->price = $variant['price'];
                    $productVariantDT->cost = $variant['cost'];
                    $productVariantDT->code = $variant['code'];

                    $productVariantUP['product_id'] = $variant['product_id'];
                    $productVariantUP['code'] = $variant['code'];
                    $productVariantUP['name'] = $variant['text'];
                    $productVariantUP['price'] = $variant['price'];
                    $productVariantUP['cost'] = $variant['cost'];

                } else {
                    $productVariantDT = new ProductVariant;

                    //-- Field Required
                    $productVariantDT->product_id = $product->id;
                    $productVariantDT->code = $variant['code'];
                    $productVariantDT->name = $variant['text'];
                    $productVariantDT->price = $variant['price'];
                    $productVariantDT->cost = $variant['cost'];

                    $productVariantUP['product_id'] = $product->id;
                    $productVariantUP['code'] = $variant['code'];
                    $productVariantUP['name'] = $variant['text'];
                    $productVariantUP['price'] = $variant['price'];
                    $productVariantUP['cost'] = $variant['cost'];
                    $productVariantUP['qty'] = 0.00;
                }

                if (!in_array($new_variants_id[$key], $old_variants_id)) {
                    $productVariantDT->save();

                    //--Store Product warehouse
                    if ($warehouses) {
                        $product_warehouse = [];
                        foreach ($warehouses as $warehouse) {

                            $product_warehouse[] = [
                                'product_id' => $product->id,
                                'warehouse_id' => $warehouse,
                                'product_variant_id' => $productVariantDT->id,
                                'manage_stock' => $manage_stock,
                            ];

                        }
                        ProductWarehouse::insert($product_warehouse);
                    }
                } else {
                    ProductVariant::where('id', $variant['id'])->update($productVariantUP);
                }
            }

        } else {
            ProductWarehouse::where('product_id', $product->id)
                ->update([
                    'deleted_at' => Carbon::now(),
                ]);

            foreach ($data['variants'] as $variant) {
                $product_warehouse_DT = [];
                $productVarDT = new ProductVariant;

                //-- Field Required
                $productVarDT->product_id = $product->id;
                $productVarDT->code = $variant['code'];
                $productVarDT->name = $variant['text'];
                $productVarDT->cost = $variant['cost'];
                $productVarDT->price = $variant['price'];
                $productVarDT->save();


                //-- Store Product warehouse
                if ($warehouses) {
                    foreach ($warehouses as $warehouse) {

                        $product_warehouse_DT[] = [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse,
                            'product_variant_id' => $productVarDT->id,
                            'manage_stock' => $manage_stock,
                        ];
                    }

                    ProductWarehouse::insert($product_warehouse_DT);
                }
            }

        }

        return $product;
    }

    private function updateProduct(Product $product, array $data): Product
    {
        $product->price = $data['price'];
        $product->cost = 0;

        $product->unit_id = NULL;
        $product->unit_sale_id = NULL;
        $product->unit_purchase_id = NULL;

        $product->stock_alert = 0;
        $product->is_variant = 0;
        $manage_stock = 0;

        $this->updateDiscountedPrice($product, $data['discounted_price']);

        list($oldVariants, $warehouses) = $this->getProductVariantAndWarehouses($product);

        if ($oldVariants->isNotEmpty()) {
            foreach ($oldVariants as $old_var) {
                $var_old = ProductVariant::where('product_id', $old_var['product_id'])
                    ->where('deleted_at', null)
                    ->first();
                $var_old->deleted_at = Carbon::now();
                $var_old->save();

                $productWarehouse = ProductWarehouse::where('product_variant_id', $old_var['id'])
                    ->update([
                        'deleted_at' => Carbon::now(),
                    ]);
            }

            if ($warehouses) {
                foreach ($warehouses as $warehouse) {

                    $product_warehouse[] = [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse,
                        'product_variant_id' => null,
                        'manage_stock' => $manage_stock,
                    ];

                }
                ProductWarehouse::insert($product_warehouse);
            }
        }

        return $product;
    }

    /**
     * @throws \Gumlet\ImageResizeException
     */
    private function updateProductImages(Product $product, array $data): string
    {
        $this->removeProductImages($product);

        if ($data['images'] === null) {
            return 'no-image.png';
        }

        foreach ($data['images'] as $file) {
            $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file['path']));

            // Check if base64 decoding was successful
            if ($fileData === false) {
                throw new ImageResizeException('Failed to decode base64 image data');
            }

            // Create an image resource from the decoded data
            $imageResource = imagecreatefromstring($fileData);

            // Check if imagecreatefromstring was successful
            if ($imageResource === false) {
                throw new ImageResizeException('Failed to create image resource from decoded data');
            }

            // Resize the image
            $newImageResource = imagescale($imageResource, 200, 200);

            // Save the resized image to a file
            $name = rand(11111111, 99999999) . $file['name'];
            $path = public_path() . '/images/products/';
            $success = imagepng($newImageResource, $path . $name);

            // Check if saving the image was successful
            if (!$success) {
                throw new ImageResizeException('Failed to save resized image');
            }

            // Free up memory by destroying the image resources
            imagedestroy($imageResource);
            imagedestroy($newImageResource);

            $images[] = $name;

        }

        return implode(",", $images);
    }

    private function removeProductImages(Product $product): void
    {
        if ($product->image !== null) {
            foreach (explode(',', $product->image) as $img) {
                $pathIMG = public_path() . '/images/products/' . $img;
                if (file_exists($pathIMG)) {
                    if ($img != 'no-image.png') {
                        @unlink($pathIMG);
                    }
                }
            }
        }
    }

    private function updateDiscountedPrice(Model $model, array $discounted_price)
    {

        if ($discounted_price['id']) {
            return $model->discountedPrices()->where('id', $discounted_price['id'])
                ->update([
                    'start_date' => $discounted_price['start_date'],
                    'end_date' => $discounted_price['end_date'],
                    'discounted_price' => $discounted_price['discounted_price'],
                ]);
        }

        return $model->discountedPrices()->where('id', $discounted_price['id'])
            ->create([
                'start_date' => $discounted_price['start_date'],
                'end_date' => $discounted_price['end_date'],
                'discounted_price' => $discounted_price['discounted_price'],
            ]);
    }

}
