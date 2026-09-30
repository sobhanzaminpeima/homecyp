<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_a_review(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($user, 'sanctum')->postJson("/api/v1/businesses/{$business->slug}/reviews", [
            'rating' => 5,
            'body' => 'Excellent service!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.is_own', true);

        $business->refresh();
        $this->assertEquals(5, $business->rating_avg);
        $this->assertEquals(1, $business->rating_count);
    }

    public function test_submitting_a_review_twice_updates_it_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);

        $this->actingAs($user, 'sanctum')->postJson("/api/v1/businesses/{$business->slug}/reviews", ['rating' => 3]);
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/businesses/{$business->slug}/reviews", ['rating' => 5]);

        $this->assertDatabaseCount('reviews', 1);
        $this->assertEquals(5, $business->fresh()->rating_avg);
    }

    public function test_guest_cannot_submit_a_review(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);

        $this->postJson("/api/v1/businesses/{$business->slug}/reviews", ['rating' => 4])
            ->assertUnauthorized();
    }

    public function test_rating_must_be_between_1_and_5(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/businesses/{$business->slug}/reviews", ['rating' => 6])
            ->assertUnprocessable();
    }

    public function test_index_only_returns_approved_reviews(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 5, 'status' => 'approved']);
        $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'status' => 'rejected']);

        $response = $this->getJson("/api/v1/businesses/{$business->slug}/reviews");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_user_can_delete_their_own_review(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);
        $review = $business->reviews()->create(['user_id' => $user->id, 'rating' => 4, 'status' => 'approved']);
        $business->recalculateRating();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/businesses/{$business->slug}/reviews/{$review->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id, 'deleted_at' => null]);
        $this->assertEquals(0, $business->fresh()->rating_count);
    }

    public function test_user_cannot_delete_someone_elses_review(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $business = Business::factory()->create(['status' => 'approved']);
        $review = $business->reviews()->create(['user_id' => $owner->id, 'rating' => 4, 'status' => 'approved']);

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/v1/businesses/{$business->slug}/reviews/{$review->id}")
            ->assertForbidden();
    }
}
