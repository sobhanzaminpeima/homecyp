<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProjectResource;
use App\Models\AiConversation;
use App\Services\PropertyAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class AiChatController extends Controller {
 public function chat(Request $r,PropertyAiService $ai){$d=$r->validate(['conversation_id'=>'nullable|uuid','message'=>'required|string|min:1|max:2000','locale'=>'nullable|string|max:12']);$locale=$d['locale']??'en';$conversation=!empty($d['conversation_id'])?AiConversation::find($d['conversation_id']):null;if(!$conversation)$conversation=AiConversation::create(['id'=>(string)Str::uuid(),'user_id'=>$r->user()?->id,'locale'=>$locale,'title'=>Str::limit($d['message'],80)]);abort_if($conversation->user_id&&$conversation->user_id!==$r->user()?->id,403);$conversation->messages()->create(['role'=>'user','content'=>$d['message']]);$result=$ai->reply($conversation,$d['message'],$locale);$conversation->update(['locale'=>$locale,'search_context'=>$result['filters'],'last_intent'=>$result['filters']['listing_type']??'property_search']);$conversation->messages()->create(['role'=>'assistant','content'=>$result['answer'],'property_ids'=>$result['properties']->pluck('id')->all()]);return response()->json(['conversation_id'=>$conversation->id,'message'=>$result['answer'],'properties'=>BusinessProjectResource::collection($result['properties'])->resolve(),'filters'=>$result['filters']]);}
 public function history(Request $r,string $id){$conversation=AiConversation::findOrFail($id);abort_if($conversation->user_id&&$conversation->user_id!==$r->user()?->id,403);return response()->json(['conversation_id'=>$conversation->id,'messages'=>$conversation->messages()->oldest()->get(['id','role','content','property_ids','created_at'])]);}
}
