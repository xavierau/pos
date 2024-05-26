<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    function createUser(array $data = []): User
    {
        return User::factory()->create($data);
    }

    function assignPermissionToUser(User $user, array|string $permissions): Role
    {

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $role = Role::factory()->create();

        collect($permissions)
            ->map(fn($permission) => Permission::factory()->create(['name' => $permission]))
            ->each(fn(Permission $permission) => $role->givePermissionTo($permission));

        $user->assignRole($role);

        return $role;

    }

}
