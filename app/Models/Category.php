<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    protected $fillable = ['slug', 'name', 'name_tr', 'type', 'color', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function getLocalizedNameAttribute(): string
    {
        return app()->getLocale() === 'tr' && $this->name_tr ? $this->name_tr : $this->name;
    }

    public static function forType(string $type = 'blog')
    {
        return Cache::remember("categories_{$type}", 3600, fn () =>
            static::where('type', $type)->where('is_active', true)->orderBy('sort_order')->get()
        );
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }
}
