<?php

namespace App\Http\Controllers\Api;

use App\Events\ChatMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatMessageRequest;
use App\Http\Requests\StoreChatRequest;
use App\Http\Resources\PublicChatResource;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Customer Lookup
    |--------------------------------------------------------------------------
    |
    | Endpoint ini sudah tidak diperlukan oleh frontend Chat.
    | Sebaiknya route-nya nanti dihapus agar email customer
    | tidak bisa dienumerasi dari endpoint public.
    |
    */

    public function lookupCustomer(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ]);

        $email = strtolower(
            trim($request->email)
        );

        $customer = Customer::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();

        if (! $customer) {
            return response()->json([
                'message' => 'Data customer atau order tidak valid.',
            ], 404);
        }

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create / Get Chat
    |--------------------------------------------------------------------------
    |
    | Customer wajib memberikan:
    |
    | - Email
    | - Order number
    |
    | Email harus cocok dengan customer.
    | Order number harus benar-benar milik customer tersebut.
    |
    */

    public function store(
        StoreChatRequest $request
    ): PublicChatResource {
        $email = strtolower(
            trim($request->validated('email'))
        );

        $orderNumber = trim(
            $request->validated('order_number')
        );

        /*
        |--------------------------------------------------------------------------
        | Find Customer
        |--------------------------------------------------------------------------
        */

        $customer = Customer::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Do not reveal whether email exists
        |--------------------------------------------------------------------------
        */

        if (! $customer) {
            abort(
                404,
                'Data customer atau order tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Order Ownership
        |--------------------------------------------------------------------------
        */

        $order = Order::where(
            'order_number',
            $orderNumber
        )
            ->where(
                'customer_id',
                $customer->id
            )
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid Customer / Order
        |--------------------------------------------------------------------------
        */

        if (! $order) {
            abort(
                404,
                'Data customer atau order tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Existing Open Chat
        |--------------------------------------------------------------------------
        */

        $chat = Chat::where(
            'customer_id',
            $customer->id
        )
            ->where(
                'status',
                'open'
            )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Create Chat
        |--------------------------------------------------------------------------
        */

        if (! $chat) {
            $chat = Chat::create([
                'customer_id' => $customer->id,
                'public_token' => Str::uuid()->toString(),
                'status' => 'open',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ensure Public Token Exists
        |--------------------------------------------------------------------------
        */

        if (! $chat->public_token) {
            $chat->update([
                'public_token' => Str::uuid()->toString(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Relationships
        |--------------------------------------------------------------------------
        */

        $chat->load([
            'customer',
            'messages',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Return Chat
        |--------------------------------------------------------------------------
        */

        return new PublicChatResource($chat);
    }

    /*
    |--------------------------------------------------------------------------
    | Find Customer Chat
    |--------------------------------------------------------------------------
    |
    | Customer harus memberikan:
    |
    | - Email
    | - Order number
    |
    | Keduanya harus cocok dengan order milik customer.
    |
    */

    public function customerChat(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'order_number' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $email = strtolower(
            trim($request->email)
        );

        $orderNumber = trim(
            $request->order_number
        );

        /*
        |--------------------------------------------------------------------------
        | Find Customer
        |--------------------------------------------------------------------------
        */

        $customer = Customer::whereRaw(
            'LOWER(email) = ?',
            [$email]
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Do not reveal whether email exists
        |--------------------------------------------------------------------------
        */

        if (! $customer) {
            return response()->json([
                'message' => 'Data customer atau order tidak valid.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Order Ownership
        |--------------------------------------------------------------------------
        */

        $order = Order::where(
            'order_number',
            $orderNumber
        )
            ->where(
                'customer_id',
                $customer->id
            )
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'Data customer atau order tidak valid.',
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Latest Chat
        |--------------------------------------------------------------------------
        */

        $chat = Chat::where(
            'customer_id',
            $customer->id
        )
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Customer Has No Chat
        |--------------------------------------------------------------------------
        */

        if (! $chat) {
            return response()->json([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                ],

                'data' => null,

                'message' => 'Customer belum memiliki percakapan.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ensure Public Token Exists
        |--------------------------------------------------------------------------
        */

        if (! $chat->public_token) {
            $chat->update([
                'public_token' => Str::uuid()->toString(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Load Chat
        |--------------------------------------------------------------------------
        */

        $chat->load([
            'customer',
            'messages',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Return Chat
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],

            'data' => new PublicChatResource($chat),

            'public_token' => $chat->public_token,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Show Chat
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Chat $chat
    ): PublicChatResource {
        $this->authorizePublicChat(
            $request,
            $chat
        );

        $chat->load([
            'customer',
            'messages',
        ]);

        return new PublicChatResource($chat);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Customer Message
    |--------------------------------------------------------------------------
    */

    public function storeMessage(
        StoreChatMessageRequest $request,
        Chat $chat
    ): PublicChatResource {
        $this->authorizePublicChat(
            $request,
            $chat
        );

        if ($chat->status === 'closed') {
            abort(
                422,
                'Chat sudah ditutup.'
            );
        }

        $message = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => null,
            'sender_type' => 'customer',
            'message' => $request->validated('message'),
        ]);

        broadcast(
            new ChatMessageSent($message)
        );

        $chat->load([
            'customer',
            'messages',
        ]);

        return new PublicChatResource($chat);
    }

    /*
    |--------------------------------------------------------------------------
    | Authorize Public Chat
    |--------------------------------------------------------------------------
    */

    private function authorizePublicChat(
        Request $request,
        Chat $chat
    ): void {
        $token = $request->header(
            'X-Chat-Token'
        );

        if (! $token) {
            abort(
                403,
                'Chat token tidak ditemukan.'
            );
        }

        if (
            ! $chat->public_token ||
            ! hash_equals(
                (string) $chat->public_token,
                (string) $token
            )
        ) {
            abort(
                403,
                'Akses chat tidak valid.'
            );
        }
    }
}
