<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Photo Editing',
                'description' => 'Professional photo editing untuk menghasilkan foto yang lebih menarik dan berkualitas.',
                'price' => 50000,
                'duration' => '1-2 Days',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Photo Retouching',
                'description' => 'Retouching foto untuk memperbaiki detail dan meningkatkan tampilan foto.',
                'price' => 75000,
                'duration' => '1-2 Days',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Photo Restoration',
                'description' => 'Memulihkan foto lama atau rusak agar terlihat lebih bersih dan jelas.',
                'price' => 100000,
                'duration' => '2-3 Days',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Background Removal',
                'description' => 'Menghapus atau mengganti background foto dengan hasil yang rapi.',
                'price' => 25000,
                'duration' => '1 Day',
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                ...$service,
                'slug' => Str::slug($service['name']),
            ]);
        }
    }
}