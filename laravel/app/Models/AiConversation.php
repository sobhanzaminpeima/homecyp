<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AiConversation extends Model {
    use HasUuids;
    public $incrementing=false; protected $keyType='string'; protected $guarded=[];
    protected function casts():array{return ['search_context'=>'array'];}
    public function user():BelongsTo{return $this->belongsTo(User::class);}
    public function messages():HasMany{return $this->hasMany(AiMessage::class,'conversation_id');}
}
