<?php

use App\Models\Warehouse as WarehouseModel;
use Illuminate\Database\Seeder;

class Warehouse extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $warehouses = [
            [
                'name' => 'HQ',
                'city' => NULL,
                'mobile' => NULL,
                'zip' => NULL,
                'email' => NULL,
                'country' => NULL,
            ]
        ];

        for ($i = 1; $i <= 3; $i++) {
            $warehouses[] = [
                'name' => 'Shop' . $i,
                'city' => "Hong Kong",
                'country' => "Hong Kong",
                'mobile' => NULL,
                'zip' => NULL,
                'email' => NULL,
            ];
        }

        WarehouseModel::insert($warehouses);


    }
}
