<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->increments('id_wali_kelas');

            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_semester');

            $table->timestamps();

            $table->foreign('id_kelas')
                ->references('id_kelas')
                ->on('kelas');

            $table->foreign('id_guru')
                ->references('id_guru')
                ->on('guru');

            $table->foreign('id_semester')
                ->references('id_semester')
                ->on('semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_kelas');
    }
};
