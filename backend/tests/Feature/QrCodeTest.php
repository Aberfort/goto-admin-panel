<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_an_svg_qr_code_for_a_known_short_code(): void
    {
        Link::factory()->for(Site::factory())->create(['short_code' => 'promo']);

        $response = $this->get('/qr/promo.svg');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_it_is_public_and_needs_no_authentication(): void
    {
        Link::factory()->for(Site::factory())->create(['short_code' => 'promo']);

        $this->get('/qr/promo.svg')->assertOk();
    }

    public function test_it_returns_404_for_an_unknown_short_code(): void
    {
        $this->get('/qr/nope.svg')->assertNotFound();
    }

    public function test_it_still_renders_for_an_inactive_link(): void
    {
        // An owner may well prepare printed material before switching the
        // link on, so this deliberately ignores is_active.
        Link::factory()->for(Site::factory())->create([
            'short_code' => 'promo',
            'is_active' => false,
        ]);

        $this->get('/qr/promo.svg')->assertOk();
    }
}
