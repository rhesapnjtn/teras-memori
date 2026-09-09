<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicChatResource extends JsonResource
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

            /*
            |--------------------------------------------------------------------------
            | Token hanya diberikan kepada public chat
            |--------------------------------------------------------------------------
            */

            'public_token' => $this->public_token,

            'messages' => ChatMessageResource::collection(
                $this->whenLoaded('messages')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
