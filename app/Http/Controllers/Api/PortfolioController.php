<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Public
    |--------------------------------------------------------------------------
    */

    /**
     * Get all published portfolios for public pages.
     */
    public function index()
    {
        $portfolios = Portfolio::query()
            ->where('is_published', true)
            ->latest()
            ->get();

        return PortfolioResource::collection($portfolios);
    }

    /**
     * Get a single published portfolio.
     */
    public function show(Portfolio $portfolio): PortfolioResource
    {
        abort_if(
            ! $portfolio->is_published,
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

    /**
     * Get all portfolios for admin dashboard.
     */
    public function adminIndex()
    {
        $portfolios = Portfolio::query()
            ->latest()
            ->get();

        return PortfolioResource::collection($portfolios);
    }

    /**
     * Store a new portfolio.
     */
    public function store(
        StorePortfolioRequest $request
    ): PortfolioResource {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        $data['slug'] = Str::slug($data['slug']);

        /*
        |--------------------------------------------------------------------------
        | Image Upload
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {
            $data['image'] = $request
                ->file('image')
                ->store('portfolios', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Create Portfolio
        |--------------------------------------------------------------------------
        */

        $portfolio = Portfolio::create($data);

        return new PortfolioResource(
            $portfolio->fresh()
        );
    }

    /**
     * Get a single portfolio for admin.
     */
    public function adminShow(
        Portfolio $portfolio
    ): PortfolioResource {
        return new PortfolioResource($portfolio);
    }

    /**
     * Update portfolio.
     */
    public function update(
        UpdatePortfolioRequest $request,
        Portfolio $portfolio
    ): PortfolioResource {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        /*
        |--------------------------------------------------------------------------
        | Image Replacement
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            /*
            | Delete old local image
            */

            if (
                $portfolio->image &&
                ! Str::startsWith(
                    $portfolio->image,
                    [
                        'http://',
                        'https://',
                        '/',
                    ]
                )
            ) {
                Storage::disk('public')
                    ->delete($portfolio->image);
            }

            /*
            | Store new image
            */

            $data['image'] = $request
                ->file('image')
                ->store('portfolios', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | Update Portfolio
        |--------------------------------------------------------------------------
        */

        $portfolio->update($data);

        return new PortfolioResource(
            $portfolio->fresh()
        );
    }

    /**
     * Delete portfolio.
     */
    public function destroy(
        Portfolio $portfolio
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Delete Image
        |--------------------------------------------------------------------------
        */

        if (
            $portfolio->image &&
            ! Str::startsWith(
                $portfolio->image,
                [
                    'http://',
                    'https://',
                    '/',
                ]
            )
        ) {
            Storage::disk('public')
                ->delete($portfolio->image);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Portfolio
        |--------------------------------------------------------------------------
        */

        $portfolio->delete();

        return response()->json([
            'message' => 'Portfolio berhasil dihapus.',
        ]);
    }
}
