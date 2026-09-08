<?php

namespace Database\Factories;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->company(),
            'domain' => fake()->domainName(),
            'description' => fake()->sentence(),
        ];
    }
}
