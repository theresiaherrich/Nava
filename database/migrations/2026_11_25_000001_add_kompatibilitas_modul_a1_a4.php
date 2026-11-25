<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kompatibilitas skema A2 dengan kode modul A1 (Presensi) dan A4 (pendaftaran orang tua).
 *
 * Skema tabel bersama (user, siswa, guru, kelas, semester, orang_tua, dst.) mengikuti
 * migrasi A2. Migrasi ini hanya MENAMBAH kolom yang masih dibaca/ditulis kode A1/A4,
 * tanpa mengubah kolom A2:
 * - user.role            : peran tunggal versi A1. Bila kosong, App\Models\User::role
 *                          diturunkan dari pivot role_user (sumber kebenaran A2).
 * - siswa.urutan_masuk   : dipakai tambah/impor siswa A1.
 *                          (siswa.agama TIDAK ditambah: A2 memakai relasi agama()/id_agama;
 *                          nilai string dari A1 dipetakan di mutator Siswa::setAgamaAttribute.)
 * - orang_tua.id_siswa, hubungan, status_verifikasi, diverifikasi_oleh, waktu_verifikasi:
 *                          pendaftaran mandiri orang tua (satu baris per akun + anak).
 *                          Relasi resmi A2 tetap di pivot orang_tua_siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->enum('role', [
                'SISWA', 'GURU', 'ORANG_TUA', 'AKADEMIK', 'KURIKULUM', 'BUDAYA_POSITIF', 'KESISWAAN', 'ADMIN',
            ])->nullable()->after('nama');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->unsignedInteger('urutan_masuk')->nullable()->after('tahun_masuk');
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->unsignedInteger('id_siswa')->nullable()->after('id_user');
            $table->enum('hubungan', ['Ayah', 'Ibu', 'Wali'])->nullable()->after('id_siswa');
            $table->enum('status_verifikasi', ['pending', 'verified', 'rejected'])->nullable()->after('alamat');
            $table->unsignedInteger('diverifikasi_oleh')->nullable()->after('status_verifikasi');
            $table->dateTime('waktu_verifikasi')->nullable()->after('diverifikasi_oleh');

            // Satu akun orang tua (A4) boleh tertaut ke beberapa anak, satu baris per anak.
            // Indeks komposit dibuat dulu agar FK id_user tetap punya indeks saat unique lama dilepas.
            $table->unique(['id_user', 'id_siswa'], 'uq_ortu_user_siswa');
            $table->unique(['id_siswa', 'hubungan'], 'uq_orang_tua_siswa_hubungan');
            $table->foreign('id_siswa')->references('id_siswa')->on('siswa')->nullOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->dropUnique('orang_tua_id_user_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orang_tua', function (Blueprint $table) {
            $table->unique('id_user');
        });

        Schema::table('orang_tua', function (Blueprint $table) {
            $table->dropForeign(['id_siswa']);
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropUnique('uq_ortu_user_siswa');
            $table->dropUnique('uq_orang_tua_siswa_hubungan');
            $table->dropColumn(['id_siswa', 'hubungan', 'status_verifikasi', 'diverifikasi_oleh', 'waktu_verifikasi']);
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('urutan_masuk');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
