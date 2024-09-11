<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $providers = [];
        for($i=0; $i<5; $i++){
            $providers[] = [
                'name' => fake()->company(),
                'code' => fake()->randomNumber(5),
                'email' => fake()->email(),
                'country' => fake()->country(),
                'city' => fake()->city(),
                'address' => fake()->address(),
            ];
        }

        Provider::insert($providers);

    }
}
