<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester', function (Blueprint $table) {
            $table->increments('id_semester');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->enum('nama', ['GASAL', 'GENAP']);
            $table->boolean('aktif')->default(false);
            $table->timestamps();

            $table->foreign('id_tahun_ajaran')
                ->references('id_tahun_ajaran')
                ->on('tahun_ajaran');

            $table->unique(
                ['id_tahun_ajaran', 'nama'],
                'uq_sem_ta'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester');
    }
};