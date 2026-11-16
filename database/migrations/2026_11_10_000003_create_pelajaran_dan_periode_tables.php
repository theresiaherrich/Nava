<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->increments('id_mapel');
            $table->string('kode', 30)->unique();
            $table->string('nama', 120);
            $table->enum('kelompok', ['umum', 'agama'])->default('umum');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('rombel', function (Blueprint $table) {
            $table->increments('id_rombel');
            $table->unsignedInteger('id_semester');
            $table->unsignedInteger('id_mapel');
            $table->string('nama', 80);
            $table->string('tingkat', 10)->nullable();
            $table->string('jam', 50)->nullable();
            $table->string('kelompok_rapor', 80)->nullable();
            $table->string('metode_nilai', 80)->nullable();
            $table->timestamps();
            $table->foreign('id_semester')->references('id_semester')->on('semester');
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel');
        });

        Schema::create('guru_rombel', function (Blueprint $table) {
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_rombel');
            $table->primary(['id_guru', 'id_rombel']);
            $table->foreign('id_guru')->references('id_guru')->on('guru')->cascadeOnDelete();
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->cascadeOnDelete();
        });

        // id_mapel dihilangkan dari SQL revisi karena sudah bisa diambil lewat rombel.
        Schema::create('siswa_rombel', function (Blueprint $table) {
            $table->increments('id_siswarombel');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->unsignedInteger('id_rombel');
            $table->timestamps();
            $table->unique(['id_riwayat_kelas', 'id_rombel']);
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->cascadeOnDelete();
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->cascadeOnDelete();
        });

        Schema::create('periode_nilai', function (Blueprint $table) {
            $table->increments('id_periode');
            $table->unsignedInteger('id_semester');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->enum('jenis', ['STS', 'SAS']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['draf', 'dibuka', 'ditutup', 'terkunci'])->default('draf');
            $table->timestamps();
            $table->unique(['id_semester', 'tingkat', 'jenis']);
            $table->foreign('id_semester')->references('id_semester')->on('semester');
        });

        // Unik per (rombel, periode, kelas asal): satu rombel campuran X-A & X-B tetap punya paket per kelas.
        Schema::create('paket_nilai', function (Blueprint $table) {
            $table->increments('id_paket_nilai');
            $table->unsignedInteger('id_periode');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_rombel');
            $table->enum('status', ['draf', 'terkirim', 'revisi', 'disetujui', 'terkunci'])->default('draf');
            $table->unsignedInteger('dikirim_oleh')->nullable();
            $table->dateTime('waktu_kirim')->nullable();
            $table->unsignedInteger('dikunci_oleh')->nullable();
            $table->dateTime('waktu_kunci')->nullable();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamps();
            $table->unique(['id_rombel', 'id_periode', 'id_kelas']);
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel');
            $table->foreign('dikirim_oleh')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('dikunci_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('verifikasi_nilai', function (Blueprint $table) {
            $table->bigIncrements('id_verifikasi_nilai');
            $table->unsignedInteger('id_paket_nilai');
            $table->unsignedInteger('id_pemeriksa');
            $table->enum('keputusan', ['disetujui', 'revisi']);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->index(['id_paket_nilai', 'created_at'], 'idx_vn_paket_time');
            $table->foreign('id_paket_nilai')->references('id_paket_nilai')->on('paket_nilai');
            $table->foreign('id_pemeriksa')->references('id_user')->on('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_nilai');
        Schema::dropIfExists('paket_nilai');
        Schema::dropIfExists('periode_nilai');
        Schema::dropIfExists('siswa_rombel');
        Schema::dropIfExists('guru_rombel');
        Schema::dropIfExists('rombel');
        Schema::dropIfExists('mapel');
    }
};
