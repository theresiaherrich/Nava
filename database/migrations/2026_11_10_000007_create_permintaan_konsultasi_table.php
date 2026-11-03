<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_konsultasi', function (Blueprint $t) {
            $t->increments('id_permintaan');
            $t->unsignedInteger('id_siswa')->nullable();
            $t->string('status', 30)->default('Menunggu'); // Menunggu, Diproses, Dijadwalkan, Selesai
            $t->text('catatan')->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->foreign('id_siswa', 'fk_pk_siswa')
              ->references('id_siswa')->on('siswa')
              ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_konsultasi');
    }
};

