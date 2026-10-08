<?php

namespace Tests\Feature\Integration;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BuyerAdminIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_buyer_registration_login_flow(): void
    {
        // Registration
        $response = $this->postJson('/api/register', [
            'name' => 'Test Buyer',
            'email' => 'buyer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
            'name' => 'Test Buyer',
        ]);

        // Login with registered user
        $response = $this->postJson('/api/login', [
            'email' => 'buyer@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user',
            ]);
    }

    public function test_login_validation_errors(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_credentials_login(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_public_routes_accessible(): void
    {
        // Services index
        $response = $this->getJson('/api/services');
        $response->assertStatus(200);

        // Portfolios index
        $response = $this->getJson('/api/portfolios');
        $response->assertStatus(200);

        // Reviews published
        $response = $this->getJson('/api/reviews/published');
        $response->assertStatus(200);
    }

    public function test_buyer_requires_auth_for_order_creation(): void
    {
        $service = Service::factory()->create([
            'is_active' => true,
            'price' => 100000,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => 'Test',
                'email' => 'test@example.com',
                'phone' => '081234567890',
            ],
            'items' => [
                [
                    'service_id' => $service->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }
}
