<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProduct>
 */
class BusinessProductFactory extends Factory
{
    protected $model = BusinessProduct::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1, 100),
            'is_available' => true,
        ];
    }
}
