<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $t) {
            $t->increments('id_riwayat_kelas');
            $t->unsignedInteger('id_siswa');
            $t->unsignedInteger('id_guru')->nullable();
            $t->unsignedInteger('id_kelas');

            $t->foreign('id_siswa', 'fk_rk_siswa')->references('id_siswa')->on('siswa');
            $t->foreign('id_guru', 'fk_rk_guru')->references('id_guru')->on('guru');
            $t->foreign('id_kelas', 'fk_rk_kelas')->references('id_kelas')->on('kelas');
            $t->unique(['id_siswa', 'id_kelas'], 'uq_riwayat_siswa_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};
