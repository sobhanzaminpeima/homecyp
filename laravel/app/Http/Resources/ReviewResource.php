<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'body' => $this->body,
            'status' => $this->status,
            'user_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'business_name' => $this->whenLoaded('business', fn () => $this->business->name),
            'business_slug' => $this->whenLoaded('business', fn () => $this->business->slug),
            'is_own' => $request->user()?->id === $this->user_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
