<?php

namespace Tests\Feature\Api;

use App\Models\Business;
use App\Models\Deal;
use App\Models\LocalEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GrowthFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_report_and_request_a_booking(): void
    {
        $business = Business::factory()->create(['slug' => 'trusted-place']);
        $this->postJson('/api/v1/businesses/trusted-place/report', ['reason' => 'wrong_hours'])->assertCreated();
        $this->postJson('/api/v1/businesses/trusted-place/inquiries', ['type' => 'booking', 'name' => 'Alex', 'phone' => '555', 'message' => 'Tomorrow'])->assertCreated();
        $this->assertDatabaseHas('business_reports', ['business_id' => $business->id, 'reason' => 'wrong_hours']);
        $this->assertDatabaseHas('business_inquiries', ['business_id' => $business->id, 'type' => 'booking']);
    }

    public function test_authenticated_user_can_claim_a_business(): void
    {
        $user = User::factory()->create();
        $business = Business::factory()->create(['slug' => 'claim-me']);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/businesses/claim-me/claim', ['role' => 'Owner'])->assertCreated();
        $this->assertDatabaseHas('business_claims', ['business_id' => $business->id, 'user_id' => $user->id, 'status' => 'pending']);
    }

    public function test_discovery_only_returns_current_published_content(): void
    {
        $business = Business::factory()->create();
        Deal::create(['business_id' => $business->id, 'title' => 'Live deal', 'is_active' => true, 'ends_at' => now()->addDay()]);
        Deal::create(['business_id' => $business->id, 'title' => 'Expired deal', 'is_active' => true, 'ends_at' => now()->subDay()]);
        LocalEvent::create(['title' => 'Festival', 'starts_at' => now()->addDay(), 'status' => 'published']);
        LocalEvent::create(['title' => 'Draft', 'starts_at' => now()->addDay(), 'status' => 'draft']);
        $this->getJson('/api/v1/deals')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Live deal');
        $this->getJson('/api/v1/events')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Festival');
    }

    public function test_admin_can_approve_claim_and_assign_owner(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $owner = User::factory()->create();
        $business = Business::factory()->create(['owner_id' => null]);
        $id = DB::table('business_claims')->insertGetId(['business_id' => $business->id, 'user_id' => $owner->id, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        $this->actingAs($admin, 'sanctum')->putJson("/api/v1/admin/growth/claims/$id", ['status' => 'approved'])->assertOk();
        $this->assertDatabaseHas('businesses', ['id' => $business->id, 'owner_id' => $owner->id, 'is_verified' => true]);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $owner->id]);
    }
}
