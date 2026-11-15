<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aman dijalankan bila sebagian tabel sudah ada (mis. dibuat dari file SQL): tabel yang sudah ada dilewati, tidak diubah.
 * Tabel bagian 4.7 dan 4.8 (ekskul, pelengkap rapor, template dan arsip rapor).
 * Nama tabel dan kolom mengikuti siakad_sma1_revisi.sql. Perbedaan yang disengaja:
 *  - riwayat_kelas di SQL = siswa_kelas di migrasi A2, jadi FK id_riwayat_kelas menuju siswa_kelas.id.
 *  - user(id_user) di SQL = users.id; siswa/semester/periode_nilai/sekolah memakai PK "id".
 *  - predikat.nilai_min/nilai_max boleh null (panduan 7.4: rentang belum dikonfirmasi Kesiswaan).
 *  - pelengkap_rapor.sakit/izin/alpha boleh null tanpa default 0: null = belum ada data A1, BUKAN nol.
 *  - pelengkap_rapor.id_presensi tanpa FK karena tabel presensi milik A1 belum ada di migrasi A2.
 *  - pelengkap_rapor diberi timestamps untuk "terakhir tersimpan".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kesiswaan')) {
        Schema::create('kesiswaan', function (Blueprint $table) {
            $table->id('id_kesiswaan');
            $table->foreignId('id_user')->unique()->constrained('users')->restrictOnDelete();
        });
        }

        if (! Schema::hasTable('ekskul')) {
        Schema::create('ekskul', function (Blueprint $table) {
            $table->id('id_ekskul');
            $table->string('nama', 120);
        });
        }

        if (! Schema::hasTable('predikat')) {
        Schema::create('predikat', function (Blueprint $table) {
            $table->id('id_predikat');
            $table->enum('predikat', ['SB', 'B', 'K']);
            $table->decimal('nilai_min', 5, 2)->nullable();
            $table->decimal('nilai_max', 5, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
        });
        }

        if (! Schema::hasTable('template_rapor')) {
        Schema::create('template_rapor', function (Blueprint $table) {
            $table->id('id_template');
            $table->foreignId('id_sekolah')->constrained('sekolah')->restrictOnDelete();
            $table->string('jenis', 50)->nullable();
            $table->string('versi', 30)->nullable();
            $table->string('nama', 120)->nullable();
            $table->text('identitas_cetak')->nullable();
            $table->text('lokasi_template')->nullable();
        });
        }

        if (! Schema::hasTable('peserta_ekskul')) {
        Schema::create('peserta_ekskul', function (Blueprint $table) {
            $table->id('id_peserta_ekskul');
            $table->foreignId('id_ekskul')->constrained('ekskul', 'id_ekskul')->restrictOnDelete();
            $table->foreignId('id_siswa')->constrained('siswa')->restrictOnDelete();
            $table->foreignId('id_semester')->constrained('semester')->restrictOnDelete();
            $table->foreignId('id_predikat')->constrained('predikat', 'id_predikat')->restrictOnDelete();
            $table->text('catatan')->nullable();
            $table->foreignId('diisi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['id_ekskul', 'id_siswa', 'id_semester'], 'uq_peserta_ekskul_sem');
        });
        }

        if (! Schema::hasTable('pelengkap_rapor')) {
        Schema::create('pelengkap_rapor', function (Blueprint $table) {
            $table->id('id_pelengkap_rapor');
            $table->foreignId('id_riwayat_kelas')->constrained('siswa_kelas')->restrictOnDelete();
            $table->foreignId('id_periode')->constrained('periode_nilai')->restrictOnDelete();
            $table->text('catatan_wali')->nullable();
            $table->text('kokurikuler')->nullable();
            $table->unsignedInteger('sakit')->nullable();
            $table->unsignedInteger('izin')->nullable();
            $table->unsignedInteger('alpha')->nullable();
            $table->unsignedBigInteger('id_presensi')->nullable();
            $table->timestamps();
            $table->unique(['id_riwayat_kelas', 'id_periode'], 'uq_pelengkap_rapor');
        });
        }

        if (! Schema::hasTable('rapor')) {
        Schema::create('rapor', function (Blueprint $table) {
            $table->id('id_rapor');
            $table->foreignId('id_riwayat_kelas')->constrained('siswa_kelas')->restrictOnDelete();
            $table->foreignId('id_periode')->constrained('periode_nilai')->restrictOnDelete();
            $table->foreignId('id_template')->constrained('template_rapor', 'id_template')->restrictOnDelete();
            $table->string('versi', 30);
            $table->json('data_final');
            $table->string('file_pdf', 255)->nullable();
            $table->date('tanggal_cetak')->nullable();
            $table->enum('status', ['terkunci', 'siap', 'terbit', 'dicabut']);
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('waktu_terbit')->nullable();
            $table->timestamps();
            $table->index(['id_riwayat_kelas', 'id_periode'], 'idx_rapor_riwayat_periode');
        });
        }
    }

    public function down(): void
    {
        foreach (['rapor', 'pelengkap_rapor', 'peserta_ekskul', 'template_rapor', 'predikat', 'ekskul', 'kesiswaan'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
