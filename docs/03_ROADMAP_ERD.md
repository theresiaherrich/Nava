# ERD, pemetaan nama tabel, dan sisa pekerjaan

## 1. Keputusan nama tabel

Implementasi mempertahankan nama tabel yang sudah dipakai tim (dan SQL revisi `siakad_sma1_revisi.sql`). Nama ERD yang berbeda dipetakan seperti berikut. **Gunakan nama di kolom kiri** di kode, query, dan diskusi.

| Tabel di database | Nama di ERD | Alasan |
|---|---|---|
| `user` (PK `id_user`) | users | Dipakai bersama A1–A4 |
| `riwayat_kelas` | anggota_kelas | Penempatan siswa ke kelas per semester. Unik `(id_siswa, id_semester)` dan `(id_kelas, id_siswa, id_semester)` |
| `rombel` + `guru_rombel` | pengajaran | ERD `pengajaran(guru, mapel, kelas)` tidak bisa menampung **rombel campuran** (satu kelompok belajar berisi siswa X-A dan X-B, mis. mapel pilihan/agama). Rombel = mapel × semester; guru pengampu di `guru_rombel` (PK `id_guru, id_rombel`) |
| `siswa_rombel` | anggota pengajaran | Siswa (lewat `riwayat_kelas`) dalam rombel; unik `(id_rombel, id_riwayat_kelas)` |
| `periode_nilai` + `periode_tingkat` | periode_penilaian | Unik `(id_semester, jenis)`; tingkat peserta per periode di `periode_tingkat` (mis. XII hanya ganjil) |
| `paket_nilai` | pengajuan_nilai | Unik `(id_rombel, id_periode, id_kelas)`. Ditambah `id_kelas` karena rombel campuran diverifikasi per kelas oleh wali masing-masing |
| `peserta_ekskul` | nilai_ekskul | Unik `(id_ekskul, id_riwayat_kelas)`. `riwayat_kelas` sudah per semester, jadi semester tidak diulang |
| `orang_tua` + `orang_tua_siswa` | orang_tua | Satu orang tua bisa punya banyak anak (dropdown "pilih anak") |

## 2. Daftar tabel (urutan migrasi)

| Migrasi | Tabel |
|---|---|
| `0001_01_01_000000_create_auth_tables` | `user`, `roles`, `role_user`, `sessions`, `log_aktivitas`, `notifikasi` |
| `0001_01_01_000001/2` (bawaan) | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` |
| `2026_11_16_000001_create_struktur_sekolah_tables` | `sekolah`, `agama`, `tahun_ajaran`, `semester`, `kelas`, `siswa`, `guru`, `orang_tua`, `orang_tua_siswa`, `riwayat_kelas`, `mutasi_kelas`, `wali_kelas` |
| `2026_11_16_000002_create_penilaian_tables` | `mapel`, `rombel`, `guru_rombel`, `siswa_rombel`, `periode_nilai`, `periode_tingkat`, `tp`, `nilai_tp`, `paket_nilai`, `verifikasi_nilai` |
| `2026_11_16_000003_create_rapor_ekskul_tables` | `ekskul`, `peserta_ekskul`, `pelengkap_rapor`, `template_rapor`, `rapor` |

## 3. Aturan bisnis dan tempat penegakannya

| Aturan | Database | Aplikasi |
|---|---|---|
| Maks. 10 TP per rombel, urutan 1–10 | unik `tp(id_rombel, urutan)` | `TpService` (batas, urutan rapat, geser dua langkah) |
| Satu nilai per TP × peserta | unik `nilai_tp(id_tp, id_siswa_rombel)` | `InputNilaiService` |
| Satu siswa satu kelas per semester | unik `riwayat_kelas(id_siswa, id_semester)` | `AnggotaKelasService` |
| Guru pengampu tidak dobel | PK `guru_rombel(id_guru, id_rombel)` | `RombelService` |
| Satu paket per rombel × periode × kelas | unik `paket_nilai(...)` | `PaketService` |
| Satu predikat ekskul per peserta | unik `peserta_ekskul(id_ekskul, id_riwayat_kelas)` | `EkskulController` |
| Satu STS & satu SAS per semester | unik `periode_nilai(id_semester, jenis)` | `PeriodeService::pastikanAda` |
| Nilai semester dihitung, tidak disimpan | tidak ada kolom nilai akhir | `NilaiService::hitung` |
| Mapel agama hanya untuk siswa seagama | `mapel.id_agama`, `siswa.id_agama` | `RombelService::tambahPeserta` |
| Guru hanya rombel yang diampu | | `PenilaianAccess::pastikanPengampu` |
| Wali hanya kelasnya | `wali_kelas(id_kelas, id_semester)` unik | `PenilaianAccess::pastikanWaliKelas`, `RaporAccess` |
| Siswa/ortu hanya dirinya/anaknya | `siswa.id_user`, `orang_tua_siswa` | `RaporAccess` |
| Riwayat verifikasi tidak ditimpa | `verifikasi_nilai` tanpa `updated_at` | `PaketService`, `PeriodeService` |
| Rapor versi lama tidak berubah | unik `rapor(id_riwayat_kelas, id_periode, versi)`, `data_final` JSON | `RaporService::cetak` |

Batas urutan 1–10 tidak dibuat sebagai CHECK constraint, karena penggeseran urutan memakai nilai sementara 100+ agar unik tidak bentrok. Batasnya dijaga di service.

## 4. Sisa pekerjaan / di luar cakupan saat ini

| Fitur | Status | Catatan |
|---|---|---|
| Presensi otomatis | Sebagian | Tabel `presensi` milik modul A1 (branch `feature/a1-presensi`). Bila sudah digabung, `POST /wali/pelengkap/tarik-presensi` membaca tabel itu. Sebelum itu wali mengisi manual |
| Modul Guru BK (BUDAYA_POSITIF) | Belum | Peran sudah ada; modul A3 |
| Kalender semester (tanggal mulai/selesai) | Belum | Rentang tarik presensi dikirim manual (`dari`, `sampai`) |
| Lupa kata sandi mandiri | Sengaja tidak ada | Belum ada kanal email/WA (A4). Reset dilakukan pengelola akun |
| Kenaikan kelas massal | Belum | Saat ini dengan menambah anggota kelas di semester baru |
| CORS frontend beda origin | Belum | Asumsi satu origin |
| Uji konkurensi MySQL | Belum | Tes otomatis memakai SQLite in-memory; penguncian baris (`lockForUpdate`) belum diuji beban di MySQL |
