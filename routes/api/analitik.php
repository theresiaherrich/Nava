<?php

use App\Http\Controllers\Analitik\AnalitikController;
use Illuminate\Support\Facades\Route;

// Cakupan data diterapkan di controller sesuai peran aktif.
Route::prefix('analitik')->middleware('role:KURIKULUM,WALI_KELAS,GURU')->group(function () {
    Route::get('/sebaran', [AnalitikController::class, 'sebaran']);
    Route::get('/ketuntasan', [AnalitikController::class, 'ketuntasan']);
    Route::get('/early-warning', [AnalitikController::class, 'earlyWarning']);
    Route::get('/siswa/{siswa}/tren', [AnalitikController::class, 'trenSiswa']);
});

Route::middleware('role:SISWA,ORANG_TUA')->get('/portal/analitik/tren', [AnalitikController::class, 'trenPortal']);
