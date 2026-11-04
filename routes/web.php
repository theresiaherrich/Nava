<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RombelController;

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
    });