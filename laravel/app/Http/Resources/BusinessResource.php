<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => $this->address,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'website' => $this->website,
            'website_secondary' => $this->website_secondary,
            'notes' => $this->notes,
            'logo' => $this->logo,
            'cover_image' => $this->cover_image,
            'gallery' => $this->gallery ?? [],
            'social' => $this->social ?? [],
            'hours' => $this->hours ?? [],
            'amenities' => $this->amenities ?? [],
            'languages' => $this->languages ?? [],
            'payment_methods' => $this->payment_methods ?? [],
            'is_open_now' => $this->isOpenNow(),
            'rating_avg' => (float) $this->rating_avg,
            'rating_count' => $this->rating_count,
            'external_rating_avg' => (float) $this->external_rating_avg,
            'external_rating_count' => $this->external_rating_count,
            'view_count' => $this->view_count,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'last_verified_at' => $this->last_verified_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
            'expire_at' => $this->expire_at?->toIso8601String(),
            'city' => new CityResource($this->whenLoaded('city')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'package' => new PackageResource($this->whenLoaded('package')),
            'menus' => MenuResource::collection($this->whenLoaded('menus')),
            'reviews' => ReviewResource::collection($this->whenLoaded('reviews')),
            'projects' => BusinessProjectResource::collection($this->whenLoaded('projects')),
            'products' => BusinessProductResource::collection($this->whenLoaded('products')),
            'services' => BusinessServiceResource::collection($this->whenLoaded('services')),
            'is_favorited' => $this->when(isset($this->is_favorited), fn () => (bool) $this->is_favorited),
            'distance_km' => $this->when(isset($this->distance_km), fn () => round($this->distance_km, 1)),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
