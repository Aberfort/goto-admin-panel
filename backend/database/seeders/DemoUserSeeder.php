<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Idempotent by design (updateOrCreate) - this runs on every deploy
 * (see backend/docker/railway-start.sh), not just once.
 */
class DemoUserSeeder extends Seeder
{
    public const EMAIL = 'demo@linkfleet.app';

    public const PASSWORD = 'demo12345';

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Demo',
                'password' => Hash::make(self::PASSWORD),
                'is_demo' => true,
            ]
        );
    }
}
