<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'slug', 'developer', 'location', 'region',
        'latitude', 'longitude', 'price_from', 'currency',
        'completion_date', 'status', 'is_featured', 'views',
        'source_url', 'amenities', 'investment_benefits',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'amenities' => 'array',
        'investment_benefits' => 'array',
        'completion_date' => 'date',
    ];

    public function translations()
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', 'en')->first();
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->useDisk('public');
        $this->addMediaCollection('cover')->useDisk('public')->singleFile();
        $this->addMediaCollection('brochure')->useDisk('public')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonQueued();
        $this->addMediaConversion('medium')->width(800)->height(600)->nonQueued();
    }

    public function getCoverImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'medium')
            ?: $this->getFirstMediaUrl('gallery', 'medium')
            ?: null;
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? '';
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
