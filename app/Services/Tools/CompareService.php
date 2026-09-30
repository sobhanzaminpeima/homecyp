<?php

namespace App\Services\Tools;

use App\Models\Property;
use Illuminate\Support\Collection;

class CompareService
{
    /**
     * @param int[] $propertyIds
     */
    public function compare(array $propertyIds): array
    {
        $properties = Property::with('translations')->whereIn('id', $propertyIds)->get();

        return [
            'type' => 'compare',
            'rows' => $properties->map(fn (Property $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'price' => $p->price,
                'currency' => $p->currency,
                'region' => $p->region,
                'bedrooms' => $p->bedrooms,
                'area' => $p->area,
                'area_unit' => $p->area_unit,
                'payment_plan' => $p->payment_plan,
                'amenities' => array_values($p->amenities ?? []),
                'roi_estimate' => $p->investment_benefits['rental_yield_percent'] ?? null,
            ])->values()->all(),
        ];
    }
}
