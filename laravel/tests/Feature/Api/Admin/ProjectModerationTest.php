<?php

namespace Tests\Feature\Api\Admin;

use App\Models\Business;
use App\Models\BusinessProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_moderate_projects(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/projects')->assertForbidden();
    }

    public function test_admin_can_list_projects_filtered_by_status(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'pending']);
        BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'approved']);

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/projects?status=pending');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_approve_a_project(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        $project = BusinessProject::factory()->create(['business_id' => $business->id, 'status' => 'pending']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/projects/{$project->id}", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_delete_a_project(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $business = Business::factory()->create();
        $project = BusinessProject::factory()->create(['business_id' => $business->id]);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/projects/{$project->id}")
            ->assertOk();

        $this->assertSoftDeleted('business_projects', ['id' => $project->id]);
    }
}
