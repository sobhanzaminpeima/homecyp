<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportAirbnbListings extends Command
{
    protected $signature = 'import:airbnb';
    protected $description = 'Create draft Airbnb daily-rental listings (pending) linked to their Airbnb URLs for the admin to complete';

    protected array $urls = [
        'https://www.airbnb.com/rooms/1455573956054841586',
        'https://www.airbnb.com/rooms/1462981117964655970',
        'https://www.airbnb.com/rooms/1493419103845465334',
        'https://www.airbnb.com/rooms/1557828555101311207',
        'https://www.airbnb.com/rooms/1613784587869754850',
        'https://www.airbnb.com/rooms/1652239151480476014',
        'https://www.airbnb.com/rooms/1659213425452916164',
        'https://www.airbnb.com/rooms/1659238277209591146',
        'https://www.airbnb.com/rooms/1674478588178742236',
        'https://www.airbnb.com/rooms/1678890602216705631',
        'https://www.airbnb.com/rooms/1687688632865344336',
    ];

    public function handle(): int
    {
        $created = 0;

        foreach ($this->urls as $i => $url) {
            // Skip if this Airbnb URL was already imported.
            if (Property::where('airbnb_url', $url)->exists()) {
                $this->line("  • Already exists, skipping: {$url}");
                continue;
            }

            $n = $i + 1;
            $slug = $this->uniqueSlug("airbnb-rental-{$n}");

            $property = Property::create([
                'slug' => $slug,
                'type' => 'apartment',
                'category' => 'daily_rental',
                'status' => 'pending', // hidden on frontend until completed by admin
                'currency' => 'GBP',
                'location' => 'North Cyprus',
                'is_airbnb' => true,
                'airbnb_url' => $url,
                'has_pool' => true,
            ]);

            PropertyTranslation::create([
                'property_id' => $property->id,
                'locale' => 'en',
                'title' => "Airbnb Daily Rental #{$n} — North Cyprus",
                'short_description' => 'Luxury Airbnb daily rental in North Cyprus. Complete this listing with photos, price and description, then set status to Active to publish.',
                'meta_title' => "Airbnb Daily Rental in North Cyprus | HomeCyp",
                'meta_description' => 'Book this luxury daily rental in North Cyprus directly on Airbnb. Premium accommodation with modern amenities.',
            ]);

            $this->info("  ✓ Draft #{$property->id} created for {$url}");
            $created++;
        }

        $this->newLine();
        $this->info("{$created} Airbnb draft listing(s) created (status: pending).");
        $this->comment('Complete each one in Admin → Properties: add photos, price, description, then set status to Active.');
        return self::SUCCESS;
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
