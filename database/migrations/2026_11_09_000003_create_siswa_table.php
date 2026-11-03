<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $t) {
            $t->increments('id_siswa');
            $t->unsignedInteger('id_user')->unique();
            $t->string('nis', 30)->unique();
            $t->string('nisn', 30)->nullable()->unique();
            $t->string('agama', 30)->nullable();
            $t->enum('jenis_kelamin', ['L','P'])->nullable();
            $t->unsignedSmallInteger('tahun_masuk')->nullable();
            $t->unsignedInteger('urutan_masuk')->nullable();
            $t->text('alamat')->nullable();
            $t->string('nama', 120);

            $t->foreign('id_user', 'fk_siswa_user')->references('id_user')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
