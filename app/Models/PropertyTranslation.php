<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id', 'locale', 'title', 'description',
        'short_description', 'meta_title', 'meta_description', 'faq',
    ];

    protected $casts = ['faq' => 'array'];
}
