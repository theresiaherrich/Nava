<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->increments('id_guru');

            $table->unsignedInteger('id_user')->unique();
            $table->string('nip', 30)->nullable();
            $table->string('status_kepegawaian', 50)->nullable();
            $table->string('nama', 120);
            $table->string('agama', 30)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('email', 100)->nullable();
            $table->string('no_telepon', 25)->nullable();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
