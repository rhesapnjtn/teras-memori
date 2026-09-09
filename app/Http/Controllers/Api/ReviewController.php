<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * Customer mengirim review.
     */
    public function store(
        StoreReviewRequest $request
    ): ReviewResource {
        $data = $request->validated();

        $review = Review::create([
            'customer_id' => $data['customer_id'],
            'order_id' => $data['order_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_published' => false,
        ]);

        $review->load([
            'customer',
            'order',
        ]);

        return new ReviewResource($review);
    }

    /**
     * Menampilkan review berdasarkan order.
     */
    public function showByOrder(
        Order $order
    ): JsonResponse|ReviewResource {
        $review = $order
            ->load([
                'review.customer',
                'review.order',
            ])
            ->review;

        if (!$review) {
            return response()->json([
                'data' => null,
                'message' => 'Review belum diberikan.',
            ]);
        }

        return new ReviewResource($review);
    }
    public function published(): AnonymousResourceCollection
{
    $reviews = Review::query()
        ->with([
            'customer',
            'order',
        ])
        ->where('is_published', true)
        ->latest()
        ->get();

    return ReviewResource::collection($reviews);
}
}
