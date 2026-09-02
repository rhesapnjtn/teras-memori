<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\StoreChatRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function store(StoreChatRequest $request): ChatResource
    {
        $chat = Chat::create([
            'customer_id' => $request->validated('customer_id'),
            'status' => 'open',
        ]);

        $chat->load('customer');

        return new ChatResource($chat);
    }

    public function show(Chat $chat): ChatResource
    {
        $chat->load([
            'customer',
            'messages',
        ]);

        return new ChatResource($chat);
    }

    public function storeMessage(
        StoreChatMessageRequest $request,
        Chat $chat
    ): ChatResource {
        if ($chat->status === 'closed') {
            abort(422, 'Chat sudah ditutup.');
        }

        ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'sender_type' => 'customer',
            'message' => $request->validated('message'),
        ]);

        $chat->load([
            'customer',
            'messages',
        ]);

        return new ChatResource($chat);
    }
}