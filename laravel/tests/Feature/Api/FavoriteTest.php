<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_toggle_favorite_on_and_off(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);

        $on = $this->actingAs($user, 'sanctum')->postJson("/api/v1/businesses/{$business->slug}/favorite");
        $on->assertOk()->assertJson(['favorited' => true]);
        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'business_id' => $business->id]);

        $off = $this->actingAs($user, 'sanctum')->postJson("/api/v1/businesses/{$business->slug}/favorite");
        $off->assertOk()->assertJson(['favorited' => false]);
        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'business_id' => $business->id]);
    }

    public function test_guest_cannot_toggle_favorite(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);

        $this->postJson("/api/v1/businesses/{$business->slug}/favorite")->assertUnauthorized();
    }

    public function test_favorites_index_only_lists_the_current_users_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $favorited = Business::factory()->create(['status' => 'approved']);
        $notFavorited = Business::factory()->create(['status' => 'approved']);

        $favorited->favorites()->create(['user_id' => $user->id]);
        $notFavorited->favorites()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/favorites');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($favorited->id));
        $this->assertFalse($ids->contains($notFavorited->id));
    }
}
