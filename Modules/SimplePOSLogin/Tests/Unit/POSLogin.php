<?php

namespace Modules\SimplePOSLogin\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class POSLogin extends TestCase
{
    use DatabaseMigrations;
    use DatabaseTransactions;


    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_invalid_pos_login()
    {
        $response = $this->post(
            '/pos/login',
            ['employee_code' => '124']
        );

        $response->assertStatus(403);
    }

    public function test_invalid_login_not_correct_permission()
    {

        $employee_code = 'A124';

        $user = $this->createUser(['employee_code' => $employee_code]);

        $this->assignPermissionToUser($user, 'something else');

        $response = $this->post(
            '/pos/login',
            ['employee_code' => $employee_code]
        );

        $response->assertStatus(403);
    }
}
