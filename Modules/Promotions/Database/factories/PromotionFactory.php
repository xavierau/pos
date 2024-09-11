<?php

namespace Modules\Promotions\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Promotions\Entities\Promotion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'start_date' => now()->addDays(-1),
            'end_date' => now()->addDays(1),
            'max_applications_per_sale' => 1
        ];
    }
}

