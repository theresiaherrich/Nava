<?php

use App\Http\Controllers\RiasecController;

// Route untuk Modul RIASEC BK
Route::get('/riasec/soal', [RiasecController::class, 'index']);
Route::post('/riasec/submit', [RiasecController::class, 'submit']);
Route::get('/riasec/hasil/{idSesiTes}', [RiasecController::class, 'hasil']);