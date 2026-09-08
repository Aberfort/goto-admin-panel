<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_own_sites(): void
    {
        $user = User::factory()->create();
        Site::factory()->for($user)->create(['name' => 'Mine']);
        Site::factory()->create(['name' => 'Someone Elses']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/sites');

        $response->assertOk()->assertJsonCount(1)->assertJsonFragment(['name' => 'Mine']);
    }

    public function test_user_can_create_a_site(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/sites', [
            'name' => 'My Site',
            'domain' => 'example.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('sites', ['name' => 'My Site', 'user_id' => $user->id]);
    }

    public function test_user_cannot_view_another_users_site(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $site = Site::factory()->for($owner)->create();

        $response = $this->actingAs($other, 'sanctum')->getJson("/api/sites/{$site->id}");

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_site(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $site = Site::factory()->for($owner)->create();

        $response = $this->actingAs($other, 'sanctum')->deleteJson("/api/sites/{$site->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('sites', ['id' => $site->id]);
    }

    public function test_demo_user_cannot_create_a_site(): void
    {
        $demo = User::factory()->create(['is_demo' => true]);

        $response = $this->actingAs($demo, 'sanctum')->postJson('/api/sites', [
            'name' => 'Should Not Exist',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('sites', ['name' => 'Should Not Exist']);
    }

    public function test_demo_user_can_still_read_sites(): void
    {
        $demo = User::factory()->create(['is_demo' => true]);
        Site::factory()->for($demo)->create();

        $response = $this->actingAs($demo, 'sanctum')->getJson('/api/sites');

        $response->assertOk();
    }

    public function test_site_name_must_be_unique_per_user_not_globally(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Site::factory()->for($userA)->create(['name' => 'Shared Name']);

        // Same name, different owner - allowed.
        $response = $this->actingAs($userB, 'sanctum')->postJson('/api/sites', [
            'name' => 'Shared Name',
        ]);
        $response->assertCreated();

        // Same name, same owner - rejected.
        $response = $this->actingAs($userA, 'sanctum')->postJson('/api/sites', [
            'name' => 'Shared Name',
        ]);
        $response->assertUnprocessable();
    }
}
