<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration_days' => $this->duration_days,
            'is_featured' => $this->is_featured,
            'is_premium' => $this->is_premium,
            'listing_limit' => $this->listing_limit,
            'features' => $this->features ?? [],
        ];
    }
}
