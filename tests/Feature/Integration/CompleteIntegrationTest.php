<?php

namespace Tests\Feature\Integration;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompleteIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_complete_buyer_journey_with_order_creation(): void
    {
        // Create a service first
        $service = Service::factory()->create([
            'is_active' => true,
            'price' => 150000,
        ]);

        // Register buyer
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('token');
        $user = User::where('email', 'john@example.com')->first();

        // Browse products/services (public)
        $this->getJson('/api/services')->assertStatus(200);
        $this->getJson("/api/services/{$service->id}")->assertStatus(200);

        // Create order as authenticated buyer
        $orderResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '081234567890',
                ],
                'items' => [
                    [
                        'service_id' => $service->id,
                        'quantity' => 2,
                    ],
                ],
                'notes' => 'Test order notes',
            ]);

        $orderResponse->assertStatus(201);
        $orderData = $orderResponse->json('data');

        $this->assertNotEmpty($orderData['order_number']);
        $this->assertEquals('pending', $orderData['status']);
        $this->assertEquals(300000, $orderData['total_amount']); // 150000 * 2

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'order_number' => $orderData['order_number'],
            'status' => 'pending',
        ]);

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'order_id' => $orderData['id'],
            'amount' => 300000,
            'status' => 'pending',
        ]);

        // Buyer can view their orders
        $ordersResponse = $this->actingAs($user, 'sanctum')
            ->getJson('/api/orders');
        $ordersResponse->assertStatus(200);
    }

    public function test_admin_can_manage_orders(): void
    {
        $service = Service::factory()->create(['is_active' => true, 'price' => 200000]);

        // Create admin
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create a buyer and order
        $buyer = User::factory()->create();
        $buyer->assignRole('customer');

        $orderResponse = $this->actingAs($buyer, 'sanctum')
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

        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('data.id');
        $orderNumber = $orderResponse->json('data.order_number');

        // Admin can view all orders
        $adminOrdersResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/orders');
        $adminOrdersResponse->assertStatus(200);

        // Admin can view specific order
        $adminOrderDetail = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/admin/orders/{$orderId}");
        $adminOrderDetail->assertStatus(200)
            ->assertJsonPath('data.order_number', $orderNumber);

        // Admin can update order status
        $updateStatusResponse = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/status", [
                'status' => 'processing',
            ]);
        $updateStatusResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'processing');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'processing',
        ]);

        // Admin can update payment status
        $updatePaymentResponse = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/payment", [
                'status' => 'paid',
                'method' => 'bank_transfer',
                'transaction_id' => 'TRX123456',
            ]);
        $updatePaymentResponse->assertStatus(200);

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'status' => 'paid',
            'method' => 'bank_transfer',
            'transaction_id' => 'TRX123456',
        ]);
    }

    public function test_buyer_cannot_access_admin_routes(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('customer');

        // Try to access admin dashboard/orders
        $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/admin/orders')
            ->assertStatus(403);

        $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/admin/services')
            ->assertStatus(403);

        $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/admin/roles')
            ->assertStatus(403);
    }

    public function test_admin_can_manage_services(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create service
        $createResponse = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/services', [
                'name' => 'Photography Service',
                'slug' => 'photography-service',
                'description' => 'Professional photography',
                'price' => 500000,
                'duration' => '2',
                'is_active' => true,
            ]);
        $createResponse->assertStatus(201);

        $this->assertDatabaseHas('services', [
            'name' => 'Photography Service',
            'price' => 500000,
        ]);

        $serviceId = $createResponse->json('data.id');

        // Update service
        $updateResponse = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/services/{$serviceId}", [
                'name' => 'Updated Photography',
                'slug' => 'updated-photography',
                'description' => 'Updated description',
                'price' => 600000,
                'duration' => '3',
                'is_active' => true,
            ]);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('services', [
            'id' => $serviceId,
            'name' => 'Updated Photography',
            'price' => 600000,
        ]);
    }

    public function test_order_tracking(): void
    {
        $service = Service::factory()->create(['is_active' => true]);
        $buyer = User::factory()->create();
        $buyer->assignRole('customer');

        $orderResponse = $this->actingAs($buyer, 'sanctum')
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

        $orderResponse->assertStatus(201);
        $orderNumber = $orderResponse->json('data.order_number');
        $email = $buyer->email;

        // Track order
        $trackResponse = $this->postJson('/api/orders/track', [
            'order_number' => $orderNumber,
            'email' => $email,
        ]);
        $trackResponse->assertStatus(200)
            ->assertJsonPath('data.order_number', $orderNumber);
    }

    public function test_cross_role_integration_order_status_sync(): void
    {
        $service = Service::factory()->create(['is_active' => true, 'price' => 100000]);
        $buyer = User::factory()->create(['email' => 'buyer.sync@test.com']);
        $buyer->assignRole('customer');
        $admin = User::factory()->create(['email' => 'admin.sync@test.com']);
        $admin->assignRole('admin');

        // Buyer creates order
        $orderResponse = $this->actingAs($buyer, 'sanctum')
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
        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('data.id');

        // Buyer sees order as pending
        $buyerOrders = $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/orders');
        $buyerOrders->assertStatus(200);

        // Admin sees order
        $adminOrders = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/orders');
        $adminOrders->assertStatus(200);

        // Admin updates status to completed
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/status", ['status' => 'completed'])
            ->assertStatus(200);

        // Buyer sees updated status (if they fetch again)
        $buyerOrdersAfter = $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/orders');
        $buyerOrdersAfter->assertStatus(200);
    }
}
