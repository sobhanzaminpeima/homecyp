<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'slug' => 'iskele',
                'name' => 'Iskele',
                'overview' => 'North Cyprus\'s fastest-growing coastal region, known for new-build investment projects, Long Beach, and strong short-term rental demand.',
                'highlights' => [
                    'beach' => 'high', 'university' => 'low', 'school' => 'medium',
                    'hospital' => 'low', 'nightlife' => 'medium', 'quiet' => 'medium',
                    'remote_work_ready' => 'medium', 'roi_growth' => 'high',
                ],
            ],
            [
                'slug' => 'kyrenia',
                'name' => 'Kyrenia',
                'overview' => 'The most established and premium coastal city, with a historic harbour, marina, international schools, and the widest range of restaurants and amenities.',
                'highlights' => [
                    'beach' => 'high', 'university' => 'medium', 'school' => 'high',
                    'hospital' => 'high', 'nightlife' => 'high', 'quiet' => 'low',
                    'remote_work_ready' => 'high', 'roi_growth' => 'medium',
                ],
            ],
            [
                'slug' => 'famagusta',
                'name' => 'Famagusta',
                'overview' => 'Home to Eastern Mediterranean University and a large student population, making it strong for long-term rental yield and affordability.',
                'highlights' => [
                    'beach' => 'medium', 'university' => 'high', 'school' => 'medium',
                    'hospital' => 'medium', 'nightlife' => 'medium', 'quiet' => 'low',
                    'remote_work_ready' => 'medium', 'roi_growth' => 'high',
                ],
            ],
            [
                'slug' => 'nicosia',
                'name' => 'Nicosia',
                'overview' => 'The capital and main business hub, best suited to residents working locally rather than holiday or beach-focused buyers.',
                'highlights' => [
                    'beach' => 'low', 'university' => 'medium', 'school' => 'high',
                    'hospital' => 'high', 'nightlife' => 'medium', 'quiet' => 'medium',
                    'remote_work_ready' => 'high', 'roi_growth' => 'low',
                ],
            ],
        ];

        foreach ($areas as $data) {
            $area = Area::updateOrCreate(['slug' => $data['slug']], ['name' => $data['name'], 'is_active' => true]);

            $area->translations()->updateOrCreate(
                ['locale' => 'en'],
                ['title' => $data['name'], 'overview' => $data['overview'], 'highlights' => $data['highlights']]
            );
        }
    }
}
