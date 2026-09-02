<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $services = Service::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(Service $service): ServiceResource
    {
        abort_if(
            !$service->is_active,
            404,
            'Service not found.'
        );

        return new ServiceResource($service);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    public function adminIndex()
    {
        $services = Service::query()
            ->latest()
            ->get();

        return ServiceResource::collection($services);
    }

    public function store(StoreServiceRequest $request): ServiceResource
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['slug']);

        $service = Service::create($data);

        return new ServiceResource($service);
    }

    public function adminShow(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function update(
        UpdateServiceRequest $request,
        Service $service
    ): ServiceResource {
        $data = $request->validated();

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        $service->update($data);

        return new ServiceResource($service->fresh());
    }

    public function destroy(Service $service): JsonResponse
    {
        if ($service->orderItems()->exists()) {
            return response()->json([
                'message' => 'Service tidak dapat dihapus karena sudah digunakan dalam order.',
            ], 422);
        }

        $service->delete();

        return response()->json([
            'message' => 'Service berhasil dihapus.',
        ]);
    }
}