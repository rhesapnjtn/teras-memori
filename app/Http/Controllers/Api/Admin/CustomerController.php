<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::query()
            ->withCount('orders')
            ->latest()
            ->get();

        return response()->json([
            'data' => $customers,
        ]);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load([
            'orders' => function ($query) {
                $query->latest();
            },
        ]);

        return response()->json([
            'data' => $customer,
        ]);
    }
}