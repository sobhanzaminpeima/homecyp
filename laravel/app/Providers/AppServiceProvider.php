<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
        // Required for MySQL 5.7 / MariaDB 10.4 shared hosting (utf8mb4 index key length).
        Schema::defaultStringLength(191);

        // Named limiters keyed by email+IP so brute-forcing one account (or one
        // endpoint) can't lock other users out of login/register/reset — unlike
        // a bare "throttle:N,1" string, whose key ignores the route entirely and
        // is shared by every route that uses it.
        RateLimiter::for('login', fn ($request) => Limit::perMinute(10)->by(strtolower((string) $request->input('email')) . '|' . $request->ip()));
        RateLimiter::for('register', fn ($request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('password-reset', fn ($request) => Limit::perMinute(5)->by(strtolower((string) $request->input('email')) . '|' . $request->ip()));
    }
}
