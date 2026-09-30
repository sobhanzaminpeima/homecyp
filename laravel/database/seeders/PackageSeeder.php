<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $features = ['Listed in the app', 'Contact & location', 'Appears in search'];

        $plans = [
            ['name' => 'Small Business Plan', 'monthly' => 50, 'yearly' => 250],
            ['name' => 'Real Estate Plan', 'monthly' => 80, 'yearly' => 400],
            ['name' => 'Construction Plan', 'monthly' => 80, 'yearly' => 400],
            ['name' => 'Hotel & Casino Plan', 'monthly' => 150, 'yearly' => 700],
        ];

        $sortOrder = 0;

        foreach ($plans as $plan) {
            Package::query()->updateOrCreate(
                ['name' => $plan['name'] . ' (Monthly)'],
                [
                    'description' => $plan['name'],
                    'price' => $plan['monthly'],
                    'duration_days' => 30,
                    'is_featured' => false,
                    'is_premium' => false,
                    'features' => $features,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );

            Package::query()->updateOrCreate(
                ['name' => $plan['name'] . ' (Yearly)'],
                [
                    'description' => $plan['name'],
                    'price' => $plan['yearly'],
                    'duration_days' => 365,
                    'is_featured' => false,
                    'is_premium' => false,
                    'features' => $features,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }
    }
}
