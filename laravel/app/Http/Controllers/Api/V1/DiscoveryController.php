<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\LocalEvent;
use Illuminate\Http\Request;
class DiscoveryController extends Controller {
 public function deals(Request $r){$q=Deal::with('business.city')->where('is_active',true)->where(fn($x)=>$x->whereNull('starts_at')->orWhere('starts_at','<=',now()))->where(fn($x)=>$x->whereNull('ends_at')->orWhere('ends_at','>=',now())); if($r->filled('city'))$q->whereHas('business.city',fn($x)=>$x->where('slug',$r->string('city'))); return response()->json(['data'=>$q->latest()->limit(30)->get()]);}
 public function events(Request $r){$q=LocalEvent::with(['city','business'])->where('status','published')->where('starts_at','>=',now()->subHours(6)); if($r->filled('city'))$q->whereHas('city',fn($x)=>$x->where('slug',$r->string('city'))); return response()->json(['data'=>$q->orderBy('starts_at')->limit(50)->get()]);}
}
