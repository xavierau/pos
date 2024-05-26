<?php

namespace App\Services\products\actions;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Gumlet\ImageResizeException;
use Illuminate\Support\Facades\DB;

class CreateProductAction
{

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $product = $this->createNewProduct($data);

            //-- check if type is_single
            if ($data['type'] == 'is_single') {
                $product->price = $data['price'];
                $product->cost = $data['cost'];

                $product->unit_id = $data['unit_id'];
                $product->unit_sale_id = $data['unit_sale_id'] ? $data['unit_sale_id'] : $data['unit_id'];
                $product->unit_purchase_id = $data['unit_purchase_id'] ? $data['unit_purchase_id'] : $data['unit_id'];

                $product->stock_alert = $data['stock_alert'] ? $data['stock_alert'] : 0;

                $manage_stock = 1;

                //-- check if type is_variant
            } elseif ($data['type'] == 'is_variant') {

                $product->price = 0;
                $product->cost = 0;

                $product->unit_id = $data['unit_id'];
                $product->unit_sale_id = $data['unit_sale_id'] ? $data['unit_sale_id'] : $data['unit_id'];
                $product->unit_purchase_id = $data['unit_purchase_id'] ? $data['unit_purchase_id'] : $data['unit_id'];

                $product->stock_alert = $data['stock_alert'] ? $data['stock_alert'] : 0;

                $manage_stock = 1;

                //-- check if type is_service
            } else {
                $product->price = $data['price'];
                $product->cost = 0;

                $product->unit_id = NULL;
                $product->unit_sale_id = NULL;
                $product->unit_purchase_id = NULL;

                $product->stock_alert = 0;

                $manage_stock = 0;

            }

            $filename = $this->uploadImages($data['images'] ?? []);

            $product->image = $filename;
            $product->save();

            // Store Variants Product
            if ($data['type'] == 'is_variant') {
                $variants = json_decode($data['variants']);

                foreach ($variants as $variant) {
                    $product_variants_data[] = [
                        'product_id' => $product->id,
                        'name' => $variant->text,
                        'cost' => $variant->cost,
                        'price' => $variant->price,
                        'code' => $variant->code,
                    ];
                }
                ProductVariant::insert($product_variants_data);
            }

            //--Store Product Warehouse
            $warehouses = Warehouse::whereNull('deleted_at')->pluck('id')->toArray();
            if ($warehouses) {
                $product_variants = ProductVariant::where('product_id', $product->id)
                    ->whereNull('deleted_at')
                    ->get();
                foreach ($warehouses as $warehouse) {
                    if ($data['is_variant'] == 'true') {
                        foreach ($product_variants as $product_variant) {

                            $product_warehouse[] = [
                                'product_id' => $product->id,
                                'warehouse_id' => $warehouse,
                                'product_variant_id' => $product_variant->id,
                                'manage_stock' => $manage_stock,
                            ];
                        }
                    } else {
                        $product_warehouse[] = [
                            'product_id' => $product->id,
                            'warehouse_id' => $warehouse,
                            'manage_stock' => $manage_stock,
                        ];
                    }
                }
                ProductWarehouse::insert($product_warehouse);
            }

        }, 10);
    }

    private function uploadImages($images): string
    {
        if ($images) {
            foreach ($images as $file) {
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

        return 'no-image.png';

    }

    private function createNewProduct(array $data)
    {
        //-- Create New Product
        $product = new Product;

        //-- Field Required
        $product->type = $data['type'];
        $product->name = $data['name'];
        $product->code = $data['code'];
        $product->type_barcode = $data['type_barcode'];
        $product->category_id = $data['category_id'];
        $product->brand_id = $data['brand_id'];
        $product->note = $data['note'];
        $product->TaxNet = $data['TaxNet'] ? $data['TaxNet'] : 0;
        $product->tax_method = $data['tax_method'];
        $product->promotional_price = $data['promotional_price'];
        $product->promotional_start_date = $data['promotional_price'] ? $data['promotional_start_date'] : null;;
        $product->promotional_end_date = $data['promotional_price'] ? $data['promotional_end_date'] : null;;
        $product->is_variant = $data['is_variant'] == 'true' ? 1 : 0;
        $product->is_imei = $data['is_imei'] == 'true' ? 1 : 0;
        $product->not_selling = $data['not_selling'] == 'true' ? 1 : 0;

        return $product;
    }


}
