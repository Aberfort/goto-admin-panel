<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Single source of truth for "the demo account is read-only" -
        // every Policy's write abilities fall through here regardless of
        // resource type, instead of repeating an is_demo check in each one.
        Gate::before(function ($user, string $ability) {
            if ($user->is_demo && ! in_array($ability, ['viewAny', 'view'], true)) {
                return false;
            }

            return null;
        });
    }
}
