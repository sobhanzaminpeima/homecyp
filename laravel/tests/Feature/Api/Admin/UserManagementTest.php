<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['account_type' => 'user']);

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_guest_cannot_access_admin_routes(): void
    {
        $this->getJson('/api/v1/admin/users')->assertUnauthorized();
    }

    public function test_admin_can_list_and_create_users(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/users')->assertOk();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/users', [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'account_type' => 'user',
        ]);

        $response->assertCreated()->assertJsonPath('data.email', 'staff@example.com');
    }

    public function test_cannot_delete_the_last_remaining_admin(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_cannot_demote_the_last_remaining_admin(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/users/{$admin->id}", ['account_type' => 'user'])
            ->assertUnprocessable();

        $this->assertEquals('admin', $admin->fresh()->account_type);
    }

    public function test_can_delete_an_admin_when_another_admin_remains(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $secondAdmin = User::factory()->create(['account_type' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/users/{$secondAdmin->id}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $secondAdmin->id]);
    }
}
