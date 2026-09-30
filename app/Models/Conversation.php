<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'lead_id', 'agent_persona_id', 'locale', 'title', 'memory', 'last_message_at',
    ];

    protected $casts = [
        'memory' => 'array',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            $conversation->uuid ??= (string) Str::uuid();
        });
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function agentPersona()
    {
        return $this->belongsTo(Agent::class, 'agent_persona_id');
    }

    public function rememberFact(string $key, mixed $value): void
    {
        $memory = $this->memory ?? [];
        $memory[$key] = $value;
        $this->memory = $memory;
        $this->save();
    }
}
