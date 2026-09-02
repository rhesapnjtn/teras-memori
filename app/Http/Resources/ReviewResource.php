<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
            ],

            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
            ],

            'rating' => $this->rating,
            'comment' => $this->comment,
            'is_published' => $this->is_published,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}