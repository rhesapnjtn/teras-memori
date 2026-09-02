<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ],

            'status' => $this->status,

            'messages' => ChatMessageResource::collection(
                $this->whenLoaded('messages')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}