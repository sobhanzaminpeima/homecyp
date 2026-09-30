<?php

namespace Tests\Unit;

use App\Services\AdaptiveRecommendationService;
use App\Services\PropertySearchService;
use PHPUnit\Framework\TestCase;

class PropertySearchServiceTest extends TestCase
{
    private function service(): PropertySearchService
    {
        return new PropertySearchService($this->createMock(AdaptiveRecommendationService::class));
    }

    public function test_extracts_persian_digits_region_bedrooms_and_approximate_budget(): void
    {
        $filters = $this->service()->extractFilters('یک آپارتمان ۲ خوابه در گیرنه با بودجه حدود ۱۵۰ هزار پوند');

        $this->assertNull($filters['category']);
        $this->assertSame('Kyrenia', $filters['region']);
        $this->assertSame(2, $filters['bedrooms']);
        $this->assertSame(150000.0, $filters['budget_max']);
    }

    public function test_uses_upper_value_for_a_budget_range(): void
    {
        $filters = $this->service()->extractFilters('budget between 100 thousand and 175 thousand in Kyrenia');
        $this->assertSame(175000.0, $filters['budget_max']);
    }
}
