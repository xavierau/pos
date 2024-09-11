<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{

    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => $this->faker->date(),
            'ref' => $this->faker->word(),
            'is_pos' => true,
            'client_id' => fn() => Client::factory()->create()->id,
            'grand_total' => $this->faker->randomFloat(),
            'tax_net' => $this->faker->randomFloat(),
            'tax_rate' => $this->faker->randomFloat(),
            'notes' => $this->faker->text(),

            'warehouse_id' => fn() => Warehouse::factory()->create()->id,
            'user_id' => fn() => User::factory()->create()->id,
            'status' => $this->faker->randomElement(PaymentStatus::values()),
            'discount' => $this->faker->randomFloat(),
            'shipping' => $this->faker->randomFloat(),

            'paid_amount' => $this->faker->randomFloat(),
            'payment_status' => $this->faker->randomElement(PaymentStatus::values()),
            'shipping_status' => $this->faker->randomElement(PaymentStatus::values()),
        ];
    }
}
