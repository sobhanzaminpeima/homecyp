<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class BusinessSeeder extends Seeder
{
    public function run(): void
    {
        $cities = City::query()->get()->keyBy('slug');
        $categories = Category::query()->get()->keyBy('slug');
        $owner = User::query()->where('email', 'business@easycyprus.com')->first();

        $businesses = [
            ['n' => 0, 'city' => 'kyrenia', 'cat' => 'taxi', 'name' => 'Girne Express Taxi', 'slug' => 'girne-express-taxi-1', 'phone' => '+90 533 527 2528', 'whatsapp' => '+90 533 914 3853', 'view' => 400, 'featured' => true, 'owned' => true],
            ['n' => 1, 'city' => 'nicosia', 'cat' => 'taxi', 'name' => '24/7 City Taxi', 'slug' => '247-city-taxi-2', 'phone' => '+90 533 779 9035', 'whatsapp' => '+90 533 678 3906', 'view' => 824, 'featured' => false, 'owned' => true],
            ['n' => 2, 'city' => 'kyrenia', 'cat' => 'barber', 'name' => 'Sharp Cuts Barber', 'slug' => 'sharp-cuts-barber-3', 'phone' => '+90 533 375 9756', 'whatsapp' => '+90 533 514 5921', 'view' => 583, 'featured' => false],
            ['n' => 3, 'city' => 'famagusta', 'cat' => 'barber', 'name' => 'Gentlemen Barber Shop', 'slug' => 'gentlemen-barber-shop-4', 'phone' => '+90 533 973 3994', 'whatsapp' => '+90 533 659 8742', 'view' => 601, 'featured' => false],
            ['n' => 4, 'city' => 'nicosia', 'cat' => 'market', 'name' => 'Lefkoşa Fresh Market', 'slug' => 'lefkosa-fresh-market-5', 'phone' => '+90 533 556 5523', 'whatsapp' => '+90 533 514 1457', 'view' => 631, 'featured' => false],
            ['n' => 5, 'city' => 'iskele', 'cat' => 'market', 'name' => 'Bay Mini Market', 'slug' => 'bay-mini-market-6', 'phone' => '+90 533 163 2846', 'whatsapp' => '+90 533 255 7773', 'view' => 696, 'featured' => true],
            ['n' => 6, 'city' => 'kyrenia', 'cat' => 'coffee-shop', 'name' => 'Harbour Coffee Shop', 'slug' => 'harbour-coffee-shop-7', 'phone' => '+90 533 364 3340', 'whatsapp' => '+90 533 799 1838', 'view' => 250, 'featured' => false, 'menu' => 'cafe'],
            ['n' => 7, 'city' => 'lapta', 'cat' => 'coffee-shop', 'name' => 'Bean & Leaf Café', 'slug' => 'bean-leaf-cafe-8', 'phone' => '+90 533 685 7653', 'whatsapp' => '+90 533 916 2796', 'view' => 627, 'featured' => false, 'menu' => 'cafe'],
            ['n' => 8, 'city' => 'kyrenia', 'cat' => 'restaurant', 'name' => 'Blue Wave Restaurant', 'slug' => 'blue-wave-restaurant-9', 'phone' => '+90 533 740 1344', 'whatsapp' => '+90 533 870 2279', 'view' => 747, 'featured' => false, 'menu' => 'restaurant'],
            ['n' => 9, 'city' => 'famagusta', 'cat' => 'restaurant', 'name' => 'Anatolian Grill', 'slug' => 'anatolian-grill-10', 'phone' => '+90 533 114 2752', 'whatsapp' => '+90 533 869 8158', 'view' => 151, 'featured' => false, 'menu' => 'restaurant'],
            ['n' => 10, 'city' => 'iskele', 'cat' => 'real-estate', 'name' => 'Cyprus Prime Real Estate', 'slug' => 'cyprus-prime-real-estate-11', 'phone' => '+90 533 452 8647', 'whatsapp' => '+90 533 830 6822', 'view' => 81, 'featured' => true],
            ['n' => 11, 'city' => 'esentepe', 'cat' => 'real-estate', 'name' => 'Esentepe Homes', 'slug' => 'esentepe-homes-12', 'phone' => '+90 533 620 2192', 'whatsapp' => '+90 533 408 8665', 'view' => 231, 'featured' => false],
            ['n' => 12, 'city' => 'nicosia', 'cat' => 'construction', 'name' => 'Mediterranean Construction', 'slug' => 'mediterranean-construction-13', 'phone' => '+90 533 532 8927', 'whatsapp' => '+90 533 187 4736', 'view' => 791, 'featured' => false],
            ['n' => 13, 'city' => 'famagusta', 'cat' => 'construction', 'name' => 'BuildPro Cyprus', 'slug' => 'buildpro-cyprus-14', 'phone' => '+90 533 475 4403', 'whatsapp' => '+90 533 115 7066', 'view' => 761, 'featured' => false],
            ['n' => 14, 'city' => 'famagusta', 'cat' => 'hotel-casino', 'name' => 'Merit Grand Hotel & Casino', 'slug' => 'merit-grand-hotel-casino-15', 'phone' => '+90 533 253 5854', 'whatsapp' => '+90 533 871 4135', 'view' => 335, 'featured' => false],
            ['n' => 15, 'city' => 'kyrenia', 'cat' => 'hotel-casino', 'name' => 'Kaya Palazzo Resort & Casino', 'slug' => 'kaya-palazzo-resort-casino-16', 'phone' => '+90 533 917 8255', 'whatsapp' => '+90 533 374 6280', 'view' => 722, 'featured' => true],
        ];

        $menus = [
            'cafe' => [['Espresso', 3.5], ['Cappuccino', 4], ['Cheesecake', 5.5], ['Avocado Toast', 7]],
            'restaurant' => [['Grilled Halloumi', 8], ['Seafood Platter', 18], ['Lamb Kebab', 12], ['Baklava', 6]],
        ];

        $cityCoords = [
            'kyrenia' => [35.3414000, 33.3192000],
            'famagusta' => [35.1264000, 33.9410000],
            'nicosia' => [35.1856000, 33.3823000],
            'iskele' => [35.2860000, 33.8920000],
            'lapta' => [35.3380000, 33.1750000],
            'alsancak' => [35.3430000, 33.2230000],
            'catalkoy' => [35.3480000, 33.3760000],
            'esentepe' => [35.3600000, 33.5660000],
            'guzelyurt' => [35.1990000, 32.9920000],
            'lefke' => [35.1136000, 32.8486000],
        ];

        foreach ($businesses as $data) {
            $city = $cities->get($data['city']);
            $category = $categories->get($data['cat']);

            if (!$city || !$category) {
                continue;
            }

            [$lat, $lng] = $cityCoords[$data['city']] ?? [null, null];

            $business = Business::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'owner_id' => !empty($data['owned']) ? $owner?->id : null,
                    'city_id' => $city->id,
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => "{$data['name']} — a trusted local business in Northern Cyprus, registered on Easy Cyprus.",
                    'address' => "{$city->name}, Northern Cyprus",
                    'lat' => $lat,
                    'lng' => $lng,
                    'phone' => $data['phone'],
                    'whatsapp' => $data['whatsapp'],
                    'website' => 'https://example.com',
                    'logo' => "https://picsum.photos/seed/eclogo{$data['n']}/200/200",
                    'cover_image' => "https://picsum.photos/seed/ecbiz{$data['n']}/800/600",
                    'gallery' => [],
                    'social' => ['instagram' => 'https://instagram.com'],
                    'hours' => [
                        'mon' => '09:00-22:00', 'tue' => '09:00-22:00', 'wed' => '09:00-22:00',
                        'thu' => '09:00-22:00', 'fri' => '09:00-23:00', 'sat' => '09:00-23:00', 'sun' => '10:00-21:00',
                    ],
                    'rating_avg' => 0,
                    'rating_count' => 0,
                    'view_count' => $data['view'],
                    'status' => 'approved',
                    'is_verified' => true,
                    'is_featured' => $data['featured'],
                    'expire_at' => '2026-12-28',
                ]
            );

            if (!empty($data['menu'])) {
                $menu = Menu::query()->firstOrCreate(['business_id' => $business->id, 'name' => 'Main Menu']);

                foreach ($menus[$data['menu']] as $order => [$itemName, $price]) {
                    MenuItem::query()->updateOrCreate(
                        ['menu_id' => $menu->id, 'name' => $itemName],
                        ['price' => $price, 'sort_order' => $order, 'is_available' => true]
                    );
                }
            }
        }

        $girneTaxi = Business::query()->where('slug', 'girne-express-taxi-1')->first();

        if ($girneTaxi) {
            Advertisement::query()->updateOrCreate(
                ['title' => 'Girne Express Taxi — 24/7 Airport Transfers'],
                [
                    'image' => 'https://picsum.photos/seed/ecad1/1200/400',
                    'target_url' => 'https://example.com',
                    'placement' => 'home_slider',
                    'city_id' => null,
                    'starts_at' => '2026-06-28',
                    'ends_at' => '2026-07-28',
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
