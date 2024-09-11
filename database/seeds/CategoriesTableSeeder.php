<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'code' => 'cat_1',
                'name' => 'Category 1',
            ],
            [
                'code' => 'cat_2',
                'name' => 'Category 2',
            ],
            [
                'code' => 'cat_3',
                'name' => 'Category 3',
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
