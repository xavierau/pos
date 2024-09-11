<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UnitsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
//            [
//                'name' => 'Gram',
//                'short_name' => 'g',
//            ],
//            [
//                'name' => 'Kilogram',
//                'short_name' => 'kg',
//                'base_unit' => 1,
//                'operator' => '*',
//                'operator_value' => 1000,
//            ],
//            [
//                'name' => 'Milligram',
//                'short_name' => 'mg',
//                'base_unit' => 1,
//                'operator' => '/',
//                'operator_value' => 1000,
//            ],
//            [
//                'name' => 'Liter',
//                'short_name' => 'L',
//            ],
//            [
//                'name' => 'Milliliter',
//                'short_name' => 'mL',
//                'base_unit' => 4,
//                'operator' => '/',
//                'operator_value' => 1000,
//            ],
//            [
//                'name' => 'Kiloliter',
//                'short_name' => 'kL',
//                'base_unit' => 4,
//                'operator' => '*',
//                'operator_value' => 1000,
//            ],
//            [
//                'name' => 'Centimeter',
//                'short_name' => 'cm',
//            ],
//            [
//                'name' => 'Meter',
//                'short_name' => 'm',
//                'base_unit' => 7,
//                'operator' => '*',
//                'operator_value' => 100,
//            ],
//            [
//                'name' => 'Kilometer',
//                'short_name' => 'km',
//                'base_unit' => 7,
//                'operator' => '*',
//                'operator_value' => 100000,
//            ],
//            [
//                'name' => 'Millimeter',
//                'short_name' => 'mm',
//                'base_unit' => 7,
//                'operator' => '/',
//                'operator_value' => 1000,
//            ],
//            [
//                'name' => 'Bag',
//                'short_name' => 'bag',
//            ],
//            [
//                'name' => 'Box',
//                'short_name' => 'box',
//            ],
//            [
//                'name' => 'Bottle',
//                'short_name' => 'bottle',
//            ],
//            [
//                'name' => 'Can',
//                'short_name' => 'can',
//            ],
//            [
//                'name' => 'Carton',
//                'short_name' => 'carton',
//            ],
//            [
//                'name' => 'Pack',
//                'short_name' => 'pack',
//            ],
//            [
//                'name' => 'Piece',
//                'short_name' => 'piece',
//            ],
//            [
//                'name' => 'Roll',
//                'short_name' => 'roll',
//            ],

            [
                'name'=>'件',
                'short_name'=>'件',
            ],
            [
                'name'=>'包',
                'short_name'=>'包',
            ],
            [
                'name'=>'個',
                'short_name'=>'個',
            ],
        ];


        foreach ($units as $unit) {
            \App\Models\Unit::create($unit);
        }
    }
}
