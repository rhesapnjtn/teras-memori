<?php

namespace Tests\Feature\Integration;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createBuyer(string $email): User
    {
        $buyer = User::factory()->create(['email' => $email]);
        $buyer->assignRole('customer');

        return $buyer;
    }

    private function createOrderFor(User $buyer, Service $service): Order
    {
        $response = $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/orders', [
                'customer' => [
                    'name' => $buyer->name,
                    'email' => $buyer->email,
                    'phone' => '081234567890',
                ],
                'items' => [
                    [
                        'service_id' => $service->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201);

        return Order::findOrFail($response->json('data.id'));
    }

    public function test_guest_cannot_list_orders(): void
    {
        $this->getJson('/api/orders')->assertStatus(401);
    }

    public function test_guest_cannot_view_order_detail(): void
    {
        $customer = Customer::create([
            'name' => 'Guest Buyer',
            'email' => 'guest.view@test.com',
            'phone' => '081234567890',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'TM-GUEST-VIEW-0001',
            'status' => 'pending',
            'total_amount' => 100000,
        ]);

        $this->getJson("/api/orders/{$order->id}")->assertStatus(401);
    }

    public function test_buyer_only_sees_their_own_orders(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        $buyerA = $this->createBuyer('buyer.a@test.com');
        $buyerB = $this->createBuyer('buyer.b@test.com');

        $orderA = $this->createOrderFor($buyerA, $service);
        $orderB = $this->createOrderFor($buyerB, $service);

        $response = $this->actingAs($buyerA, 'sanctum')
            ->getJson('/api/orders')
            ->assertStatus(200);

        $orderNumbers = collect($response->json('data'))
            ->pluck('order_number')
            ->all();

        $this->assertContains($orderA->order_number, $orderNumbers);
        $this->assertNotContains($orderB->order_number, $orderNumbers);
    }

    public function test_buyer_can_view_own_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('buyer.own@test.com');
        $order = $this->createOrderFor($buyer, $service);

        $this->actingAs($buyer, 'sanctum')
            ->getJson("/api/orders/{$order->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        $buyerA = $this->createBuyer('buyer.view.a@test.com');
        $buyerB = $this->createBuyer('buyer.view.b@test.com');

        $orderA = $this->createOrderFor($buyerA, $service);

        $this->actingAs($buyerB, 'sanctum')
            ->getJson("/api/orders/{$orderA->id}")
            ->assertStatus(403);
    }

    public function test_buyer_can_cancel_own_pending_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('buyer.cancel@test.com');
        $order = $this->createOrderFor($buyer, $service);

        $this->actingAs($buyer, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_buyer_cannot_cancel_another_buyers_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);

        $buyerA = $this->createBuyer('buyer.cancel.a@test.com');
        $buyerB = $this->createBuyer('buyer.cancel.b@test.com');

        $orderA = $this->createOrderFor($buyerA, $service);

        $this->actingAs($buyerB, 'sanctum')
            ->patchJson("/api/orders/{$orderA->id}/cancel")
            ->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $orderA->id,
            'status' => 'pending',
        ]);
    }

    public function test_buyer_cannot_cancel_non_pending_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('buyer.cancel.processing@test.com');
        $admin = User::factory()->create(['email' => 'admin.cancel@test.com']);
        $admin->assignRole('admin');

        $order = $this->createOrderFor($buyer, $service);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/status", [
                'status' => 'processing',
            ])
            ->assertStatus(200);

        $this->actingAs($buyer, 'sanctum')
            ->patchJson("/api/orders/{$order->id}/cancel")
            ->assertStatus(403);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_admin_can_view_any_order(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = $this->createBuyer('buyer.adminview@test.com');
        $admin = User::factory()->create(['email' => 'admin.view@test.com']);
        $admin->assignRole('admin');

        $order = $this->createOrderFor($buyer, $service);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/orders/{$order->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.order_number', $order->order_number);
    }
}
