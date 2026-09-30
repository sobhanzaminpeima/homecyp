<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeSource extends Model
{
    protected $fillable = [
        'title', 'type', 'category', 'file_path', 'source_url', 'content', 'status', 'error_message',
    ];

    public function chunks()
    {
        return $this->hasMany(KnowledgeChunk::class);
    }
}
