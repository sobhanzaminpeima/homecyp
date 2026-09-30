<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'page_id', 'locale', 'title', 'content', 'meta_title', 'meta_description',
    ];
}
