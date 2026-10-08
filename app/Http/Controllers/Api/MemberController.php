<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->is_member && $user->points > 0) {
            $user->update(['is_member' => true]);
            $user = $user->fresh();
        }

        $transactions = $user->pointTransactions()
            ->with(['order:id,order_number', 'payment:id'])
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'member' => [
                'is_member' => $user->is_member,
                'points' => $user->points,
                'member_tier' => $user->member_tier,
                'member_joined_at' => $user->member_joined_at,
                'next_tier' => $user->next_tier,
                'next_tier_threshold' => $user->next_tier_threshold,
                'progress_to_next_tier' => $user->progress_to_next_tier,
            ],
            'transactions' => $transactions,
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->pointTransactions()
            ->with(['order:id,order_number', 'payment:id'])
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }
}