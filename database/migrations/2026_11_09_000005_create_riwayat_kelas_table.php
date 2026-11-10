<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester', function (Blueprint $t) {
            $t->increments('id_semester');
            $t->unsignedInteger('id_tahun_ajaran');
            $t->enum('nama', ['GASAL', 'GENAP']);
            $t->boolean('aktif')->default(false);
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();

            $t->foreign('id_tahun_ajaran', 'fk_sem_ta')
                ->references('id_tahun_ajaran')->on('tahun_ajaran');
            $t->unique(['id_tahun_ajaran', 'nama'], 'uq_sem_ta');
        });

        Schema::create('riwayat_kelas', function (Blueprint $t) {
            $t->increments('id_riwayat_kelas');
            $t->unsignedInteger('id_siswa');
            $t->unsignedInteger('id_kelas');
            $t->unsignedInteger('id_semester');

            $t->foreign('id_siswa', 'fk_rk_siswa')->references('id_siswa')->on('siswa');
            $t->foreign('id_kelas', 'fk_rk_kelas')->references('id_kelas')->on('kelas');
            $t->foreign('id_semester', 'fk_rk_sem')->references('id_semester')->on('semester');
            $t->unique(['id_siswa', 'id_semester'], 'uq_riwayat_siswa_sem');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
        Schema::dropIfExists('semester');
    }
};
