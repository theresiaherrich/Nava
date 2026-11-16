<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemetaan nama ERD → nama tabel (lihat docs/03_ROADMAP_ERD.md):
 *   pengajaran        → rombel + guru_rombel (rombel boleh campuran beberapa kelas)
 *   anggota pengajaran→ siswa_rombel
 *   periode_penilaian → periode_nilai + periode_tingkat
 *   pengajuan_nilai   → paket_nilai (per rombel × periode × kelas asal)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->increments('id_mapel');
            $table->string('kode', 30)->unique();
            $table->string('nama', 120);
            $table->string('kelompok', 60)->nullable(); // mis. "Umum", "Pilihan", "Muatan Lokal"
            $table->unsignedSmallInteger('urutan_rapor')->default(99);
            // Terisi = mapel agama; hanya dinilai untuk siswa dengan agama yang sama.
            $table->unsignedInteger('id_agama')->nullable();
            $table->decimal('kktp_default', 5, 2)->default(75);
            // Template deskripsi capaian. :tp diganti daftar TP. Kosong = pakai config/siakad.php.
            $table->string('deskripsi_kuat', 255)->nullable();
            $table->string('deskripsi_penguatan', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->foreign('id_agama')->references('id_agama')->on('agama')->nullOnDelete();
        });

        Schema::create('rombel', function (Blueprint $table) {
            $table->increments('id_rombel');
            $table->unsignedInteger('id_semester');
            $table->unsignedInteger('id_mapel');
            $table->string('nama', 100);
            $table->timestamps();
            $table->unique(['id_semester', 'id_mapel', 'nama']);
            $table->foreign('id_semester')->references('id_semester')->on('semester')->restrictOnDelete();
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
        });

        Schema::create('guru_rombel', function (Blueprint $table) {
            $table->unsignedInteger('id_guru');
            $table->unsignedInteger('id_rombel');
            $table->timestamps();
            $table->primary(['id_guru', 'id_rombel']);
            $table->foreign('id_guru')->references('id_guru')->on('guru')->restrictOnDelete();
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->cascadeOnDelete();
        });

        Schema::create('siswa_rombel', function (Blueprint $table) {
            $table->increments('id_siswa_rombel');
            $table->unsignedInteger('id_rombel');
            $table->unsignedInteger('id_riwayat_kelas');
            $table->timestamps();
            $table->unique(['id_rombel', 'id_riwayat_kelas']);
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->restrictOnDelete();
            $table->foreign('id_riwayat_kelas')->references('id_riwayat_kelas')->on('riwayat_kelas')->restrictOnDelete();
        });

        // Satu periode STS dan satu SAS per semester; tingkat peserta di periode_tingkat.
        // Status "ditutup" tidak disimpan: diturunkan dari tgl_tutup yang sudah lewat.
        Schema::create('periode_nilai', function (Blueprint $table) {
            $table->increments('id_periode');
            $table->unsignedInteger('id_semester');
            $table->enum('jenis', ['STS', 'SAS']);
            $table->dateTime('tgl_buka')->nullable();
            $table->dateTime('tgl_tutup')->nullable();
            $table->enum('status', ['belum_diatur', 'dibuka', 'dikunci'])->default('belum_diatur');
            $table->timestamps();
            $table->unique(['id_semester', 'jenis']);
            $table->foreign('id_semester')->references('id_semester')->on('semester')->restrictOnDelete();
        });

        Schema::create('periode_tingkat', function (Blueprint $table) {
            $table->unsignedInteger('id_periode');
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->primary(['id_periode', 'tingkat']);
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai')->cascadeOnDelete();
        });

        // Maks. 10 TP per rombel (= per pengajaran per semester), urutan 1–10.
        Schema::create('tp', function (Blueprint $table) {
            $table->increments('id_tp');
            $table->unsignedInteger('id_rombel');
            $table->unsignedTinyInteger('urutan');
            $table->string('kode', 30)->nullable();
            $table->string('deskripsi', 500);
            $table->decimal('kktp', 5, 2)->nullable(); // null = pakai mapel.kktp_default
            $table->boolean('hanya_sts')->default(false); // "mode STS saja": hanya diisi di periode STS
            $table->timestamps();
            $table->unique(['id_rombel', 'urutan']);
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->restrictOnDelete();
        });

        // id_periode = periode tempat nilai diisi. Rapor STS hanya membaca nilai periode STS.
        Schema::create('nilai_tp', function (Blueprint $table) {
            $table->increments('id_nilai_tp');
            $table->unsignedInteger('id_tp');
            $table->unsignedInteger('id_siswa_rombel');
            $table->unsignedInteger('id_periode');
            $table->decimal('nilai', 5, 2)->nullable(); // null = belum diisi, BUKAN 0
            $table->unsignedInteger('diubah_oleh')->nullable();
            $table->timestamps();
            $table->unique(['id_tp', 'id_siswa_rombel']);
            $table->foreign('id_tp')->references('id_tp')->on('tp')->restrictOnDelete();
            $table->foreign('id_siswa_rombel')->references('id_siswa_rombel')->on('siswa_rombel')->restrictOnDelete();
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai')->restrictOnDelete();
            $table->foreign('diubah_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('paket_nilai', function (Blueprint $table) {
            $table->increments('id_paket_nilai');
            $table->unsignedInteger('id_rombel');
            $table->unsignedInteger('id_periode');
            $table->unsignedInteger('id_kelas');
            $table->enum('status', ['draf', 'terkirim', 'perlu_revisi', 'disetujui', 'dikunci'])->default('draf');
            $table->boolean('lengkap')->default(false); // snapshot kelengkapan saat dikirim
            // true = Kurikulum membuka koreksi: guru boleh mengubah nilai walau periode sudah ditutup.
            $table->boolean('koreksi')->default(false);
            $table->text('alasan_koreksi')->nullable();
            $table->unsignedInteger('dikirim_oleh')->nullable();
            $table->dateTime('waktu_kirim')->nullable();
            $table->unsignedInteger('diverifikasi_oleh')->nullable();
            $table->dateTime('waktu_verifikasi')->nullable();
            $table->unsignedInteger('dikunci_oleh')->nullable();
            $table->dateTime('waktu_kunci')->nullable();
            $table->timestamps();
            $table->unique(['id_rombel', 'id_periode', 'id_kelas']);
            $table->foreign('id_rombel')->references('id_rombel')->on('rombel')->restrictOnDelete();
            $table->foreign('id_periode')->references('id_periode')->on('periode_nilai')->restrictOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->restrictOnDelete();
            $table->foreign('dikirim_oleh')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id_user')->on('user')->nullOnDelete();
            $table->foreign('dikunci_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        // Riwayat keputusan paket (append-only, tidak pernah diubah).
        Schema::create('verifikasi_nilai', function (Blueprint $table) {
            $table->bigIncrements('id_verifikasi_nilai');
            $table->unsignedInteger('id_paket_nilai');
            $table->unsignedInteger('id_user')->nullable();
            $table->enum('aksi', ['kirim', 'setujui', 'minta_revisi', 'kunci', 'buka_koreksi']);
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('id_paket_nilai')->references('id_paket_nilai')->on('paket_nilai')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->nullOnDelete();
            $table->index(['id_paket_nilai', 'created_at'], 'idx_vn_paket_waktu');
        });
    }

    public function down(): void
    {
        foreach (['verifikasi_nilai', 'paket_nilai', 'nilai_tp', 'tp', 'periode_tingkat', 'periode_nilai', 'siswa_rombel', 'guru_rombel', 'rombel', 'mapel'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
