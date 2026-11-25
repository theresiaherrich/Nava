<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin', function (Blueprint $table) {
            $table->increments('id_izin');

            $table->unsignedInteger('id_presensi');

            $table->string('file_bukti', 255)->nullable();

            $table->text('alasan')->nullable();

            $table->foreign('id_presensi')
                ->references('id_presensi')
                ->on('presensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin');
    }
};