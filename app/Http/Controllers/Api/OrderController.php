<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with([
                'customer',
                'items.service',
                'payment',
                'files',
                'review',
            ])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(StoreOrderRequest $request): OrderResource
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $request) {

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

            $customerData = $validated['customer'];

            if (! empty($customerData['email'])) {

                $customer = Customer::updateOrCreate(
                    ['email' => $customerData['email']],
                    $customerData
                );

            } else {

                $customer = Customer::create($customerData);

            }

            /*
            |--------------------------------------------------------------------------
            | SERVICES
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

            if ($services->count() !== $serviceIds->count()) {

                abort(
                    422,
                    'One or more selected services are not available.'
                );

            }

            /*
            |--------------------------------------------------------------------------
            | ORDER NUMBER
            |--------------------------------------------------------------------------
            */

            do {

                $orderNumber = 'TM-'
                    .now()->format('YmdHis')
                    .'-'
                    .Str::upper(Str::random(4));

            } while (
                Order::where('order_number', $orderNumber)->exists()
            );

            /*
            |--------------------------------------------------------------------------
            | CREATE ORDER
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
            | ORDER ITEMS
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
            | UPDATE TOTAL
            |--------------------------------------------------------------------------
            */

            $order->update([
                'total_amount' => $total,
            ]);

            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            $order->payment()->create([
                'amount' => $total,
                'method' => null,
                'status' => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | UPLOAD FILES
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('files')) {

                foreach ($request->file('files') as $file) {

                    $path = $file->store(
                        'orders',
                        'public'
                    );

                    $order->files()->create([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            return $order;
        });

        /*
        |--------------------------------------------------------------------------
        | LOAD RELATIONSHIPS
        |--------------------------------------------------------------------------
        */

        $order->load([
            'customer',
            'items.service',
            'payment',
            'files',
            'review',
        ]);

        return new OrderResource($order);
    }

    /*
    |--------------------------------------------------------------------------
    | TRACK ORDER
    |--------------------------------------------------------------------------
    |
    | Customer mengecek order menggunakan:
    |
    | - Order Number
    | - Email
    |
    | Kedua data harus cocok.
    |
    */

    public function track(Request $request): OrderResource
    {
        $validated = $request->validate([
            'order_number' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | FIND ORDER
        |--------------------------------------------------------------------------
        */

        $order = Order::query()
            ->with([
                'customer',
                'items.service',
                'payment',
                'files',
                'review',
            ])
            ->where(
                'order_number',
                trim($validated['order_number'])
            )
            ->whereHas('customer', function ($query) use ($validated) {

                $query->where(
                    'email',
                    strtolower(trim($validated['email']))
                );

            })
            ->first();

        /*
        |--------------------------------------------------------------------------
        | ORDER NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (! $order) {

            abort(
                404,
                'We could not find an order with those details.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | RETURN ORDER
        |--------------------------------------------------------------------------
        */

        return new OrderResource($order);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Order $order): OrderResource
    {
        $order->load([
            'customer',
            'items.service',
            'payment',
            'files',
            'review',
        ]);

        return new OrderResource($order);
    }
}
