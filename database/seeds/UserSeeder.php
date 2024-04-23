<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
        DB::table('users')->insert(
            array(
                'id' => 1,
                'firstname' => 'William',
                'lastname' => 'Castillo',
                'username' => 'William Castillo',
                'email' => 'admin@example.com',
                'password' => Hash::make("password"),
                'avatar' => 'no_avatar.png',
                'phone' => '0123456789',
                'role_id' => 1,
                'status' => 1,
                'is_all_warehouses' => 1,
            )
        );
    }
}
