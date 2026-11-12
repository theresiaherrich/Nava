<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi_sekolah', function (Blueprint $table) {
            $table->increments('id_lokasi');

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('nama_lokasi', 120);

            $table->unsignedInteger('radius');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_sekolah');
    }
};