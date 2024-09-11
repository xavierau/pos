<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $unitId = null;

        $getUnitId = function () use ($unitId) {
            if ($unitId == null) {
                $unitId = \App\Models\Unit::factory()->create()->id;
            }
            return $unitId;
        };

        return [
            'code' => $this->faker->unique()->ean13,
            'type_barcode' => $this->faker->ean13,
            'name' => $this->faker->name,
            'cost' => $this->faker->randomFloat(2, 1, 1000),
            'price' => $this->faker->randomFloat(2, 1, 1000),
            'unit_id' => $getUnitId(),
            'unit_sale_id' => $getUnitId(),
            'unit_purchase_id' => $getUnitId(),

            'stock_alert' => 2,
            'category_id' => fn() => \App\Models\Category::factory()->create()->id,
            'is_variant' => false,
            'is_imei' => false,

            'tax_method' => 'simple',
            'brand_id' => fn() => \App\Models\Brand::factory()->create()->id,
            'is_active' => true,
            'note' => "",
            'type' => "type",
        ];
    }
}
