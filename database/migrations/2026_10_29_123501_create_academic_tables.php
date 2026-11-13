<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TABLE IF NOT EXISTS sekolah (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                npsn VARCHAR(20) NULL UNIQUE,
                nama VARCHAR(200) NOT NULL,
                alamat TEXT NULL,
                nama_kepala_sekolah VARCHAR(150) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS guru (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL UNIQUE,
                nama VARCHAR(150) NOT NULL,
                nip VARCHAR(30) NULL UNIQUE,
                status_kepegawaian ENUM('ASN','GTT','LAINNYA') NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS siswa (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL UNIQUE,
                nis VARCHAR(30) NOT NULL UNIQUE,
                nisn VARCHAR(20) NULL UNIQUE,
                nama VARCHAR(150) NOT NULL,
                agama VARCHAR(30) NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS wali_siswa (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                siswa_id BIGINT UNSIGNED NOT NULL,
                hubungan VARCHAR(30) NOT NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                diverifikasi_oleh BIGINT UNSIGNED NULL,
                waktu_verifikasi DATETIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (user_id, siswa_id),
                FOREIGN KEY (user_id) REFERENCES users(id),
                FOREIGN KEY (siswa_id) REFERENCES siswa(id),
                FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS tahun_ajaran (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(20) NOT NULL UNIQUE,
                aktif BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS semester (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tahun_ajaran_id BIGINT UNSIGNED NOT NULL,
                nama ENUM('ganjil','genap') NOT NULL,
                aktif BOOLEAN NOT NULL DEFAULT FALSE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (tahun_ajaran_id, nama),
                FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS kelas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                semester_id BIGINT UNSIGNED NOT NULL,
                nama VARCHAR(30) NOT NULL,
                tingkat TINYINT UNSIGNED NOT NULL,
                fase ENUM('E','F') NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (semester_id, nama),
                UNIQUE (id, semester_id),
                CHECK (
                    (tingkat = 10 AND fase = 'E')
                    OR (tingkat IN (11,12) AND fase = 'F')
                ),
                FOREIGN KEY (semester_id) REFERENCES semester(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS siswa_kelas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                siswa_id BIGINT UNSIGNED NOT NULL,
                kelas_id BIGINT UNSIGNED NOT NULL,
                semester_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (siswa_id, semester_id),
                UNIQUE (id, semester_id),
                FOREIGN KEY (siswa_id) REFERENCES siswa(id),
                FOREIGN KEY (kelas_id, semester_id) REFERENCES kelas(id, semester_id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS wali_kelas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                kelas_id BIGINT UNSIGNED NOT NULL UNIQUE,
                guru_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (kelas_id) REFERENCES kelas(id),
                FOREIGN KEY (guru_id) REFERENCES guru(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS mapel (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                kode VARCHAR(30) NOT NULL UNIQUE,
                nama VARCHAR(150) NOT NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS rombel (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                semester_id BIGINT UNSIGNED NOT NULL,
                mapel_id BIGINT UNSIGNED NOT NULL,
                nama VARCHAR(100) NOT NULL,
                tingkat TINYINT UNSIGNED NOT NULL,
                jenis ENUM('reguler','agama','bahasa_asing','pilihan') NOT NULL DEFAULT 'reguler',
                kelompok_rapor ENUM('agama','umum','wajib','pilihan','mulok','bahasa_asing') NOT NULL DEFAULT 'umum',
                urutan_rapor SMALLINT UNSIGNED NOT NULL DEFAULT 1,
                metode_nilai ENUM('rata_rata','bobot') NOT NULL DEFAULT 'rata_rata',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (semester_id, mapel_id, nama),
                UNIQUE (id, semester_id, mapel_id),
                CHECK (tingkat IN (10,11,12)),
                FOREIGN KEY (semester_id) REFERENCES semester(id),
                FOREIGN KEY (mapel_id) REFERENCES mapel(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS guru_rombel (
                guru_id BIGINT UNSIGNED NOT NULL,
                rombel_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (guru_id, rombel_id),
                FOREIGN KEY (guru_id) REFERENCES guru(id),
                FOREIGN KEY (rombel_id) REFERENCES rombel(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS siswa_rombel (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                siswa_kelas_id BIGINT UNSIGNED NOT NULL,
                rombel_id BIGINT UNSIGNED NOT NULL,
                semester_id BIGINT UNSIGNED NOT NULL,
                mapel_id BIGINT UNSIGNED NOT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (siswa_kelas_id, mapel_id),
                UNIQUE (id, rombel_id),
                FOREIGN KEY (siswa_kelas_id, semester_id) REFERENCES siswa_kelas(id, semester_id),
                FOREIGN KEY (rombel_id, semester_id, mapel_id) REFERENCES rombel(id, semester_id, mapel_id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS tp (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                rombel_id BIGINT UNSIGNED NOT NULL,
                kode VARCHAR(20) NOT NULL,
                deskripsi TEXT NOT NULL,
                urutan TINYINT UNSIGNED NOT NULL,
                masuk_sts BOOLEAN NOT NULL DEFAULT FALSE,
                is_sas BOOLEAN NOT NULL DEFAULT FALSE,
                bobot DECIMAL(5,2) NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (rombel_id, urutan),
                UNIQUE (rombel_id, kode),
                UNIQUE (id, rombel_id),
                CHECK (urutan BETWEEN 1 AND 10),
                CHECK (bobot IS NULL OR (bobot > 0 AND bobot <= 100)),
                FOREIGN KEY (rombel_id) REFERENCES rombel(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS kktp (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tp_id BIGINT UNSIGNED NOT NULL UNIQUE,
                batas_minimal DECIMAL(5,2) NOT NULL,
                rentang_predikat JSON NULL,
                template_capaian TEXT NULL,
                template_penguatan TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                CHECK (batas_minimal BETWEEN 0 AND 100),
                FOREIGN KEY (tp_id) REFERENCES tp(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS nilai_tp (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                tp_id BIGINT UNSIGNED NOT NULL,
                siswa_rombel_id BIGINT UNSIGNED NOT NULL,
                rombel_id BIGINT UNSIGNED NOT NULL,
                nilai DECIMAL(5,2) NULL,
                diubah_oleh BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (tp_id, siswa_rombel_id),
                CHECK (nilai IS NULL OR nilai BETWEEN 0 AND 100),
                FOREIGN KEY (tp_id, rombel_id) REFERENCES tp(id, rombel_id),
                FOREIGN KEY (siswa_rombel_id, rombel_id) REFERENCES siswa_rombel(id, rombel_id),
                FOREIGN KEY (diubah_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS periode_nilai (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                semester_id BIGINT UNSIGNED NOT NULL,
                tingkat TINYINT UNSIGNED NOT NULL,
                jenis ENUM('STS','SAS') NOT NULL,
                tanggal_buka DATETIME NOT NULL,
                tanggal_tutup DATETIME NOT NULL,
                status ENUM('draf','dibuka','ditutup') NOT NULL DEFAULT 'draf',
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (semester_id, tingkat, jenis),
                CHECK (tingkat IN (10,11,12)),
                CHECK (tanggal_tutup > tanggal_buka),
                FOREIGN KEY (semester_id) REFERENCES semester(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS paket_nilai (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                rombel_id BIGINT UNSIGNED NOT NULL,
                periode_id BIGINT UNSIGNED NOT NULL,
                kelas_id BIGINT UNSIGNED NOT NULL,
                status ENUM('draf','terkirim','revisi','disetujui','terkunci') NOT NULL DEFAULT 'draf',
                dikirim_oleh BIGINT UNSIGNED NULL,
                waktu_kirim DATETIME NULL,
                dikunci_oleh BIGINT UNSIGNED NULL,
                waktu_kunci DATETIME NULL,
                alasan_koreksi TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (rombel_id, periode_id, kelas_id),
                FOREIGN KEY (rombel_id) REFERENCES rombel(id),
                FOREIGN KEY (periode_id) REFERENCES periode_nilai(id),
                FOREIGN KEY (kelas_id) REFERENCES kelas(id),
                FOREIGN KEY (dikirim_oleh) REFERENCES users(id),
                FOREIGN KEY (dikunci_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS verifikasi_nilai (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                paket_id BIGINT UNSIGNED NOT NULL,
                pemeriksa_id BIGINT UNSIGNED NOT NULL,
                keputusan ENUM('disetujui','revisi') NOT NULL,
                catatan TEXT NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                CHECK (
                    keputusan <> 'revisi'
                    OR (catatan IS NOT NULL AND CHAR_LENGTH(TRIM(catatan)) > 0)
                ),
                FOREIGN KEY (paket_id) REFERENCES paket_nilai(id),
                FOREIGN KEY (pemeriksa_id) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS ekskul (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama VARCHAR(150) NOT NULL UNIQUE,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS peserta_ekskul (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ekskul_id BIGINT UNSIGNED NOT NULL,
                siswa_kelas_id BIGINT UNSIGNED NOT NULL,
                predikat ENUM('SB','B','K') NULL,
                catatan TEXT NULL,
                diisi_oleh BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (ekskul_id, siswa_kelas_id),
                FOREIGN KEY (ekskul_id) REFERENCES ekskul(id),
                FOREIGN KEY (siswa_kelas_id) REFERENCES siswa_kelas(id),
                FOREIGN KEY (diisi_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS pelengkap_rapor (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                siswa_kelas_id BIGINT UNSIGNED NOT NULL,
                periode_id BIGINT UNSIGNED NOT NULL,
                catatan_wali TEXT NULL,
                kokurikuler TEXT NULL,
                sakit SMALLINT UNSIGNED NULL,
                izin SMALLINT UNSIGNED NULL,
                alpa SMALLINT UNSIGNED NULL,
                sumber_presensi VARCHAR(150) NULL,
                diisi_oleh BIGINT UNSIGNED NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (siswa_kelas_id, periode_id),
                FOREIGN KEY (siswa_kelas_id) REFERENCES siswa_kelas(id),
                FOREIGN KEY (periode_id) REFERENCES periode_nilai(id),
                FOREIGN KEY (diisi_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS template_rapor (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                sekolah_id BIGINT UNSIGNED NOT NULL,
                jenis ENUM('STS','SEMESTER') NOT NULL,
                versi INT UNSIGNED NOT NULL DEFAULT 1,
                nama VARCHAR(150) NOT NULL,
                identitas_cetak JSON NULL,
                lokasi_template VARCHAR(255) NULL,
                aktif BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (sekolah_id, jenis, versi),
                FOREIGN KEY (sekolah_id) REFERENCES sekolah(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS rapor (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                siswa_kelas_id BIGINT UNSIGNED NOT NULL,
                periode_id BIGINT UNSIGNED NOT NULL,
                template_id BIGINT UNSIGNED NOT NULL,
                versi INT UNSIGNED NOT NULL DEFAULT 1,
                data_final JSON NOT NULL,
                file_pdf VARCHAR(500) NULL,
                tanggal_rapor DATE NOT NULL,
                status ENUM('terkunci','siap','terbit','dicabut') NOT NULL DEFAULT 'terkunci',
                diterbitkan_oleh BIGINT UNSIGNED NULL,
                waktu_terbit DATETIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                UNIQUE (siswa_kelas_id, periode_id, versi),
                CHECK (versi >= 1),
                FOREIGN KEY (siswa_kelas_id) REFERENCES siswa_kelas(id),
                FOREIGN KEY (periode_id) REFERENCES periode_nilai(id),
                FOREIGN KEY (template_id) REFERENCES template_rapor(id),
                FOREIGN KEY (diterbitkan_oleh) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE TABLE IF NOT EXISTS log_aktivitas (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                aksi VARCHAR(50) NOT NULL,
                nama_entitas VARCHAR(100) NOT NULL,
                entitas_id BIGINT UNSIGNED NULL,
                alasan TEXT NULL,
                data_sebelum JSON NULL,
                data_sesudah JSON NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                FOREIGN KEY (user_id) REFERENCES users(id)
            ) ENGINE=InnoDB;

            CREATE OR REPLACE VIEW v_nilai_semester AS
            SELECT
                sr.id AS siswa_rombel_id,
                sr.siswa_kelas_id,
                sr.rombel_id,
                COUNT(t.id) AS jumlah_tp,
                COUNT(n.nilai) AS jumlah_tp_terisi,
                CASE
                    WHEN COUNT(t.id) = 0 OR COUNT(n.nilai) <> COUNT(t.id) THEN NULL
                    WHEN r.metode_nilai = 'rata_rata' THEN ROUND(AVG(n.nilai), 0)
                    WHEN COUNT(t.bobot) <> COUNT(t.id) OR SUM(t.bobot) <> 100 THEN NULL
                    ELSE ROUND(SUM(n.nilai * t.bobot) / 100, 0)
                END AS nilai_akhir
            FROM siswa_rombel sr
            JOIN rombel r ON r.id = sr.rombel_id
            LEFT JOIN tp t ON t.rombel_id = r.id
            LEFT JOIN nilai_tp n ON n.tp_id = t.id AND n.siswa_rombel_id = sr.id
            GROUP BY sr.id, sr.siswa_kelas_id, sr.rombel_id, r.metode_nilai;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kosongkan tidak apa-apa
    }
};