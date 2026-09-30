<?php

namespace Tests\Feature\Api\Dashboard;

use App\Models\Business;
use App\Models\BusinessProduct;
use App\Models\BusinessProject;
use App\Models\BusinessService;
use App\Models\Category;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingManagementTest extends TestCase
{
    use RefreshDatabase;

    private function realEstateOwner(): array
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'projects']);
        $business = Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id, 'status' => 'approved']);

        return [$owner, $business];
    }

    public function test_owner_can_add_a_project_with_up_to_5_images(): void
    {
        [$owner, $business] = $this->realEstateOwner();

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Seaside Villa',
            'description' => 'A lovely villa.',
            'images' => ['/uploads/a.jpg', '/uploads/b.jpg', '/uploads/c.jpg', '/uploads/d.jpg', '/uploads/e.jpg'],
            'property_type' => 'villa',
            'listing_type' => 'sale',
            'price' => 250000,
            'area_m2' => 180,
            'bedrooms' => 4,
            'bathrooms' => 3,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Seaside Villa')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonCount(5, 'data.images');

        $this->assertDatabaseHas('business_projects', ['business_id' => $business->id, 'title' => 'Seaside Villa']);
    }

    public function test_project_rejects_more_than_5_images(): void
    {
        [$owner] = $this->realEstateOwner();

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Too Many Photos',
            'images' => ['1.jpg', '2.jpg', '3.jpg', '4.jpg', '5.jpg', '6.jpg'],
            'property_type' => 'villa',
            'listing_type' => 'sale',
        ])->assertUnprocessable()->assertJsonValidationErrors('images');
    }

    public function test_business_in_wrong_category_cannot_add_a_project(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'services']);
        Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id, 'status' => 'approved']);

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Not Allowed',
            'property_type' => 'villa',
            'listing_type' => 'sale',
        ])->assertUnprocessable();
    }

    public function test_free_tier_is_capped_at_3_listings(): void
    {
        [$owner, $business] = $this->realEstateOwner();
        BusinessProject::factory()->count(3)->create(['business_id' => $business->id]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Fourth Project',
            'property_type' => 'villa',
            'listing_type' => 'sale',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('limit');
        $this->assertEquals(3, $business->projects()->count());
    }

    public function test_package_with_higher_listing_limit_allows_more(): void
    {
        [$owner, $business] = $this->realEstateOwner();
        $package = Package::factory()->create(['listing_limit' => 10]);
        $business->update(['package_id' => $package->id]);
        BusinessProject::factory()->count(3)->create(['business_id' => $business->id]);

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Fourth Project With Package',
            'property_type' => 'villa',
            'listing_type' => 'sale',
        ])->assertCreated();

        $this->assertEquals(4, $business->projects()->count());
    }

    public function test_package_with_null_listing_limit_is_unlimited(): void
    {
        [$owner, $business] = $this->realEstateOwner();
        $package = Package::factory()->create(['listing_limit' => null]);
        $business->update(['package_id' => $package->id]);
        BusinessProject::factory()->count(20)->create(['business_id' => $business->id]);

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/projects', [
            'title' => 'Listing 21',
            'property_type' => 'villa',
            'listing_type' => 'sale',
        ])->assertCreated();
    }

    public function test_editing_a_project_sends_it_back_to_pending(): void
    {
        [$owner, $business] = $this->realEstateOwner();
        $project = BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved']);

        $this->actingAs($owner, 'sanctum')->putJson("/api/v1/dashboard/projects/{$project->id}", [
            'title' => 'Updated Title',
            'property_type' => $project->property_type,
            'listing_type' => $project->listing_type,
        ])->assertOk()->assertJsonPath('data.status', 'pending');
    }

    public function test_non_owner_cannot_edit_or_delete_a_project(): void
    {
        [, $business] = $this->realEstateOwner();
        $intruder = User::factory()->create();
        $project = BusinessProject::factory()->create(['business_id' => $business->id]);

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/dashboard/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_owner_can_delete_a_project(): void
    {
        [$owner, $business] = $this->realEstateOwner();
        $project = BusinessProject::factory()->create(['business_id' => $business->id]);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/dashboard/projects/{$project->id}")
            ->assertOk();

        $this->assertSoftDeleted('business_projects', ['id' => $project->id]);
    }

    public function test_market_owner_can_add_products(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'products']);
        $business = Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/products', [
            'name' => 'Fresh Bread',
            'price' => 2.5,
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Fresh Bread');
        $this->assertDatabaseHas('business_products', ['business_id' => $business->id, 'name' => 'Fresh Bread']);
    }

    public function test_products_are_capped_at_3_on_the_free_tier(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'products']);
        $business = Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        BusinessProduct::factory()->count(3)->create(['business_id' => $business->id]);

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/products', [
            'name' => 'One Too Many',
        ])->assertUnprocessable()->assertJsonValidationErrors('limit');
    }

    public function test_salon_owner_can_add_priced_services(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'services']);
        $business = Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/services', [
            'name' => 'Haircut',
            'price' => 15,
            'duration_minutes' => 30,
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Haircut');
        $this->assertDatabaseHas('business_services', ['business_id' => $business->id, 'name' => 'Haircut']);
    }

    public function test_services_are_capped_at_3_on_the_free_tier(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['content_type' => 'services']);
        $business = Business::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        BusinessService::factory()->count(3)->create(['business_id' => $business->id]);

        $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/services', [
            'name' => 'One Too Many',
        ])->assertUnprocessable()->assertJsonValidationErrors('limit');
    }
}
