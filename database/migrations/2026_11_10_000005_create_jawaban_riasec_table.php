<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawaban_riasec', function (Blueprint $t) {
            $t->increments('id_jawaban_riasec');
            $t->unsignedInteger('id_sesi_tes');
            $t->unsignedInteger('id_soal_riasec');
            $t->decimal('nilai', 6, 2)->nullable();

            $t->foreign('id_sesi_tes', 'fk_jawaban_sesi')
              ->references('id_sesi_tes')->on('sesi_tes');
            $t->foreign('id_soal_riasec', 'fk_jawaban_soal')
              ->references('id_soal_riasec')->on('soal_riasec');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_riasec');
    }
};
