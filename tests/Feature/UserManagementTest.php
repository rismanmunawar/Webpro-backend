<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    protected function authenticate(): array
    {
        $admin = User::where('email', 'admin@webpro.com')->first();
        $token = $admin->createToken('admin_test_token')->plainTextToken;

        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_can_list_users(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'role', 'department', 'status'],
                    ],
                    'current_page',
                    'total',
                ],
            ]);
    }

    public function test_can_filter_and_search_users(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->getJson('/api/users?search=Emma');

        $response->assertStatus(200);
        $users = $response->json('data.data');
        $this->assertNotEmpty($users);
        $this->assertEquals('Emma Wilson', $users[0]['name']);
    }

    public function test_can_create_and_delete_user(): void
    {
        $headers = $this->authenticate();

        $createResponse = $this->withHeaders($headers)->postJson('/api/users', [
            'name' => 'Temporary Member',
            'email' => 'temp_' . time() . '@webpro.com',
            'role' => 'Editor',
            'department' => 'Product',
            'status' => 'Active',
        ]);

        $createResponse->assertStatus(201);
        $userId = $createResponse->json('data.user.id');

        $deleteResponse = $this->withHeaders($headers)->deleteJson("/api/users/{$userId}");
        $deleteResponse->assertStatus(200);
    }
}
