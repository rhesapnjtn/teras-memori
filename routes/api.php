<?php

use App\Http\Controllers\Api\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

Route::get(
    '/services',
    [ServiceController::class, 'index']
);

Route::get(
    '/services/{service}',
    [ServiceController::class, 'show']
);

/*
|--------------------------------------------------------------------------
| Portfolios
|--------------------------------------------------------------------------
*/

Route::get(
    '/portfolios',
    [PortfolioController::class, 'index']
);

Route::get(
    '/portfolios/{portfolio}',
    [PortfolioController::class, 'show']
);

/*
|--------------------------------------------------------------------------
| Orders
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Create Order
|--------------------------------------------------------------------------
*/

Route::post(
    '/orders',
    [OrderController::class, 'store']
)->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Track Order
|--------------------------------------------------------------------------
|
| Customer dapat mengecek order menggunakan
| order number + email.
|
*/

Route::post(
    '/orders/track',
    [OrderController::class, 'track']
);

/*
|--------------------------------------------------------------------------
| Order List
|--------------------------------------------------------------------------
*/

Route::get(
    '/orders',
    [OrderController::class, 'index']
)->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Order Detail
|--------------------------------------------------------------------------
*/

Route::get(
    '/orders/{order}',
    [OrderController::class, 'show']
)->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Cancel Order
|--------------------------------------------------------------------------
|
| Customer dapat membatalkan order miliknya sendiri
| selama masih berstatus pending.
|
*/

Route::patch(
    '/orders/{order}/cancel',
    [OrderController::class, 'cancel']
)->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Public Reviews
|--------------------------------------------------------------------------
|
| Customer dapat mengirim review untuk order
| yang sudah selesai.
|
*/

Route::get(
    '/reviews/published',
    [ReviewController::class, 'published']
);

Route::post(
    '/reviews',
    [ReviewController::class, 'store']
);

Route::get(
    '/orders/{order}/review',
    [ReviewController::class, 'showByOrder']
);

/*
|--------------------------------------------------------------------------
| Public Chat
|--------------------------------------------------------------------------
|
| Customer dapat melakukan chat tanpa login.
|
| Security:
|
| - Customer harus memberikan email
| - Customer harus memberikan order number
| - Order harus benar-benar milik customer
| - Setelah berhasil, customer mendapatkan public chat token
| - Token digunakan untuk mengakses chat dan mengirim pesan
|
*/

/*
|--------------------------------------------------------------------------
| Create Chat
|--------------------------------------------------------------------------
|
| Customer wajib mengirim:
|
| - email
| - order_number
|
| Rate limit:
| 10 request per menit berdasarkan IP + email.
|
*/

Route::post(
    '/chats',
    [ChatController::class, 'store']
)->middleware(
    'throttle:public-chat-create'
);

/*
|--------------------------------------------------------------------------
| Find Customer Chat
|--------------------------------------------------------------------------
|
| Customer dapat mencari chat sebelumnya menggunakan:
|
| - email
| - order_number
|
| Keduanya harus cocok dengan order milik customer.
|
| Rate limit:
| 20 request per menit berdasarkan IP + email.
|
*/

Route::get(
    '/chats/customer',
    [ChatController::class, 'customerChat']
)->middleware(
    'throttle:public-chat-customer'
);

/*
|--------------------------------------------------------------------------
| Chat Detail
|--------------------------------------------------------------------------
|
| Setelah mendapatkan public token,
| token dikirim melalui header:
|
| X-Chat-Token
|
| Rate limit:
| 30 request per menit berdasarkan IP + chat token.
|
*/

Route::get(
    '/chats/{chat}',
    [ChatController::class, 'show']
)->middleware(
    'throttle:public-chat-show'
);

/*
|--------------------------------------------------------------------------
| Send Customer Message
|--------------------------------------------------------------------------
|
| Customer menggunakan X-Chat-Token
| untuk mengirim pesan.
|
| Rate limit:
| 20 request per menit berdasarkan IP + chat token.
|
*/

Route::post(
    '/chats/{chat}/messages',
    [ChatController::class, 'storeMessage']
)->middleware(
    'throttle:public-chat-message'
);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::post(
    '/login',
    [AuthController::class, 'login']
);

Route::post(
    '/register',
    [AuthController::class, 'register']
);

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
|
| Semua route di bawah ini membutuhkan:
| auth:sanctum
|
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::get(
        '/user',
        [AuthController::class, 'user']
    );

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [AuthController::class, 'profile']
    );

    Route::put(
        '/profile',
        [AuthController::class, 'updateProfile']
    );

    Route::post(
        '/profile/avatar',
        [AuthController::class, 'updateAvatar']
    );

    /*
    |--------------------------------------------------------------------------
    | Member
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/member',
        [MemberController::class, 'show']
    );

    Route::get(
        '/member/transactions',
        [MemberController::class, 'transactions']
    );
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
| Semua route admin membutuhkan:
|
| - auth:sanctum
| - role admin / superadmin
|
*/

Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin - Services
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/services',
        [ServiceController::class, 'adminIndex']
    );

    Route::post(
        '/admin/services',
        [ServiceController::class, 'store']
    );

    Route::get(
        '/admin/services/{service}',
        [ServiceController::class, 'adminShow']
    );

    Route::put(
        '/admin/services/{service}',
        [ServiceController::class, 'update']
    );

    Route::delete(
        '/admin/services/{service}',
        [ServiceController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Portfolios
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/portfolios',
        [PortfolioController::class, 'adminIndex']
    );

    Route::post(
        '/admin/portfolios',
        [PortfolioController::class, 'store']
    );

    Route::get(
        '/admin/portfolios/{portfolio}',
        [PortfolioController::class, 'adminShow']
    );

    Route::put(
        '/admin/portfolios/{portfolio}',
        [PortfolioController::class, 'update']
    );

    Route::delete(
        '/admin/portfolios/{portfolio}',
        [PortfolioController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Orders
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/orders',
        [AdminOrderController::class, 'index']
    );

    Route::get(
        '/admin/orders/{order}',
        [AdminOrderController::class, 'show']
    );

    Route::patch(
        '/admin/orders/{order}/status',
        [AdminOrderController::class, 'updateStatus']
    );

    Route::patch(
        '/admin/orders/{order}/payment',
        [AdminOrderController::class, 'updatePayment']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Customers
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/customers',
        [CustomerController::class, 'index']
    );

    Route::get(
        '/admin/customers/{customer}',
        [CustomerController::class, 'show']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Chats
    |--------------------------------------------------------------------------
    |
    | Admin chat tetap terpisah dari public chat.
    | Route ini membutuhkan auth:sanctum.
    |
    */

    Route::get(
        '/admin/chats',
        [AdminChatController::class, 'index']
    );

    Route::get(
        '/admin/chats/{chat}',
        [AdminChatController::class, 'show']
    );

    Route::post(
        '/admin/chats/{chat}/messages',
        [AdminChatController::class, 'storeMessage']
    );

    Route::patch(
        '/admin/chats/{chat}/close',
        [AdminChatController::class, 'close']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Roles
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/roles',
        [RoleController::class, 'index']
    );

    Route::post(
        '/admin/roles',
        [RoleController::class, 'store']
    );

    Route::get(
        '/admin/roles/{role}',
        [RoleController::class, 'show']
    );

    Route::put(
        '/admin/roles/{role}',
        [RoleController::class, 'update']
    );

    Route::delete(
        '/admin/roles/{role}',
        [RoleController::class, 'destroy']
    );

    /*
    |--------------------------------------------------------------------------
    | Admin - Reviews
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/reviews',
        [AdminReviewController::class, 'index']
    );

    Route::get(
        '/admin/reviews/{review}',
        [AdminReviewController::class, 'show']
    );

    Route::patch(
        '/admin/reviews/{review}/visibility',
        [AdminReviewController::class, 'updateVisibility']
    );

    Route::delete(
        '/admin/reviews/{review}',
        [AdminReviewController::class, 'destroy']
    );
});
