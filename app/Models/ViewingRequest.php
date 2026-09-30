<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViewingRequest extends Model
{
    protected $fillable = [
        'lead_id', 'property_id', 'agent_id', 'conversation_id',
        'status', 'requested_at', 'confirmed_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Points at users.id, matching Property::agent() — properties.agent_id is
     * also a users.id, not agents.id (Agent is a profile extension of User).
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
