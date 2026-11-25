<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Login: 5 percobaan/menit per (username, IP), ditambah batas kasar per IP.
        RateLimiter::for('login', function (Request $request) {
            $username = mb_strtolower(trim((string) $request->input('username')));

            return [
                Limit::perMinute(5)->by('login:'.hash('sha256', $username.'|'.$request->ip())),
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
        });

        // Kebijakan kata sandi kuat untuk semua pembuatan/penggantian sandi.
        Password::defaults(fn () => Password::min((int) config('siakad.password.min', 10))
            ->letters()->mixedCase()->numbers()->symbols());
    }
}
