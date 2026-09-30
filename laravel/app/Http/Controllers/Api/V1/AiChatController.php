<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessProjectResource;
use App\Models\AiConversation;
use App\Models\BusinessProject;
use App\Services\PropertyAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiChatController extends Controller
{
    public function chat(Request $request, PropertyAiService $ai)
    {
        $data = $request->validate([
            'conversation_id' => 'nullable|uuid',
            'message' => 'required|string|min:1|max:2000',
            'locale' => 'nullable|string|max:12',
        ]);

        $locale = $data['locale'] ?? 'en';
        $conversation = !empty($data['conversation_id'])
            ? AiConversation::find($data['conversation_id'])
            : null;

        if (!$conversation) {
            $conversation = AiConversation::create([
                'id' => (string) Str::uuid(),
                'user_id' => $request->user()?->id,
                'locale' => $locale,
                'title' => Str::limit($data['message'], 80),
            ]);
        }

        abort_if($conversation->user_id && $conversation->user_id !== $request->user()?->id, 403);

        $conversation->messages()->create(['role' => 'user', 'content' => $data['message']]);
        $result = $ai->reply($conversation, $data['message'], $locale);
        $conversation->update([
            'locale' => $locale,
            'search_context' => $result['filters'],
            'last_intent' => $result['filters']['listing_type'] ?? 'property_search',
        ]);
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $result['answer'],
            'property_ids' => $result['properties']->pluck('id')->all(),
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'message' => $result['answer'],
            'properties' => BusinessProjectResource::collection($result['properties'])->resolve(),
            'filters' => $result['filters'],
        ]);
    }

    public function history(Request $request, string $id)
    {
        $conversation = AiConversation::findOrFail($id);
        abort_if($conversation->user_id && $conversation->user_id !== $request->user()?->id, 403);

        $messages = $conversation->messages()->oldest()->get();
        $propertyIds = $messages->flatMap(fn ($message) => $message->property_ids ?? [])->unique();
        $properties = BusinessProject::query()
            ->whereIn('id', $propertyIds)
            ->with(['business.city', 'business.category'])
            ->get()
            ->keyBy('id');

        return response()->json([
            'conversation_id' => $conversation->id,
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'properties' => BusinessProjectResource::collection(
                    collect($message->property_ids ?? [])->map(fn ($propertyId) => $properties->get($propertyId))->filter()
                )->resolve(),
                'created_at' => $message->created_at,
            ]),
        ]);
    }
}
