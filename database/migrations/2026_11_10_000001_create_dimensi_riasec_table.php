<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensi_riasec', function (Blueprint $t) {
            $t->increments('id_dimensi');
            $t->string('kode', 30)->unique();
            $t->string('nama', 120);
            $t->text('deskripsi')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimensi_riasec');
    }
};
