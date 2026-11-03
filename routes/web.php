<?php

use App\Http\Controllers\A3_BK\RiasecController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AUTENTIKASI (LOGIN & LOGOUT)
// ==========================================
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// 2. DASHBOARD MODUL A3 (GURU BK & SISWA)
// ==========================================
Route::get('/bk/dashboard', [RiasecController::class, 'dashboardGuru'])->name('bk.guru-dashboard');
Route::get('/siswa/dashboard', [RiasecController::class, 'dashboardSiswa'])->name('siswa.dashboard');

// Alias route untuk kompatibilitas tautan lama
Route::get('/riasec/guru-dashboard', [RiasecController::class, 'dashboardGuru']);
Route::get('/riasec/siswa-dashboard', [RiasecController::class, 'dashboardSiswa']);

// ==========================================
// 3. ALUR TES MINAT BAKAT RIASEC (BLADE VIEW)
// ==========================================
Route::get('/riasec/tes-view', function () {
    return view('bk.index');
})->name('riasec.petunjuk');

Route::get('/riasec/soal-view', function () {
    return view('bk.soal');
})->name('riasec.lembar-soal');

Route::get('/riasec/hasil-view/{idSesiTes?}', function ($idSesiTes = 1) {
    return view('bk.hasil', ['idSesiTes' => $idSesiTes]);
})->name('riasec.hasil');

// ==========================================
// 4. DATA & API ENDPOINTS TES RIASEC
// ==========================================
Route::get('/riasec/soal', [RiasecController::class, 'getSoal']);
Route::post('/riasec/submit', [RiasecController::class, 'submit']);
