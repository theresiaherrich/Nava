
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->unsignedInteger('id_siswa');

            $table->enum('hubungan', [
                'Ayah',
                'Ibu',
                'Wali',
            ]);

            $table->unsignedInteger('id_user')->nullable();

            $table->text('alamat')->nullable();
            $table->string('no_telepon', 25)->nullable();

            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();

            $table->string('nama', 120);

            $table->enum('status_verifikasi', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->unsignedInteger('diverifikasi_oleh')->nullable();

            $table->dateTime('waktu_verifikasi')->nullable();

            $table->primary(
                ['id_siswa', 'hubungan'],
                'pk_orang_tua'
            );

            $table->unique(
                ['id_user', 'id_siswa'],
                'uq_ortu_user_siswa'
            );

            $table->foreign('id_siswa')
                ->references('id_siswa')
                ->on('siswa');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('user');

            $table->foreign('diverifikasi_oleh')
                ->references('id_user')
                ->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua');
    }
};
