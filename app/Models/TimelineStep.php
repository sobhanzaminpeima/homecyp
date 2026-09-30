<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineStep extends Model
{
    protected $fillable = ['project_id', 'key', 'label', 'description', 'next_action', 'sort_order'];
}
