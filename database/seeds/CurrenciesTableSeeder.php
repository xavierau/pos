<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = [
            [
                'code' => 'USD',
                'name' => 'United States Dollar',
                'symbol' => '$'
            ],
            [
                'code' => 'HKD',
                'name' => 'Hong Kong Dollar',
                'symbol' => '$'
            ],
            [
                'code' => 'RMB',
                'name' => 'Chinese Yuan',
                'symbol' => '¥'
            ]
        ];

        foreach ($currencies as $currency) {
            \App\Models\Currency::create($currency);
        }

    }
}
