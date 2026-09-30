<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'slug', 'type', 'category', 'status', 'price', 'currency',
        'bedrooms', 'bathrooms', 'area', 'area_unit',
        'has_parking', 'has_pool', 'has_gym', 'has_sea_view',
        'payment_plan', 'completion_date', 'location', 'region',
        'latitude', 'longitude', 'video_url', 'virtual_tour_url',
        'airbnb_url', 'is_airbnb', 'is_featured', 'views',
        'agent_id', 'project_id', 'amenities', 'investment_benefits',
    ];

    protected $casts = [
        'has_parking' => 'boolean',
        'has_pool' => 'boolean',
        'has_gym' => 'boolean',
        'has_sea_view' => 'boolean',
        'is_airbnb' => 'boolean',
        'is_featured' => 'boolean',
        'amenities' => 'array',
        'investment_benefits' => 'array',
        'completion_date' => 'date',
    ];

    public function translations()
    {
        return $this->hasMany(PropertyTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', 'en')->first();
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->useDisk('public');
        $this->addMediaCollection('brochure')->useDisk('public')->singleFile();
        $this->addMediaCollection('cover')->useDisk('public')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(400)->height(300)->nonOptimized()->nonQueued();
        $this->addMediaConversion('medium')->width(800)->height(600)->nonOptimized()->nonQueued();
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

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation()?->description;
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
