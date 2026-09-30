<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'phone', 'country', 'password', 'type', 'status', 'message',
        'source', 'property_id', 'project_id', 'agent_id',
        'preferred_language', 'meta_data', 'contacted_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'meta_data' => 'array',
        'contacted_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class);
    }

    public static function statusColors(): array
    {
        return [
            'new' => 'info',
            'contacted' => 'warning',
            'qualified' => 'success',
            'follow_up' => 'primary',
            'closed' => 'gray',
        ];
    }
}
