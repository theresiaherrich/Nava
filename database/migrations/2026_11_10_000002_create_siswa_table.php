
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->increments('id_siswa');

            $table->unsignedInteger('id_user')->unique();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->unique();

            $table->string('agama', 30)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();

            $table->unsignedSmallInteger('tahun_masuk')->nullable();
            $table->unsignedInteger('urutan_masuk')->nullable();

            $table->text('alamat')->nullable();
            $table->string('nama', 120);

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
