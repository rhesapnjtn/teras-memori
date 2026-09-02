<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
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

    public function show(Order $order): OrderResource
    {
        $order->load([
            'customer',
            'items.service',
            'payment',
        ]);

        return new OrderResource($order);
    }

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
        ]);

        return new OrderResource($order);
    }

    public function updatePayment(
        UpdatePaymentStatusRequest $request,
        Order $order
    ): OrderResource {
        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {

            $payment = $order->payment;

            if (!$payment) {
                abort(404, 'Payment untuk order ini tidak ditemukan.');
            }

            $payment->update([
                'status' => $data['status'],
                'method' => $data['method'] ?? $payment->method,
                'transaction_id' => $data['transaction_id']
                    ?? $payment->transaction_id,
                'paid_at' => $data['status'] === 'paid'
                    ? now()
                    : null,
            ]);

            if ($data['status'] === 'paid' && $order->status === 'pending') {
                $order->update([
                    'status' => 'confirmed',
                ]);
            }
        });

        $order->load([
            'customer',
            'items.service',
            'payment',
        ]);

        return new OrderResource($order);
    }
}