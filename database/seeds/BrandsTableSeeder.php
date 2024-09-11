<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [];

        for($i=0; $i<5; $i++) {
            $brands[] = [
                'name' => fake()->company,
                'description' => fake()->sentence,
                'image'=>fake()->imageUrl,
            ];
        }
        Brand::insert($brands);
    }
}
