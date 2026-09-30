<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Agent extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id', 'phone', 'whatsapp', 'title', 'bio',
        'facebook', 'instagram', 'linkedin', 'status', 'is_featured',
    ];

    protected $casts = ['is_featured' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'agent_id', 'user_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->useDisk('public')->singleFile();
    }

    public function getPhotoAttribute(): ?string
    {
        return $this->getFirstMediaUrl('photo');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
