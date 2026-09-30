<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportNorthCyprusDirectory extends Command
{
    protected $signature = 'import:north-cyprus-directory
        {--file=data/directory.json : Path to the JSON dump of the Directory sheet, relative to the project root}
        {--skip-logos : Skip the logo fetch step entirely (faster re-runs while iterating)}';

    protected $description = 'Import the North Cyprus business directory Excel export into the businesses table';

    protected const SOURCE = 'north_cyprus_directory_2026';

    /** @var array<string, string> Excel city label => cities.slug */
    protected array $cityMap = [
        'Kyrenia (Girne)' => 'kyrenia',
        'Famagusta (Gazimağusa)' => 'famagusta',
        'Nicosia (Lefkosa)' => 'nicosia',
        'Iskele' => 'iskele',
        'Guzelyurt (Morphou)' => 'guzelyurt',
        'Lefke' => 'lefke',
        // Not its own city in the app; user asked to fold it into Iskele.
        'Yeni Boğaziçi' => 'iskele',
    ];

    protected array $unmatchedCities = [];

    protected array $unmatchedCategories = [];

    public function handle(): int
    {
        $docroot = dirname(base_path());
        $jsonPath = $docroot . '/' . ltrim($this->option('file'), '/');

        if (! is_file($jsonPath)) {
            $this->error("File not found: {$jsonPath}");

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($jsonPath), true, flags: JSON_THROW_ON_ERROR);
        $this->info('Loaded ' . count($rows) . ' rows from ' . $jsonPath);

        $cities = City::query()->get()->keyBy('slug');
        $categories = Category::query()->get()->keyBy('slug');

        $cleaned = [];
        foreach ($rows as $i => $row) {
            $cleaned[] = $this->cleanRow($row, $i + 2, $cities, $categories); // +2: header row + 1-index
        }

        // Drop rows that failed to resolve a required field (name/city/category).
        $valid = array_values(array_filter($cleaned, fn ($r) => $r !== null));
        $droppedCount = count($cleaned) - count($valid);

        // Dedupe: same normalized name + city + phone => true duplicate. Keep the more complete row.
        $groups = [];
        foreach ($valid as $r) {
            $key = Str::slug($r['name']) . '|' . $r['city_id'] . '|' . preg_replace('/\D+/', '', (string) $r['phone']);
            $groups[$key][] = $r;
        }

        $merged = [];
        $mergedAwayCount = 0;
        foreach ($groups as $key => $group) {
            if (count($group) === 1) {
                $merged[] = $group[0];

                continue;
            }

            // Same name+city+phone repeated (e.g. exact re-listing) — keep the most complete row.
            usort($group, fn ($a, $b) => $this->completeness($b) <=> $this->completeness($a));
            $merged[] = $group[0];
            $mergedAwayCount += count($group) - 1;
            $this->line('  merged duplicate: ' . $group[0]['name'] . ' (' . $group[0]['city_label'] . ')');
        }

        $this->info(sprintf(
            '%d rows valid, %d dropped (missing required field), %d merged away as true duplicates.',
            count($valid),
            $droppedCount,
            $mergedAwayCount
        ));

        if (! empty($this->unmatchedCategories)) {
            $this->warn('Unmapped categories seen (fell back to "Other"): ' . implode(', ', array_unique($this->unmatchedCategories)));
        }

        $existingByKey = Business::query()
            ->where('source', self::SOURCE)
            ->get()
            ->keyBy(fn ($b) => Str::slug($b->name) . '|' . $b->city_id . '|' . preg_replace('/\D+/', '', (string) $b->phone));

        $created = 0;
        $updated = 0;
        $logosFromClearbit = 0;
        $logosFromFavicon = 0;
        $logosNone = 0;

        foreach ($merged as $r) {
            $key = Str::slug($r['name']) . '|' . $r['city_id'] . '|' . preg_replace('/\D+/', '', (string) $r['phone']);
            $existing = $existingByKey->get($key);

            $slug = $existing?->slug ?? $this->uniqueSlug($r['name']);

            $data = [
                'city_id' => $r['city_id'],
                'category_id' => $r['category_id'],
                'name' => $r['name'],
                'slug' => $slug,
                'description' => null,
                'address' => $r['address'],
                'phone' => $r['phone'],
                'whatsapp' => $r['whatsapp'],
                'email' => $r['email'],
                'website' => $r['website'],
                'website_secondary' => $r['website_secondary'],
                'notes' => $r['notes'],
                // Stored separately from rating_avg/rating_count so real on-platform
                // reviews (Business::recalculateRating()) blend with this instead of
                // overwriting it.
                'external_rating_avg' => $r['rating'] ?? 0,
                'external_rating_count' => $r['review_count'] ?? 0,
                'status' => 'approved',
                'is_verified' => $r['is_verified'],
                'source' => self::SOURCE,
            ];

            if ($existing) {
                $existing->fill($data);
                $existing->save();
                $business = $existing;
                $updated++;
            } else {
                $business = Business::query()->create($data);
                $created++;
            }

            // Recompute the displayed rating_avg/rating_count as external + any
            // real on-platform reviews, now that external_rating_* is current.
            $business->recalculateRating();

            if (! $this->option('skip-logos') && ! $business->logo) {
                $result = $this->fetchLogo($business, $r['clearbit_url'], $r['favicon_url'], $docroot);

                if ($result === 'clearbit') {
                    $logosFromClearbit++;
                } elseif ($result === 'favicon') {
                    $logosFromFavicon++;
                } else {
                    $logosNone++;
                }
            } elseif ($business->logo) {
                // Already has a working logo from a previous run — leave it alone (idempotent).
            } else {
                $logosNone++;
            }
        }

        $this->newLine();
        $this->info('=== Import summary ===');
        $this->line("Created: {$created}");
        $this->line("Updated (re-run, matched existing source row): {$updated}");
        $this->line("Dropped (missing required field): {$droppedCount}");
        $this->line("Merged away as true duplicates: {$mergedAwayCount}");
        if (! $this->option('skip-logos')) {
            $this->line("Logos from Clearbit: {$logosFromClearbit}");
            $this->line("Logos from Google Favicon fallback: {$logosFromFavicon}");
            $this->line("No logo available: {$logosNone}");
        } else {
            $this->line('Logo fetch skipped (--skip-logos).');
        }
        $this->line('Total businesses now tagged source=' . self::SOURCE . ': ' . Business::query()->where('source', self::SOURCE)->count());

        return self::SUCCESS;
    }

    protected function completeness(array $row): int
    {
        return collect($row)->filter(fn ($v) => $v !== null && $v !== '')->count();
    }

    protected function cleanRow(array $row, int $excelRow, $cities, $categories): ?array
    {
        $name = trim((string) ($row['Business Name'] ?? ''));
        if ($name === '') {
            $this->warn("Row {$excelRow}: missing Business Name, skipped.");

            return null;
        }

        $cityLabel = trim((string) ($row['City'] ?? ''));
        $citySlug = $this->cityMap[$cityLabel] ?? null;
        if (! $citySlug || ! $cities->has($citySlug)) {
            $this->unmatchedCities[] = $cityLabel;
            $this->warn("Row {$excelRow} ({$name}): unrecognized city '{$cityLabel}', skipped.");

            return null;
        }
        $city = $cities->get($citySlug);

        $categoryLabel = trim((string) ($row['Category'] ?? ''));
        $categorySlug = $this->mapCategory($categoryLabel);
        if (! $categories->has($categorySlug)) {
            // Should not happen given mapCategory only returns known slugs, but guard anyway.
            $categorySlug = 'other';
        }
        $category = $categories->get($categorySlug);

        $phone = $this->cleanText($row['Phone'] ?? null);
        $whatsapp = $this->cleanText($row['WhatsApp'] ?? null);
        $email = $this->cleanEmail($row['Email'] ?? null);
        $address = $this->cleanText($row['Address'] ?? null);
        $notes = $this->cleanText($row['Notes'] ?? null);

        [$website, $websiteSecondary] = $this->cleanWebsite($row['Website'] ?? null);

        [$rating, $reviewCount] = $this->parseRating($row['Rating'] ?? null);

        $isVerified = str_contains((string) ($row['Website Status'] ?? ''), 'Verified live');

        $clearbitUrl = $this->cleanText($row['Logo URL (Clearbit)'] ?? null);
        $faviconUrl = $this->cleanText($row['Logo URL (Google Favicon)'] ?? null);

        return [
            'name' => $name,
            'city_id' => $city->id,
            'city_label' => $cityLabel,
            'category_id' => $category->id,
            'phone' => $phone,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'website' => $website,
            'website_secondary' => $websiteSecondary,
            'address' => $address,
            'rating' => $rating,
            'review_count' => $reviewCount,
            'notes' => $notes,
            'is_verified' => $isVerified,
            'clearbit_url' => $website ? $clearbitUrl : null,
            'favicon_url' => $website ? $faviconUrl : null,
        ];
    }

    protected function mapCategory(string $label): string
    {
        $l = strtolower($label);

        return match (true) {
            str_contains($l, 'construction') => 'construction',
            str_contains($l, 'real estate'), str_contains($l, 'property'), str_contains($l, 'rental agency'), str_contains($l, 'apartment rental') => 'real-estate',
            str_contains($l, 'hotel') => 'hotel-casino',
            str_contains($l, 'restaurant') => 'restaurant',
            str_contains($l, 'cafe'), str_contains($l, 'bar/cafe') => 'coffee-shop',
            str_contains($l, 'hair') || str_contains($l, 'beauty') => 'hair-beauty',
            str_contains($l, 'market') => 'market',
            str_contains($l, 'taxi'), str_contains($l, 'transport') => 'taxi',
            str_contains($l, 'retail') => 'retail',
            default => (function () use ($label) {
                $this->unmatchedCategories[] = $label;

                return 'other';
            })(),
        };
    }

    protected function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim($value);
        if ($value === '' || strcasecmp($value, 'Not available') === 0 || strcasecmp($value, 'N/A') === 0) {
            return null;
        }

        return $value;
    }

    protected function cleanEmail(?string $value): ?string
    {
        $value = $this->cleanText($value);
        if ($value === null) {
            return null;
        }
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->warn("Malformed email dropped: {$value}");

            return null;
        }

        return $value;
    }

    /** @return array{0: ?string, 1: ?string} */
    protected function cleanWebsite(?string $value): array
    {
        $value = $this->cleanText($value);
        if ($value === null) {
            return [null, null];
        }

        $parts = array_map('trim', explode(' / ', $value));
        $parts = array_filter($parts, fn ($p) => $p !== '');
        $parts = array_values($parts);

        $normalize = function (string $url): ?string {
            if (! preg_match('#^https?://#i', $url)) {
                $url = 'https://' . $url;
            }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                $this->warn("Malformed website dropped: {$url}");

                return null;
            }

            return $url;
        };

        $primary = isset($parts[0]) ? $normalize($parts[0]) : null;
        $secondary = isset($parts[1]) ? $normalize($parts[1]) : null;

        return [$primary, $secondary];
    }

    /** @return array{0: ?float, 1: ?int} */
    protected function parseRating(?string $value): array
    {
        $value = trim((string) $value);
        if ($value === '' || strcasecmp($value, 'Not available') === 0) {
            return [null, null];
        }
        if (stripos($value, 'no reviews') !== false) {
            return [null, 0];
        }
        if (preg_match('/([\d.]+)\s*\((\d+)\s*reviews?\)/i', $value, $m)) {
            return [(float) $m[1], (int) $m[2]];
        }
        if (is_numeric($value)) {
            return [(float) $value, null];
        }

        return [null, null];
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Business::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Try Clearbit, then the Google favicon URL, and only persist a logo that
     * genuinely loaded as an image. Returns 'clearbit', 'favicon', or null.
     */
    protected function fetchLogo(Business $business, ?string $clearbitUrl, ?string $faviconUrl, string $docroot): ?string
    {
        $uploadsDir = $docroot . '/uploads';
        if (! is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        foreach ([['clearbit', $clearbitUrl], ['favicon', $faviconUrl]] as [$source, $url]) {
            if (! $url) {
                continue;
            }

            $saved = $this->downloadImage($url, $uploadsDir);
            if ($saved) {
                // Relative, not config('app.url') — an absolute URL bakes in whatever
                // host this ran on (e.g. localhost), which breaks everywhere else.
                $url = '/uploads/' . $saved;
                $business->logo = $url;
                // Public cards/detail page render cover_image, not logo — reuse the
                // same downloaded image there since we have no separate cover photo.
                $business->cover_image = $url;
                $business->save();

                return $source;
            }
        }

        return null;
    }

    protected function downloadImage(string $url, string $uploadsDir): ?string
    {
        try {
            $response = Http::timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0 (EasyCyprusImporter)'])->get($url);
        } catch (\Throwable $e) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $contentType = $response->header('Content-Type');
        if (! $contentType || ! str_starts_with($contentType, 'image/')) {
            return null;
        }

        $body = $response->body();
        // Clearbit/Google both return a tiny 1x1 or near-empty placeholder for unknown domains.
        if (strlen($body) < 200) {
            return null;
        }

        $ext = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'jpeg'), str_contains($contentType, 'jpg') => 'jpg',
            str_contains($contentType, 'svg') => 'svg',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'gif') => 'gif',
            str_contains($contentType, 'x-icon'), str_contains($contentType, 'vnd.microsoft.icon') => 'ico',
            default => null,
        };
        if (! $ext) {
            return null;
        }

        $filename = Str::random(24) . '.' . $ext;
        file_put_contents($uploadsDir . '/' . $filename, $body);

        return $filename;
    }
}
