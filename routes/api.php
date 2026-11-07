<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaController;

Route::get('/siswa', [SiswaController::class, 'index']);
Route::post('/siswa', [SiswaController::class, 'store']);