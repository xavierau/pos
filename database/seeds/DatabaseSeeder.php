<?php

use Database\Seeders\AccountsTableSeeder;
use Database\Seeders\BrandsTableSeeder;
use Database\Seeders\CategoriesTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Database\Seeders\ProvidersTableSeeder;
use Database\Seeders\UnitsTableSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call([
            ClientSeeder::class,
            CurrencySeeder::class,
            SettingSeeder::class,
            ServerSeeder::class,
            PermissionsSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            UserRoleSeeder::class,
            PermissionRoleSeeder::class,
            Warehouse::class,
            UnitsTableSeeder::class,
            CategoriesTableSeeder::class,
            BrandsTableSeeder::class,
            ProductsTableSeeder::class,
            ProvidersTableSeeder::class,
            AccountsTableSeeder::class,
        ]);

    }
}
