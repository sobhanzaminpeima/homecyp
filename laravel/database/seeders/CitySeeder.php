<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        // Real photos of each town, sourced from Wikimedia Commons (public domain / CC-licensed).
        $cities = [
            ['name' => 'Kyrenia', 'name_tr' => 'Girne', 'name_fa' => 'کرنیا', 'slug' => 'kyrenia', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/c4/Kyrenia_01-2017_img04_view_from_castle_bastion.jpg/330px-Kyrenia_01-2017_img04_view_from_castle_bastion.jpg'],
            ['name' => 'Famagusta', 'name_tr' => 'Gazimağusa', 'name_fa' => 'فاماگوستا', 'slug' => 'famagusta', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Varosha_utsikt.jpg/330px-Varosha_utsikt.jpg'],
            ['name' => 'Nicosia', 'name_tr' => 'Lefkoşa', 'name_fa' => 'نیکوزیا', 'slug' => 'nicosia', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Nicosia%27s_skyline_2024.jpg/330px-Nicosia%27s_skyline_2024.jpg'],
            ['name' => 'Iskele', 'name_tr' => 'İskele', 'name_fa' => 'ایسکله', 'slug' => 'iskele', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/80/%C4%B0skele_Trikomo_main_square_July_2015.jpg/330px-%C4%B0skele_Trikomo_main_square_July_2015.jpg'],
            ['name' => 'Lapta', 'name_tr' => 'Lapta', 'name_fa' => 'لاپتا', 'slug' => 'lapta', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/Lapta_general_view.jpg/330px-Lapta_general_view.jpg'],
            ['name' => 'Alsancak', 'name_tr' => 'Alsancak', 'name_fa' => 'آلسانجاک', 'slug' => 'alsancak', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/Escape_Beach_North_Cyprus.jpg/330px-Escape_Beach_North_Cyprus.jpg'],
            ['name' => 'Catalkoy', 'name_tr' => 'Çatalköy', 'name_fa' => 'چاتالکوی', 'slug' => 'catalkoy', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/ae/Diana_Beach%2C_Catalkoy%2C_North_Cyprus_-_panoramio.jpg/330px-Diana_Beach%2C_Catalkoy%2C_North_Cyprus_-_panoramio.jpg'],
            ['name' => 'Esentepe', 'name_tr' => 'Esentepe', 'name_fa' => 'اسن‌تپه', 'slug' => 'esentepe', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Esentepemunicipality.jpg/330px-Esentepemunicipality.jpg'],
            ['name' => 'Guzelyurt', 'name_tr' => 'Güzelyurt', 'name_fa' => 'گوزلیورت', 'slug' => 'guzelyurt', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Morphou_orange_monument.jpg/330px-Morphou_orange_monument.jpg'],
            ['name' => 'Lefke', 'name_tr' => 'Lefke', 'name_fa' => 'لفکه', 'slug' => 'lefke', 'image' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d0/Lefke_heykel.jpg/330px-Lefke_heykel.jpg'],
        ];

        foreach ($cities as $index => $city) {
            City::query()->updateOrCreate(
                ['slug' => $city['slug']],
                [
                    'name' => $city['name'],
                    'name_tr' => $city['name_tr'],
                    'name_fa' => $city['name_fa'],
                    'image' => $city['image'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
