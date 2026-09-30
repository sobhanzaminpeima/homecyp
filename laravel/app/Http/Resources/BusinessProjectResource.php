<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'business' => new BusinessResource($this->whenLoaded('business')),
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->images ?? [],
            'property_type' => $this->property_type,
            'listing_type' => $this->listing_type,
            'rental_period' => $this->rental_period,
            'available_from' => $this->available_from?->toDateString(),
            'price' => $this->price !== null ? (float) $this->price : null,
            'currency' => $this->currency,
            'area_m2' => $this->area_m2,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'max_guests' => $this->max_guests,
            'minimum_stay' => $this->minimum_stay,
            'floor' => $this->floor,
            'amenities' => $this->amenities ?? [],
            'address' => $this->address,
            'booking_url' => $this->booking_url,
            'lat' => $this->lat !== null ? (float) $this->lat : null,
            'lng' => $this->lng !== null ? (float) $this->lng : null,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'view_count' => $this->view_count,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
