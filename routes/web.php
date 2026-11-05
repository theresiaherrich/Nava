<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RombelController;
use App\Http\Controllers\Api\TpController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')
    ->middleware('auth')
    ->group(function () {
        // GET daftar rombel
        Route::get('/rombel', [App\Http\Controllers\Api\RombelController::class, 'index']);

        // POST tambah rombel
        Route::post('/rombel', [App\Http\Controllers\Api\RombelController::class, 'store']);

        // GET detail rombel
        Route::get('/rombel/{rombel}', [App\Http\Controllers\Api\RombelController::class, 'show']);

        // PUT edit rombel
        Route::put('/rombel/{rombel}', [App\Http\Controllers\Api\RombelController::class, 'update']);

        // PUT penetapan guru
        Route::put('/rombel/{rombel}/guru', [App\Http\Controllers\Api\RombelController::class, 'setGuru']);

        // POST tambah peserta didik ke rombel
        Route::post('/rombel/{rombel}/peserta', [App\Http\Controllers\Api\RombelController::class, 'addPeserta']);

        // MODUL AKADEMIK - TUJUAN PEMBELAJARAN (TP)
        // Menampilkan daftar TP milik rombel
        Route::get('/rombel/{rombel}/tp', [TpController::class, 'index']);

        // Menambahkan TP ke rombel
        Route::post('/rombel/{rombel}/tp', [TpController::class, 'store']);

        // Memperbarui TP
        Route::put('/tp/{tp}', [TpController::class, 'update']);

        // Menghapus TP
        Route::delete('/tp/{tp}', [TpController::class, 'destroy']);

        // Menyalin TP antarrombel
        Route::post('/tp/copy', [TpController::class, 'copy']);
    });