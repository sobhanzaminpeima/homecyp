<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecommendationRule extends Model
{
    protected $fillable = ['label', 'trigger_keywords', 'intent', 'boosted_attribute', 'weight', 'is_active'];

    protected $casts = [
        'trigger_keywords' => 'array',
        'weight' => 'float',
        'is_active' => 'boolean',
    ];
}
