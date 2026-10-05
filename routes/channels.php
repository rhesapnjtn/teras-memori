<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Customer Public Chat
|--------------------------------------------------------------------------
|
| Customer menggunakan public channel berdasarkan public_token.
|
*/

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::where('id', $chatId)->exists();
});

/*
|--------------------------------------------------------------------------
| Admin Chat
|--------------------------------------------------------------------------
|
| Hanya admin dan superadmin yang boleh subscribe.
|
*/

Broadcast::channel('admin-chat.{chatId}', function ($user, $chatId) {
    if (! $user) {
        return false;
    }

    return $user->hasAnyRole([
        'admin',
        'superadmin',
    ]);
});
