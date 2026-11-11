<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hasil_rekomendasi_kelas_10', function (Blueprint $table) {
            $table->increments('id');
            // FK ke sesi_tes.id_sesi_tes (bukan id)
            $table->unsignedInteger('id_sesi_tes');
            $table->foreign('id_sesi_tes', 'fk_hrk10_sesi')
                  ->references('id_sesi_tes')->on('sesi_tes')->onDelete('cascade');
            // FK ke mapel.id_mapel
            $table->unsignedInteger('id_mapel');
            $table->foreign('id_mapel', 'fk_hrk10_mapel')
                  ->references('id_mapel')->on('mapel');
            $table->unsignedTinyInteger('ranking');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_rekomendasi_kelas_10');
    }
};
