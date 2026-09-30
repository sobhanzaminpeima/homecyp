<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'target_url' => $this->target_url,
            'placement' => $this->placement,
            'city_id' => $this->city_id,
            'city' => new CityResource($this->whenLoaded('city')),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
