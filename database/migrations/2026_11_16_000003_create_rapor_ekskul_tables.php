<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ekskul, pelengkap rapor, template, dan arsip rapor.
 * Presensi milik modul A1; pelengkap_rapor hanya menyimpan ringkasan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul', function (Blueprint $table) {
            $table->increments('id_ekskul');
            $table->string('nama', 120)->unique();
            $table->unsignedInteger('id_pembina')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->foreign('id_pembina')->references('id_guru')->on('guru')->nullOnDelete();
        });

        // nilai_ekskul pada ERD. riwayat_kelas sudah per semester, jadi unik (ekskul, anggota kelas).
        Schema::create('peserta_ekskul', function (Blueprint $table) {
            $table->increments('id_peserta_ekskul');
            $table->unsignedInteger('id_ekskul');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->enum('predikat', ['SB', 'B', 'K'])->nullable();
            $table->string('catatan', 255)->nullable();
            $table->unsignedInteger('diisi_oleh')->nullable();
            $table->timestamps();
            $table->unique(['id_ekskul', 'id_riwayat_kelas']);
            $table->foreign('id_ekskul')->references('id_ekskul')->on('ekskul')->restrictOnDelete();
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->restrictOnDelete();
            $table->foreign('diisi_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        // sakit/izin/alpha null = belum ada data (A1 belum tersambung), BUKAN nol.
        Schema::create('pelengkap_rapor', function (Blueprint $table) {
            $table->increments('id_pelengkap_rapor');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->unsignedInteger('id_periode');
            $table->unsignedSmallInteger('sakit')->nullable();
            $table->unsignedSmallInteger('izin')->nullable();
            $table->unsignedSmallInteger('alpha')->nullable();
            $table->enum('sumber_presensi', ['manual', 'a1'])->nullable();
            $table->text('catatan_wali')->nullable();
            $table->text('kokurikuler')->nullable();
            $table->unsignedInteger('diisi_oleh')->nullable();
            $table->timestamps();
            $table->unique(['id_riwayat_kelas', 'id_periode']);
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->restrictOnDelete();
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai')->restrictOnDelete();
            $table->foreign('diisi_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        // view = nama Blade di whitelist config/siakad.php; file template tidak diunggah bebas.
        Schema::create('template_rapor', function (Blueprint $table) {
            $table->increments('id_template');
            $table->enum('jenis', ['STS', 'SAS']);
            $table->string('nama', 120);
            $table->string('versi', 20)->default('1');
            $table->string('view', 60)->default('rapor.semester');
            $table->string('judul', 120)->default('LAPORAN HASIL BELAJAR');
            $table->string('tempat_cetak', 60)->nullable();
            $table->boolean('tampilkan_deskripsi')->default(true);
            $table->boolean('aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('rapor', function (Blueprint $table) {
            $table->increments('id_rapor');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->unsignedInteger('id_periode');
            $table->unsignedInteger('id_template');
            $table->unsignedSmallInteger('versi');
            $table->json('data_final'); // snapshot; tidak ikut berubah bila nilai dikoreksi
            $table->string('file_pdf', 255);
            $table->enum('status', ['draf', 'terbit', 'dicabut'])->default('draf');
            $table->unsignedInteger('dicetak_oleh')->nullable();
            $table->unsignedInteger('diterbitkan_oleh')->nullable();
            $table->dateTime('waktu_terbit')->nullable();
            $table->text('alasan_cabut')->nullable();
            $table->timestamps();
            $table->unique(['id_riwayat_kelas', 'id_periode', 'versi']);
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->restrictOnDelete();
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai')->restrictOnDelete();
            $table->foreign('id_template')->references('id_template')->on('template_rapor')->restrictOnDelete();
            $table->foreign('dicetak_oleh')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('diterbitkan_oleh')->references('id_user')->on('user')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['rapor', 'template_rapor', 'pelengkap_rapor', 'peserta_ekskul', 'ekskul'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
