<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'order_id' => [
                'required',
                'integer',
                'exists:orders,id',
            ],

            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customerId = $this->input('customer_id');
            $orderId = $this->input('order_id');

            if (!$customerId || !$orderId) {
                return;
            }

            $order = Order::with('review')->find($orderId);

            if (!$order) {
                return;
            }

            /*
             * Pastikan order milik customer.
             */
            if ((int) $order->customer_id !== (int) $customerId) {
                $validator->errors()->add(
                    'order_id',
                    'Order bukan milik customer tersebut.'
                );
            }

            /*
             * Review hanya dapat diberikan
             * setelah order selesai.
             */
            if ($order->status !== 'completed') {
                $validator->errors()->add(
                    'order_id',
                    'Review hanya dapat diberikan untuk order yang sudah selesai.'
                );
            }

            /*
             * Satu order hanya boleh memiliki
             * satu review.
             */
            if ($order->review) {
                $validator->errors()->add(
                    'order_id',
                    'Order ini sudah memiliki review.'
                );
            }
        });
    }
}
