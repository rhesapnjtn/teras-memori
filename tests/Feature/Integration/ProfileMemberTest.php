<?php

namespace Tests\Feature\Integration;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_user_can_view_and_update_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'profile@test.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('customer');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile')
            ->assertStatus(200);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'name' => 'Updated Name',
                'email' => $user->email,
                'phone' => '081234567890',
                'address' => 'Jl. Test',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '081234567890',
        ]);
    }

    public function test_member_endpoint_returns_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('customer');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/member')
            ->assertStatus(200)
            ->assertJsonStructure([
                'member',
                'transactions',
            ]);
    }

    public function test_points_awarded_on_payment_paid(): void
    {
        $service = Service::factory()->create(['is_active' => true, 'price' => 100000]);
        $buyer = User::factory()->create(['email' => 'buyerpoints@test.com']);
        $buyer->assignRole('customer');
        $admin = User::factory()->create(['email' => 'adminpoints@test.com']);
        $admin->assignRole('admin');

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

        // Mark payment as paid
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/payment", [
                'status' => 'paid',
                'method' => 'bank_transfer',
            ])
            ->assertStatus(200);

        $buyer->refresh();
        $this->assertGreaterThan(0, $buyer->points);
        $this->assertTrue($buyer->is_member);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $buyer->id,
            'order_id' => $orderId,
            'type' => 'earn',
        ]);
    }

    public function test_points_refunded_when_payment_refunded(): void
    {
        $service = Service::factory()->create(['is_active' => true, 'price' => 100000]);
        $buyer = User::factory()->create(['email' => 'buyerrefund@test.com']);
        $buyer->assignRole('customer');
        $admin = User::factory()->create(['email' => 'adminrefund@test.com']);
        $admin->assignRole('admin');

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

        // Payment becomes paid -> points awarded
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/payment", [
                'status' => 'paid',
                'method' => 'bank_transfer',
            ])
            ->assertStatus(200);

        $buyer->refresh();
        $earnedPoints = $buyer->points;
        $this->assertGreaterThan(0, $earnedPoints);

        // Payment refunded -> points pulled back
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$orderId}/payment", [
                'status' => 'refunded',
            ])
            ->assertStatus(200);

        $buyer->refresh();
        $this->assertEquals(0, $buyer->points);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $buyer->id,
            'order_id' => $orderId,
            'type' => 'refund',
            'points' => -$earnedPoints,
        ]);
    }
}
