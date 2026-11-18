<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/frontend.php';

// Seluruh API memakai middleware "web" (sesi cookie + CSRF), prefix /api.
Route::prefix('api')->group(base_path('routes/siakad.php'));
