<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaTranslation extends Model
{
    public $timestamps = false;

    protected $fillable = ['area_id', 'locale', 'title', 'overview', 'highlights'];

    protected $casts = ['highlights' => 'array'];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
