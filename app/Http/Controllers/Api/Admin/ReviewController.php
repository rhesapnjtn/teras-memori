<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends Controller
{
    /**
     * Menampilkan semua review untuk admin.
     */
    public function index(): AnonymousResourceCollection
    {
        $reviews = Review::query()
            ->with([
                'customer',
                'order',
            ])
            ->latest()
            ->get();

        return ReviewResource::collection($reviews);
    }

    /**
     * Menampilkan detail satu review.
     */
    public function show(Review $review): ReviewResource
    {
        $review->load([
            'customer',
            'order',
        ]);

        return new ReviewResource($review);
    }

    /**
     * Publish / unpublish review.
     */
    public function updateVisibility(
        Review $review
    ): ReviewResource {
        $review->update([
            'is_published' => ! $review->is_published,
        ]);

        $review->load([
            'customer',
            'order',
        ]);

        return new ReviewResource($review);
    }

    /**
     * Menghapus review.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([
            'message' => 'Review berhasil dihapus.',
        ]);
    }
}
