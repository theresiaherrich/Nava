<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $t) {
            $t->increments('id_kelas');
            $t->string('nama', 50);
            $t->enum('tingkat', ['X','XI','XII']);
            $t->enum('fase', ['E','F']);
            $t->string('tahun_ajaran', 9);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
