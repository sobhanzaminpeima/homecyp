<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeChunk extends Model
{
    protected $fillable = [
        'knowledge_source_id', 'full_text', 'embedding', 'embedding_model', 'chunk_index',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function source()
    {
        return $this->belongsTo(KnowledgeSource::class, 'knowledge_source_id');
    }
}
