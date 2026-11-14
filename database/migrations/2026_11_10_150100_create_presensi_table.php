<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->increments('id_presensi');

            $table->unsignedInteger('id_siswa');

            $table->enum('metode', ['QR', 'Geofencing', 'Manual'])->nullable();

            $table->enum('status', [
                'Hadir',
                'Alpa',
                'Sakit',
                'Izin',
                'Terlambat'
            ])->nullable();

            $table->date('tanggal');
            $table->time('waktu_presensi')->nullable();

            $table->foreign('id_siswa')
                ->references('id_siswa')
                ->on('siswa');

            $table->index('tanggal');
            $table->index('status');
            $table->index(['id_siswa', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};