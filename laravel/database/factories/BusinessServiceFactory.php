<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessService>
 */
class BusinessServiceFactory extends Factory
{
    protected $model = BusinessService::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5, 200),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
            'is_available' => true,
        ];
    }
}
