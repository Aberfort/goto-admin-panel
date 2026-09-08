<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'target_url' => fake()->url(),
            'is_active' => true,
            'clicks_count' => 0,
        ];
    }
}
