<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_siswa', function (Blueprint $table) {
            $table->increments('id_qr');

            $table->unsignedInteger('id_siswa')->unique();

            $table->string('kode_qr', 255)->unique();

            $table->foreign('id_siswa')
                ->references('id_siswa')
                ->on('siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_siswa');
    }
};