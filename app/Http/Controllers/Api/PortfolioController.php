<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $portfolios = Portfolio::query()
            ->where('is_published', true)
            ->latest()
            ->get();

        return PortfolioResource::collection($portfolios);
    }

    public function show(Portfolio $portfolio): PortfolioResource
    {
        abort_if(
            !$portfolio->is_published,
            404,
            'Portfolio not found.'
        );

        return new PortfolioResource($portfolio);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    public function adminIndex()
    {
        $portfolios = Portfolio::query()
            ->latest()
            ->get();

        return PortfolioResource::collection($portfolios);
    }

    public function store(StorePortfolioRequest $request): PortfolioResource
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['slug']);

        $portfolio = Portfolio::create($data);

        return new PortfolioResource($portfolio);
    }

    public function adminShow(Portfolio $portfolio): PortfolioResource
    {
        return new PortfolioResource($portfolio);
    }

    public function update(
        UpdatePortfolioRequest $request,
        Portfolio $portfolio
    ): PortfolioResource {
        $data = $request->validated();

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        $portfolio->update($data);

        return new PortfolioResource($portfolio->fresh());
    }

    public function destroy(Portfolio $portfolio): JsonResponse
    {
        $portfolio->delete();

        return response()->json([
            'message' => 'Portfolio berhasil dihapus.',
        ]);
    }
}