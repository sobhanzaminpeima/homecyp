<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Clean up anything actually written to the shared docroot/uploads dir.
        $uploadsDir = dirname(base_path()) . '/uploads';
        if (is_dir($uploadsDir)) {
            foreach (glob($uploadsDir . '/*') as $file) {
                @unlink($file);
            }
        }

        parent::tearDown();
    }

    public function test_business_owner_can_upload_a_valid_image(): void
    {
        $user = User::factory()->create();
        // create() (not image()) — the sandbox's PHP build has no GD extension,
        // and create() doesn't need one; Laravel's file-type validation trusts
        // the declared mime type for fake uploads in tests either way.
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertOk()->assertJsonStructure(['url']);
        $this->assertStringEndsWith('.png', $response->json('url'));
    }

    public function test_svg_upload_is_rejected(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('malicious.svg', '<svg onload="alert(1)"></svg>');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', ['file' => $file]);

        $response->assertUnprocessable()->assertJsonValidationErrors('file');
    }

    public function test_guest_cannot_upload(): void
    {
        $file = UploadedFile::fake()->create('logo.png', 100, 'image/png');

        $this->postJson('/api/v1/uploads', ['file' => $file])->assertUnauthorized();
    }

    public function test_admin_upload_endpoint_also_rejects_svg(): void
    {
        $admin = User::factory()->create(['account_type' => 'admin']);
        $file = UploadedFile::fake()->createWithContent('malicious.svg', '<svg onload="alert(1)"></svg>');

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/uploads', ['file' => $file])
            ->assertUnprocessable();
    }
}
