<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedDemoContent extends Command
{
    protected $signature = 'content:demo';
    protected $description = 'Populate homepage sections: activate daily rentals, create featured sale properties, set hero image — all using imported project imagery';

    public function handle(): int
    {
        $projects = Project::with('media')->get();
        if ($projects->isEmpty()) {
            $this->error('No projects found. Run import:northernland first.');
            return self::FAILURE;
        }

        // Pool of gallery media to reuse as imagery
        $imagePool = [];
        foreach ($projects as $project) {
            foreach ($project->getMedia('gallery') as $m) {
                $imagePool[] = $m;
            }
        }

        $regions = ['Kyrenia', 'Esentepe', 'İskele Long Beach', 'Çatalköy', 'Bahçeli', 'Famagusta', 'Bafra', 'Tuzla'];
        $rentalTypes = ['studio', 'apartment', 'penthouse', 'villa'];

        // 1) Activate the Airbnb daily rentals with real-looking content + images
        $dailyRentals = Property::where('category', 'daily_rental')->get();
        $i = 0;
        foreach ($dailyRentals as $rental) {
            $region = $regions[$i % count($regions)];
            $beds = rand(1, 4);
            $price = [80, 95, 110, 130, 150, 175, 200][$i % 7];

            $rental->update([
                'status' => 'active',
                'type' => $rentalTypes[$i % count($rentalTypes)],
                'location' => $region . ', North Cyprus',
                'region' => $region,
                'price' => $price,
                'bedrooms' => $beds,
                'bathrooms' => max(1, $beds - 1),
                'area' => 60 + $beds * 25,
                'has_pool' => true,
                'has_sea_view' => $i % 2 === 0,
                'has_parking' => true,
                'is_featured' => $i < 6,
                'amenities' => ['Swimming Pool', 'Air Conditioning', 'Wi-Fi', 'Sea View', 'Smart TV', 'Fully Equipped Kitchen', 'Balcony', 'Parking'],
            ]);

            $t = $rental->translation('en');
            if ($t) {
                $t->update([
                    'title' => "Luxury {$rental->type} in {$region}",
                    'short_description' => "Stylish {$beds}-bedroom {$rental->type} in {$region} with pool access and modern comforts — perfect for a North Cyprus getaway.",
                    'description' => "<p>Enjoy a premium short stay in this beautifully furnished {$beds}-bedroom {$rental->type} located in {$region}, North Cyprus. The property features a swimming pool, fast Wi-Fi, air conditioning, and a fully equipped kitchen.</p><p>Ideally located close to beaches, restaurants and attractions, it is the perfect base for exploring the Mediterranean's best-kept secret. Book directly on Airbnb for instant confirmation.</p>",
                    'meta_title' => "Luxury {$rental->type} in {$region} | Daily Rental | HomeCyp",
                    'meta_description' => "Book a luxury {$beds}-bedroom {$rental->type} in {$region}, North Cyprus from £{$price}/night. Pool, sea view and modern amenities.",
                ]);
            }

            $this->attachPoolImages($rental, $imagePool, $i, 5);
            $i++;
        }
        $this->info("Activated {$dailyRentals->count()} daily rentals with images.");

        // 2) Create featured sale / resale properties from project data
        $created = 0;
        $saleTypes = ['apartment', 'villa', 'penthouse', 'townhouse', 'studio'];
        foreach ($projects as $idx => $project) {
            foreach (range(0, 1) as $variant) { // 2 units per project
                $type = $saleTypes[($idx + $variant) % count($saleTypes)];
                $beds = rand(1, 4);
                $price = [85000, 120000, 165000, 220000, 295000, 380000][($idx + $variant) % 6];
                $title = ucfirst($type) . " at " . ($project->translation('en')?->title ?? 'North Cyprus');
                $slug = $this->uniqueSlug(Str::slug($title) . '-' . ($variant + 1));

                $property = Property::create([
                    'slug' => $slug,
                    'type' => $type,
                    'category' => $variant === 0 ? 'resale' : 'project',
                    'status' => 'active',
                    'price' => $price,
                    'currency' => 'GBP',
                    'bedrooms' => $beds,
                    'bathrooms' => max(1, $beds - 1),
                    'area' => 70 + $beds * 30,
                    'location' => $project->location,
                    'region' => $project->region,
                    'project_id' => $project->id,
                    'has_pool' => true,
                    'has_parking' => true,
                    'has_sea_view' => $idx % 2 === 0,
                    'is_featured' => $created < 8,
                    'amenities' => $project->amenities ?: ['Swimming Pool', 'Gym', 'Security', 'Parking'],
                ]);

                PropertyTranslation::create([
                    'property_id' => $property->id,
                    'locale' => 'en',
                    'title' => $title,
                    'short_description' => "A {$beds}-bedroom {$type} for sale in {$project->location}. Premium finishes, resort amenities, and excellent investment potential.",
                    'description' => "<p>This {$beds}-bedroom {$type} is part of the prestigious " . ($project->translation('en')?->title ?? '') . " development in {$project->location}. Featuring contemporary design, high-quality finishes and access to world-class on-site amenities.</p><p>An outstanding opportunity for both lifestyle buyers and investors seeking strong rental yields in North Cyprus.</p>",
                    'meta_title' => Str::limit("{$title} | HomeCyp", 60, ''),
                    'meta_description' => "Buy a {$beds}-bedroom {$type} in {$project->location} from £" . number_format($price) . ". Luxury living and high ROI in North Cyprus.",
                ]);

                $this->attachPoolImages($property, $imagePool, $idx * 2 + $variant, 6);
                $created++;
            }
        }
        $this->info("Created {$created} featured sale/resale properties with images.");

        // 3) Hero background image — copy a wide gallery image to public
        $hero = $projects->first()->getMedia('gallery')->first();
        if ($hero && file_exists($hero->getPath())) {
            $dest = public_path('images/hero-bg.jpg');
            @copy($hero->getPath(), $dest);
            $this->info("Hero image set: {$dest}");
        }

        $this->newLine();
        $this->info('Demo content complete. Homepage sections are now populated.');
        return self::SUCCESS;
    }

    protected function attachPoolImages(Property $property, array $pool, int $offset, int $count): void
    {
        if (empty($pool)) {
            return;
        }
        $n = count($pool);
        // Cover
        $cover = $pool[$offset % $n];
        try { $cover->copy($property, 'cover'); } catch (\Throwable $e) {}
        // Gallery
        for ($k = 0; $k < $count; $k++) {
            $img = $pool[($offset + $k) % $n];
            try { $img->copy($property, 'gallery'); } catch (\Throwable $e) {}
        }
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 1;
        while (Property::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
