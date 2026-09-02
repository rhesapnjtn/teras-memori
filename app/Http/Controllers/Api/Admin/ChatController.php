<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::query()
            ->with('customer')
            ->withCount('messages')
            ->latest()
            ->get();

        return ChatResource::collection($chats);
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
            'user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => $request->validated('message'),
        ]);

        $chat->load([
            'customer',
            'messages',
        ]);

        return new ChatResource($chat);
    }

    public function close(Chat $chat): JsonResponse
    {
        $chat->update([
            'status' => 'closed',
        ]);

        return response()->json([
            'message' => 'Chat berhasil ditutup.',
        ]);
    }
}