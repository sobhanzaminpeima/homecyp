<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class LocalEvent extends Model { protected $table='events'; protected $guarded=[]; protected function casts():array{return ['starts_at'=>'datetime','ends_at'=>'datetime','price'=>'decimal:2'];} public function city():BelongsTo{return $this->belongsTo(City::class);} public function business():BelongsTo{return $this->belongsTo(Business::class);} }
