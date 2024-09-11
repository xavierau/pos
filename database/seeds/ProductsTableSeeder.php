<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $simpleProducts = [];

        $lastCategoryId = Category::latest('id')->first()->id;
        $lastBrandId = Brand::latest('id')->first()->id;

        for ($i = 0; $i < 5; $i++) {
            $simpleProducts[] = [
                'type' => 'is_single',
                'name' => fake()->word,
                'code' => fake()->slug,
                'type_barcode' => 'CODE128',
                'image' => "no-image.png",
                'price' => fake()->randomFloat(2, 1, 100),
                'cost' => fake()->randomFloat(2, 1, 50),
                'note' => fake()->sentence,
                'stock_alert' => 5,
                'is_imei' => false,
                'not_selling' => false,
                'is_active' => true,
                'unit_id' => 17,
                'unit_sale_id' => 17,
                'unit_purchase_id' => 17,
                'brand_id' => fake()->numberBetween(1, $lastBrandId),
                'category_id' => fake()->numberBetween(1, $lastCategoryId),
            ];
        }

        Product::insert($simpleProducts);

        $warehouses = Warehouse::all();

        $product_warehouse = [];
        $simpleProducts = Product::all();

        foreach ($simpleProducts as $product) {
            foreach ($warehouses as $warehouse) {
                $product_warehouse[] = [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'qty' => 0,
                    'manage_stock' => true,
                ];
            }
        }

        ProductWarehouse::insert($product_warehouse);


        $variantProducts = [];

        for ($i = 0; $i < 5; $i++) {
            $variantProducts[] = [
                'product' => ['type' => 'is_variant',
                    'name' => fake()->word,
                    'code' => fake()->slug,
                    'type_barcode' => 'CODE128',
                    'image' => "no-image.png",
                    'price' => fake()->randomFloat(2, 1, 100),
                    'cost' => fake()->randomFloat(2, 1, 50),
                    'note' => fake()->sentence,
                    'stock_alert' => 5,
                    'is_imei' => false,
                    'not_selling' => false,
                    'is_active' => true,
                    'unit_id' => 17,
                    'unit_sale_id' => 17,
                    'unit_purchase_id' => 17,
                    'brand_id' => fake()->numberBetween(1, $lastBrandId),
                    'category_id' => fake()->numberBetween(1, $lastCategoryId),
                ],
                'variants' => fn($productId) => [
                    [
                        'name' => fake()->word,
                        'product_id' => $productId,
                        'cost' => fake()->randomFloat(2, 1, 50),
                        'price' => fake()->randomFloat(2, 1, 100),
                        'code' => fake()->slug,
                        'image' => "no-image.png",
                    ],
                    [
                        'name' => fake()->word,
                        'product_id' => $productId,
                        'cost' => fake()->randomFloat(2, 1, 50),
                        'price' => fake()->randomFloat(2, 1, 100),
                        'code' => fake()->slug,
                        'image' => "no-image.png",
                    ],
                    [
                        'name' => fake()->word,
                        'product_id' => $productId,
                        'cost' => fake()->randomFloat(2, 1, 50),
                        'price' => fake()->randomFloat(2, 1, 100),
                        'code' => fake()->slug,
                        'image' => "no-image.png",
                    ],
                    [
                        'name' => fake()->word,
                        'product_id' => $productId,
                        'cost' => fake()->randomFloat(2, 1, 50),
                        'price' => fake()->randomFloat(2, 1, 100),
                        'code' => fake()->slug,
                        'image' => "no-image.png",
                    ],
                    [
                        'name' => fake()->word,
                        'product_id' => $productId,
                        'cost' => fake()->randomFloat(2, 1, 50),
                        'price' => fake()->randomFloat(2, 1, 100),
                        'code' => fake()->slug,
                        'image' => "no-image.png",
                    ]
                ]

            ];
        }

        foreach ($variantProducts as $data) {
            $product = Product::create($data['product']);
            $product->variants()->insert($data['variants']($product->id));
        }

        $variantProducts = Product::with('variants')
            ->whereNotIn('id', $simpleProducts->pluck('id'))
            ->get();


        $product_variant_warehouse = [];

        foreach ($variantProducts as $product) {
            foreach ($product->variants as $variant) {
                foreach ($warehouses as $warehouse) {
                    $product_variant_warehouse[] = [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouse->id,
                        'product_variant_id' => $variant->id,
                        'qty' => 0,
                        'manage_stock' => true,
                    ];
                }
            }
        }

        ProductWarehouse::insert($product_variant_warehouse);
    }
}
