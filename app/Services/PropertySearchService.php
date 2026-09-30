<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Collection;

/**
 * Extracts simple structured filters from free-text chat and queries the
 * internal properties table. Property search always hits the DB — never
 * the internet — per spec section 5.3.
 */
class PropertySearchService
{
    protected const REGIONS = [
        'iskele' => 'Iskele', 'iskele' => 'Iskele', 'اسکله' => 'Iskele', 'İskele' => 'Iskele',
        'kyrenia' => 'Kyrenia', 'girne' => 'Kyrenia', 'گیرنه' => 'Kyrenia', 'كيرينيا' => 'Kyrenia', 'гирне' => 'Kyrenia',
        'famagusta' => 'Famagusta', 'gazimagusa' => 'Famagusta', 'فاماگوستا' => 'Famagusta', 'غازي ماغوسا' => 'Famagusta',
        'nicosia' => 'Nicosia', 'lefkosa' => 'Nicosia', 'نیکوزیا' => 'Nicosia', 'لفکوشا' => 'Nicosia', 'نيقوسيا' => 'Nicosia',
    ];

    protected const CATEGORY_KEYWORDS = [
        'project' => ['off-plan', 'off plan', 'new build', 'new project', 'under construction', 'پیش فروش', 'پروژه جدید', 'yeni proje', 'проекты'],
        'resale' => ['resale', 'second hand', 'ready property', 'فروش', 'خرید', 'دست دوم', 'satılık', 'ikinci el', 'продажа', 'купить', 'kaufen'],
        'daily_rental' => ['airbnb', 'daily rental', 'nightly', 'short term', 'holiday rental', 'اجاره روزانه', 'اجاره کوتاه مدت', 'günlük kiralık', 'краткосрочная аренда', 'tagesmiete'],
        'long_term_rental' => ['long term rental', 'long-term rental', 'rent monthly', 'annual rental', 'اجاره بلند مدت', 'اجاره ماهانه', 'uzun dönem', 'долгосрочная аренда', 'langzeitmiete'],
    ];

    /**
     * Maps a recommendation_rules.boosted_attribute to how it's actually
     * detected on a property record — kept out of the rule table itself so
     * admins edit plain-English attribute names, not property schema details.
     */
    protected const ATTRIBUTE_AMENITY_KEYWORDS = [
        'near_school' => ['school', 'international school'],
        'near_hospital' => ['hospital', 'clinic', 'medical'],
        'remote_work_ready' => ['wifi', 'internet', 'fibre', 'fiber', 'coworking', 'office'],
    ];

    public function __construct(protected AdaptiveRecommendationService $adaptive)
    {
    }

    /**
     * @return Collection<int, Property>
     */
    public function search(string $text, int $limit = 6, ?string $intent = null): Collection
    {
        $filters = $this->extractFilters($text);

        $query = Property::query()->active()->with('translations');

        if ($filters['category']) {
            $query->category($filters['category']);
        } elseif ($intent === 'investment') {
            // Investment searches should surface properties that can actually be
            // purchased. Without this guard, a broad investment query could rank
            // nightly Airbnb inventory ahead of projects and resale listings.
            $query->whereIn('category', ['project', 'resale']);
        } elseif ($intent === 'property_search' && ($filters['budget_max'] ?? 0) >= 10000) {
            // A five-figure property budget is a purchase signal even when the
            // visitor omits the word "buy". Keep nightly/monthly rentals out of
            // those results so prices with different units are never compared.
            $query->whereIn('category', ['project', 'resale']);
        }

        if ($filters['region']) {
            $query->where('region', 'like', "%{$filters['region']}%");
        }

        if ($filters['bedrooms'] !== null) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if ($filters['budget_max'] !== null) {
            $query->where('price', '<=', $filters['budget_max']);
        }

        // Pull a wider candidate pool than $limit so the adaptive boost (spec 5.9)
        // has something to actually re-rank, then trim to $limit after scoring.
        $candidates = $query->orderByDesc('is_featured')->orderByDesc('views')->take($limit * 4)->get();

        $boosts = $this->adaptive->match($text, $intent);
        if (empty($boosts)) {
            return $candidates->take($limit)->values();
        }

        return $candidates
            ->map(fn (Property $p) => [$p, $this->score($p, $boosts)])
            ->sortByDesc(fn ($pair) => $pair[1])
            ->take($limit)
            ->map(fn ($pair) => $pair[0])
            ->values();
    }

    /**
     * @param array<string, float> $boosts boosted_attribute => weight
     */
    protected function score(Property $property, array $boosts): float
    {
        $score = ($property->is_featured ? 10 : 0) + min($property->views, 100) / 100;
        $amenities = array_map('mb_strtolower', $property->amenities ?? []);

        foreach ($boosts as $attribute => $weight) {
            if ($attribute === 'high_roi') {
                $yield = $property->investment_benefits['rental_yield_percent'] ?? 0;
                $score += $weight * $yield;
                continue;
            }

            $keywords = self::ATTRIBUTE_AMENITY_KEYWORDS[$attribute] ?? [];
            foreach ($keywords as $keyword) {
                if (in_array($keyword, $amenities, true) || collect($amenities)->contains(fn ($a) => str_contains($a, $keyword))) {
                    $score += $weight * 5;
                    break;
                }
            }
        }

        return $score;
    }

    /**
     * @return array{category: ?string, region: ?string, bedrooms: ?int, budget_max: ?float}
     */
    public function extractFilters(string $text): array
    {
        $lower = mb_strtolower($text);

        $category = null;
        foreach (self::CATEGORY_KEYWORDS as $key => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $category = $key;
                    break 2;
                }
            }
        }

        $region = null;
        foreach (self::REGIONS as $needle => $canonical) {
            if (str_contains($lower, mb_strtolower($needle))) {
                $region = $canonical;
                break;
            }
        }

        $bedrooms = null;
        if (preg_match('/([0-9۰-۹٠-٩]+)\s*(?:-|\s)?\s*(?:bed|bedroom|br|خوابه?|اتاق خواب|yatak odası|спальн|schlafzimmer)/u', $lower, $m)) {
            $bedrooms = (int) $this->normalizeDigits($m[1]);
        }

        $budgetMax = null;
        $normalizedForNumbers = $this->normalizeDigits($lower);
        if (preg_match('/(?:between|from|بین)\D{0,8}(?:£|\$|€)?\s*([\d,]+(?:\.\d+)?)\s*(k|thousand|m|million|هزار|میلیون|bin|milyon)?\D{0,12}(?:and|to|تا|الی|و)\D{0,5}(?:£|\$|€)?\s*([\d,]+(?:\.\d+)?)\s*(k|thousand|m|million|هزار|میلیون|bin|milyon)?/u', $normalizedForNumbers, $range)) {
            $budgetMax = $this->scaledAmount($range[3], $range[4] ?? $range[2] ?? '');
        } elseif (preg_match('/(?:budget|have|afford|under|below|up to|max(?:imum)?|around|about|approximately|بودجه|تا سقف|زیر|حدود|تقریباً|تقریبا|bütçe|altında|yaklaşık|бюджет|до|около|unter|etwa)\D{0,12}(?:£|\$|€)?\s*([\d,]+(?:\.\d+)?)\s*(k|thousand|m|million|هزار|میلیون|bin|milyon)?/u', $normalizedForNumbers, $m)) {
            $budgetMax = $this->scaledAmount($m[1], $m[2] ?? '');
        }

        return [
            'category' => $category,
            'region' => $region,
            'bedrooms' => $bedrooms,
            'budget_max' => $budgetMax,
        ];
    }

    protected function scaledAmount(string $number, string $unit): float
    {
        $amount = (float) str_replace(',', '', $number);
        return match (true) {
            in_array($unit, ['k', 'thousand', 'هزار', 'bin'], true) => $amount * 1000,
            in_array($unit, ['m', 'million', 'میلیون', 'milyon'], true) => $amount * 1_000_000,
            default => $amount,
        };
    }

    protected function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }
}
