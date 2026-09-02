<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'notes' => $this->notes,

            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
                'address' => $this->customer->address,
            ],

            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),

            'payment' => $this->whenLoaded('payment', function () {
                return [
                    'id' => $this->payment->id,
                    'amount' => $this->payment->amount,
                    'method' => $this->payment->method,
                    'status' => $this->payment->status,
                    'transaction_id' => $this->payment->transaction_id,
                    'paid_at' => $this->payment->paid_at,
                ];
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}