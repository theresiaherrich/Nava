<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $t) {
            $t->increments('id_guru');
            $t->unsignedInteger('id_user')->unique();
            $t->string('nip', 30)->nullable()->unique();
            $t->string('status_kepegawaian', 50)->nullable();
            $t->string('nama', 120);
            $t->string('agama', 30)->nullable();
            $t->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $t->text('alamat')->nullable();

            $t->foreign('id_user', 'fk_guru_user')->references('id_user')->on('user');
        });

        Schema::create('guru_bk', function (Blueprint $t) {
            $t->unsignedInteger('id_guru')->primary();
            $t->string('email', 100)->nullable();
            $t->string('no_telepon', 25)->nullable();
            $t->enum('tingkat', ['X', 'XI', 'XII'])->nullable();
            $t->string('jadwal_layanan', 100)->nullable();
            $t->boolean('aktif')->default(true);

            $t->foreign('id_guru', 'fk_gbk_guru')->references('id_guru')->on('guru');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_bk');
        Schema::dropIfExists('guru');
    }
};
