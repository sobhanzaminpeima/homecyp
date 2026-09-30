<?php

namespace App\Services\Tools;

use App\Models\Property;

/**
 * ROI must come from real per-project data, never LLM guessing (spec 5.4). If a
 * property lacks the numbers, this returns a widget saying so instead of fabricating.
 */
class RoiCalculatorService
{
    public function calculate(Property $property): array
    {
        $benefits = $property->investment_benefits ?? [];
        $annualRentalIncome = $benefits['annual_rental_income'] ?? null;
        $rentalYieldPercent = $benefits['rental_yield_percent'] ?? null;

        if ($annualRentalIncome === null && $rentalYieldPercent !== null && $property->price) {
            $annualRentalIncome = round($property->price * $rentalYieldPercent / 100, 2);
        }

        if ($annualRentalIncome === null || !$property->price) {
            return [
                'type' => 'roi',
                'property_id' => $property->id,
                'property_title' => $property->title,
                'available' => false,
                'message' => 'ROI data is not yet published for this property. An agent can share verified rental income figures.',
            ];
        }

        $roiPercent = round(($annualRentalIncome / $property->price) * 100, 2);
        $paybackYears = $annualRentalIncome > 0 ? round($property->price / $annualRentalIncome, 1) : null;

        return [
            'type' => 'roi',
            'property_id' => $property->id,
            'property_title' => $property->title,
            'available' => true,
            'price' => $property->price,
            'currency' => $property->currency,
            'annual_rental_income' => $annualRentalIncome,
            'roi_percent' => $roiPercent,
            'payback_years' => $paybackYears,
        ];
    }
}
