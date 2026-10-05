<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    | Menampilkan seluruh order untuk dashboard admin.
    */

    public function index(): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with([
                'customer',
                'items.service',
                'payment',
                'files',
            ])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    | Menampilkan detail satu order beserta foto customer.
    */

    public function show(Order $order): OrderResource
    {
        $order->load([
            'customer',
            'items.service',
            'payment',
            'files',
        ]);

        return new OrderResource($order);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ORDER STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order
    ): OrderResource {
        $order->update([
            'status' => $request->validated('status'),
        ]);

        $order->load([
            'customer',
            'items.service',
            'payment',
            'files',
        ]);

        return new OrderResource($order);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PAYMENT STATUS
    |--------------------------------------------------------------------------
    */

    public function updatePayment(
        UpdatePaymentStatusRequest $request,
        Order $order
    ): OrderResource {
        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {

            $payment = $order->payment;

            if (! $payment) {
                abort(
                    404,
                    'Payment untuk order ini tidak ditemukan.'
                );
            }

            $payment->update([
                'status' => $data['status'],

                'method' => $data['method']
                    ?? $payment->method,

                'transaction_id' => $data['transaction_id']
                    ?? $payment->transaction_id,

                'paid_at' => $data['status'] === 'paid'
                    ? now()
                    : null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Jika payment menjadi PAID
            | otomatis ubah order pending → confirmed
            |--------------------------------------------------------------------------
            */

            if (
                $data['status'] === 'paid' &&
                $order->status === 'pending'
            ) {
                $order->update([
                    'status' => 'confirmed',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Jika payment bukan PAID
            | paid_at dikosongkan oleh logic di atas.
            |--------------------------------------------------------------------------
            */
        });

        $order->load([
            'customer',
            'items.service',
            'payment',
            'files',
        ]);

        return new OrderResource($order);
    }
}
