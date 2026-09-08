<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_a_short_code_redirects_to_the_target_url(): void
    {
        $link = Link::factory()->for(Site::factory())->create([
            'short_code' => 'abc123',
            'target_url' => 'https://example.com/target',
        ]);

        $response = $this->get('/r/abc123');

        $response->assertRedirect('https://example.com/target');
    }

    public function test_visiting_a_short_code_records_a_click(): void
    {
        $link = Link::factory()->for(Site::factory())->create(['short_code' => 'abc123']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/129.0.0.0 Safari/537.36',
            'Referer' => 'https://twitter.com/some/post?query=x',
        ])->get('/r/abc123');

        $this->assertDatabaseCount('clicks', 1);
        $this->assertEquals(1, $link->fresh()->clicks_count);

        $click = $link->clicks()->first();
        $this->assertEquals('twitter.com', $click->referrer);
        $this->assertEquals('Chrome', $click->browser);
        $this->assertEquals('desktop', $click->device_type);
    }

    public function test_click_never_stores_the_raw_visitor_ip(): void
    {
        $link = Link::factory()->for(Site::factory())->create(['short_code' => 'abc123']);

        $this->call('GET', '/r/abc123', server: ['REMOTE_ADDR' => '203.0.113.42']);

        $click = $link->clicks()->first();
        $this->assertNotNull($click->ip_hash);
        $this->assertStringNotContainsString('203.0.113.42', $click->ip_hash);
        $this->assertNotEquals('203.0.113.42', $click->ip_hash);
    }

    public function test_unknown_short_code_returns_404(): void
    {
        $response = $this->get('/r/does-not-exist');

        $response->assertNotFound();
    }

    public function test_inactive_link_returns_404_and_does_not_record_a_click(): void
    {
        $link = Link::factory()->for(Site::factory())->create([
            'short_code' => 'abc123',
            'is_active' => false,
        ]);

        $response = $this->get('/r/abc123');

        $response->assertNotFound();
        $this->assertDatabaseCount('clicks', 0);
    }
}
