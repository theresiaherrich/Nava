<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->increments('id_sekolah');
            $table->string('nama', 120);
            $table->text('alamat')->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('npsn', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->increments('id_tahun_ajaran');
            $table->string('nama', 9)->unique();
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('semester', function (Blueprint $table) {
            $table->increments('id_semester');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->enum('nama', ['GASAL', 'GENAP']);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
            $table->unique(['id_tahun_ajaran', 'nama']);
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran');
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->increments('id_kelas');
            $table->string('nama', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->enum('fase', ['E', 'F']);
            $table->unsignedInteger('id_tahun_ajaran');
            $table->timestamps();
            $table->unique(['nama', 'id_tahun_ajaran']);
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran');
        });

        // id_user boleh kosong: admin dapat mengimpor data siswa sebelum akunnya dibuat.
        Schema::create('siswa', function (Blueprint $table) {
            $table->increments('id_siswa');
            $table->unsignedInteger('id_user')->nullable()->unique();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 30)->nullable()->unique();
            $table->string('nama', 120);
            $table->string('agama', 30)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->unsignedSmallInteger('tahun_masuk')->nullable();
            $table->unsignedInteger('urutan_masuk')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('guru', function (Blueprint $table) {
            $table->increments('id_guru');
            $table->unsignedInteger('id_user')->nullable()->unique();
            $table->string('nip', 30)->nullable()->unique();
            $table->enum('status_kepegawaian', ['ASN', 'GTT'])->nullable();
            $table->string('nama', 120);
            $table->string('agama', 30)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
        });

        // Relasi orang tua-anak. Tanpa timestamps, mengikuti SQL revisi.
        Schema::create('orang_tua', function (Blueprint $table) {
            $table->unsignedInteger('id_siswa');
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Wali']);
            $table->unsignedInteger('id_user')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 25)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('nama', 120);
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->default('pending');
            $table->unsignedInteger('diverifikasi_oleh')->nullable();
            $table->dateTime('waktu_verifikasi')->nullable();
            $table->primary(['id_siswa', 'hubungan']);
            $table->unique(['id_user', 'id_siswa']);
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        // Kelas asal siswa per semester (task menyebut siswa_kelas; SQL revisi memakai riwayat_kelas).
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->increments('id_riwayat_kelas');
            $table->unsignedInteger('id_siswa');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_semester');
            $table->timestamps();
            $table->unique(['id_siswa', 'id_semester']);
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('id_semester')->references('id_semester')->on('semester');
        });

        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->increments('id_wali_kelas');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_semester');
            $table->timestamps();
            $table->unique(['id_kelas', 'id_semester']);
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('id_guru')->references('id_guru')->on('guru');
            $table->foreign('id_semester')->references('id_semester')->on('semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_kelas');
        Schema::dropIfExists('riwayat_kelas');
        Schema::dropIfExists('orang_tua');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('semester');
        Schema::dropIfExists('tahun_ajaran');
        Schema::dropIfExists('sekolah');
    }
};
