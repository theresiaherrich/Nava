<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_tes_riasec', function (Blueprint $t) {
            $t->increments('id_tes_riasec');
            $t->unsignedInteger('id_sesi_tes');
            $t->unsignedInteger('id_dimensi');
            $t->decimal('skor_mentah', 6, 2)->nullable();
            $t->decimal('skor_normal', 6, 2)->nullable();

            $t->unique(['id_sesi_tes', 'id_dimensi'], 'uq_sesi_dimensi');
            $t->foreign('id_sesi_tes', 'fk_skor_sesi')
              ->references('id_sesi_tes')->on('sesi_tes');
            $t->foreign('id_dimensi', 'fk_tr_dimensi')
              ->references('id_dimensi')->on('dimensi_riasec');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_tes_riasec');
    }
};
