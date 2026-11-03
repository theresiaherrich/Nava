<?php

use App\Http\Controllers\RiasecController;

// ==========================================
// ROUTE FRONTEND VIEW (BLADE)
// ==========================================
Route::get('/riasec/tes-view', function () {
    return view('bk.riasec.index');
});
Route::get('/riasec/soal-view', function () {
    return view('bk.riasec.soal');
});
Route::get('/riasec/hasil-view/{idSesiTes}', function () {
    return view('bk.riasec.hasil');
});
Route::get('/riasec/guru-dashboard', function () {
    return view('bk.riasec.dashboard_guru');
});

// ==========================================
// ROUTE BACKEND LOGIC (DATA ENDPOINTS)
// ==========================================
// Endpoint Siswa
Route::get('/riasec/soal', [RiasecController::class, 'index']);
Route::post('/riasec/submit', [RiasecController::class, 'submit']);
Route::get('/riasec/hasil/{idSesiTes}', [RiasecController::class, 'hasil']);

// Endpoint Guru BK
Route::get('/riasec/dashboard-data', [RiasecController::class, 'dashboardGuru']);
Route::get('/riasec/detail-siswa/{idSesiTes}', [RiasecController::class, 'detailSiswa']);