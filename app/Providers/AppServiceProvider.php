<?php

namespace App\Providers;

use App\Support\Navigation;
use Illuminate\Support\Facades\View;
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
        // Menu sidebar & header per peran lewat props yang sudah disediakan komponen,
        // tanpa mengubah komponen sidebar/topbar maupun layout.
        View::composer('components.sidebar', function ($view) {
            if ($peran = Navigation::peran()) {
                $view->with('menu', Navigation::menu($peran));
            }
            $view->with('academicYear', '2026/2027 · Ganjil');
        });

        View::composer('layouts.app', function ($view) {
            $peran = Navigation::peran();
            if ($peran && ! array_key_exists('topbar', $view->getData())) {
                $view->with('topbar', Navigation::topbar($peran));
            }
        });
    }
}
