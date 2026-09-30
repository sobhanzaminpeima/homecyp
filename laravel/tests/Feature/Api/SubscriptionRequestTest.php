<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_packages_index_only_returns_active_packages(): void
    {
        Package::factory()->create(['is_active' => true]);
        Package::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/v1/packages');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_owner_can_request_a_subscription(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id]);
        $package = Package::factory()->create();

        $response = $this->actingAs($owner, 'sanctum')->postJson('/api/v1/dashboard/subscription-requests', [
            'package_id' => $package->id,
            'note' => 'Will pay via bank transfer',
        ]);

        $response->assertCreated()->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('subscription_requests', ['business_id' => $business->id, 'package_id' => $package->id]);
    }

    public function test_owner_cannot_submit_a_second_request_while_one_is_pending(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id]);
        $package = Package::factory()->create();
        $business->subscriptionRequests()->create(['package_id' => $package->id, 'status' => 'pending']);

        $this->actingAs($owner, 'sanctum')
            ->postJson('/api/v1/dashboard/subscription-requests', ['package_id' => $package->id])
            ->assertUnprocessable();
    }

    public function test_owner_can_list_their_own_subscription_requests(): void
    {
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => $owner->id]);
        $package = Package::factory()->create();
        $business->subscriptionRequests()->create(['package_id' => $package->id, 'status' => 'pending']);

        $response = $this->actingAs($owner, 'sanctum')->getJson('/api/v1/dashboard/subscription-requests');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_non_admin_cannot_moderate_subscription_requests(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/subscription-requests')->assertForbidden();
    }

    public function test_admin_approving_a_request_assigns_the_package_and_extends_expiry(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create(['package_id' => null]);
        $package = Package::factory()->create(['duration_days' => 30]);
        $subscriptionRequest = $business->subscriptionRequests()->create(['package_id' => $package->id, 'status' => 'pending']);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/subscription-requests/{$subscriptionRequest->id}", ['status' => 'approved']);

        $response->assertOk()->assertJsonPath('data.status', 'approved');

        $business->refresh();
        $this->assertEquals($package->id, $business->package_id);
        $this->assertTrue($business->expire_at->isAfter(now()->addDays(29)));
    }

    public function test_admin_rejecting_a_request_does_not_touch_the_business(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create(['package_id' => null]);
        $package = Package::factory()->create();
        $subscriptionRequest = $business->subscriptionRequests()->create(['package_id' => $package->id, 'status' => 'pending']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/subscription-requests/{$subscriptionRequest->id}", ['status' => 'rejected'])
            ->assertOk();

        $this->assertNull($business->fresh()->package_id);
    }
}
