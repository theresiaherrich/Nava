<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $t) {
            $t->increments('id_tahun_ajaran');
            $t->string('nama', 9)->unique();
            $t->boolean('aktif')->default(false);
            $t->timestamp('created_at')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        Schema::create('kelas', function (Blueprint $t) {
            $t->increments('id_kelas');
            $t->string('nama', 50);
            $t->enum('tingkat', ['X', 'XI', 'XII']);
            $t->enum('fase', ['E', 'F']);
            $t->unsignedInteger('id_tahun_ajaran');

            $t->foreign('id_tahun_ajaran', 'fk_kelas_ta')
                ->references('id_tahun_ajaran')->on('tahun_ajaran');
            $t->unique(['nama', 'id_tahun_ajaran'], 'uq_kelas_ta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('tahun_ajaran');
    }
};
