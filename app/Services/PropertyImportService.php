<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PropertyImportService
{
    /**
     * Known amenity keywords to detect within page content.
     */
    protected array $amenityKeywords = [
        'Swimming Pool', 'Infinity Pool', 'Gym', 'Fitness Center', 'SPA', 'Sauna',
        'Turkish Bath', 'Restaurant', 'Cafe', 'Bar', 'Security', '24/7 Security',
        'Parking', 'Underground Parking', 'Sea View', 'Beach', 'Private Beach',
        'Garden', 'Children Playground', 'Aquapark', 'Tennis Court', 'Basketball Court',
        'Concierge', 'Reception', 'Elevator', 'Air Conditioning', 'Smart Home',
        'Shopping Center', 'Cinema', 'Mini Golf', 'Walking Track', 'BBQ Area',
    ];

    /**
     * Import a project from a NorthernLand-style URL.
     * Returns the created Project or throws on failure.
     */
    public function importProject(string $url, string $category = 'project'): Project
    {
        $data = $this->fetchAndParse($url);

        $slug = $this->uniqueSlug($data['title'], Project::class);

        $project = Project::create([
            'slug' => $slug,
            'developer' => $data['developer'],
            'location' => $data['location'],
            'region' => $data['region'],
            'currency' => 'GBP',
            'status' => 'draft',
            'is_featured' => false,
            'source_url' => $url,
            'amenities' => $data['amenities'],
            'investment_benefits' => $data['investment_benefits'],
        ]);

        ProjectTranslation::create([
            'project_id' => $project->id,
            'locale' => 'en',
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'faq' => $data['faq'],
        ]);

        $this->attachImages($project, $data['cover'], $data['gallery']);

        return $project;
    }

    /**
     * Import as a Property instead of a Project.
     */
    public function importProperty(string $url, string $category = 'resale', string $type = 'apartment'): Property
    {
        $data = $this->fetchAndParse($url);

        $slug = $this->uniqueSlug($data['title'], Property::class);

        $property = Property::create([
            'slug' => $slug,
            'type' => $type,
            'category' => $category,
            'status' => 'draft',
            'currency' => 'GBP',
            'location' => $data['location'],
            'region' => $data['region'],
            'amenities' => $data['amenities'],
            'investment_benefits' => $data['investment_benefits'],
        ]);

        PropertyTranslation::create([
            'property_id' => $property->id,
            'locale' => 'en',
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'faq' => $data['faq'],
        ]);

        $this->attachImages($property, $data['cover'], $data['gallery']);

        return $property;
    }

    /**
     * Fetch the URL and extract structured data.
     */
    public function fetchAndParse(string $url): array
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml',
        ])->timeout(30)->get($url);

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch URL ({$response->status()}): {$url}");
        }

        $html = $response->body();

        $rawTitle = $this->meta($html, 'og:title') ?? $this->tag($html, 'title') ?? 'Imported Property';
        $title = $this->cleanTitle($rawTitle);
        $metaDescription = $this->metaName($html, 'description') ?? '';
        $cover = $this->meta($html, 'og:image');
        $gallery = $this->extractGallery($html);
        $amenities = $this->detectAmenities($html);
        $location = $this->guessLocation($rawTitle . ' ' . $metaDescription);

        return [
            'title' => $title,
            'developer' => $this->guessDeveloper($url),
            'location' => $location['location'],
            'region' => $location['region'],
            'cover' => $cover,
            'gallery' => $gallery,
            'amenities' => $amenities,
            'short_description' => $this->generateShortDescription($title, $location['location']),
            'description' => $this->generateDescription($title, $location['location'], $amenities, $metaDescription),
            'meta_title' => $this->generateMetaTitle($title, $location['location']),
            'meta_description' => $this->generateMetaDescription($title, $location['location'], $metaDescription),
            'faq' => $this->generateFaq($title, $location['location']),
            'investment_benefits' => $this->generateInvestmentBenefits($location['location']),
        ];
    }

    // ---------- Parsing helpers ----------

    protected function meta(string $html, string $property): ?string
    {
        if (preg_match('/<meta\s+property=["\']' . preg_quote($property, '/') . '["\']\s+content=["\']([^"\']*)["\']/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        return null;
    }

    protected function metaName(string $html, string $name): ?string
    {
        if (preg_match('/<meta\s+name=["\']' . preg_quote($name, '/') . '["\']\s+content=["\']([^"\']*)["\']/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        return null;
    }

    protected function tag(string $html, string $tag): ?string
    {
        if (preg_match('/<' . $tag . '[^>]*>([^<]*)<\/' . $tag . '>/i', $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES);
        }
        return null;
    }

    protected function extractGallery(string $html): array
    {
        preg_match_all('/https?:\/\/[^"\'\s]+\/storage\/gallery\/[^"\'\s]+\.(?:jpg|jpeg|png|webp)/i', $html, $m);
        $images = array_values(array_unique($m[0]));
        // Limit to a reasonable number to avoid huge imports.
        return array_slice($images, 0, 15);
    }

    protected function detectAmenities(string $html): array
    {
        $found = [];
        foreach ($this->amenityKeywords as $keyword) {
            if (stripos($html, $keyword) !== false) {
                $found[] = $keyword;
            }
        }
        return array_values(array_unique($found));
    }

    protected function cleanTitle(string $title): string
    {
        // Remove trailing "for Sale in ..., Northern Cyprus" style suffixes for a cleaner name.
        $title = preg_replace('/\s+(Residences|Apartments|Villas)?\s*for Sale.*$/i', '', $title);
        $title = preg_replace('/\s*[\|\-–]\s*Northernland.*$/i', '', $title);
        return trim($title) ?: 'Imported Property';
    }

    protected function guessLocation(string $text): array
    {
        $regions = ['İskele', 'Iskele', 'Kyrenia', 'Girne', 'Famagusta', 'Gazimağusa', 'Nicosia', 'Lefkoşa',
            'Esentepe', 'Bahçeli', 'Çatalköy', 'Catalkoy', 'Tatlısu', 'Long Beach', 'Tuzla', 'Yeni Boğaziçi', 'Bafra'];
        foreach ($regions as $region) {
            if (stripos($text, $region) !== false) {
                return ['location' => $region . ', North Cyprus', 'region' => $region];
            }
        }
        return ['location' => 'North Cyprus', 'region' => null];
    }

    protected function guessDeveloper(string $url): ?string
    {
        if (stripos($url, 'northernland') !== false) {
            return 'Northernland';
        }
        return null;
    }

    // ---------- SEO content generation (unique per listing) ----------

    protected function generateShortDescription(string $title, string $location): string
    {
        return "Review the available information for {$title} in {$location}. Price, availability and legal details must be verified before publication.";
    }

    protected function generateDescription(string $title, string $location, array $amenities, string $original): string
    {
        $amenityText = $amenities
            ? 'The source page mentions these amenities: ' . $this->humanList($amenities) . '.'
            : 'No amenities were reliably detected on the source page.';

        $context = $original ? '<p>' . e(Str::limit($original, 300)) . '</p>' : '';

        return <<<HTML
{$context}
<p>{$title} is a property listing in {$location}. This imported draft contains information detected from the source page and must be reviewed by a HomeCyp editor before it is published.</p>
<p>{$amenityText} Availability, specifications, ownership documentation, payment terms and investment performance require confirmation from the listing agent.</p>
HTML;
    }

    protected function generateMetaTitle(string $title, string $location): string
    {
        // Prefer the fullest variant that still fits within ~60 chars (no mid-word cuts).
        $candidates = [
            "{$title} | Luxury Property in {$location} | HomeCyp",
            "{$title} | {$location} | HomeCyp",
            "{$title} | HomeCyp",
            $title,
        ];
        foreach ($candidates as $candidate) {
            if (mb_strlen($candidate) <= 60) {
                return $candidate;
            }
        }
        return mb_substr($title, 0, 60);
    }

    protected function generateMetaDescription(string $title, string $location, string $original): string
    {
        $base = $original ?: "Explore {$title} in {$location}. Luxury residences with premium amenities and strong investment returns in North Cyprus.";
        return Str::limit($base, 158, '');
    }

    protected function generateFaq(string $title, string $location): array
    {
        return [[
            'question' => "How can I verify the details for {$title}?",
            'answer' => 'Ask a HomeCyp agent to confirm current availability, price, title documentation, taxes and payment terms before making a decision.',
        ]];
    }

    protected function generateInvestmentBenefits(string $location): array
    {
        return [];
    }

    // ---------- Utilities ----------

    protected function humanList(array $items): string
    {
        $items = array_slice($items, 0, 6);
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }
        $last = array_pop($items);
        return implode(', ', $items) . ' and ' . $last;
    }

    protected function uniqueSlug(string $title, string $modelClass): string
    {
        $base = Str::slug($title) ?: 'property';
        $slug = $base;
        $i = 1;
        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    protected function attachImages($model, ?string $cover, array $gallery): void
    {
        try {
            if ($cover) {
                $model->addMediaFromUrl($cover)->toMediaCollection('cover');
            }
        } catch (\Throwable $e) {
            // Cover download failed — continue without blocking the import.
        }

        foreach ($gallery as $img) {
            try {
                $model->addMediaFromUrl($img)->toMediaCollection('gallery');
            } catch (\Throwable $e) {
                // Skip individual failed images.
            }
        }
    }
}
