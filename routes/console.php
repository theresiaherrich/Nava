
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Notifikasi presensi Alpa dan Terlambat
Schedule::command('wa:kirim-presensi --kirim --maks=10')
    ->everyFiveMinutes()
    ->between('06:00', '18:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->when(fn () => config('wa.aktif'));

// Rekap presensi mingguan setiap Sabtu pukul 09.00 WIB
Schedule::command('wa:rekap-presensi --kirim')
    ->weeklyOn(6, '09:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->when(fn () => config('wa.aktif'));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
