<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $portfolios = [
            [
                'title' => 'Wedding Photography',
                'description' => 'Wedding photo editing project.',
                'category' => 'Wedding',
                'image' => null,
                'is_published' => true,
            ],
            [
                'title' => 'Portrait Retouching',
                'description' => 'Professional portrait retouching project.',
                'category' => 'Portrait',
                'image' => null,
                'is_published' => true,
            ],
            [
                'title' => 'Product Photography',
                'description' => 'Product photo editing and enhancement.',
                'category' => 'Product',
                'image' => null,
                'is_published' => true,
            ],
            [
                'title' => 'Old Photo Restoration',
                'description' => 'Restoration of an old damaged photograph.',
                'category' => 'Restoration',
                'image' => null,
                'is_published' => true,
            ],
        ];

        foreach ($portfolios as $portfolio) {
            Portfolio::create([
                ...$portfolio,
                'slug' => Str::slug($portfolio['title']),
            ]);
        }
    }
}
