<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'host' => 'go.'.fake()->unique()->domainName(),
            'verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verified_at' => now()]);
    }
}
