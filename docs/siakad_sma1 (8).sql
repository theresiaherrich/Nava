-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 14, 2026 at 07:54 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siakad_sma1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `akademik`
--

CREATE TABLE `akademik` (
  `id_akademik` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budaya_positif`
--

CREATE TABLE `budaya_positif` (
  `id_budaya_positif` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catatan_akademik`
--

CREATE TABLE `catatan_akademik` (
  `id_catatan` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `kategori` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dimensi_riasec`
--

CREATE TABLE `dimensi_riasec` (
  `id_dimensi` int UNSIGNED NOT NULL,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ekskul`
--

CREATE TABLE `ekskul` (
  `id_ekskul` int UNSIGNED NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id_guru` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `nip` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kepegawaian` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agama` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guru_rombel`
--

CREATE TABLE `guru_rombel` (
  `id_guru` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_tes_riasec`
--

CREATE TABLE `hasil_tes_riasec` (
  `id_tes_riasec` int UNSIGNED NOT NULL,
  `id_sesi_tes` int UNSIGNED NOT NULL,
  `id_dimensi` int UNSIGNED NOT NULL,
  `skor_mentah` decimal(6,2) DEFAULT NULL,
  `skor_normal` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `izin`
--

CREATE TABLE `izin` (
  `id_izin` int UNSIGNED NOT NULL,
  `id_presensi` int UNSIGNED NOT NULL,
  `file_bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jawaban_riasec`
--

CREATE TABLE `jawaban_riasec` (
  `id_jawaban_riasec` int UNSIGNED NOT NULL,
  `id_sesi_tes` int UNSIGNED NOT NULL,
  `id_soal_riasec` int UNSIGNED NOT NULL,
  `nilai` decimal(6,2) DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int UNSIGNED NOT NULL,
  `nama` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` enum('X','XI','XII') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fase` enum('E','F') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_tahun_ajaran` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kepala_sekolah`
--

CREATE TABLE `kepala_sekolah` (
  `id_kepala_sekolah` int UNSIGNED NOT NULL,
  `id_sekolah` int UNSIGNED NOT NULL,
  `id_guru` int UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kesiswaan`
--

CREATE TABLE `kesiswaan` (
  `id_kesiswaan` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kktp`
--

CREATE TABLE `kktp` (
  `id_kktp` int UNSIGNED NOT NULL,
  `id_tp` int UNSIGNED NOT NULL,
  `batas_minimal` decimal(6,2) DEFAULT NULL,
  `tentang_predikat` text COLLATE utf8mb4_unicode_ci,
  `template_capaian` text COLLATE utf8mb4_unicode_ci,
  `template_penguatan` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kurikulum`
--

CREATE TABLE `kurikulum` (
  `id_kurikulum` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` bigint UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `aksi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_entitas` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entitas_id` bigint UNSIGNED NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci,
  `data_sebelum` json DEFAULT NULL,
  `data_sesudah` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_sekolah`
--

CREATE TABLE `lokasi_sekolah` (
  `id_lokasi` int UNSIGNED NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `nama_lokasi` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `radius` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id_mapel` int UNSIGNED NOT NULL,
  `id_dimensi` int UNSIGNED NOT NULL,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rumpun` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `is_pilihan` tinyint(1) NOT NULL DEFAULT '0',
  `kode_riasec` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `status_validasi` enum('menunggu','tervalidasi','perlu_revisi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan_validasi` text COLLATE utf8mb4_unicode_ci,
  `divalidasi_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_validasi` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `mapel_prodi`
--

CREATE TABLE `mapel_prodi` (
  `id_mapel` int UNSIGNED NOT NULL,
  `id_prodi` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_semester`
--

CREATE TABLE `nilai_semester` (
  `id_nilai_semester` int UNSIGNED NOT NULL,
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED DEFAULT NULL,
  `jumlah_tp` int DEFAULT NULL,
  `jumlah_tp_terisi` int DEFAULT NULL,
  `nilai_akhir` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nilai_tp`
--

CREATE TABLE `nilai_tp` (
  `id_nilai_tp` int UNSIGNED NOT NULL,
  `id_tp` int UNSIGNED NOT NULL,
  `id_siswarombel` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED NOT NULL,
  `nilai` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orang_tua`
--

CREATE TABLE `orang_tua` (
  `id_siswa` int UNSIGNED NOT NULL,
  `hubungan` enum('Ayah','Ibu','Wali') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int UNSIGNED DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telepon` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_verifikasi` enum('pending','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `diverifikasi_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_verifikasi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paket_nilai`
--

CREATE TABLE `paket_nilai` (
  `id_paket_nilai` int UNSIGNED NOT NULL,
  `id_periode` int UNSIGNED NOT NULL,
  `id_kelas` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED NOT NULL,
  `status` enum('draf','terkirim','revisi','disetujui','terkunci') COLLATE utf8mb4_unicode_ci NOT NULL,
  `dikirim_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_kirim` datetime DEFAULT NULL,
  `dikunci_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_kunci` datetime DEFAULT NULL,
  `alasan_koreksi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelengkap_rapor`
--

CREATE TABLE `pelengkap_rapor` (
  `id_pelengkap_rapor` int UNSIGNED NOT NULL,
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `id_periode` int UNSIGNED NOT NULL,
  `catatan_wali` text COLLATE utf8mb4_unicode_ci,
  `kokurikuler` text COLLATE utf8mb4_unicode_ci,
  `sakit` int UNSIGNED DEFAULT '0',
  `izin` int UNSIGNED DEFAULT '0',
  `alpha` int UNSIGNED DEFAULT '0',
  `id_presensi` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan_bk`
--

CREATE TABLE `pengajuan_bk` (
  `id_pengajuan` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `topik` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('menunggu','diproses','dijadwalkan','selesai','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_guru` int UNSIGNED DEFAULT NULL,
  `balasan` text COLLATE utf8mb4_unicode_ci,
  `tanggal_bimbingan` date DEFAULT NULL,
  `jam_bimbingan` time DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `penugasan_bk`
--

CREATE TABLE `penugasan_bk` (
  `id_penugasan_bk` int UNSIGNED NOT NULL,
  `id_guru` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `tingkat` enum('X','XI','XII') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jadwal_layanan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `periode_nilai`
--

CREATE TABLE `periode_nilai` (
  `id_periode` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `tingkat` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` enum('STS','SAS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('draf','dibuka','ditutup','terkunci') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `peserta_ekskul`
--

CREATE TABLE `peserta_ekskul` (
  `id_peserta_ekskul` int UNSIGNED NOT NULL,
  `id_ekskul` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `id_predikat` int UNSIGNED NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `diisi_oleh` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `predikat`
--

CREATE TABLE `predikat` (
  `id_predikat` int UNSIGNED NOT NULL,
  `predikat` enum('SB','B','K') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_min` decimal(5,2) NOT NULL,
  `nilai_max` decimal(5,2) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `presensi`
--

CREATE TABLE `presensi` (
  `id_presensi` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `metode` enum('QR','Geofencing') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Hadir','Alpa','Sakit','Izin','Terlambat') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu_presensi` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prodi_profesi`
--

CREATE TABLE `prodi_profesi` (
  `id_prodi` int UNSIGNED NOT NULL,
  `id_profesi` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profesi`
--

CREATE TABLE `profesi` (
  `id_profesi` int UNSIGNED NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `aktif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id_prodi` int UNSIGNED NOT NULL,
  `id_dimensi` int UNSIGNED NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rumpun` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tentang` text COLLATE utf8mb4_unicode_ci,
  `kode_riasec` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `status_validasi` enum('menunggu','tervalidasi','perlu_revisi') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `catatan_validasi` text COLLATE utf8mb4_unicode_ci,
  `divalidasi_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_validasi` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `qr_siswa`
--

CREATE TABLE `qr_siswa` (
  `id_qr` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `kode_qr` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rapor`
--

CREATE TABLE `rapor` (
  `id_rapor` int UNSIGNED NOT NULL,
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `id_periode` int UNSIGNED NOT NULL,
  `id_template` int UNSIGNED NOT NULL,
  `versi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_final` json NOT NULL,
  `file_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_cetak` date DEFAULT NULL,
  `status` enum('terkunci','siap','terbit','dicabut') COLLATE utf8mb4_unicode_ci NOT NULL,
  `diterbitkan_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_terbit` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi_dua_belas`
--

CREATE TABLE `rekomendasi_dua_belas` (
  `id_rekomendasi_dua_belas` int UNSIGNED NOT NULL,
  `id_sesi_tes` int UNSIGNED NOT NULL,
  `id_prodi` int UNSIGNED NOT NULL,
  `peringkat` tinyint UNSIGNED NOT NULL,
  `skor` decimal(5,2) NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci
) ;

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi_sepuluh`
--

CREATE TABLE `rekomendasi_sepuluh` (
  `id_rekomendasi_sepuluh` int UNSIGNED NOT NULL,
  `id_sesi_tes` int UNSIGNED NOT NULL,
  `id_mapel` int UNSIGNED NOT NULL,
  `peringkat` tinyint UNSIGNED NOT NULL,
  `skor` decimal(5,2) NOT NULL,
  `alasan` text COLLATE utf8mb4_unicode_ci
) ;

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_kelas`
--

CREATE TABLE `riwayat_kelas` (
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `id_siswa` int UNSIGNED NOT NULL,
  `id_kelas` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rombel`
--

CREATE TABLE `rombel` (
  `id_rombel` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `id_mapel` int UNSIGNED NOT NULL,
  `nama` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelompok_rapor` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metode_nilai` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sakit`
--

CREATE TABLE `sakit` (
  `id_sakit` int UNSIGNED NOT NULL,
  `id_presensi` int UNSIGNED NOT NULL,
  `file_bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sekolah`
--

CREATE TABLE `sekolah` (
  `id_sekolah` int UNSIGNED NOT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npsn` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `semester`
--

CREATE TABLE `semester` (
  `id_semester` int UNSIGNED NOT NULL,
  `id_tahun_ajaran` int UNSIGNED NOT NULL,
  `nama` enum('GASAL','GENAP') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sesi_tes`
--

CREATE TABLE `sesi_tes` (
  `id_sesi_tes` int UNSIGNED NOT NULL,
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `tingkat` enum('X','XII') COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_holland` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `status` enum('berlangsung','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'berlangsung',
  `diverifikasi_oleh` int UNSIGNED DEFAULT NULL,
  `waktu_verifikasi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int UNSIGNED NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `nis` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nisn` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agama` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tahun_masuk` smallint UNSIGNED DEFAULT NULL,
  `urutan_masuk` int UNSIGNED DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa_rombel`
--

CREATE TABLE `siswa_rombel` (
  `id_siswarombel` int UNSIGNED NOT NULL,
  `id_riwayat_kelas` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED NOT NULL,
  `id_mapel` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soal_riasec`
--

CREATE TABLE `soal_riasec` (
  `id_soal_riasec` int UNSIGNED NOT NULL,
  `id_dimensi` int UNSIGNED NOT NULL,
  `teks` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` int DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id_tahun_ajaran` int UNSIGNED NOT NULL,
  `nama` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `template_rapor`
--

CREATE TABLE `template_rapor` (
  `id_template` int UNSIGNED NOT NULL,
  `id_sekolah` int UNSIGNED NOT NULL,
  `jenis` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `versi` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identitas_cetak` text COLLATE utf8mb4_unicode_ci,
  `lokasi_template` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tp`
--

CREATE TABLE `tp` (
  `id_tp` int UNSIGNED NOT NULL,
  `id_rombel` int UNSIGNED NOT NULL,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `urutan` int DEFAULT NULL,
  `masuk_sts` tinyint(1) DEFAULT '0',
  `is_sas` tinyint(1) DEFAULT '0',
  `bobot` decimal(6,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int UNSIGNED NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `must_change_password` tinyint(1) DEFAULT '1',
  `role` enum('SISWA','GURU','GURU_BK','ORANG_TUA','AKADEMIK','KURIKULUM','BUDAYA_POSITIF','KESISWAAN','ADMIN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `password_changed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifikasi_nilai`
--

CREATE TABLE `verifikasi_nilai` (
  `id_verifikasi_nilai` bigint UNSIGNED NOT NULL,
  `id_paket_nilai` int UNSIGNED NOT NULL,
  `id_pemeriksa` int UNSIGNED NOT NULL,
  `keputusan` enum('disetujui','revisi') COLLATE utf8mb4_unicode_ci NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wali_kelas`
--

CREATE TABLE `wali_kelas` (
  `id_wali_kelas` int UNSIGNED NOT NULL,
  `id_kelas` int UNSIGNED NOT NULL,
  `id_guru` int UNSIGNED NOT NULL,
  `id_semester` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `akademik`
--
ALTER TABLE `akademik`
  ADD PRIMARY KEY (`id_akademik`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `budaya_positif`
--
ALTER TABLE `budaya_positif`
  ADD PRIMARY KEY (`id_budaya_positif`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `catatan_akademik`
--
ALTER TABLE `catatan_akademik`
  ADD PRIMARY KEY (`id_catatan`),
  ADD KEY `fk_catatan_siswa` (`id_siswa`);

--
-- Indexes for table `dimensi_riasec`
--
ALTER TABLE `dimensi_riasec`
  ADD PRIMARY KEY (`id_dimensi`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indexes for table `ekskul`
--
ALTER TABLE `ekskul`
  ADD PRIMARY KEY (`id_ekskul`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `id_user` (`id_user`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `uq_guru_email` (`email`);

--
-- Indexes for table `guru_rombel`
--
ALTER TABLE `guru_rombel`
  ADD PRIMARY KEY (`id_guru`,`id_rombel`),
  ADD KEY `fk_gr_rombel` (`id_rombel`);

--
-- Indexes for table `hasil_tes_riasec`
--
ALTER TABLE `hasil_tes_riasec`
  ADD PRIMARY KEY (`id_tes_riasec`),
  ADD UNIQUE KEY `uq_sesi_dimensi` (`id_sesi_tes`,`id_dimensi`),
  ADD KEY `fk_tr_dimensi` (`id_dimensi`);

--
-- Indexes for table `izin`
--
ALTER TABLE `izin`
  ADD PRIMARY KEY (`id_izin`),
  ADD KEY `fk_izin_presensi` (`id_presensi`);

--
-- Indexes for table `jawaban_riasec`
--
ALTER TABLE `jawaban_riasec`
  ADD PRIMARY KEY (`id_jawaban_riasec`),
  ADD UNIQUE KEY `uq_jawaban_sesi_soal` (`id_sesi_tes`,`id_soal_riasec`),
  ADD KEY `fk_jawaban_soal` (`id_soal_riasec`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD UNIQUE KEY `uq_kelas_ta` (`nama`,`id_tahun_ajaran`),
  ADD KEY `fk_kelas_ta` (`id_tahun_ajaran`);

--
-- Indexes for table `kepala_sekolah`
--
ALTER TABLE `kepala_sekolah`
  ADD PRIMARY KEY (`id_kepala_sekolah`),
  ADD KEY `fk_kepsek_sekolah` (`id_sekolah`),
  ADD KEY `fk_kepsek_guru` (`id_guru`);

--
-- Indexes for table `kesiswaan`
--
ALTER TABLE `kesiswaan`
  ADD PRIMARY KEY (`id_kesiswaan`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `kktp`
--
ALTER TABLE `kktp`
  ADD PRIMARY KEY (`id_kktp`),
  ADD KEY `fk_kktp_tp` (`id_tp`);

--
-- Indexes for table `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD PRIMARY KEY (`id_kurikulum`),
  ADD UNIQUE KEY `id_user` (`id_user`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_entitas` (`nama_entitas`,`entitas_id`),
  ADD KEY `idx_log_user_created` (`id_user`,`created_at`);

--
-- Indexes for table `lokasi_sekolah`
--
ALTER TABLE `lokasi_sekolah`
  ADD PRIMARY KEY (`id_lokasi`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id_mapel`),
  ADD UNIQUE KEY `kode` (`kode`),
  ADD KEY `fk_dimensi` (`id_dimensi`),
  ADD KEY `fk_mapel_validator` (`divalidasi_oleh`);

--
-- Indexes for table `mapel_prodi`
--
ALTER TABLE `mapel_prodi`
  ADD PRIMARY KEY (`id_mapel`,`id_prodi`),
  ADD KEY `fk_mp_prodi` (`id_prodi`);

--
-- Indexes for table `nilai_semester`
--
ALTER TABLE `nilai_semester`
  ADD PRIMARY KEY (`id_nilai_semester`),
  ADD UNIQUE KEY `uq_ns_siswa_rombel` (`id_riwayat_kelas`,`id_rombel`),
  ADD KEY `fk_ns_rombel` (`id_rombel`);

--
-- Indexes for table `nilai_tp`
--
ALTER TABLE `nilai_tp`
  ADD PRIMARY KEY (`id_nilai_tp`),
  ADD UNIQUE KEY `uq_nilai_tp_siswa` (`id_tp`,`id_siswarombel`),
  ADD KEY `fk_nilai_tp_siswarombel` (`id_siswarombel`),
  ADD KEY `fk_nilai_tp_rombel` (`id_rombel`);

--
-- Indexes for table `orang_tua`
--
ALTER TABLE `orang_tua`
  ADD PRIMARY KEY (`id_siswa`,`hubungan`),
  ADD UNIQUE KEY `id_user` (`id_user`,`id_siswa`),
  ADD KEY `fk_ortu_pemeriksa` (`diverifikasi_oleh`);

--
-- Indexes for table `paket_nilai`
--
ALTER TABLE `paket_nilai`
  ADD PRIMARY KEY (`id_paket_nilai`),
  ADD UNIQUE KEY `uq_paket_rombel_periode` (`id_rombel`,`id_periode`),
  ADD KEY `fk_pn_kelas` (`id_kelas`),
  ADD KEY `fk_pn_periode` (`id_periode`),
  ADD KEY `fk_pk_user` (`dikunci_oleh`),
  ADD KEY `fk_pn_pengirim` (`dikirim_oleh`);

--
-- Indexes for table `pelengkap_rapor`
--
ALTER TABLE `pelengkap_rapor`
  ADD PRIMARY KEY (`id_pelengkap_rapor`),
  ADD UNIQUE KEY `uq_pelengkap_rapor` (`id_riwayat_kelas`,`id_periode`),
  ADD KEY `fk_pelengkap_periode` (`id_periode`),
  ADD KEY `fk_pelengkap_presensi` (`id_presensi`);

--
-- Indexes for table `pengajuan_bk`
--
ALTER TABLE `pengajuan_bk`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `fk_pk_siswa` (`id_siswa`),
  ADD KEY `fk_pbk_sem` (`id_semester`),
  ADD KEY `idx_pbk_status` (`status`,`created_at`),
  ADD KEY `idx_pbk_guru` (`id_guru`);

--
-- Indexes for table `penugasan_bk`
--
ALTER TABLE `penugasan_bk`
  ADD PRIMARY KEY (`id_penugasan_bk`),
  ADD UNIQUE KEY `uq_penugasan_bk` (`id_guru`,`id_semester`,`tingkat`),
  ADD KEY `fk_pbkt_sem` (`id_semester`);

--
-- Indexes for table `periode_nilai`
--
ALTER TABLE `periode_nilai`
  ADD PRIMARY KEY (`id_periode`),
  ADD UNIQUE KEY `uq_periode` (`id_semester`,`tingkat`,`jenis`);

--
-- Indexes for table `peserta_ekskul`
--
ALTER TABLE `peserta_ekskul`
  ADD PRIMARY KEY (`id_peserta_ekskul`),
  ADD UNIQUE KEY `uq_peserta_ekskul_sem` (`id_ekskul`,`id_siswa`,`id_semester`),
  ADD KEY `fk_pe_siswa` (`id_siswa`),
  ADD KEY `fk_pe_diisi` (`diisi_oleh`),
  ADD KEY `fk_pe_predikat` (`id_predikat`),
  ADD KEY `fk_pe_sem` (`id_semester`);

--
-- Indexes for table `predikat`
--
ALTER TABLE `predikat`
  ADD PRIMARY KEY (`id_predikat`);

--
-- Indexes for table `presensi`
--
ALTER TABLE `presensi`
  ADD PRIMARY KEY (`id_presensi`),
  ADD KEY `fk_presensi_siswa` (`id_siswa`);

--
-- Indexes for table `prodi_profesi`
--
ALTER TABLE `prodi_profesi`
  ADD PRIMARY KEY (`id_prodi`,`id_profesi`),
  ADD KEY `fk_pp_profesi` (`id_profesi`);

--
-- Indexes for table `profesi`
--
ALTER TABLE `profesi`
  ADD PRIMARY KEY (`id_profesi`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id_prodi`),
  ADD KEY `fk_ps_dimensi` (`id_dimensi`),
  ADD KEY `fk_prodi_validator` (`divalidasi_oleh`);

--
-- Indexes for table `qr_siswa`
--
ALTER TABLE `qr_siswa`
  ADD PRIMARY KEY (`id_qr`),
  ADD UNIQUE KEY `kode_qr` (`kode_qr`),
  ADD KEY `fk_qr_siswa` (`id_siswa`);

--
-- Indexes for table `rapor`
--
ALTER TABLE `rapor`
  ADD PRIMARY KEY (`id_rapor`),
  ADD KEY `fk_rapor_riwayat` (`id_riwayat_kelas`),
  ADD KEY `fk_rapor_periode` (`id_periode`),
  ADD KEY `fk_rapor_template` (`id_template`),
  ADD KEY `fk_rapor_diterbitkan` (`diterbitkan_oleh`);

--
-- Indexes for table `rekomendasi_dua_belas`
--
ALTER TABLE `rekomendasi_dua_belas`
  ADD PRIMARY KEY (`id_rekomendasi_dua_belas`),
  ADD UNIQUE KEY `uq_rd_sesi_prodi` (`id_sesi_tes`,`id_prodi`),
  ADD UNIQUE KEY `uq_rd_sesi_rank` (`id_sesi_tes`,`peringkat`),
  ADD KEY `fk_rd_prodi` (`id_prodi`);

--
-- Indexes for table `rekomendasi_sepuluh`
--
ALTER TABLE `rekomendasi_sepuluh`
  ADD PRIMARY KEY (`id_rekomendasi_sepuluh`),
  ADD UNIQUE KEY `uq_rs_sesi_mapel` (`id_sesi_tes`,`id_mapel`),
  ADD UNIQUE KEY `uq_rs_sesi_rank` (`id_sesi_tes`,`peringkat`),
  ADD KEY `fk_rs_mapel` (`id_mapel`);

--
-- Indexes for table `riwayat_kelas`
--
ALTER TABLE `riwayat_kelas`
  ADD PRIMARY KEY (`id_riwayat_kelas`),
  ADD UNIQUE KEY `uq_riwayat_siswa_sem` (`id_siswa`,`id_semester`),
  ADD KEY `fk_rk_kelas` (`id_kelas`),
  ADD KEY `fk_rk_sem` (`id_semester`);

--
-- Indexes for table `rombel`
--
ALTER TABLE `rombel`
  ADD PRIMARY KEY (`id_rombel`),
  ADD KEY `fk_rombel_mapel` (`id_mapel`),
  ADD KEY `fk_rombel_sem` (`id_semester`);

--
-- Indexes for table `sakit`
--
ALTER TABLE `sakit`
  ADD PRIMARY KEY (`id_sakit`),
  ADD KEY `fk_sakit_presensi` (`id_presensi`);

--
-- Indexes for table `sekolah`
--
ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id_sekolah`);

--
-- Indexes for table `semester`
--
ALTER TABLE `semester`
  ADD PRIMARY KEY (`id_semester`),
  ADD UNIQUE KEY `uq_sem_ta` (`id_tahun_ajaran`,`nama`);

--
-- Indexes for table `sesi_tes`
--
ALTER TABLE `sesi_tes`
  ADD PRIMARY KEY (`id_sesi_tes`),
  ADD KEY `fk_sesi_verifikator` (`diverifikasi_oleh`),
  ADD KEY `idx_sesi_riwayat` (`id_riwayat_kelas`,`waktu_selesai`),
  ADD KEY `idx_sesi_status` (`status`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `id_user` (`id_user`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD UNIQUE KEY `nisn` (`nisn`);

--
-- Indexes for table `siswa_rombel`
--
ALTER TABLE `siswa_rombel`
  ADD PRIMARY KEY (`id_siswarombel`),
  ADD UNIQUE KEY `uq_siswa_rombel` (`id_riwayat_kelas`,`id_rombel`),
  ADD KEY `fk_sr_rombel` (`id_rombel`),
  ADD KEY `fk_sr_mapel` (`id_mapel`);

--
-- Indexes for table `soal_riasec`
--
ALTER TABLE `soal_riasec`
  ADD PRIMARY KEY (`id_soal_riasec`),
  ADD KEY `fk_sr_dimensi` (`id_dimensi`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun_ajaran`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `template_rapor`
--
ALTER TABLE `template_rapor`
  ADD PRIMARY KEY (`id_template`),
  ADD KEY `fk_tr_sekolah` (`id_sekolah`);

--
-- Indexes for table `tp`
--
ALTER TABLE `tp`
  ADD PRIMARY KEY (`id_tp`),
  ADD KEY `fk_tp_rombel` (`id_rombel`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `verifikasi_nilai`
--
ALTER TABLE `verifikasi_nilai`
  ADD PRIMARY KEY (`id_verifikasi_nilai`),
  ADD KEY `fk_vn_pemeriksa` (`id_pemeriksa`),
  ADD KEY `idx_vn_paket_time` (`id_paket_nilai`,`created_at`);

--
-- Indexes for table `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD PRIMARY KEY (`id_wali_kelas`),
  ADD UNIQUE KEY `uq_wali_kelas_sem` (`id_kelas`,`id_semester`),
  ADD KEY `fk_wk_guru` (`id_guru`),
  ADD KEY `fk_wk_sem` (`id_semester`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `akademik`
--
ALTER TABLE `akademik`
  MODIFY `id_akademik` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budaya_positif`
--
ALTER TABLE `budaya_positif`
  MODIFY `id_budaya_positif` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catatan_akademik`
--
ALTER TABLE `catatan_akademik`
  MODIFY `id_catatan` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dimensi_riasec`
--
ALTER TABLE `dimensi_riasec`
  MODIFY `id_dimensi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ekskul`
--
ALTER TABLE `ekskul`
  MODIFY `id_ekskul` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id_guru` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hasil_tes_riasec`
--
ALTER TABLE `hasil_tes_riasec`
  MODIFY `id_tes_riasec` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `izin`
--
ALTER TABLE `izin`
  MODIFY `id_izin` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jawaban_riasec`
--
ALTER TABLE `jawaban_riasec`
  MODIFY `id_jawaban_riasec` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kepala_sekolah`
--
ALTER TABLE `kepala_sekolah`
  MODIFY `id_kepala_sekolah` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kesiswaan`
--
ALTER TABLE `kesiswaan`
  MODIFY `id_kesiswaan` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kktp`
--
ALTER TABLE `kktp`
  MODIFY `id_kktp` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kurikulum`
--
ALTER TABLE `kurikulum`
  MODIFY `id_kurikulum` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lokasi_sekolah`
--
ALTER TABLE `lokasi_sekolah`
  MODIFY `id_lokasi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id_mapel` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_semester`
--
ALTER TABLE `nilai_semester`
  MODIFY `id_nilai_semester` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nilai_tp`
--
ALTER TABLE `nilai_tp`
  MODIFY `id_nilai_tp` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paket_nilai`
--
ALTER TABLE `paket_nilai`
  MODIFY `id_paket_nilai` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelengkap_rapor`
--
ALTER TABLE `pelengkap_rapor`
  MODIFY `id_pelengkap_rapor` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengajuan_bk`
--
ALTER TABLE `pengajuan_bk`
  MODIFY `id_pengajuan` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `penugasan_bk`
--
ALTER TABLE `penugasan_bk`
  MODIFY `id_penugasan_bk` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `periode_nilai`
--
ALTER TABLE `periode_nilai`
  MODIFY `id_periode` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peserta_ekskul`
--
ALTER TABLE `peserta_ekskul`
  MODIFY `id_peserta_ekskul` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `predikat`
--
ALTER TABLE `predikat`
  MODIFY `id_predikat` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `presensi`
--
ALTER TABLE `presensi`
  MODIFY `id_presensi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profesi`
--
ALTER TABLE `profesi`
  MODIFY `id_profesi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id_prodi` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_siswa`
--
ALTER TABLE `qr_siswa`
  MODIFY `id_qr` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rapor`
--
ALTER TABLE `rapor`
  MODIFY `id_rapor` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekomendasi_dua_belas`
--
ALTER TABLE `rekomendasi_dua_belas`
  MODIFY `id_rekomendasi_dua_belas` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekomendasi_sepuluh`
--
ALTER TABLE `rekomendasi_sepuluh`
  MODIFY `id_rekomendasi_sepuluh` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riwayat_kelas`
--
ALTER TABLE `riwayat_kelas`
  MODIFY `id_riwayat_kelas` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rombel`
--
ALTER TABLE `rombel`
  MODIFY `id_rombel` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sakit`
--
ALTER TABLE `sakit`
  MODIFY `id_sakit` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sekolah`
--
ALTER TABLE `sekolah`
  MODIFY `id_sekolah` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `semester`
--
ALTER TABLE `semester`
  MODIFY `id_semester` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sesi_tes`
--
ALTER TABLE `sesi_tes`
  MODIFY `id_sesi_tes` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa_rombel`
--
ALTER TABLE `siswa_rombel`
  MODIFY `id_siswarombel` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soal_riasec`
--
ALTER TABLE `soal_riasec`
  MODIFY `id_soal_riasec` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id_tahun_ajaran` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `template_rapor`
--
ALTER TABLE `template_rapor`
  MODIFY `id_template` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tp`
--
ALTER TABLE `tp`
  MODIFY `id_tp` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verifikasi_nilai`
--
ALTER TABLE `verifikasi_nilai`
  MODIFY `id_verifikasi_nilai` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wali_kelas`
--
ALTER TABLE `wali_kelas`
  MODIFY `id_wali_kelas` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `akademik`
--
ALTER TABLE `akademik`
  ADD CONSTRAINT `fk_akademik_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `budaya_positif`
--
ALTER TABLE `budaya_positif`
  ADD CONSTRAINT `fk_bp_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `catatan_akademik`
--
ALTER TABLE `catatan_akademik`
  ADD CONSTRAINT `fk_catatan_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `guru_rombel`
--
ALTER TABLE `guru_rombel`
  ADD CONSTRAINT `fk_gr_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_gr_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`);

--
-- Constraints for table `hasil_tes_riasec`
--
ALTER TABLE `hasil_tes_riasec`
  ADD CONSTRAINT `fk_skor_sesi` FOREIGN KEY (`id_sesi_tes`) REFERENCES `sesi_tes` (`id_sesi_tes`),
  ADD CONSTRAINT `fk_tr_dimensi` FOREIGN KEY (`id_dimensi`) REFERENCES `dimensi_riasec` (`id_dimensi`);

--
-- Constraints for table `izin`
--
ALTER TABLE `izin`
  ADD CONSTRAINT `fk_izin_presensi` FOREIGN KEY (`id_presensi`) REFERENCES `presensi` (`id_presensi`);

--
-- Constraints for table `jawaban_riasec`
--
ALTER TABLE `jawaban_riasec`
  ADD CONSTRAINT `fk_jawaban_sesi` FOREIGN KEY (`id_sesi_tes`) REFERENCES `sesi_tes` (`id_sesi_tes`),
  ADD CONSTRAINT `fk_jawaban_soal` FOREIGN KEY (`id_soal_riasec`) REFERENCES `soal_riasec` (`id_soal_riasec`);

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_ta` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`);

--
-- Constraints for table `kepala_sekolah`
--
ALTER TABLE `kepala_sekolah`
  ADD CONSTRAINT `fk_kepsek_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_kepsek_sekolah` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`);

--
-- Constraints for table `kesiswaan`
--
ALTER TABLE `kesiswaan`
  ADD CONSTRAINT `fk_kesiswaan_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `kktp`
--
ALTER TABLE `kktp`
  ADD CONSTRAINT `fk_kktp_tp` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`);

--
-- Constraints for table `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD CONSTRAINT `fk_kurikulum_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `fk_log_aktivitas_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT;

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `fk_dimensi` FOREIGN KEY (`id_dimensi`) REFERENCES `dimensi_riasec` (`id_dimensi`),
  ADD CONSTRAINT `fk_mapel_validator` FOREIGN KEY (`divalidasi_oleh`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `mapel_prodi`
--
ALTER TABLE `mapel_prodi`
  ADD CONSTRAINT `fk_mp_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  ADD CONSTRAINT `fk_mp_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`);

--
-- Constraints for table `nilai_semester`
--
ALTER TABLE `nilai_semester`
  ADD CONSTRAINT `fk_na_riwayat` FOREIGN KEY (`id_riwayat_kelas`) REFERENCES `riwayat_kelas` (`id_riwayat_kelas`),
  ADD CONSTRAINT `fk_ns_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`);

--
-- Constraints for table `nilai_tp`
--
ALTER TABLE `nilai_tp`
  ADD CONSTRAINT `fk_nilai_tp_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`),
  ADD CONSTRAINT `fk_nilai_tp_siswarombel` FOREIGN KEY (`id_siswarombel`) REFERENCES `siswa_rombel` (`id_siswarombel`),
  ADD CONSTRAINT `fk_nilai_tp_tp` FOREIGN KEY (`id_tp`) REFERENCES `tp` (`id_tp`);

--
-- Constraints for table `orang_tua`
--
ALTER TABLE `orang_tua`
  ADD CONSTRAINT `fk_ortu_pemeriksa` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_ortu_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `fk_ortu_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `paket_nilai`
--
ALTER TABLE `paket_nilai`
  ADD CONSTRAINT `fk_pk_user` FOREIGN KEY (`dikunci_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_pn_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `fk_pn_pengirim` FOREIGN KEY (`dikirim_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_pn_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_nilai` (`id_periode`),
  ADD CONSTRAINT `fk_pn_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`);

--
-- Constraints for table `pelengkap_rapor`
--
ALTER TABLE `pelengkap_rapor`
  ADD CONSTRAINT `fk_pelengkap_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_nilai` (`id_periode`),
  ADD CONSTRAINT `fk_pelengkap_presensi` FOREIGN KEY (`id_presensi`) REFERENCES `presensi` (`id_presensi`),
  ADD CONSTRAINT `fk_pelengkap_riwayat` FOREIGN KEY (`id_riwayat_kelas`) REFERENCES `riwayat_kelas` (`id_riwayat_kelas`);

--
-- Constraints for table `pengajuan_bk`
--
ALTER TABLE `pengajuan_bk`
  ADD CONSTRAINT `fk_pbk_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_pbk_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_pk_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `penugasan_bk`
--
ALTER TABLE `penugasan_bk`
  ADD CONSTRAINT `fk_pbkt_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_pbkt_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`);

--
-- Constraints for table `periode_nilai`
--
ALTER TABLE `periode_nilai`
  ADD CONSTRAINT `fk_periode_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`);

--
-- Constraints for table `peserta_ekskul`
--
ALTER TABLE `peserta_ekskul`
  ADD CONSTRAINT `fk_pe_diisi` FOREIGN KEY (`diisi_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_pe_ekskul` FOREIGN KEY (`id_ekskul`) REFERENCES `ekskul` (`id_ekskul`),
  ADD CONSTRAINT `fk_pe_predikat` FOREIGN KEY (`id_predikat`) REFERENCES `predikat` (`id_predikat`),
  ADD CONSTRAINT `fk_pe_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_pe_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `presensi`
--
ALTER TABLE `presensi`
  ADD CONSTRAINT `fk_presensi_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `prodi_profesi`
--
ALTER TABLE `prodi_profesi`
  ADD CONSTRAINT `fk_pp_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`),
  ADD CONSTRAINT `fk_pp_profesi` FOREIGN KEY (`id_profesi`) REFERENCES `profesi` (`id_profesi`);

--
-- Constraints for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD CONSTRAINT `fk_prodi_validator` FOREIGN KEY (`divalidasi_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_ps_dimensi` FOREIGN KEY (`id_dimensi`) REFERENCES `dimensi_riasec` (`id_dimensi`);

--
-- Constraints for table `qr_siswa`
--
ALTER TABLE `qr_siswa`
  ADD CONSTRAINT `fk_qr_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `rapor`
--
ALTER TABLE `rapor`
  ADD CONSTRAINT `fk_rapor_diterbitkan` FOREIGN KEY (`diterbitkan_oleh`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `fk_rapor_periode` FOREIGN KEY (`id_periode`) REFERENCES `periode_nilai` (`id_periode`),
  ADD CONSTRAINT `fk_rapor_riwayat` FOREIGN KEY (`id_riwayat_kelas`) REFERENCES `riwayat_kelas` (`id_riwayat_kelas`),
  ADD CONSTRAINT `fk_rapor_template` FOREIGN KEY (`id_template`) REFERENCES `template_rapor` (`id_template`);

--
-- Constraints for table `rekomendasi_dua_belas`
--
ALTER TABLE `rekomendasi_dua_belas`
  ADD CONSTRAINT `fk_rd_prodi` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id_prodi`),
  ADD CONSTRAINT `fk_rd_sesi` FOREIGN KEY (`id_sesi_tes`) REFERENCES `sesi_tes` (`id_sesi_tes`);

--
-- Constraints for table `rekomendasi_sepuluh`
--
ALTER TABLE `rekomendasi_sepuluh`
  ADD CONSTRAINT `fk_rs_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  ADD CONSTRAINT `fk_rs_sesi` FOREIGN KEY (`id_sesi_tes`) REFERENCES `sesi_tes` (`id_sesi_tes`);

--
-- Constraints for table `riwayat_kelas`
--
ALTER TABLE `riwayat_kelas`
  ADD CONSTRAINT `fk_rk_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `fk_rk_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`),
  ADD CONSTRAINT `fk_rk_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

--
-- Constraints for table `rombel`
--
ALTER TABLE `rombel`
  ADD CONSTRAINT `fk_rombel_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  ADD CONSTRAINT `fk_rombel_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`);

--
-- Constraints for table `sakit`
--
ALTER TABLE `sakit`
  ADD CONSTRAINT `fk_sakit_presensi` FOREIGN KEY (`id_presensi`) REFERENCES `presensi` (`id_presensi`);

--
-- Constraints for table `semester`
--
ALTER TABLE `semester`
  ADD CONSTRAINT `fk_sem_ta` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`);

--
-- Constraints for table `sesi_tes`
--
ALTER TABLE `sesi_tes`
  ADD CONSTRAINT `fk_sesi_riwayat` FOREIGN KEY (`id_riwayat_kelas`) REFERENCES `riwayat_kelas` (`id_riwayat_kelas`),
  ADD CONSTRAINT `fk_sesi_verifikator` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `siswa_rombel`
--
ALTER TABLE `siswa_rombel`
  ADD CONSTRAINT `fk_sr_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`),
  ADD CONSTRAINT `fk_sr_riwayat_kelas` FOREIGN KEY (`id_riwayat_kelas`) REFERENCES `riwayat_kelas` (`id_riwayat_kelas`),
  ADD CONSTRAINT `fk_sr_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`);

--
-- Constraints for table `soal_riasec`
--
ALTER TABLE `soal_riasec`
  ADD CONSTRAINT `fk_sr_dimensi` FOREIGN KEY (`id_dimensi`) REFERENCES `dimensi_riasec` (`id_dimensi`);

--
-- Constraints for table `template_rapor`
--
ALTER TABLE `template_rapor`
  ADD CONSTRAINT `fk_tr_sekolah` FOREIGN KEY (`id_sekolah`) REFERENCES `sekolah` (`id_sekolah`);

--
-- Constraints for table `tp`
--
ALTER TABLE `tp`
  ADD CONSTRAINT `fk_tp_rombel` FOREIGN KEY (`id_rombel`) REFERENCES `rombel` (`id_rombel`);

--
-- Constraints for table `verifikasi_nilai`
--
ALTER TABLE `verifikasi_nilai`
  ADD CONSTRAINT `fk_vn_paket` FOREIGN KEY (`id_paket_nilai`) REFERENCES `paket_nilai` (`id_paket_nilai`),
  ADD CONSTRAINT `fk_vn_pemeriksa` FOREIGN KEY (`id_pemeriksa`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `wali_kelas`
--
ALTER TABLE `wali_kelas`
  ADD CONSTRAINT `fk_wk_guru` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `fk_wk_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`),
  ADD CONSTRAINT `fk_wk_sem` FOREIGN KEY (`id_semester`) REFERENCES `semester` (`id_semester`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
