<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'note' => $this->note,
            'package' => new PackageResource($this->whenLoaded('package')),
            'business_name' => $this->whenLoaded('business', fn () => $this->business->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
