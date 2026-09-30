<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuItem extends Model
{
    protected $fillable = ['location', 'label', 'url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function forLocation(string $location)
    {
        return Cache::remember("menu_{$location}", 3600, function () use ($location) {
            return static::where('location', $location)->where('is_active', true)
                ->orderBy('sort_order')->get();
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }
}
