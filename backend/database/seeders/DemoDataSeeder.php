<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Idempotent by design - safe to run on every deploy alongside
 * DemoUserSeeder. Gives the read-only demo account something to actually
 * look at (empty tables would defeat the point of a public demo).
 */
class DemoDataSeeder extends Seeder
{
    private const REFERRERS = [
        'https://twitter.com/',
        'https://www.google.com/',
        'https://www.facebook.com/',
        null, // direct traffic
    ];

    private const BROWSERS = [
        ['browser' => 'Chrome', 'browser_version' => '129.0', 'platform' => 'Windows', 'device_type' => 'desktop'],
        ['browser' => 'Safari', 'browser_version' => '17.6', 'platform' => 'iOS', 'device_type' => 'mobile'],
        ['browser' => 'Firefox', 'browser_version' => '131.0', 'platform' => 'macOS', 'device_type' => 'desktop'],
        ['browser' => 'Chrome', 'browser_version' => '129.0', 'platform' => 'Android', 'device_type' => 'mobile'],
    ];

    public function run(): void
    {
        $user = User::query()->where('email', DemoUserSeeder::EMAIL)->first();

        if (! $user) {
            return;
        }

        $marketing = Site::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Marketing Site'],
            ['domain' => 'example.com', 'description' => 'Landing pages and social campaigns.']
        );

        $docs = Site::query()->updateOrCreate(
            ['user_id' => $user->id, 'name' => 'Docs Site'],
            ['domain' => 'docs.example.com', 'description' => 'Public documentation redirects.']
        );

        $this->seedLink($marketing, 'homepage', 'https://example.com/', 220);
        $this->seedLink($marketing, 'twitter-promo', 'https://example.com/promo?utm_source=twitter', 140);
        $this->seedLink($marketing, 'newsletter', 'https://example.com/newsletter', 65);
        $this->seedLink($docs, 'getting-started', 'https://docs.example.com/getting-started', 95);
        $this->seedLink($docs, 'api-reference', 'https://docs.example.com/api', 40);
    }

    private function seedLink(Site $site, string $shortCode, string $targetUrl, int $clickCount): void
    {
        $link = Link::query()->updateOrCreate(
            ['site_id' => $site->id, 'short_code' => $shortCode],
            ['target_url' => $targetUrl, 'is_active' => true]
        );

        if ($link->clicks()->exists()) {
            return;
        }

        $rows = [];
        for ($i = 0; $i < $clickCount; $i++) {
            $referrer = self::REFERRERS[array_rand(self::REFERRERS)];
            $browser = self::BROWSERS[array_rand(self::BROWSERS)];

            $rows[] = [
                'link_id' => $link->id,
                'ip_hash' => hash('sha256', 'demo-seed-'.$i.'-'.$link->id),
                'referrer' => $referrer,
                'user_agent' => null,
                'browser' => $browser['browser'],
                'browser_version' => $browser['browser_version'],
                'platform' => $browser['platform'],
                'device_type' => $browser['device_type'],
                'created_at' => Carbon::now()->subDays(rand(0, 29))->subMinutes(rand(0, 1439)),
            ];
        }

        // Chunked bulk insert - this can be a few hundred rows per link,
        // no need for individual Eloquent create() calls (no events to
        // fire, no relationships to touch on each row).
        foreach (array_chunk($rows, 200) as $chunk) {
            $link->clicks()->insert($chunk);
        }

        $link->update(['clicks_count' => $clickCount]);
    }
}
