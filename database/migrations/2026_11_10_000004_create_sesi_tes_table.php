<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUTUH tabel `riwayat_kelas` dari tabel inti (milik There).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periode_tes', function (Blueprint $t) {
            $t->increments('id_periode_tes');
            $t->unsignedInteger('id_semester');
            $t->string('nama', 120);
            $t->enum('tingkat', ['X', 'XI', 'XII']);
            $t->date('tanggal_mulai');
            $t->date('tanggal_selesai');
            $t->enum('status', ['terjadwal', 'aktif', 'selesai'])->default('terjadwal');

            $t->foreign('id_semester', 'fk_pt_semester')->references('id_semester')->on('semester');
            $t->unique(['id_semester', 'tingkat'], 'uq_periode_tes');
        });

        Schema::create('sesi_tes', function (Blueprint $t) {
            $t->increments('id_sesi_tes');
            $t->unsignedInteger('id_riwayat_kelas');
            $t->decimal('skor_r', 5, 2);
            $t->decimal('skor_i', 5, 2);
            $t->decimal('skor_a', 5, 2);
            $t->decimal('skor_s', 5, 2);
            $t->decimal('skor_e', 5, 2);
            $t->decimal('skor_c', 5, 2);
            $t->char('kode_holland', 3);
            $t->dateTime('waktu_selesai')->useCurrent();

            $t->foreign('id_riwayat_kelas', 'fk_sesi_riwayat')
                ->references('id_riwayat_kelas')->on('riwayat_kelas');
            $t->unique('id_riwayat_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_tes');
        Schema::dropIfExists('periode_tes');
    }
};
