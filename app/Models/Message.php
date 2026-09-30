<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'conversation_id', 'role', 'content', 'suggested_questions', 'quick_replies',
        'property_cards', 'sponsored_cards', 'widget', 'intent', 'feedback',
        'attachment_path', 'attachment_name', 'attachment_type', 'attachment_text',
    ];

    protected $casts = [
        'suggested_questions' => 'array',
        'quick_replies' => 'array',
        'property_cards' => 'array',
        'sponsored_cards' => 'array',
        'widget' => 'array',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
