<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();

        $photoEditing = Service::where(
            'slug',
            'photo-editing'
        )->first();

        $retouching = Service::where(
            'slug',
            'photo-retouching'
        )->first();

        DB::transaction(function () use (
            $customer,
            $photoEditing,
            $retouching
        ) {
            $items = [
                [
                    'service' => $photoEditing,
                    'quantity' => 2,
                ],
                [
                    'service' => $retouching,
                    'quantity' => 1,
                ],
            ];

            $total = 0;

            foreach ($items as $item) {
                $total += $item['service']->price * $item['quantity'];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => 'TM-'.now()->format('YmdHis'),
                'status' => 'processing',
                'total_amount' => $total,
                'notes' => 'Order contoh untuk testing.',
            ]);

            foreach ($items as $item) {
                $price = $item['service']->price;
                $quantity = $item['quantity'];

                $order->items()->create([
                    'service_id' => $item['service']->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $price * $quantity,
                ]);
            }

            $order->payment()->create([
                'amount' => $total,
                'method' => 'qris',
                'status' => 'paid',
                'transaction_id' => 'TEST-'.uniqid(),
                'paid_at' => now(),
            ]);
        });
    }
}
