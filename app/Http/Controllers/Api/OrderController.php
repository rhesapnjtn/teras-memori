<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with([
                'customer',
                'items.service',
                'payment',
            ])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): OrderResource
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated) {

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customerData = $validated['customer'];

            if (!empty($customerData['email'])) {
                $customer = Customer::updateOrCreate(
                    [
                        'email' => $customerData['email'],
                    ],
                    $customerData
                );
            } else {
                $customer = Customer::create($customerData);
            }

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            $serviceIds = collect($validated['items'])
                ->pluck('service_id')
                ->unique();

            $services = Service::query()
                ->whereIn('id', $serviceIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            /*
            |--------------------------------------------------------------------------
            | Pastikan semua service aktif
            |--------------------------------------------------------------------------
            */

            if ($services->count() !== $serviceIds->count()) {
                abort(
                    422,
                    'One or more selected services are not available.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Order Number
            |--------------------------------------------------------------------------
            */

            do {
                $orderNumber = 'TM-'
                    . now()->format('YmdHis')
                    . '-'
                    . Str::upper(Str::random(4));
            } while (
                Order::where('order_number', $orderNumber)->exists()
            );

            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'total_amount' => 0,
                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Order Items
            |--------------------------------------------------------------------------
            */

            $total = 0;

            foreach ($validated['items'] as $item) {

                $service = $services->get($item['service_id']);

                $price = (float) $service->price;
                $quantity = (int) $item['quantity'];
                $subtotal = $price * $quantity;

                $order->items()->create([
                    'service_id' => $service->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            /*
            |--------------------------------------------------------------------------
            | Update Total
            |--------------------------------------------------------------------------
            */

            $order->update([
                'total_amount' => $total,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Pending Payment
            |--------------------------------------------------------------------------
            */

            $order->payment()->create([
                'amount' => $total,
                'method' => null,
                'status' => 'pending',
            ]);

            return $order;
        });

        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $order->load([
            'customer',
            'items.service',
            'payment',
        ]);

        return new OrderResource($order);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): OrderResource
    {
        $order->load([
            'customer',
            'items.service',
            'payment',
        ]);

        return new OrderResource($order);
    }
}

