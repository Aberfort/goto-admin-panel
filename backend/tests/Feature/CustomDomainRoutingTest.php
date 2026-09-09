<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Link;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomDomainRoutingTest extends TestCase
{
    use RefreshDatabase;

    /** Simulates the request arriving with a given Host header. */
    private function onHost(string $host, string $path)
    {
        return $this->get("http://{$host}{$path}");
    }

    public function test_a_verified_domain_resolves_its_own_sites_link(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->verified()->create(['host' => 'go.acme.test']);
        Link::factory()->for($site)->create([
            'short_code' => 'promo',
            'target_url' => 'https://acme.test/promo',
        ]);

        $this->onHost('go.acme.test', '/promo')->assertRedirect('https://acme.test/promo');
        $this->assertDatabaseCount('clicks', 1);
    }

    public function test_a_domain_never_resolves_another_sites_link(): void
    {
        $mine = Site::factory()->create();
        $theirs = Site::factory()->create();
        Domain::factory()->for($mine)->verified()->create(['host' => 'go.acme.test']);
        Link::factory()->for($theirs)->create(['short_code' => 'secret']);

        $this->onHost('go.acme.test', '/secret')->assertNotFound();
        $this->assertDatabaseCount('clicks', 0);
    }

    public function test_an_unverified_domain_resolves_nothing(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->create(['host' => 'go.acme.test']);
        Link::factory()->for($site)->create(['short_code' => 'promo']);

        $this->onHost('go.acme.test', '/promo')->assertNotFound();
    }

    public function test_an_unknown_host_resolves_nothing(): void
    {
        $site = Site::factory()->create();
        Link::factory()->for($site)->create(['short_code' => 'promo']);

        $this->onHost('nobody.test', '/promo')->assertNotFound();
    }

    public function test_the_shared_r_route_keeps_working_alongside_custom_domains(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->verified()->create(['host' => 'go.acme.test']);
        Link::factory()->for($site)->create([
            'short_code' => 'promo',
            'target_url' => 'https://acme.test/promo',
        ]);

        $this->get('/r/promo')->assertRedirect('https://acme.test/promo');
    }

    public function test_the_catch_all_does_not_shadow_the_apps_own_routes(): void
    {
        $site = Site::factory()->create();
        Link::factory()->for($site)->create(['short_code' => 'promo']);

        // Health check, root and the QR endpoint must all still answer as
        // themselves rather than being eaten by /{code}.
        $this->get('/')->assertOk()->assertJsonPath('status', 'ok');
        $this->get('/up')->assertOk();
        $this->get('/qr/promo.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
    }

    public function test_password_gate_on_a_custom_domain_posts_back_to_that_domain(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->verified()->create(['host' => 'go.acme.test']);
        $link = Link::factory()->for($site)->create([
            'short_code' => 'locked',
            'target_url' => 'https://acme.test/vault',
        ]);
        $link->forceFill(['password' => Hash::make('letmein')])->save();

        // The form action stays relative so the visitor never leaves the
        // branded host mid-flow.
        $this->onHost('go.acme.test', '/locked')
            ->assertOk()
            ->assertSee('action="/locked"', false);

        $this->post('http://go.acme.test/locked', ['password' => 'letmein'])
            ->assertRedirect('https://acme.test/vault');

        $this->assertDatabaseCount('clicks', 1);
    }

    public function test_expired_link_on_a_custom_domain_still_answers_410(): void
    {
        $site = Site::factory()->create();
        Domain::factory()->for($site)->verified()->create(['host' => 'go.acme.test']);
        Link::factory()->for($site)->create([
            'short_code' => 'old',
            'expires_at' => now()->subDay(),
        ]);

        $this->onHost('go.acme.test', '/old')->assertStatus(410);
    }
}
