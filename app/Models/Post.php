<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'slug', 'category', 'is_published', 'is_featured', 'views', 'published_at', 'author_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first()
            ?? $this->translations()->where('locale', 'en')->first();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->useDisk('public')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(500)->height(350)->nonOptimized()->nonQueued();
        $this->addMediaConversion('medium')->width(1000)->height(600)->nonOptimized()->nonQueued();
    }

    public function getCoverImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'medium') ?: null;
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? '';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
