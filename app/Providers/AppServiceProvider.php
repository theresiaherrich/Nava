<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $identity = strtolower(trim((string) $request->input('login')));

            return [Limit::perMinute(20)->by('ip:'.$request->ip()), Limit::perMinute(5)->by('identity:'.hash('sha256', $identity))];
        });
        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
    }
}
