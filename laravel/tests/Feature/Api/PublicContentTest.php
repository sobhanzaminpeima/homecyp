<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_index_only_returns_active_cities(): void
    {
        City::factory()->create(['is_active' => true]);
        City::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/cities');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_categories_index_only_returns_top_level_active_categories_with_children(): void
    {
        $parent = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        Category::factory()->create(['is_active' => true, 'parent_id' => $parent->id]);
        Category::factory()->create(['is_active' => false, 'parent_id' => null]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertCount(1, $response->json('data.0.children'));
    }

    public function test_settings_index_exposes_only_the_safe_public_subset(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertOk()->assertJsonStructure(['ios_app_url', 'android_app_url']);
        $this->assertArrayNotHasKey('support_email', $response->json());
    }

    public function test_home_returns_featured_and_popular_businesses(): void
    {
        Business::factory()->create(['status' => 'approved', 'is_featured' => true]);
        Business::factory()->create(['status' => 'approved', 'is_featured' => false, 'view_count' => 50]);
        Business::factory()->create(['status' => 'pending', 'is_featured' => true]);

        $response = $this->getJson('/api/v1/home');

        $response->assertOk()->assertJsonStructure(['featured', 'popular', 'ads', 'view_counter', 'counter_label']);
        $this->assertCount(1, $response->json('featured'));
        $this->assertCount(2, $response->json('popular'));
    }

    public function test_home_filters_by_city_when_given(): void
    {
        $city = City::factory()->create(['slug' => 'kyrenia']);
        $otherCity = City::factory()->create();
        Business::factory()->create(['status' => 'approved', 'is_featured' => true, 'city_id' => $city->id]);
        Business::factory()->create(['status' => 'approved', 'is_featured' => true, 'city_id' => $otherCity->id]);

        $response = $this->getJson('/api/v1/home?city=kyrenia');

        $response->assertOk();
        $this->assertCount(1, $response->json('featured'));
    }
}
