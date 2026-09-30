<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_approved_businesses(): void
    {
        Business::factory()->create(['status' => 'approved']);
        Business::factory()->create(['status' => 'pending']);
        Business::factory()->create(['status' => 'rejected']);

        $response = $this->getJson('/api/v1/businesses');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_index_filters_by_city_and_category_slug(): void
    {
        $city = City::factory()->create(['slug' => 'kyrenia']);
        $otherCity = City::factory()->create();
        Business::factory()->create(['status' => 'approved', 'city_id' => $city->id]);
        Business::factory()->create(['status' => 'approved', 'city_id' => $otherCity->id]);

        $response = $this->getJson('/api/v1/businesses?city=kyrenia');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_show_returns_business_by_slug_and_increments_view_count(): void
    {
        $business = Business::factory()->create(['status' => 'approved', 'slug' => 'my-cafe', 'view_count' => 0]);

        $response = $this->getJson('/api/v1/businesses/my-cafe');

        $response->assertOk()->assertJsonPath('data.slug', 'my-cafe');
        $this->assertEquals(1, $business->fresh()->view_count);
    }

    public function test_show_returns_404_for_unapproved_business(): void
    {
        Business::factory()->create(['status' => 'pending', 'slug' => 'hidden-biz']);

        $this->getJson('/api/v1/businesses/hidden-biz')->assertNotFound();
    }

    public function test_authenticated_user_can_create_a_business(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/businesses', [
            'name' => 'New Business',
            'city_id' => $city->id,
            'category_id' => $category->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Business')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.city.id', $city->id);

        $this->assertDatabaseHas('businesses', ['name' => 'New Business', 'owner_id' => $user->id]);
    }

    public function test_guest_cannot_create_a_business(): void
    {
        $city = City::factory()->create();
        $category = Category::factory()->create();

        $this->postJson('/api/v1/businesses', [
            'name' => 'New Business',
            'city_id' => $city->id,
            'category_id' => $category->id,
        ])->assertUnauthorized();
    }

    public function test_owner_can_update_their_own_business(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id, 'status' => 'approved']);

        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/businesses/{$business->id}", [
            'name' => $business->name,
            'city_id' => $business->city_id,
            'category_id' => $business->category_id,
            'phone' => '+90 555 111 2233',
        ]);

        $response->assertOk()->assertJsonPath('data.phone', '+90 555 111 2233');
    }

    public function test_editing_an_approved_business_sends_it_back_to_pending(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id, 'status' => 'approved']);

        $this->actingAs($owner, 'sanctum')->putJson("/api/v1/businesses/{$business->id}", [
            'name' => $business->name,
            'city_id' => $business->city_id,
            'category_id' => $business->category_id,
            'description' => 'Updated description',
        ])->assertOk()->assertJsonPath('data.status', 'pending');
    }

    public function test_non_owner_cannot_update_someone_elses_business(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($intruder, 'sanctum')->putJson("/api/v1/businesses/{$business->id}", [
            'name' => 'Hijacked name',
            'city_id' => $business->city_id,
            'category_id' => $business->category_id,
        ])->assertForbidden();
    }

    public function test_index_sorts_by_distance_when_requested(): void
    {
        // Kyrenia harbour as the "user" location.
        $near = Business::factory()->create(['status' => 'approved', 'lat' => 35.341, 'lng' => 33.319]);
        $far = Business::factory()->create(['status' => 'approved', 'lat' => 35.185, 'lng' => 33.382]); // ~Nicosia
        $noCoords = Business::factory()->create(['status' => 'approved', 'lat' => null, 'lng' => null]);

        $response = $this->getJson('/api/v1/businesses?sort=distance&lat=35.341&lng=33.319');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');

        $this->assertEquals($near->id, $ids->first());
        $this->assertFalse($ids->contains($noCoords->id));
        $this->assertNotNull($response->json('data.0.distance_km'));
        $this->assertEquals(0.0, $response->json('data.0.distance_km'));
        $this->assertTrue($ids->search($far->id) > $ids->search($near->id));
    }

    public function test_similar_excludes_the_business_itself_and_matches_category(): void
    {
        $category = Category::factory()->create();
        $business = Business::factory()->create(['status' => 'approved', 'category_id' => $category->id, 'slug' => 'main-biz']);
        $sameCategory = Business::factory()->create(['status' => 'approved', 'category_id' => $category->id]);
        Business::factory()->create(['status' => 'approved']);

        $response = $this->getJson('/api/v1/businesses/main-biz/similar');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($sameCategory->id));
        $this->assertFalse($ids->contains($business->id));
    }
}
