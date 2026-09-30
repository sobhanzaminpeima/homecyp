<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbTest extends Model
{
    protected $fillable = ['name', 'subject', 'variants', 'status', 'results'];

    protected $casts = [
        'variants' => 'array',
        'results' => 'array',
    ];
}
