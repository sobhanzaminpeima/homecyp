<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SponsorCampaign extends Model
{
    protected $fillable = [
        'sponsor_id', 'property_id', 'project_id', 'budget', 'starts_at', 'ends_at',
        'priority', 'target_region', 'target_property_type', 'target_budget_min',
        'target_budget_max', 'target_languages', 'status', 'impressions', 'clicks', 'conversions',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'target_languages' => 'array',
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
