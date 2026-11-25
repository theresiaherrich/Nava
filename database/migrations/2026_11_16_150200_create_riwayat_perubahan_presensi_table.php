<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_perubahan_presensi', function (Blueprint $table) {
            $table->id('id_riwayat');

            $table->unsignedInteger('id_presensi');
            $table->unsignedInteger('id_user');

            $table->enum('status_sebelumnya', [
                'Hadir',
                'Alpa',
                'Sakit',
                'Izin',
                'Terlambat',
            ])->nullable();

            $table->enum('status_baru', [
                'Hadir',
                'Alpa',
                'Sakit',
                'Izin',
                'Terlambat',
            ]);

            $table->text('alasan_perubahan')->nullable();
            $table->timestamp('waktu_perubahan')->useCurrent();

            $table->foreign('id_presensi')
                ->references('id_presensi')
                ->on('presensi')
                ->restrictOnDelete();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_presensi');
    }
};