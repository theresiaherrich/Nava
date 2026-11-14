<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->increments('id_kelas');
            $table->string('nama', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->enum('fase', ['E', 'F']);
            $table->unsignedInteger('id_tahun_ajaran');

            $table->foreign('id_tahun_ajaran')
                ->references('id_tahun_ajaran')
                ->on('tahun_ajaran');

            $table->unique(
                ['nama', 'id_tahun_ajaran'],
                'uq_kelas_ta'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};