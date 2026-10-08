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
            | otomatis ubah order pending → confirmed & award points
            |--------------------------------------------------------------------------
            */

            $wasJustPaid = ($data['status'] === 'paid');

            if (
                $wasJustPaid &&
                $order->status === 'pending'
            ) {
                $order->update([
                    'status' => 'confirmed',
                ]);
            }

            // Award points if just paid and not awarded before
            if ($wasJustPaid && $payment) {
                $alreadyAwarded = PointTransaction::where('order_id', $order->id)
                    ->where('payment_id', $payment->id)
                    ->where('type', 'earn')
                    ->exists();

                if (! $alreadyAwarded) {
                    $customer = $order->customer;
                    $user = null;

                    if ($customer) {
                        $user = User::where('email', strtolower(trim($customer->email)))->first();
                    }

                    if ($user) {
                        $totalAmount = (float) ($order->total_amount ?? $payment->amount ?? 0);
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

                        if ($earnedPoints !== 0) {
                            DB::transaction(function () use ($user, $order, $payment, $earnedPoints, $totalAmount) {
                                PointTransaction::create([
                                    'user_id' => $user->id,
                                    'order_id' => $order->id,
                                    'payment_id' => $payment->id,
                                    'points' => $earnedPoints,
                                    'type' => 'earn',
                                    'description' => sprintf('Earned points for order %s (Rp %s)', $order->order_number, number_format($totalAmount, 0, ',', '.')),
                                ]);

                                $newPoints = $user->points + $earnedPoints;
                                $user->points = $newPoints;

                                if (! $user->is_member) {
                                    $user->is_member = true;
                                    if (! $user->member_joined_at) {
                                        $user->member_joined_at = now();
                                    }
                                }

                                if ($newPoints >= 10000) {
                                    $user->member_tier = 'platinum';
                                } elseif ($newPoints >= 5000) {
                                    $user->member_tier = 'gold';
                                } elseif ($newPoints >= 1000) {
                                    $user->member_tier = 'silver';
                                } else {
                                    $user->member_tier = 'bronze';
                                }

                                $user->save();
                            });
                        }
                    }
                }
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
