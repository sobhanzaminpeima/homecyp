<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'name_tr' => $this->name_tr,
            'name_fa' => $this->name_fa,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'content_type' => $this->content_type,
            'is_active' => $this->is_active,
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
