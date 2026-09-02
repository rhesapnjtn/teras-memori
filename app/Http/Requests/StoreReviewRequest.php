<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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

            $order = Order::find($orderId);

            if ($order && (int) $order->customer_id !== (int) $customerId) {
                $validator->errors()->add(
                    'order_id',
                    'Order bukan milik customer tersebut.'
                );
            }

            if ($order && $order->status !== 'completed') {
                $validator->errors()->add(
                    'order_id',
                    'Review hanya dapat diberikan untuk order yang sudah selesai.'
                );
            }
        });
    }
}