<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API SIAKAD memakai middleware "web" agar sesi dan proteksi CSRF berjalan.
Route::middleware('web')->prefix('api')->group(base_path('routes/siakad.php'));