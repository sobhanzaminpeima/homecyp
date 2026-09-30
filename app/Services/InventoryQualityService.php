<?php

namespace App\Services;

use App\Models\Property;

class InventoryQualityService
{
    public function summary(): array
    {
        $active = Property::active();
        $total = (clone $active)->count();
        $complete = (clone $active)
            ->whereNotNull('price')->where('price', '>', 0)
            ->whereNotNull('region')->whereNotNull('type')
            ->whereHas('translations', fn ($q) => $q->whereNotNull('title')->where('title', '!=', ''))
            ->count();

        return [
            'active' => $total,
            'complete' => $complete,
            'missing_price' => (clone $active)->where(fn ($q) => $q->whereNull('price')->orWhere('price', '<=', 0))->count(),
            'missing_region' => (clone $active)->whereNull('region')->count(),
            'without_images' => (clone $active)->whereDoesntHave('media')->count(),
            'by_category' => (clone $active)->selectRaw('category, count(*) total')->groupBy('category')->pluck('total', 'category')->all(),
            'by_region' => (clone $active)->selectRaw('region, count(*) total')->groupBy('region')->orderByDesc('total')->pluck('total', 'region')->all(),
            'quality_percent' => $total ? round($complete / $total * 100, 1) : 0,
        ];
    }
}
