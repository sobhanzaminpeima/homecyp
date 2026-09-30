<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Package;
use App\Models\SubscriptionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubscriptionRequest>
 */
class SubscriptionRequestFactory extends Factory
{
    protected $model = SubscriptionRequest::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'package_id' => Package::factory(),
            'status' => 'pending',
        ];
    }
}
