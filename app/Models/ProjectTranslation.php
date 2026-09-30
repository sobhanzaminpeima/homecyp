<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id', 'locale', 'title', 'description',
        'short_description', 'meta_title', 'meta_description', 'faq',
    ];

    protected $casts = ['faq' => 'array'];
}
