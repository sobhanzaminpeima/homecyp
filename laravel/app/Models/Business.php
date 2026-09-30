<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'city_id',
        'category_id',
        'package_id',
        'name',
        'slug',
        'description',
        'address',
        'lat',
        'lng',
        'phone',
        'whatsapp',
        'email',
        'website',
        'website_secondary',
        'logo',
        'cover_image',
        'gallery',
        'social',
        'hours',
        'amenities',
        'languages',
        'payment_methods',
        'notes',
        'source',
        'rating_avg',
        'rating_count',
        'external_rating_avg',
        'external_rating_count',
        'status',
        'is_verified',
        'last_verified_at',
        'is_featured',
        'expire_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'gallery' => 'array',
            'social' => 'array',
            'hours' => 'array',
            'amenities' => 'array',
            'languages' => 'array',
            'payment_methods' => 'array',
            'rating_avg' => 'decimal:2',
            'external_rating_avg' => 'decimal:2',
            'is_verified' => 'boolean',
            'last_verified_at' => 'datetime',
            'is_featured' => 'boolean',
            'expire_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<City, $this> */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** @return HasMany<Menu, $this> */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /** @return HasMany<Review, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** @return HasMany<BusinessProject, $this> */
    public function projects(): HasMany
    {
        return $this->hasMany(BusinessProject::class);
    }

    /** @return HasMany<BusinessProduct, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(BusinessProduct::class);
    }

    /** @return HasMany<BusinessService, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class);
    }

    /** @return HasMany<SubscriptionRequest, $this> */
    public function subscriptionRequests(): HasMany
    {
        return $this->hasMany(SubscriptionRequest::class);
    }

    /** @return HasMany<Favorite, $this> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @deprecated use favorites() — kept as an alias to avoid touching every call site at once */
    public function favoritedBy(): HasMany
    {
        return $this->favorites();
    }

    /**
     * How many projects/products/services this business may list. The free
     * tier (no package, or a package with no explicit limit set) gets 3; a
     * paid package can raise that cap, or remove it entirely (null = unlimited).
     */
    public function listingLimit(): ?int
    {
        if (! $this->package_id || ! $this->package) {
            return 3;
        }

        // The package's own listing_limit is authoritative here, including
        // null — which means "unlimited" on a package, not "unset".
        return $this->package->listing_limit;
    }

    /**
     * Recompute rating_avg/rating_count as a blend of on-platform reviews and
     * this business's external_rating_avg/external_rating_count (the rating it
     * came in with, e.g. from an imported directory — never itself touched by
     * this method). Call after any review is created, deleted, or has its
     * status changed.
     *
     * Blending (rather than just overwriting with the on-platform average)
     * matters here: overwriting would erase the imported rating the moment a
     * single in-app review is added or moderated.
     */
    public function recalculateRating(): void
    {
        $stats = $this->reviews()->approved()->selectRaw('avg(rating) as avg_rating, count(*) as total')->first();
        $reviewAvg = (float) ($stats->avg_rating ?? 0);
        $reviewCount = (int) ($stats->total ?? 0);

        $externalAvg = (float) $this->external_rating_avg;
        $externalCount = (int) $this->external_rating_count;

        $combinedCount = $externalCount + $reviewCount;
        $combinedAvg = $combinedCount > 0
            ? (($externalAvg * $externalCount) + ($reviewAvg * $reviewCount)) / $combinedCount
            : 0;

        $this->update([
            'rating_avg' => round($combinedAvg, 2),
            'rating_count' => $combinedCount,
        ]);
    }

    /**
     * Publicly visible businesses: approved and not expired.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'approved')
            ->where(function (Builder $q) {
                $q->whereNull('expire_at')->orWhere('expire_at', '>', Carbon::now());
            });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isOpenNow(?Carbon $at = null): ?bool
    {
        if (! $this->hours) return null;
        $at ??= Carbon::now('Asia/Nicosia');
        $value = $this->hours[strtolower($at->format('l'))] ?? $this->hours[$at->format('l')] ?? null;
        if (! is_string($value) || trim($value) === '') return null;
        $normalized = strtolower(trim($value));
        if (str_contains($normalized, '24')) return true;
        if (str_contains($normalized, 'closed')) return false;
        if (! preg_match('/(\d{1,2}:\d{2})\s*(?:-|–|—|to)\s*(\d{1,2}:\d{2})/u', $normalized, $m)) return null;
        $now = $at->format('H:i');
        return $m[1] <= $m[2] ? ($now >= $m[1] && $now <= $m[2]) : ($now >= $m[1] || $now <= $m[2]);
    }

    /**
     * Great-circle distance in kilometers (Haversine). Done in PHP rather than
     * SQL so it works identically on MySQL (production) and SQLite (tests) —
     * SQLite has no built-in trig functions.
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        // Fulltext indexes (and whereFullText()) aren't supported on SQLite,
        // which the test suite uses for speed — fall back to LIKE there.
        if ($query->getConnection()->getDriverName() === 'sqlite') {
            return $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->whereFullText(['name', 'description'], $term)
                ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
