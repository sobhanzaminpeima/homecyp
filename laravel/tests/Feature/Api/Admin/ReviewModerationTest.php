<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_moderate_reviews(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/reviews')->assertForbidden();
    }

    public function test_admin_can_list_reviews_filtered_by_status(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 5, 'status' => 'approved']);
        $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 1, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/reviews?status=pending');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    public function test_admin_rejecting_a_review_recalculates_business_rating(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        $review = $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 5, 'status' => 'approved']);
        $business->recalculateRating();
        $this->assertEquals(5, $business->fresh()->rating_avg);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/reviews/{$review->id}", ['status' => 'rejected'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        $this->assertEquals(0, $business->fresh()->rating_avg);
        $this->assertEquals(0, $business->fresh()->rating_count);
    }

    public function test_admin_can_delete_a_review(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        $review = $business->reviews()->create(['user_id' => User::factory()->create()->id, 'rating' => 4, 'status' => 'approved']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/reviews/{$review->id}")
            ->assertOk();

        $this->assertSoftDeleted('reviews', ['id' => $review->id]);
    }
}
