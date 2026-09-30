<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            SiteSettingSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            PageSeeder::class,
            BlogMenuSeeder::class,
            CategoryHeaderSeeder::class,
            AreaSeeder::class,
            TimelineStepSeeder::class,
            RecommendationRuleSeeder::class,
        ]);
    }
}
