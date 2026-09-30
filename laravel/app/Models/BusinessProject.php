<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'title',
        'description',
        'images',
        'property_type',
        'listing_type',
        'rental_period',
        'available_from',
        'price',
        'currency',
        'area_m2',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'minimum_stay',
        'floor',
        'amenities',
        'address',
        'booking_url',
        'lat',
        'lng',
        'status',
        'is_featured',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'available_from' => 'date',
            'amenities' => 'array',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'is_featured' => 'boolean',
        ];
    }

    /** @return BelongsTo<Business, $this> */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
