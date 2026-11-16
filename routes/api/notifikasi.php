<?php

use App\Http\Controllers\NotifikasiController;
use Illuminate\Support\Facades\Route;

// Semua peran: notifikasi milik sendiri.
Route::get('/notifikasi', [NotifikasiController::class, 'index']);
Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua']);
Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->whereNumber('id');
