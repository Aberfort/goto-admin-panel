<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Site;
use App\Models\User;
use App\Support\DnsTxtLookup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    /** Swaps the DNS seam so no test ever hits the network. */
    private function fakeDns(array $valuesByName): void
    {
        $this->instance(DnsTxtLookup::class, new class($valuesByName) extends DnsTxtLookup
        {
            public function __construct(private array $valuesByName) {}

            public function txtValues(string $name): array
            {
                return $this->valuesByName[$name] ?? [];
            }
        });
    }

    public function test_show_reports_null_when_the_site_has_no_domain(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/sites/{$site->id}/domain")
            ->assertOk()
            ->assertExactJson(['domain' => null]);
    }

    public function test_show_returns_the_attached_domain(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();
        $domain = Domain::factory()->for($site)->create(['host' => 'go.example.com']);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/sites/{$site->id}/domain")
            ->assertOk()
            ->assertJsonPath('domain.id', $domain->id)
            ->assertJsonPath('domain.host', 'go.example.com')
            ->assertJsonPath('domain.is_verified', false);
    }

    public function test_show_is_denied_for_someone_elses_site(): void
    {
        $site = Site::factory()->for(User::factory())->create();

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->getJson("/api/sites/{$site->id}/domain")
            ->assertForbidden();
    }

    public function test_owner_can_attach_a_domain_to_their_site(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'go.example.com']);

        $response->assertCreated()
            ->assertJsonPath('host', 'go.example.com')
            ->assertJsonPath('is_verified', false)
            ->assertJsonPath('txt_record_name', '_linkfleet.go.example.com');

        $this->assertNotEmpty($response->json('verification_token'));
    }

    public function test_it_normalizes_a_pasted_url_down_to_the_host(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'HTTPS://Go.Example.com/some/path'])
            ->assertCreated()
            ->assertJsonPath('host', 'go.example.com');
    }

    public function test_it_rejects_a_host_already_claimed_by_someone_else(): void
    {
        $other = Site::factory()->create();
        Domain::factory()->for($other)->create(['host' => 'go.example.com']);

        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'go.example.com'])
            ->assertUnprocessable();
    }

    public function test_it_rejects_the_apps_own_host(): void
    {
        config(['app.url' => 'https://links.myapp.test']);
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'links.myapp.test'])
            ->assertUnprocessable();
    }

    public function test_it_rejects_a_malformed_host(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();

        foreach (['no-dot', '-bad.example.com', 'spaces here.com'] as $host) {
            $this->actingAs($user, 'sanctum')
                ->postJson("/api/sites/{$site->id}/domain", ['host' => $host])
                ->assertUnprocessable();
        }
    }

    public function test_verification_succeeds_when_the_txt_record_matches(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();
        $domain = Domain::factory()->for($site)->create(['host' => 'go.example.com']);

        $this->fakeDns(['_linkfleet.go.example.com' => ['unrelated', $domain->verification_token]]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/domains/{$domain->id}/verify")
            ->assertOk()
            ->assertJsonPath('is_verified', true);

        $this->assertNotNull($domain->fresh()->verified_at);
    }

    public function test_verification_fails_when_the_record_is_missing(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();
        $domain = Domain::factory()->for($site)->create(['host' => 'go.example.com']);

        $this->fakeDns([]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/domains/{$domain->id}/verify")
            ->assertUnprocessable();

        $this->assertNull($domain->fresh()->verified_at);
    }

    public function test_user_cannot_attach_a_domain_to_another_users_site(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $site = Site::factory()->for($owner)->create();

        $this->actingAs($other, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'go.example.com'])
            ->assertForbidden();
    }

    public function test_user_cannot_verify_another_users_domain(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $domain = Domain::factory()->for(Site::factory()->for($owner))->create();

        $this->actingAs($other, 'sanctum')
            ->postJson("/api/domains/{$domain->id}/verify")
            ->assertForbidden();
    }

    public function test_demo_user_cannot_attach_a_domain(): void
    {
        $demo = User::factory()->create(['is_demo' => true]);
        $site = Site::factory()->for($demo)->create();

        $this->actingAs($demo, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'go.example.com'])
            ->assertForbidden();
    }

    public function test_attaching_a_second_domain_replaces_the_first(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->for($user)->create();
        Domain::factory()->for($site)->create(['host' => 'old.example.com']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/sites/{$site->id}/domain", ['host' => 'new.example.com'])
            ->assertCreated();

        $this->assertDatabaseMissing('domains', ['host' => 'old.example.com']);
        $this->assertDatabaseHas('domains', ['host' => 'new.example.com']);
    }

    public function test_owner_can_detach_a_domain(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->for(Site::factory()->for($user))->create();

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/domains/{$domain->id}")
            ->assertNoContent();

        $this->assertDatabaseCount('domains', 0);
    }
}
