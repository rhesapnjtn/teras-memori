<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'slug' => $this->slug,

            'description' => $this->description,

            'category' => $this->category,

            'image' => $this->image
                ? (
                    preg_match(
                        '/^https?:\/\//',
                        $this->image
                    )
                        ? $this->image
                        : asset(
                            'storage/' . ltrim(
                                $this->image,
                                '/'
                            )
                        )
                )
                : null,

            'is_published' => (bool) $this->is_published,

            'created_at' => $this->created_at,
        ];
    }
}
