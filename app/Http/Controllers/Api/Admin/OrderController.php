<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Requests\UpdatePaymentStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\User;
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

            $previousPaymentStatus = $payment->status;

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
            | HANDLE POINT TRANSACTIONS
            |--------------------------------------------------------------------------
            */
            $this->handlePointTransaction($order, $payment, $previousPaymentStatus, $data['status']);
        });

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
    | HANDLE POINT TRANSACTIONS
    |--------------------------------------------------------------------------
    | - Earn points when payment becomes PAID
    | - Refund points when payment becomes FAILED/EXPIRED/REFUNDED (from PAID)
    |--------------------------------------------------------------------------
    */

    private function handlePointTransaction(
        Order $order,
        $payment,
        string $previousStatus,
        string $newStatus
    ): void {
        $user = $order->customer?->user;

        if (! $user) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | PAID dari status non-PAID -> tambah poin
        |--------------------------------------------------------------------------
        */

        if ($newStatus === 'paid' && $previousStatus !== 'paid') {
            $alreadyAwarded = PointTransaction::where('order_id', $order->id)
                ->where('payment_id', $payment->id)
                ->where('type', 'earn')
                ->exists();

            if (! $alreadyAwarded) {
                $earnedPoints = $this->calculateEarnedPoints($order, $user);

                if ($earnedPoints > 0) {
                    $user->increment('points', $earnedPoints);
                    $user->refresh();

                    if (! $user->is_member) {
                        $user->is_member = true;
                        $user->member_joined_at = $user->member_joined_at ?? now();
                    }

                    $user->recalculateMemberTier();
                    $user->save();

                    PointTransaction::create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'points' => $earnedPoints,
                        'type' => 'earn',
                        'description' => "Poin dari pembayaran order {$order->order_number}",
                    ]);
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PAID -> FAILED/EXPIRED/REFUNDED -> tarik kembali poin
        |--------------------------------------------------------------------------
        */

        if (
            $previousStatus === 'paid' &&
            in_array($newStatus, ['failed', 'expired', 'refunded'], true)
        ) {
            $earnTransaction = PointTransaction::where('payment_id', $payment->id)
                ->where('type', 'earn')
                ->first();

            if ($earnTransaction) {
                $user->decrement('points', $earnTransaction->points);
                $user->refresh();

                $user->recalculateMemberTier();
                $user->save();

                PointTransaction::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'points' => -$earnTransaction->points,
                    'type' => 'refund',
                    'description' => "Pembatalan poin dari order {$order->order_number} (payment: {$newStatus})",
                ]);
            }
        }
    }

    /**
     * Hitung poin yang diperoleh dari sebuah order,
     * memperhitungkan tier multiplier user.
     */
    private function calculateEarnedPoints(Order $order, User $user): int
    {
        $totalAmount = (float) ($order->total_amount ?? 0);

        $basePoints = (int) floor($totalAmount / 10000);

        if ($basePoints < 0) {
            $basePoints = 0;
        }

        $multiplier = match ($user->member_tier) {
            'silver' => 1.2,
            'gold' => 1.5,
            'platinum' => 2.0,
            default => 1.0,
        };

        $earnedPoints = (int) floor($basePoints * $multiplier);

        if ($earnedPoints === 0 && $totalAmount > 0) {
            $earnedPoints = 1;
        }

        return $earnedPoints;
    }
}
