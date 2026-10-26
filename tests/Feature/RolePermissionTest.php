<?php

namespace Tests\Feature;

use App\Models\Authorization\Role;
use App\Models\Authorization\User;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    protected function authenticate(): array
    {
        $admin = User::where('email', 'admin@webpro.com')->first();
        $token = $admin->createToken('admin_test_token')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_can_list_roles(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->getJson('/api/roles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'name', 'guard_name', 'users_count', 'permissions'],
                ],
            ]);
    }

    public function test_can_list_permissions(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->getJson('/api/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'all' => [
                        '*' => ['id', 'name', 'module', 'action', 'guard_name'],
                    ],
                    'grouped',
                ],
            ]);
    }

    public function test_can_update_role(): void
    {
        $headers = $this->authenticate();
        $role = Role::first();

        $response = $this->withHeaders($headers)->putJson('/api/roles/' . $role->id, [
            'name' => $role->name,
            'permissions' => ['users.view', 'users.create'],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'role',
                ],
            ]);
    }
}
