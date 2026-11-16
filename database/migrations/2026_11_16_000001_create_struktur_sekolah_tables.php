<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Struktur sekolah: identitas, tahun ajaran, semester, kelas, siswa, guru,
 * orang tua, anggota kelas (riwayat_kelas), mutasi kelas, dan wali kelas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sekolah', function (Blueprint $table) {
            $table->increments('id_sekolah');
            $table->string('npsn', 10)->nullable();
            $table->string('nama', 120);
            $table->text('alamat')->nullable();
            $table->string('kota', 60)->nullable();
            $table->string('telepon', 25)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('kepala_sekolah', 120)->nullable();
            $table->string('nip_kepala_sekolah', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('agama', function (Blueprint $table) {
            $table->increments('id_agama');
            $table->string('nama', 30)->unique();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->increments('id_tahun_ajaran');
            $table->string('nama', 9)->unique(); // 2026/2027
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('semester', function (Blueprint $table) {
            $table->increments('id_semester');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->enum('nama', ['GANJIL', 'GENAP']);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
            $table->unique(['id_tahun_ajaran', 'nama']);
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->increments('id_kelas');
            $table->unsignedInteger('id_tahun_ajaran');
            $table->string('nama', 50);
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->enum('fase', ['E', 'F']);
            $table->timestamps();
            $table->unique(['id_tahun_ajaran', 'nama']);
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
        });

        // id_user boleh kosong: data siswa bisa diimpor sebelum akunnya dibuat.
        Schema::create('siswa', function (Blueprint $table) {
            $table->increments('id_siswa');
            $table->unsignedInteger('id_user')->nullable()->unique();
            $table->string('nis', 30)->unique();
            $table->string('nisn', 10)->nullable()->unique();
            $table->string('nama', 120);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->unsignedInteger('id_agama')->nullable();
            $table->string('tempat_lahir', 60)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->unsignedSmallInteger('tahun_masuk')->nullable();
            $table->enum('status', ['aktif', 'lulus', 'pindah', 'keluar'])->default('aktif');
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('id_agama')->references('id_agama')->on('agama')->nullOnDelete();
            $table->index('nama');
        });

        // NIP boleh kosong untuk GTT.
        Schema::create('guru', function (Blueprint $table) {
            $table->increments('id_guru');
            $table->unsignedInteger('id_user')->nullable()->unique();
            $table->string('nip', 30)->nullable()->unique();
            $table->enum('status_kepegawaian', ['ASN', 'GTT']);
            $table->string('nama', 120);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->unsignedInteger('id_agama')->nullable();
            $table->string('no_telepon', 25)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('id_agama')->references('id_agama')->on('agama')->nullOnDelete();
            $table->index('nama');
        });

        Schema::create('orang_tua', function (Blueprint $table) {
            $table->increments('id_orang_tua');
            $table->unsignedInteger('id_user')->nullable()->unique();
            $table->string('nama', 120);
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('no_telepon', 25)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
        });

        // Relasi orang tua ↔ anak. Dibuat oleh Operator, sehingga dianggap terverifikasi.
        Schema::create('orang_tua_siswa', function (Blueprint $table) {
            $table->unsignedInteger('id_orang_tua');
            $table->unsignedInteger('id_siswa');
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Wali']);
            $table->timestamps();
            $table->primary(['id_orang_tua', 'id_siswa']);
            $table->unique(['id_siswa', 'hubungan']);
            $table->foreign('id_orang_tua')->references('id_orang_tua')->on('orang_tua')->cascadeOnDelete();
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->cascadeOnDelete();
        });

        // anggota_kelas pada ERD: penempatan siswa di kelas untuk satu semester.
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->increments('id_riwayat_kelas');
            $table->unsignedInteger('id_siswa');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_semester');
            $table->timestamps();
            $table->unique(['id_siswa', 'id_semester']);
            $table->unique(['id_kelas', 'id_siswa', 'id_semester']);
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->restrictOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete();
            $table->foreign('id_semester')->references('id_semester')->on('semester')->restrictOnDelete();
        });

        // Jejak pemindahan kelas di tengah semester (append-only).
        Schema::create('mutasi_kelas', function (Blueprint $table) {
            $table->increments('id_mutasi_kelas');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->unsignedInteger('dari_kelas');
            $table->unsignedInteger('ke_kelas');
            $table->text('alasan')->nullable();
            $table->unsignedInteger('dipindah_oleh')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->cascadeOnDelete();
            $table->foreign('dari_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('ke_kelas')->references('id_kelas')->on('kelas');
            $table->foreign('dipindah_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->increments('id_wali_kelas');
            $table->unsignedInteger('id_kelas');
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_semester');
            $table->timestamps();
            $table->unique(['id_kelas', 'id_semester']);
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete();
            $table->foreign('id_guru')->references('id_guru')->on('guru')->restrictOnDelete();
            $table->foreign('id_semester')->references('id_semester')->on('semester')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['wali_kelas', 'mutasi_kelas', 'riwayat_kelas', 'orang_tua_siswa', 'orang_tua', 'guru', 'siswa', 'kelas', 'semester', 'tahun_ajaran', 'agama', 'sekolah'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
