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
        Schema::create('hasil_rekomendasi_kelas_12', function (Blueprint $table) {
            $table->increments('id');
            // FK ke sesi_tes.id_sesi_tes
            $table->unsignedInteger('id_sesi_tes');
            $table->foreign('id_sesi_tes', 'fk_hrk12_sesi')
                  ->references('id_sesi_tes')->on('sesi_tes')->onDelete('cascade');
            // FK ke program_studi.id_prodi
            $table->unsignedInteger('id_prodi');
            $table->foreign('id_prodi', 'fk_hrk12_prodi')
                  ->references('id_prodi')->on('program_studi');
            // FK ke profesi.id_profesi (karier/prospek)
            $table->unsignedInteger('id_profesi')->nullable();
            $table->foreign('id_profesi', 'fk_hrk12_profesi')
                  ->references('id_profesi')->on('profesi')->nullOnDelete();
            $table->unsignedTinyInteger('peringkat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_rekomendasi_kelas_12');
    }
};

