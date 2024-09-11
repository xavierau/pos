<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleDetailFactory extends Factory
{
    protected $model = \App\Models\SaleDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            'sale_id' => fn() => Sale::factory()->create()->id,
            'sale_unit_id' => fn() => Unit::factory()->create()->id,
            'quantity' => $this->faker->randomNumber(),
            'product_id' => fn() => Product::factory()->create()->id,
            'product_variant_id' => null,
            'price' => $this->faker->randomFloat(),
            'tax_net' => null,
            'discount' => 0,
            'discount_method' => null,
            'tax_method' => null,

        ];
    }
}
