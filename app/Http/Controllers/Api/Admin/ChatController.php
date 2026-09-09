<?php

namespace App\Http\Controllers\Api\Admin;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    /**
     * Menampilkan seluruh percakapan.
     */
    public function index()
    {
        $chats = Chat::query()
            ->with('customer')
            ->withCount('messages')
            ->latest()
            ->get();

        return ChatResource::collection($chats);
    }


    /**
     * Menampilkan detail percakapan.
     */
    public function show(Chat $chat): ChatResource
    {
        $chat->load([
            'customer',
            'messages',
        ]);

        return new ChatResource($chat);
    }


    /**
     * Admin mengirim pesan.
     */
    public function storeMessage(
        StoreChatMessageRequest $request,
        Chat $chat
    ): ChatResource {
        /**
         * Jangan izinkan pesan dikirim
         * ke chat yang sudah ditutup.
         */
        if ($chat->status === 'closed') {
            abort(422, 'Chat sudah ditutup.');
        }

        /**
         * Simpan pesan admin.
         */
        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => $request->validated('message'),
        ]);

        /**
         * Broadcast pesan ke Reverb.
         */
        broadcast(new ChatMessageSent($message));

        /**
         * Load kembali data chat
         * agar response API tetap lengkap.
         */
        $chat->load([
            'customer',
            'messages',
        ]);

        return new ChatResource($chat);
    }


    /**
     * Menutup percakapan.
     */
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
