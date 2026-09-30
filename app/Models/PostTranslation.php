<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'post_id', 'locale', 'title', 'excerpt', 'body', 'meta_title', 'meta_description',
    ];
}
