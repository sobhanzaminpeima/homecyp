<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\BusinessProject;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_only_returns_approved_projects_of_live_businesses(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved']);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'pending']);

        $pendingBusiness = Business::factory()->create(['status' => 'pending']);
        BusinessProject::factory()->create(['business_id' => $pendingBusiness->id, 'status' => 'approved']);

        $response = $this->getJson('/api/v1/projects');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_listing_type_and_price_range(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved', 'listing_type' => 'sale', 'price' => 100000]);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved', 'listing_type' => 'rent', 'price' => 500]);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved', 'listing_type' => 'sale', 'price' => 900000]);

        $response = $this->getJson('/api/v1/projects?listing_type=sale&max_price=200000');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_city(): void
    {
        $city = City::factory()->create(['slug' => 'kyrenia']);
        $otherCity = City::factory()->create();
        $businessInCity = Business::factory()->create(['status' => 'approved', 'city_id' => $city->id]);
        $businessElsewhere = Business::factory()->create(['status' => 'approved', 'city_id' => $otherCity->id]);
        BusinessProject::factory()->create(['business_id' => $businessInCity->id, 'status' => 'approved']);
        BusinessProject::factory()->create(['business_id' => $businessElsewhere->id, 'status' => 'approved']);

        $response = $this->getJson('/api/v1/projects?city=kyrenia');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_show_returns_approved_project_and_increments_view_count(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $project = BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved', 'view_count' => 0]);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertOk()->assertJsonPath('data.id', $project->id);
        $this->assertEquals(1, $project->fresh()->view_count);
    }

    public function test_show_returns_404_for_pending_project(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $project = BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'pending']);

        $this->getJson("/api/v1/projects/{$project->id}")->assertNotFound();
    }
}
