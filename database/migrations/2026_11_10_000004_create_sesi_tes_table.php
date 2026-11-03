<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// BUTUH tabel `riwayat_kelas` dari tabel inti (milik There).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_tes', function (Blueprint $t) {
            $t->increments('id_sesi_tes');
            $t->unsignedInteger('id_riwayat_kelas');
            $t->enum('tingkat', ['X', 'XII']);
            $t->string('kode_holland', 6)->nullable();
            $t->dateTime('waktu_mulai')->nullable();
            $t->dateTime('waktu_selesai')->nullable();

            $t->foreign('id_riwayat_kelas', 'fk_sesi_riwayat')
              ->references('id_riwayat_kelas')->on('riwayat_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_tes');
    }
};
