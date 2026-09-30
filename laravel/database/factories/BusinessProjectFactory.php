<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProject>
 */
class BusinessProjectFactory extends Factory
{
    protected $model = BusinessProject::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'title' => fake()->streetAddress() . ' Residence',
            'description' => fake()->paragraph(),
            'images' => [],
            'property_type' => 'apartment',
            'listing_type' => 'sale',
            'price' => fake()->numberBetween(50000, 500000),
            'currency' => 'GBP',
            'status' => 'approved',
        ];
    }
}
