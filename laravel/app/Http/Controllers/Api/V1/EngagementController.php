<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class EngagementController extends Controller {
 public function track(Request $r){$d=$r->validate(['event'=>'required|string|max:80','business_id'=>'nullable|exists:businesses,id','metadata'=>'nullable|array','session_id'=>'nullable|string|max:100']); if(isset($d['metadata']))$d['metadata']=json_encode($d['metadata']); DB::table('activity_events')->insert([...$d,'user_id'=>$r->user()?->id,'created_at'=>now(),'updated_at'=>now()]); return response()->json(['ok'=>true],201);}
 public function claim(Request $r,Business $business){$d=$r->validate(['role'=>'nullable|string|max:80','proof_url'=>'nullable|url|max:255','note'=>'nullable|string|max:1500']); DB::table('business_claims')->updateOrInsert(['business_id'=>$business->id,'user_id'=>$r->user()->id],[...$d,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]); return response()->json(['message'=>'Claim submitted for review.'],201);}
 public function report(Request $r,Business $business){$d=$r->validate(['reason'=>'required|in:closed,wrong_phone,wrong_address,wrong_hours,duplicate,other','details'=>'nullable|string|max:1500']); DB::table('business_reports')->insert([...$d,'business_id'=>$business->id,'user_id'=>$r->user()?->id,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]); return response()->json(['message'=>'Thanks. We will review this information.'],201);}
 public function inquiry(Request $r,Business $business){$d=$r->validate(['type'=>'required|in:quote,booking','name'=>'required|string|max:120','phone'=>'required|string|max:50','email'=>'nullable|email|max:255','preferred_at'=>'nullable|date','message'=>'nullable|string|max:2000']); $id=DB::table('business_inquiries')->insertGetId([...$d,'business_id'=>$business->id,'user_id'=>$r->user()?->id,'status'=>'new','created_at'=>now(),'updated_at'=>now()]); if($business->owner_id)DB::table('app_notifications')->insert(['user_id'=>$business->owner_id,'title'=>'New '.($d['type']==='booking'?'booking':'quote').' request','body'=>$d['name'].' contacted '.$business->name,'url'=>'/dashboard','created_at'=>now(),'updated_at'=>now()]); return response()->json(['id'=>$id,'message'=>'Your request has been sent.'],201);}
 public function notifications(Request $r){return response()->json(['data'=>DB::table('app_notifications')->where('user_id',$r->user()->id)->latest()->limit(50)->get()]);}
 public function read(Request $r,int $id){DB::table('app_notifications')->where('id',$id)->where('user_id',$r->user()->id)->update(['read_at'=>now()]); return response()->json(['ok'=>true]);}
 public function ownerInquiries(Request $r){$business=$r->user()->businesses()->firstOrFail(); return response()->json(['data'=>DB::table('business_inquiries')->where('business_id',$business->id)->latest()->get()]);}
}
