<?php

use Illuminate\Support\Facades\Route;

/*
| Route API SIAKAD A2. Dimuat dari routes/web.php dengan prefix /api dan middleware "web"
| (sesi cookie + CSRF). Satu file per modul di routes/api/.
*/

require __DIR__.'/api/auth.php';

// Semua endpoint lain: wajib login, akun aktif, dan sandi awal sudah diganti.
Route::middleware(['auth', 'account.active', 'password.changed'])->group(function () {
    require __DIR__.'/api/akun.php';
    require __DIR__.'/api/master.php';
    require __DIR__.'/api/kurikulum.php';
    require __DIR__.'/api/penilaian.php';
    require __DIR__.'/api/rapor.php';
});
