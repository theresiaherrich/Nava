<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal_riasec', function (Blueprint $t) {
            $t->increments('id_soal_riasec');
            $t->unsignedInteger('id_dimensi');
            $t->text('teks');
            $t->integer('urutan')->nullable();

            $t->foreign('id_dimensi', 'fk_sr_dimensi')
              ->references('id_dimensi')->on('dimensi_riasec');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal_riasec');
    }
};
