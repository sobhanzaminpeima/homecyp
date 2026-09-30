<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Testimonial extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name', 'role', 'nationality', 'content', 'rating', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->useDisk('public')->singleFile();
    }
}
