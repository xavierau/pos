<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'account_num' => fake()->unique()->randomNumber(8),
                'account_name' => 'Cash',
                'initial_balance' => 0,
                'balance' => 0,
                'note' => null,
            ],
            [
                'account_num' => fake()->unique()->randomNumber(8),
                'account_name' => 'Saving',
                'initial_balance' => 1000000,
                'balance' => 1000000,
                'note' => null,
            ],
            [
                'account_num' => fake()->unique()->randomNumber(8),
                'account_name' => 'Current',
                'initial_balance' => 0,
                'balance' => 0,
                'note' => null,
            ],
        ];

        Account::insert($accounts);
    }
}
