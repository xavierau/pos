<?php

namespace Database\Factories;

use App\Enums\UnitOperator;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = \App\Models\Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'short_name' => $this->faker->name,
            'base_unit' => null,
            'operator' => UnitOperator::MULTIPLY,
            'operator_value' => 1,
            'is_active' => true,
        ];
    }
}
