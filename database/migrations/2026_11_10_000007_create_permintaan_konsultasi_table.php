<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_konsultasi', function (Blueprint $t) {
            $t->increments('id_permintaan');
            $t->unsignedInteger('id_siswa')->nullable();
            $t->string('status', 30)->default('Menunggu'); // Menunggu, Diproses, Dijadwalkan, Selesai
            $t->text('catatan')->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->foreign('id_siswa', 'fk_pk_siswa')
                ->references('id_siswa')->on('siswa')
                ->onDelete('cascade');
        });

        Schema::create('pengajuan_bk', function (Blueprint $t) {
            $t->increments('id_pengajuan_bk');
            $t->unsignedInteger('id_riwayat_kelas');
            $t->string('topik', 100);
            $t->text('deskripsi');
            $t->enum('status', ['menunggu', 'diproses', 'dijadwalkan', 'selesai', 'ditolak'])
                ->default('menunggu');
            $t->unsignedInteger('id_guru')->nullable();
            $t->text('balasan_bk')->nullable();
            $t->dateTime('jadwal_bimbingan')->nullable();
            $t->dateTime('created_at')->useCurrent();
            $t->dateTime('updated_at')->nullable()->useCurrentOnUpdate();

            $t->foreign('id_riwayat_kelas', 'fk_pbk_riwayat')
                ->references('id_riwayat_kelas')->on('riwayat_kelas');
            $t->foreign('id_guru', 'fk_pbk_guru')
                ->references('id_guru')->on('guru_bk');
            $t->index(['status', 'created_at'], 'idx_pbk_status_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bk');
        Schema::dropIfExists('permintaan_konsultasi');
    }
};
