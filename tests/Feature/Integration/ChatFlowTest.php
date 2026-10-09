<?php

namespace Tests\Feature\Integration;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createOrder(User $buyer, Service $service): string
    {
        $response = $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/orders', [
                'customer' => [
                    'name' => $buyer->name,
                    'email' => $buyer->email,
                    'phone' => '081234567890',
                ],
                'items' => [
                    ['service_id' => $service->id, 'quantity' => 1],
                ],
            ]);

        $response->assertStatus(201);

        return $response->json('data.order_number');
    }

    private function createBuyer(string $email): User
    {
        $buyer = User::factory()->create(['email' => $email]);
        $buyer->assignRole('customer');

        return $buyer;
    }

    public function test_customer_without_chat_returns_empty_data(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('chatflow.empty@test.com');
        $orderNumber = $this->createOrder($buyer, $service);

        $this->getJson(
            '/api/chats/customer?email='.urlencode($buyer->email)
            .'&order_number='.urlencode($orderNumber)
        )
            ->assertStatus(200)
            ->assertJsonPath('data', null);
    }

    public function test_customer_can_create_chat_with_valid_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('chatflow.create@test.com');
        $orderNumber = $this->createOrder($buyer, $service);

        $response = $this->postJson('/api/chats', [
            'email' => $buyer->email,
            'order_number' => $orderNumber,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('chats', [
            'customer_id' => $buyer->id,
            'status' => 'open',
        ]);
    }

    public function test_create_chat_requires_order_number(): void
    {
        $this->postJson('/api/chats', [
            'email' => 'someone@test.com',
        ])->assertStatus(422);
    }

    public function test_create_chat_rejects_order_not_owned_by_customer(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyerA = $this->createBuyer('chatflow.owner@test.com');
        $buyerB = $this->createBuyer('chatflow.other@test.com');

        $orderNumber = $this->createOrder($buyerA, $service);

        $this->postJson('/api/chats', [
            'email' => $buyerB->email,
            'order_number' => $orderNumber,
        ])->assertStatus(404);
    }
}
