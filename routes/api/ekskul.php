<?php

use App\Http\Controllers\Ekskul\EkskulController;
use Illuminate\Support\Facades\Route;

Route::prefix('kesiswaan')->middleware('role:KESISWAAN')->group(function () {
    Route::get('/dashboard', [EkskulController::class, 'dashboard']);
    Route::get('/ekskul', [EkskulController::class, 'index']);
    Route::post('/ekskul', [EkskulController::class, 'store']);
    Route::put('/ekskul/{ekskul}', [EkskulController::class, 'update']);
    Route::delete('/ekskul/{ekskul}', [EkskulController::class, 'destroy']);
    Route::get('/ekskul/{ekskul}/peserta', [EkskulController::class, 'peserta']);
    Route::post('/ekskul/{ekskul}/peserta', [EkskulController::class, 'tambahPeserta']);
    Route::put('/ekskul/{ekskul}/predikat', [EkskulController::class, 'predikat']);
    Route::delete('/peserta-ekskul/{peserta}', [EkskulController::class, 'hapusPeserta']);
});
