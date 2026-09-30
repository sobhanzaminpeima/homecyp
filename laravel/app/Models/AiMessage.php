<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiMessage extends Model {
    protected $guarded=[]; protected function casts():array{return ['property_ids'=>'array'];}
    public function conversation():BelongsTo{return $this->belongsTo(AiConversation::class,'conversation_id');}
}
