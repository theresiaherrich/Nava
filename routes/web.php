<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['application' => config('app.name'), 'status' => 'ok']));

// Seluruh API memakai middleware "web" (sesi cookie + CSRF), prefix /api.
Route::prefix('api')->group(base_path('routes/siakad.php'));
