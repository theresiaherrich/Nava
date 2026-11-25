<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->increments('id_riwayat_kelas');
            $table->unsignedInteger('id_siswa');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_semester');

            $table->foreign('id_siswa')
                ->references('id_siswa')
                ->on('siswa');

            $table->foreign('id_kelas')
                ->references('id_kelas')
                ->on('kelas');

            $table->foreign('id_semester')
                ->references('id_semester')
                ->on('semester');

            $table->unique(
                ['id_siswa', 'id_semester'],
                'uq_riwayat_siswa_sem'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};