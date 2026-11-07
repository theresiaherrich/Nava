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

// A3 PHASE 2: student grade X only
Route::middleware('auth')->prefix('a3/siswa/x')->name('a3.x.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\A3_BK\StudentXController::class, 'dashboard'])->name('dashboard');
    Route::get('/tes', [\App\Http\Controllers\A3_BK\StudentXController::class, 'instructions'])->name('instructions');
    Route::get('/soal', [\App\Http\Controllers\A3_BK\StudentXController::class, 'questions'])->name('questions');
    Route::post('/soal', [\App\Http\Controllers\A3_BK\StudentXController::class, 'submit'])->name('submit');
    Route::get('/hasil', [\App\Http\Controllers\A3_BK\StudentXController::class, 'latestResult'])->name('latest');
    Route::get('/hasil/{sessionId}', [\App\Http\Controllers\A3_BK\StudentXController::class, 'result'])->whereNumber('sessionId')->name('result');
    Route::get('/rekomendasi', [\App\Http\Controllers\A3_BK\StudentXController::class, 'recommendations'])->name('recommendations');
    Route::get('/riwayat', [\App\Http\Controllers\A3_BK\StudentXController::class, 'history'])->name('history');
    Route::get('/konseling', [\App\Http\Controllers\A3_BK\StudentXController::class, 'counseling'])->name('counseling');
    Route::post('/konseling', [\App\Http\Controllers\A3_BK\StudentXController::class, 'storeCounseling'])->name('counseling.store');
});
