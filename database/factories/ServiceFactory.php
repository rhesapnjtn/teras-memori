<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->randomNumber(3),
            'description' => fake()->paragraph(),
            'price' => fake()->numberBetween(100000, 5000000),
            'duration' => fake()->numberBetween(1, 10),
            'image' => fake()->imageUrl(),
            'is_active' => true,
        ];
    }
}
