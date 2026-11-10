-- DATABASE LENGKAP nava (A1-A4, A3 sudah direvisi sesuai desain)
-- PERHATIAN: file ini MENGHAPUS lalu membuat ulang semua tabel di bawah (termasuk dimensi_riasec, guru, siswa,
-- user, kelas, riwayat_kelas, sesi_tes, soal_riasec, dll). Data lamanya hilang -> EXPORT / BACKUP DULU.
-- Tabel lama A3 (jawaban_riasec, hasil_tes_riasec, rekomendasi_*, topik_bk) juga dihapus.
-- Tabel 'migrations' dan 'permintaan_konsultasi' TIDAK disentuh.

-- SIAKAD SMA 1 | Integrasi A1-A4

CREATE DATABASE IF NOT EXISTS nava
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nava;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS jawaban_riasec;
DROP TABLE IF EXISTS hasil_tes_riasec;
DROP TABLE IF EXISTS rekomendasi_sepuluh;
DROP TABLE IF EXISTS rekomendasi_dua_belas;
DROP TABLE IF EXISTS topik_bk;
DROP VIEW IF EXISTS v_hasil_minat_siswa;
DROP VIEW IF EXISTS v_riwayat_tes;
DROP VIEW IF EXISTS v_rekap_tes_bk;
DROP VIEW IF EXISTS v_skor_dimensi;
DROP VIEW IF EXISTS v_kecocokan_mapel;
DROP VIEW IF EXISTS v_kecocokan_prodi;
DROP VIEW IF EXISTS v_rekomendasi_mapel;
DROP VIEW IF EXISTS v_rekomendasi_prodi;
DROP VIEW IF EXISTS v_prodi_relevan_mapel;
DROP TABLE IF EXISTS pengajuan_bk;
DROP TABLE IF EXISTS guru_bk;
DROP TABLE IF EXISTS sesi_tes;
DROP TABLE IF EXISTS profesi_prodi;
DROP TABLE IF EXISTS profesi;
DROP TABLE IF EXISTS program_studi;
DROP TABLE IF EXISTS profil_mapel;
DROP TABLE IF EXISTS periode_tes;
DROP TABLE IF EXISTS soal_riasec;
DROP TABLE IF EXISTS wali_kelas;
DROP TABLE IF EXISTS verifikasi_nilai;
DROP TABLE IF EXISTS paket_nilai;
DROP TABLE IF EXISTS guru_rombel;
DROP TABLE IF EXISTS nilai_semester;
DROP TABLE IF EXISTS nilai_tp;
DROP TABLE IF EXISTS kktp;
DROP TABLE IF EXISTS tp;
DROP TABLE IF EXISTS siswa_rombel;
DROP TABLE IF EXISTS rombel;
DROP TABLE IF EXISTS pelengkap_rapor;
DROP TABLE IF EXISTS rapor;
DROP TABLE IF EXISTS periode_nilai;
DROP TABLE IF EXISTS peserta_ekskul;
DROP TABLE IF EXISTS riwayat_kelas;
DROP TABLE IF EXISTS semester;
DROP TABLE IF EXISTS kelas;
DROP TABLE IF EXISTS tahun_ajaran;
DROP TABLE IF EXISTS mapel;
DROP TABLE IF EXISTS dimensi_riasec;
DROP TABLE IF EXISTS predikat;
DROP TABLE IF EXISTS ekskul;
DROP TABLE IF EXISTS template_rapor;
DROP TABLE IF EXISTS izin;
DROP TABLE IF EXISTS sakit;
DROP TABLE IF EXISTS presensi;
DROP TABLE IF EXISTS lokasi_sekolah;
DROP TABLE IF EXISTS qr_siswa;
DROP TABLE IF EXISTS catatan_akademik;
DROP TABLE IF EXISTS log_aktivitas;
DROP TABLE IF EXISTS kepala_sekolah;
DROP TABLE IF EXISTS sekolah;
DROP TABLE IF EXISTS kesiswaan;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS budaya_positif;
DROP TABLE IF EXISTS kurikulum;
DROP TABLE IF EXISTS akademik;
DROP TABLE IF EXISTS orang_tua;
DROP TABLE IF EXISTS guru;
DROP TABLE IF EXISTS siswa;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;


/* =====================================================
   1. AKUN DAN PROFIL PENGGUNA
   Akun, autentikasi, profil siswa/guru/orang tua, dan peran administratif.
   Akun guru bisa merangkap tugas wali kelas melalui tabel penugasan; role umum tetap disimpan di user.
   ===================================================== */

-- USER: Identitas login dan role umum; password disimpan dalam bentuk hash.
CREATE TABLE user (
  id_user INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  must_change_password boolean default 1,
  role ENUM('SISWA','GURU','ORANG_TUA','AKADEMIK','KURIKULUM','BUDAYA_POSITIF','KESISWAAN','ADMIN') NOT NULL
) ENGINE=InnoDB;

-- SISWA: Profil siswa dan identitas akademik dasar.
CREATE TABLE siswa (
  id_siswa INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  nis VARCHAR(30) NOT NULL UNIQUE,
  nisn VARCHAR(30) UNIQUE,
  agama VARCHAR(30),
  jenis_kelamin ENUM('L','P'),
  tahun_masuk smallint unsigned,
  urutan_masuk int unsigned,
  alamat TEXT,
  nama VARCHAR(120) NOT NULL,
  CONSTRAINT fk_siswa_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- GURU: Profil guru dan identitas kepegawaian.
CREATE TABLE guru (
  id_guru INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  nip VARCHAR(30) UNIQUE,
  status_kepegawaian VARCHAR(50),
  nama VARCHAR(120) NOT NULL,
  agama VARCHAR(30),
  jenis_kelamin ENUM('L','P'),
  alamat TEXT,
  CONSTRAINT fk_guru_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- ORANG_TUA: Data hubungan orang tua dan anak beserta status verifikasi akses.
CREATE TABLE orang_tua (
    id_siswa INT UNSIGNED NOT NULL,
    hubungan ENUM('Ayah', 'Ibu', 'Wali') NOT NULL,
    id_user INT UNSIGNED,
    alamat TEXT,
    no_telepon VARCHAR(25),
    jenis_kelamin ENUM('L','P'),
    nama VARCHAR(120) NOT NULL,
    status_verifikasi ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    diverifikasi_oleh INT UNSIGNED NULL,
    waktu_verifikasi DATETIME NULL,

    PRIMARY KEY (id_siswa, hubungan),
    
    unique (id_user, id_siswa),

    CONSTRAINT fk_ortu_siswa
        FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa),

    CONSTRAINT fk_ortu_user
        FOREIGN KEY (id_user) REFERENCES user(id_user),
    CONSTRAINT fk_ortu_pemeriksa FOREIGN KEY (diverifikasi_oleh) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- AKADEMIK: Profil fungsi akademik yang terhubung ke akun.
CREATE TABLE akademik (
  id_akademik INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  CONSTRAINT fk_akademik_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- KURIKULUM: Profil fungsi kurikulum yang terhubung ke akun.
CREATE TABLE kurikulum (
  id_kurikulum INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  CONSTRAINT fk_kurikulum_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- BUDAYA_POSITIF: Profil fungsi budaya positif yang terhubung ke akun.
CREATE TABLE budaya_positif (
  id_budaya_positif INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  CONSTRAINT fk_bp_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- ADMIN: Profil fungsi admin yang terhubung ke akun.
CREATE TABLE admin (
  id_admin INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_user INT UNSIGNED NOT NULL UNIQUE,
  CONSTRAINT fk_admin_user FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- KESISWAAN: Profil fungsi kesiswaan yang mengelola kegiatan dan penilaian ekstrakurikuler.
CREATE TABLE kesiswaan (
    id_kesiswaan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT UNSIGNED NOT NULL UNIQUE,
    CONSTRAINT fk_kesiswaan_user
        FOREIGN KEY (id_user) REFERENCES user(id_user)
) ENGINE=InnoDB;


/* =====================================================
   2. IDENTITAS SEKOLAH DAN AUDIT
   Informasi sekolah, riwayat kepala sekolah, dan log aktivitas.
   Log aktivitas menyimpan jejak aksi penting dari seluruh modul.
   ===================================================== */

-- SEKOLAH: Identitas sekolah untuk administrasi dan rapor.
CREATE TABLE sekolah (
  id_sekolah INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL,
  alamat TEXT,
  telepon VARCHAR(25),
  email VARCHAR(100),
  npsn VARCHAR(10)
) ENGINE=InnoDB;

-- KEPALA_SEKOLAH: Riwayat penugasan kepala sekolah beserta masa berlaku.
CREATE TABLE kepala_sekolah (
    id_kepala_sekolah INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_sekolah INT UNSIGNED NOT NULL,
    id_guru INT UNSIGNED NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NULL,

    CONSTRAINT fk_kepsek_sekolah
        FOREIGN KEY (id_sekolah)
        REFERENCES sekolah(id_sekolah),

    CONSTRAINT fk_kepsek_guru
        FOREIGN KEY (id_guru)
        REFERENCES guru(id_guru)
) ENGINE=InnoDB;

-- LOG_AKTIVITAS: Audit perubahan penting berikut pelaku dan snapshot data.
CREATE TABLE log_aktivitas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_user INT UNSIGNED NOT NULL,
    aksi VARCHAR(50) NOT NULL,
    nama_entitas VARCHAR(50) NOT NULL,
    entitas_id BIGINT UNSIGNED NOT NULL,
    alasan TEXT NULL,
    data_sebelum JSON NULL,
    data_sesudah JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_log_aktivitas_user
        FOREIGN KEY (id_user)
        REFERENCES user(id_user)
        ON DELETE RESTRICT,

    INDEX idx_log_entitas (nama_entitas, entitas_id),
    INDEX idx_log_user_created (id_user, created_at)
) ENGINE=InnoDB;


/* =====================================================
   3. CATATAN AKADEMIK DAN PRESENSI
   Catatan siswa, QR, lokasi sekolah, presensi, sakit, dan izin.
   Data presensi dapat digunakan modul lain sesuai hak akses.
   ===================================================== */

-- CATATAN_AKADEMIK: Catatan akademik yang terkait dengan siswa.
CREATE TABLE catatan_akademik (
  id_catatan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT UNSIGNED NOT NULL,
  kategori VARCHAR(60) NOT NULL,
  catatan TEXT NOT NULL,
  CONSTRAINT fk_catatan_siswa FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
) ENGINE=InnoDB;

-- QR_SISWA: Kode QR unik untuk kebutuhan presensi.
CREATE TABLE qr_siswa (
  id_qr INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT UNSIGNED NOT NULL,
  kode_qr VARCHAR(255) NOT NULL UNIQUE,
  CONSTRAINT fk_qr_siswa FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
) ENGINE=InnoDB;

-- LOKASI_SEKOLAH: Koordinat dan radius lokasi untuk presensi.
CREATE TABLE lokasi_sekolah (
  id_lokasi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  nama_lokasi VARCHAR(120) NOT NULL,
  radius INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- PRESENSI: Rekap kehadiran siswa per tanggal.
CREATE TABLE presensi (
  id_presensi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT UNSIGNED NOT NULL,
  metode ENUM('QR', 'Geofencing') NOT NULL,
  status enum ('Hadir', 'Alpa', 'Sakit', 'Izin', 'Terlambat'),
  tanggal DATE NOT NULL,
  waktu_presensi TIME,
  CONSTRAINT fk_presensi_siswa FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa)
) ENGINE=InnoDB;

-- SAKIT: Bukti dan data izin sakit yang mengacu presensi.
CREATE TABLE sakit (
  id_sakit INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_presensi INT UNSIGNED NOT NULL,
  file_bukti VARCHAR(255),
  CONSTRAINT fk_sakit_presensi FOREIGN KEY (id_presensi) REFERENCES presensi(id_presensi)
) ENGINE=InnoDB;

-- IZIN: Bukti dan alasan izin yang mengacu presensi.
CREATE TABLE izin (
  id_izin INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_presensi INT UNSIGNED NOT NULL,
  file_bukti VARCHAR(255),
  alasan TEXT,
  CONSTRAINT fk_izin_presensi FOREIGN KEY (id_presensi) REFERENCES presensi(id_presensi)
) ENGINE=InnoDB;


/* =====================================================
   4. REFERENSI RAPOR DAN EKSTRAKURIKULER
   Template rapor, daftar ekstrakurikuler, dan predikat penilaian.
   Nilai ekskul siswa dan hasil rapor dihubungkan ke semester pada tabel berikutnya.
   ===================================================== */

-- TEMPLATE_RAPOR: Konfigurasi template keluaran rapor.
CREATE TABLE template_rapor (
  id_template INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_sekolah INT UNSIGNED NOT NULL,
  jenis VARCHAR(50),
  versi VARCHAR(30),
  nama VARCHAR(120),
  identitas_cetak TEXT,
  lokasi_template TEXT,
  CONSTRAINT fk_tr_sekolah FOREIGN KEY (id_sekolah) REFERENCES sekolah(id_sekolah)
) ENGINE=InnoDB;

-- EKSKUL: Master kegiatan ekstrakurikuler.
CREATE TABLE ekskul (
  id_ekskul INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL
) ENGINE=InnoDB;

-- PREDIKAT: Master kategori predikat dan rentang nilai ekskul.
CREATE TABLE predikat (
    id_predikat INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    predikat ENUM('SB', 'B', 'K') NOT NULL,
    nilai_min DECIMAL(5,2) NOT NULL,
    nilai_max DECIMAL(5,2) NOT NULL,
    keterangan TEXT NULL,
    aktif BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;


/* =====================================================
   5. REFERENSI DIMENSI DAN MATA PELAJARAN
   Dimensi RIASEC (A3) dan master mata pelajaran (A2). Tabel A3 lainnya ada di bagian 12.
   ===================================================== */

-- DIMENSI_RIASEC: Enam dimensi minat RIASEC (tetap). Hanya deskripsi yang dapat diedit Administrator.
-- Tetap di posisi ini karena mapel (A2) mereferensikan id_dimensi.
CREATE TABLE dimensi_riasec (
  id_dimensi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode CHAR(1) COLLATE utf8mb4_bin NOT NULL UNIQUE,
  nama VARCHAR(120) NOT NULL,
  deskripsi TEXT NOT NULL,
  CONSTRAINT chk_dimensi_kode CHECK (kode IN ('R','I','A','S','E','C'))
) ENGINE=InnoDB;


-- MAPEL: Master mata pelajaran.
CREATE TABLE mapel (
  id_mapel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_dimensi int unsigned not null,
  kode VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(120) NOT null,
  CONSTRAINT fk_dimensi FOREIGN KEY (id_dimensi) REFERENCES dimensi_riasec(id_dimensi)
) ENGINE=InnoDB;







/* =====================================================
   6. TAHUN AJARAN, SEMESTER, DAN KELAS
   Referensi tahun ajaran dan semester, kelas asal, serta riwayat penempatan siswa.
   Gasal dan Genap dari tahun ajaran berbeda memiliki ID semester berbeda.
   ===================================================== */

-- TAHUN_AJARAN: Master tahun ajaran yang dipakai bersama semua modul.
CREATE TABLE tahun_ajaran (
  id_tahun_ajaran INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(9) NOT NULL UNIQUE,
  aktif BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- KELAS: Master kelas administratif sekolah.
CREATE TABLE kelas (
  id_kelas INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(50) NOT NULL,
  tingkat ENUM('X', 'XI', 'XII') NOT NULL,
  fase ENUM('E', 'F') NOT NULL,
  id_tahun_ajaran INT UNSIGNED NOT NULL,
  CONSTRAINT fk_kelas_ta FOREIGN KEY (id_tahun_ajaran) REFERENCES tahun_ajaran(id_tahun_ajaran),
  UNIQUE KEY uq_kelas_ta (nama,id_tahun_ajaran)
) ENGINE=InnoDB;

-- SEMESTER: Identitas semester Gasal/Genap dalam suatu tahun ajaran.
CREATE TABLE semester (
  id_semester INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tahun_ajaran INT UNSIGNED NOT NULL,
  nama ENUM('GASAL','GENAP') NOT NULL,
  aktif BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_sem_ta FOREIGN KEY (id_tahun_ajaran) REFERENCES tahun_ajaran(id_tahun_ajaran),
  UNIQUE KEY uq_sem_ta (id_tahun_ajaran,nama)
) ENGINE=InnoDB;

-- RIWAYAT_KELAS: Riwayat kelas asal siswa berdasarkan semester.
CREATE TABLE riwayat_kelas (
  id_riwayat_kelas INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_siswa INT UNSIGNED NOT NULL,
  id_kelas INT UNSIGNED NOT NULL,
  id_semester INT UNSIGNED NOT NULL,
  CONSTRAINT fk_rk_siswa FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa),
  CONSTRAINT fk_rk_kelas FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas),
  CONSTRAINT fk_rk_sem FOREIGN KEY (id_semester) REFERENCES semester(id_semester),
  UNIQUE KEY uq_riwayat_siswa_sem (id_siswa,id_semester)
) ENGINE=InnoDB;


/* =====================================================
   7. PENILAIAN EKSKUL, PERIODE, DAN RAPOR
   Keikutsertaan ekskul, periode STS/SAS, rapor, dan data pelengkapnya.
   Tanggal/status pengisian nilai dikendalikan Kurikulum atau Admin Operator melalui aplikasi.
   ===================================================== */

-- PESERTA_EKSKUL: Keikutsertaan dan hasil penilaian ekstrakurikuler siswa.
CREATE TABLE peserta_ekskul (
  id_peserta_ekskul INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_ekskul INT UNSIGNED NOT NULL,
  id_siswa INT UNSIGNED NOT NULL,
  id_semester INT UNSIGNED NOT NULL,
  id_predikat int unsigned not null,
  catatan TEXT null,
  diisi_oleh INT unsigned null,
  created_at TIMESTAMP null,
  updated_at TIMESTAMP null,
  CONSTRAINT fk_pe_ekskul FOREIGN KEY (id_ekskul) REFERENCES ekskul(id_ekskul),
  CONSTRAINT fk_pe_siswa FOREIGN KEY (id_siswa) REFERENCES siswa(id_siswa),
  constraint fk_pe_diisi foreign key (diisi_oleh) references user (id_user),
  constraint fk_pe_predikat foreign key (id_predikat) references predikat (id_predikat),
  CONSTRAINT fk_pe_sem FOREIGN KEY (id_semester) REFERENCES semester(id_semester),
  UNIQUE KEY uq_peserta_ekskul_sem (id_ekskul,id_siswa,id_semester)
) ENGINE=InnoDB;

-- PERIODE_NILAI: Jadwal dan status pengisian STS/SAS per semester dan tingkat.
CREATE TABLE periode_nilai (
  id_periode INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_semester INT UNSIGNED NOT NULL,
  tingkat VARCHAR(10) NOT NULL,
  jenis ENUM('STS', 'SAS') NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  status enum ('draf', 'dibuka', 'ditutup', 'terkunci') not null,
  created_at timestamp null,
  updated_at timestamp null,
  CONSTRAINT chk_periode_tanggal CHECK (tanggal_mulai <= tanggal_selesai),
  CONSTRAINT fk_periode_sem FOREIGN KEY (id_semester) REFERENCES semester(id_semester),
  UNIQUE KEY uq_periode (id_semester, tingkat, jenis)
) ENGINE=InnoDB;

-- RAPOR: Dokumen rapor final, status publikasi, dan penerbitnya.
CREATE TABLE rapor (
  id_rapor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL,
  id_periode INT UNSIGNED NOT NULL,
  id_template INT UNSIGNED NOT NULL,
  versi VARCHAR(30) not null,
  data_final JSON not null,
  file_pdf VARCHAR(255) null,
  tanggal_cetak DATE null,
  status ENUM('terkunci','siap','terbit','dicabut') not null,
  diterbitkan_oleh INT unsigned null,
  waktu_terbit DATETIME null,
  created_at TIMESTAMP null,
  updated_at TIMESTAMP null,
  CONSTRAINT fk_rapor_riwayat FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas),
  CONSTRAINT fk_rapor_periode FOREIGN KEY (id_periode) REFERENCES periode_nilai(id_periode),
  CONSTRAINT fk_rapor_template FOREIGN KEY (id_template) REFERENCES template_rapor(id_template),
  constraint fk_rapor_diterbitkan foreign key (diterbitkan_oleh) references user (id_user)
) ENGINE=InnoDB;

-- PELENGKAP_RAPOR: Catatan wali kelas, kokurikuler, dan ringkasan kehadiran.
CREATE TABLE pelengkap_rapor (
  id_pelengkap_rapor INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL,
  id_periode INT UNSIGNED NOT NULL,
  catatan_wali TEXT,
  kokurikuler TEXT,
  sakit INT UNSIGNED DEFAULT 0,
  izin INT UNSIGNED DEFAULT 0,
  alpha INT UNSIGNED DEFAULT 0,
  id_presensi INT UNSIGNED NULL,
  CONSTRAINT fk_pelengkap_riwayat FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas),
  CONSTRAINT fk_pelengkap_periode FOREIGN KEY (id_periode) REFERENCES periode_nilai(id_periode),
  CONSTRAINT fk_pelengkap_presensi FOREIGN KEY (id_presensi) REFERENCES presensi(id_presensi),
  UNIQUE KEY uq_pelengkap_rapor (id_riwayat_kelas,id_periode)
) ENGINE=InnoDB;


/* =====================================================
   9. ROMBEL DAN NILAI PEMBELAJARAN
   Rombel mata pelajaran, peserta, tujuan pembelajaran, KKTP, dan nilai.
   Penugasan guru mapel di guru_rombel dan penilaian siswa mengikuti rombel terkait.
   ===================================================== */

-- ROMBEL: Rombongan belajar per mata pelajaran dan semester.
CREATE TABLE rombel (
  id_rombel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_semester INT UNSIGNED NOT NULL,
  id_mapel INT UNSIGNED NOT NULL,
  nama VARCHAR(80) NOT NULL,
  tingkat VARCHAR(10),
  jam VARCHAR(50),
  kelompok_rapor VARCHAR(80),
  metode_nilai VARCHAR(80),
  CONSTRAINT fk_rombel_mapel FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel),
  CONSTRAINT fk_rombel_sem FOREIGN KEY (id_semester) REFERENCES semester(id_semester)
) ENGINE=InnoDB;

-- SISWA_ROMBEL: Keanggotaan siswa dalam rombel mata pelajaran.
CREATE TABLE siswa_rombel (
  id_siswarombel INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL,
  id_rombel INT UNSIGNED NOT NULL,
  id_mapel INT UNSIGNED NOT NULL,
  CONSTRAINT fk_sr_riwayat_kelas FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas),
  CONSTRAINT fk_sr_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel),
  CONSTRAINT fk_sr_mapel FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel),
  UNIQUE KEY uq_siswa_rombel (id_riwayat_kelas,id_rombel)
) ENGINE=InnoDB;

-- TP: Tujuan pembelajaran dan penanda STS/SAS.
CREATE TABLE tp (
  id_tp INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_rombel INT UNSIGNED NOT NULL,
  kode VARCHAR(30),
  deskripsi TEXT,
  urutan INT,
  masuk_sts BOOLEAN DEFAULT FALSE,
  is_sas BOOLEAN DEFAULT FALSE,
   bobot DECIMAL(6,2),
  CONSTRAINT fk_tp_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel)
) ENGINE=InnoDB;

-- KKTP: Kriteria ketercapaian tujuan pembelajaran.
CREATE TABLE kktp (
  id_kktp INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tp INT UNSIGNED NOT NULL,
  batas_minimal DECIMAL(6,2),
  tentang_predikat TEXT,
  template_capaian TEXT,
  template_penguatan TEXT,
  CONSTRAINT fk_kktp_tp FOREIGN KEY (id_tp) REFERENCES tp(id_tp)
) ENGINE=InnoDB;

-- NILAI_TP: Nilai siswa untuk tiap tujuan pembelajaran.
CREATE TABLE nilai_tp (
  id_nilai_tp INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_tp INT UNSIGNED NOT NULL,
  id_siswarombel INT UNSIGNED NOT NULL,
  id_rombel INT UNSIGNED NOT NULL,
  nilai DECIMAL(6,2),
  CONSTRAINT fk_nilai_tp_tp FOREIGN KEY (id_tp) REFERENCES tp(id_tp),
  CONSTRAINT fk_nilai_tp_siswarombel FOREIGN KEY (id_siswarombel) REFERENCES siswa_rombel(id_siswarombel),
  CONSTRAINT fk_nilai_tp_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel),
  UNIQUE KEY uq_nilai_tp_siswa (id_tp,id_siswarombel)
) ENGINE=InnoDB;

-- NILAI_SEMESTER: Rekap nilai akhir siswa pada rombel.
CREATE TABLE nilai_semester (
  id_nilai_semester INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL,
  id_rombel INT UNSIGNED NULL,
  jumlah_tp INT,
  jumlah_tp_terisi INT,
  nilai_akhir DECIMAL(6,2),
  CONSTRAINT fk_na_riwayat FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas),
  CONSTRAINT fk_ns_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel),
  UNIQUE KEY uq_ns_siswa_rombel (id_riwayat_kelas,id_rombel)
) ENGINE=InnoDB;

-- GURU_ROMBEL: Penugasan guru pengampu mata pelajaran.
CREATE TABLE guru_rombel (
  id_guru INT UNSIGNED NOT NULL,
  id_rombel INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_guru, id_rombel),
  CONSTRAINT fk_gr_guru FOREIGN KEY (id_guru) REFERENCES guru(id_guru),
  CONSTRAINT fk_gr_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel)
) ENGINE=InnoDB;


/* =====================================================
   10. WORKFLOW DAN VERIFIKASI NILAI
   Paket nilai dan riwayat keputusan pemeriksaan nilai.
   Perubahan status draf, terkirim, revisi, disetujui, dan terkunci diatur oleh backend.
   ===================================================== */

-- PAKET_NILAI: Paket nilai dan status pengiriman/penguncian.
CREATE TABLE paket_nilai (
  id_paket_nilai INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_periode int unsigned not null,
  id_kelas INT UNSIGNED NOT NULL,
  id_rombel INT UNSIGNED NOT NULL,
  status ENUM('draf','terkirim','revisi','disetujui','terkunci') not null,
  dikirim_oleh INT unsigned null,
  waktu_kirim DATETIME null,
  dikunci_oleh INT unsigned null,
  waktu_kunci DATETIME null,
  alasan_koreksi TEXT null,
  created_at TIMESTAMP null,
  updated_at TIMESTAMP null,
  CONSTRAINT fk_pn_kelas FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas),
  CONSTRAINT fk_pn_rombel FOREIGN KEY (id_rombel) REFERENCES rombel(id_rombel),
  constraint fk_pn_periode foreign key (id_periode) references periode_nilai (id_periode),
  constraint fk_pk_user foreign key (dikunci_oleh) references user(id_user),
  CONSTRAINT fk_pn_pengirim FOREIGN KEY (dikirim_oleh) REFERENCES user(id_user),
  UNIQUE KEY uq_paket_rombel_periode (id_rombel,id_periode)
) ENGINE=InnoDB;

-- VERIFIKASI_NILAI: Riwayat keputusan pemeriksaan paket nilai.
CREATE TABLE verifikasi_nilai (
  id_verifikasi_nilai BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_paket_nilai INT UNSIGNED NOT NULL,
  id_pemeriksa INT UNSIGNED NOT NULL,
  keputusan ENUM('disetujui','revisi') NOT NULL,
  catatan TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_vn_paket FOREIGN KEY (id_paket_nilai) REFERENCES paket_nilai(id_paket_nilai),
  CONSTRAINT fk_vn_pemeriksa FOREIGN KEY (id_pemeriksa) REFERENCES user(id_user),
  INDEX idx_vn_paket_time (id_paket_nilai, created_at)
) ENGINE=InnoDB;


/* =====================================================
   11. PENUGASAN WALI KELAS
   Penugasan wali kelas secara terpisah dari riwayat penempatan siswa.
   Hak akses wali kelas diperiksa berdasarkan penugasan yang berlaku.
   ===================================================== */

-- WALI_KELAS: Penugasan guru sebagai wali kelas.
CREATE TABLE wali_kelas (
  id_wali_kelas INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_kelas INT UNSIGNED NOT NULL,
  id_guru INT UNSIGNED NOT NULL,
  id_semester INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  CONSTRAINT fk_wk_kelas FOREIGN KEY (id_kelas) REFERENCES kelas(id_kelas),
  CONSTRAINT fk_wk_guru FOREIGN KEY (id_guru) REFERENCES guru(id_guru),
  CONSTRAINT fk_wk_sem FOREIGN KEY (id_semester) REFERENCES semester(id_semester),
  UNIQUE KEY uq_wali_kelas_sem (id_kelas,id_semester)
) ENGINE=InnoDB;

/* =====================================================
   12. MODUL A3 - PEMETAAN MINAT, PEMINATAN, DAN BIMBINGAN KONSELING
   Mengikuti desain A3 (Administrator, BK, Kelas X, XI, XII).
   Data pengguna, siswa, guru, kelas, semester, riwayat kelas, mapel, dan rombel dibaca dari A1/A2.
   Metode kecocokan: Cosine Similarity (lihat view v_kecocokan_mapel / v_kecocokan_prodi).
   Tes dilakukan per semester sesuai periode_tes (jenjang X, XI, XII); satu hasil per siswa per semester.
   Rekomendasi (5 teratas) dan rekap dihitung lewat view, tidak disimpan di tabel.
   ===================================================== */

-- SOAL_RIASEC: Bank pertanyaan tes (Administrator > Pertanyaan Tes). Urutan tes mengikuti id.
CREATE TABLE soal_riasec (
  id_soal_riasec INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_dimensi INT UNSIGNED NOT NULL,
  teks TEXT NOT NULL,
  aktif BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT fk_sr_dimensi FOREIGN KEY (id_dimensi) REFERENCES dimensi_riasec(id_dimensi)
) ENGINE=InnoDB;

-- PERIODE_TES: Jadwal tes per semester dan jenjang (Administrator > Periode Tes).
CREATE TABLE periode_tes (
  id_periode_tes INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_semester INT UNSIGNED NOT NULL,
  nama VARCHAR(120) NOT NULL,
  tingkat ENUM('X','XI','XII') NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  status ENUM('terjadwal','aktif','selesai') NOT NULL DEFAULT 'terjadwal',
  CONSTRAINT chk_periode_tes_tanggal CHECK (tanggal_mulai <= tanggal_selesai),
  CONSTRAINT fk_pt_semester FOREIGN KEY (id_semester) REFERENCES semester(id_semester),
  UNIQUE KEY uq_periode_tes (id_semester, tingkat)
) ENGINE=InnoDB;

-- PROFIL_MAPEL: Katalog Mapel Pilihan (kode dan nama dari mapel A2) beserta rumpun, profil Holland 3 huruf,
-- dan status validasi Guru BK. Hanya mapel tervalidasi yang direkomendasikan.
CREATE TABLE profil_mapel (
  id_mapel INT UNSIGNED PRIMARY KEY,
  rumpun VARCHAR(50) NOT NULL,
  deskripsi TEXT NULL,
  kode_holland CHAR(3) COLLATE utf8mb4_bin NOT NULL,
  status_validasi ENUM('menunggu','tervalidasi','perlu_revisi') NOT NULL DEFAULT 'menunggu',
  catatan_validasi TEXT NULL,
  divalidasi_oleh INT UNSIGNED NULL,
  waktu_validasi DATETIME NULL,
  CONSTRAINT chk_pm_kode_holland CHECK (kode_holland REGEXP '^[RIASEC]{3}$'
    AND SUBSTRING(kode_holland,1,1) <> SUBSTRING(kode_holland,2,1)
    AND SUBSTRING(kode_holland,1,1) <> SUBSTRING(kode_holland,3,1)
    AND SUBSTRING(kode_holland,2,1) <> SUBSTRING(kode_holland,3,1)),
  CONSTRAINT chk_pm_validasi CHECK (
    (status_validasi = 'menunggu' AND divalidasi_oleh IS NULL AND waktu_validasi IS NULL)
    OR (status_validasi <> 'menunggu' AND waktu_validasi IS NOT NULL)),
  CONSTRAINT fk_pm_mapel FOREIGN KEY (id_mapel) REFERENCES mapel(id_mapel),
  CONSTRAINT fk_pm_validator FOREIGN KEY (divalidasi_oleh) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- PROGRAM_STUDI: Master program studi (kategori, deskripsi, profil Holland, status validasi Guru BK).
CREATE TABLE program_studi (
  id_prodi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL UNIQUE,
  kategori VARCHAR(60) NULL,
  deskripsi TEXT NOT NULL,
  kode_holland CHAR(3) COLLATE utf8mb4_bin NOT NULL,
  status_validasi ENUM('menunggu','tervalidasi','perlu_revisi') NOT NULL DEFAULT 'menunggu',
  catatan_validasi TEXT NULL,
  divalidasi_oleh INT UNSIGNED NULL,
  waktu_validasi DATETIME NULL,
  CONSTRAINT chk_ps_kode_holland CHECK (kode_holland REGEXP '^[RIASEC]{3}$'
    AND SUBSTRING(kode_holland,1,1) <> SUBSTRING(kode_holland,2,1)
    AND SUBSTRING(kode_holland,1,1) <> SUBSTRING(kode_holland,3,1)
    AND SUBSTRING(kode_holland,2,1) <> SUBSTRING(kode_holland,3,1)),
  CONSTRAINT chk_ps_validasi CHECK (
    (status_validasi = 'menunggu' AND divalidasi_oleh IS NULL AND waktu_validasi IS NULL)
    OR (status_validasi <> 'menunggu' AND waktu_validasi IS NOT NULL)),
  CONSTRAINT fk_ps_validator FOREIGN KEY (divalidasi_oleh) REFERENCES user(id_user)
) ENGINE=InnoDB;

-- PROFESI: Master profesi (Administrator > Profesi).
CREATE TABLE profesi (
  id_profesi INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(120) NOT NULL UNIQUE,
  deskripsi TEXT NOT NULL
) ENGINE=InnoDB;

-- PROFESI_PRODI: Program studi terkait suatu profesi (juga dipakai sebagai Prospek Profesi di detail prodi).
CREATE TABLE profesi_prodi (
  id_profesi INT UNSIGNED NOT NULL,
  id_prodi INT UNSIGNED NOT NULL,
  PRIMARY KEY (id_profesi, id_prodi),
  CONSTRAINT fk_pp_profesi FOREIGN KEY (id_profesi) REFERENCES profesi(id_profesi) ON DELETE CASCADE,
  CONSTRAINT fk_pp_prodi FOREIGN KEY (id_prodi) REFERENCES program_studi(id_prodi) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SESI_TES: Satu baris = satu tes yang sudah selesai (skor enam dimensi skala 0-100 + kode Holland 3 huruf).
-- Dibuat aplikasi saat siswa menyelesaikan tes. Satu hasil per siswa per semester (lewat riwayat_kelas).
-- Skor dihitung aplikasi: jumlah jawaban dimensi / (jumlah soal dimensi x 5) x 100.
CREATE TABLE sesi_tes (
  id_sesi_tes INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL UNIQUE,
  skor_r DECIMAL(5,2) NOT NULL,
  skor_i DECIMAL(5,2) NOT NULL,
  skor_a DECIMAL(5,2) NOT NULL,
  skor_s DECIMAL(5,2) NOT NULL,
  skor_e DECIMAL(5,2) NOT NULL,
  skor_c DECIMAL(5,2) NOT NULL,
  kode_holland CHAR(3) COLLATE utf8mb4_bin NOT NULL,
  waktu_selesai DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT chk_sesi_skor CHECK (
    skor_r BETWEEN 0 AND 100 AND skor_i BETWEEN 0 AND 100 AND skor_a BETWEEN 0 AND 100
    AND skor_s BETWEEN 0 AND 100 AND skor_e BETWEEN 0 AND 100 AND skor_c BETWEEN 0 AND 100),
  CONSTRAINT chk_sesi_kode CHECK (kode_holland REGEXP '^[RIASEC]{3}$'),
  CONSTRAINT fk_sesi_riwayat FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas)
) ENGINE=InnoDB;

-- GURU_BK: Guru (A2) yang bertugas sebagai Guru BK; kontak dan jadwal layanan (Profil Guru BK, kartu Guru BK siswa).
-- tingkat NULL = melayani semua tingkat.
CREATE TABLE guru_bk (
  id_guru INT UNSIGNED PRIMARY KEY,
  email VARCHAR(100) NULL,
  no_telepon VARCHAR(25) NULL,
  tingkat ENUM('X','XI','XII') NULL,
  jadwal_layanan VARCHAR(100) NULL,
  aktif BOOLEAN NOT NULL DEFAULT TRUE,
  CONSTRAINT fk_gbk_guru FOREIGN KEY (id_guru) REFERENCES guru(id_guru)
) ENGINE=InnoDB;

-- PENGAJUAN_BK: Pengajuan bimbingan siswa; kelas dan semester diambil dari riwayat_kelas.
-- Pilihan topik ditetapkan di aplikasi: Konsultasi pilihan jurusan, Motivasi belajar menurun, Perencanaan studi lanjut,
-- Kendala pertemanan, Persiapan masuk PTN, Perubahan pilihan jurusan, Adaptasi belajar.
CREATE TABLE pengajuan_bk (
  id_pengajuan_bk INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_riwayat_kelas INT UNSIGNED NOT NULL,
  topik VARCHAR(100) NOT NULL,
  deskripsi TEXT NOT NULL,
  status ENUM('menunggu','diproses','dijadwalkan','selesai','ditolak') NOT NULL DEFAULT 'menunggu',
  id_guru INT UNSIGNED NULL,
  balasan_bk TEXT NULL,
  jadwal_bimbingan DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_pbk_deskripsi CHECK (CHAR_LENGTH(deskripsi) BETWEEN 20 AND 500),
  CONSTRAINT chk_pbk_jadwal CHECK (status <> 'dijadwalkan' OR jadwal_bimbingan IS NOT NULL),
  CONSTRAINT chk_pbk_balasan CHECK (status NOT IN ('dijadwalkan','selesai','ditolak') OR balasan_bk IS NOT NULL),
  CONSTRAINT chk_pbk_penangani CHECK (status = 'menunggu' OR id_guru IS NOT NULL),
  CONSTRAINT fk_pbk_riwayat FOREIGN KEY (id_riwayat_kelas) REFERENCES riwayat_kelas(id_riwayat_kelas),
  CONSTRAINT fk_pbk_guru FOREIGN KEY (id_guru) REFERENCES guru_bk(id_guru),
  INDEX idx_pbk_status_created (status, created_at)
) ENGINE=InnoDB;

/* ---------- Data awal ---------- */
INSERT INTO dimensi_riasec (kode, nama, deskripsi) VALUES
('R','Realistic','Menyukai aktivitas praktis, menggunakan alat, mesin, dan bekerja di luar ruangan.'),
('I','Investigative','Tertarik pada penelitian, analisis, pemecahan masalah, dan eksplorasi ilmiah.'),
('A','Artistic','Menyukai ekspresi kreatif melalui seni, desain, bahasa, dan gagasan orisinal.'),
('S','Social','Senang membantu, mengajar, berkomunikasi, dan bekerja bersama orang lain.'),
('E','Enterprising','Tertarik memimpin, memengaruhi, berwirausaha, dan mencapai target.'),
('C','Conventional','Menyukai keteraturan, data, prosedur, administrasi, dan pekerjaan terstruktur.');

/* ---------- Trigger: tes hanya boleh disimpan saat periode tes jenjangnya aktif ---------- */
DELIMITER $$

CREATE TRIGGER trg_sesi_tes_bi BEFORE INSERT ON sesi_tes
FOR EACH ROW
BEGIN
  IF NOT EXISTS (
    SELECT 1
      FROM riwayat_kelas rk
      JOIN kelas k ON k.id_kelas = rk.id_kelas
      JOIN periode_tes p ON p.id_semester = rk.id_semester AND p.tingkat = k.tingkat
     WHERE rk.id_riwayat_kelas = NEW.id_riwayat_kelas AND p.status = 'aktif') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Tidak ada periode tes aktif untuk jenjang siswa ini';
  END IF;
END$$

DELIMITER ;

/* ---------- View: perhitungan dan kontrak baca (read-only) ---------- */

-- Hasil enam dimensi per tes (hasil siswa, Detail Hasil Tes BK, dan konsumsi A4).
CREATE OR REPLACE VIEW v_hasil_minat_siswa AS
SELECT s.id_sesi_tes, rk.id_siswa, s.id_riwayat_kelas, k.tingkat, s.kode_holland, s.waktu_selesai,
       s.skor_r, s.skor_i, s.skor_a, s.skor_s, s.skor_e, s.skor_c
  FROM sesi_tes s
  JOIN riwayat_kelas rk ON rk.id_riwayat_kelas = s.id_riwayat_kelas
  JOIN kelas k ON k.id_kelas = rk.id_kelas;

-- Riwayat Tes siswa: tanggal, tahun ajaran, kelas saat tes, kode, dan skor tertinggi.
CREATE OR REPLACE VIEW v_riwayat_tes AS
SELECT s.id_sesi_tes, rk.id_siswa, s.waktu_selesai, ta.nama AS tahun_ajaran,
       k.nama AS kelas, k.tingkat, k.fase, s.kode_holland,
       GREATEST(s.skor_r, s.skor_i, s.skor_a, s.skor_s, s.skor_e, s.skor_c) AS skor_tertinggi
  FROM sesi_tes s
  JOIN riwayat_kelas rk ON rk.id_riwayat_kelas = s.id_riwayat_kelas
  JOIN kelas k ON k.id_kelas = rk.id_kelas
  JOIN tahun_ajaran ta ON ta.id_tahun_ajaran = k.id_tahun_ajaran;

-- Rekap Tes RIASEC (BK) dan hitungan dashboard: setiap siswa di semester tertentu dengan hasil tes terbarunya.
-- status 'Belum' bila siswa belum pernah menyelesaikan tes. Filter id_semester (semester aktif) saat dipakai.
CREATE OR REPLACE VIEW v_rekap_tes_bk AS
SELECT rk.id_riwayat_kelas, rk.id_semester, rk.id_siswa, sw.nis, sw.nama,
       k.nama AS kelas, k.tingkat,
       t.kode_holland, t.waktu_selesai AS tanggal_tes,
       CASE WHEN t.id_sesi_tes IS NULL THEN 'Belum' ELSE 'Selesai' END AS status
  FROM riwayat_kelas rk
  JOIN siswa sw ON sw.id_siswa = rk.id_siswa
  JOIN kelas k ON k.id_kelas = rk.id_kelas
  LEFT JOIN sesi_tes t ON t.id_sesi_tes = (
         SELECT t2.id_sesi_tes
           FROM sesi_tes t2
           JOIN riwayat_kelas r2 ON r2.id_riwayat_kelas = t2.id_riwayat_kelas
          WHERE r2.id_siswa = rk.id_siswa
          ORDER BY t2.waktu_selesai DESC LIMIT 1);

-- Skor per dimensi dalam bentuk baris (dasar perhitungan Cosine Similarity dan distribusi dashboard).
CREATE OR REPLACE VIEW v_skor_dimensi AS
SELECT id_sesi_tes, 'R' AS kode, skor_r AS skor FROM sesi_tes
UNION ALL SELECT id_sesi_tes, 'I', skor_i FROM sesi_tes
UNION ALL SELECT id_sesi_tes, 'A', skor_a FROM sesi_tes
UNION ALL SELECT id_sesi_tes, 'S', skor_s FROM sesi_tes
UNION ALL SELECT id_sesi_tes, 'E', skor_e FROM sesi_tes
UNION ALL SELECT id_sesi_tes, 'C', skor_c FROM sesi_tes;

-- Cosine Similarity skor siswa terhadap profil mapel tervalidasi.
-- Vektor profil: huruf ke-1 = 3, ke-2 = 2, ke-3 = 1, lainnya 0 (norma = SQRT(14)). Hasil 0-100.
CREATE OR REPLACE VIEW v_kecocokan_mapel AS
SELECT v.id_sesi_tes, p.id_mapel,
       ROUND(100 * SUM(v.skor * CASE LOCATE(v.kode, p.kode_holland) WHEN 1 THEN 3 WHEN 2 THEN 2 WHEN 3 THEN 1 ELSE 0 END)
             / (SQRT(SUM(v.skor * v.skor)) * SQRT(14)), 2) AS skor_kecocokan
  FROM v_skor_dimensi v
  CROSS JOIN profil_mapel p
 WHERE p.status_validasi = 'tervalidasi'
 GROUP BY v.id_sesi_tes, p.id_mapel;

-- Cosine Similarity skor siswa terhadap profil program studi tervalidasi.
CREATE OR REPLACE VIEW v_kecocokan_prodi AS
SELECT v.id_sesi_tes, p.id_prodi,
       ROUND(100 * SUM(v.skor * CASE LOCATE(v.kode, p.kode_holland) WHEN 1 THEN 3 WHEN 2 THEN 2 WHEN 3 THEN 1 ELSE 0 END)
             / (SQRT(SUM(v.skor * v.skor)) * SQRT(14)), 2) AS skor_kecocokan
  FROM v_skor_dimensi v
  CROSS JOIN program_studi p
 WHERE p.status_validasi = 'tervalidasi'
 GROUP BY v.id_sesi_tes, p.id_prodi;

-- Rekomendasi Mapel (5 teratas per tes) untuk halaman Rekomendasi Mapel dan dashboard siswa.
CREATE OR REPLACE VIEW v_rekomendasi_mapel AS
SELECT id_sesi_tes, id_mapel, skor_kecocokan AS skor, peringkat
  FROM (SELECT k.id_sesi_tes, k.id_mapel, k.skor_kecocokan,
               ROW_NUMBER() OVER (PARTITION BY k.id_sesi_tes ORDER BY k.skor_kecocokan DESC, k.id_mapel) AS peringkat
          FROM v_kecocokan_mapel k) x
 WHERE peringkat <= 5;

-- Rekomendasi Program Studi (5 teratas per tes) untuk halaman Rekomendasi Prodi.
CREATE OR REPLACE VIEW v_rekomendasi_prodi AS
SELECT id_sesi_tes, id_prodi, skor_kecocokan AS skor, peringkat
  FROM (SELECT k.id_sesi_tes, k.id_prodi, k.skor_kecocokan,
               ROW_NUMBER() OVER (PARTITION BY k.id_sesi_tes ORDER BY k.skor_kecocokan DESC, k.id_prodi) AS peringkat
          FROM v_kecocokan_prodi k) x
 WHERE peringkat <= 5;

-- Program studi tervalidasi yang relevan untuk sebuah mapel (Mendukung Pilihan Studi Lanjut, kelas XI).
-- Cosine-style antara dua profil Holland (bobot 3/2/1 di kedua sisi).
CREATE OR REPLACE VIEW v_prodi_relevan_mapel AS
SELECT m.id_mapel, p.id_prodi,
       ROUND(100 * SUM(CASE LOCATE(d.kode, m.kode_holland) WHEN 1 THEN 3 WHEN 2 THEN 2 WHEN 3 THEN 1 ELSE 0 END
                     * CASE LOCATE(d.kode, p.kode_holland) WHEN 1 THEN 3 WHEN 2 THEN 2 WHEN 3 THEN 1 ELSE 0 END) / 14, 2) AS skor_relevansi
  FROM profil_mapel m
  CROSS JOIN program_studi p
  CROSS JOIN dimensi_riasec d
 WHERE m.status_validasi = 'tervalidasi' AND p.status_validasi = 'tervalidasi'
 GROUP BY m.id_mapel, p.id_prodi;
