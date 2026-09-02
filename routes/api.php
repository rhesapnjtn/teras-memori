<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PortfolioController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ChatController;

use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\CustomerController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ChatController as AdminChatController;

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
);


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
);


/*
|--------------------------------------------------------------------------
| Order Detail
|--------------------------------------------------------------------------
*/

Route::get(
    '/orders/{order}',
    [OrderController::class, 'show']
);


/*
|--------------------------------------------------------------------------
| Public Reviews
|--------------------------------------------------------------------------
|
| Customer dapat mengirim review untuk order
| yang sudah selesai.
|
*/

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
*/

Route::post(
    '/chats',
    [ChatController::class, 'store']
);

Route::get(
    '/chats/{chat}',
    [ChatController::class, 'show']
);

Route::post(
    '/chats/{chat}/messages',
    [ChatController::class, 'storeMessage']
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


/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
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
