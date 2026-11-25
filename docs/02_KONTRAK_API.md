# Kontrak API SIAKAD A2

Berlaku untuk branch `fix/backend-konsolidasi` dan seterusnya. Dokumen ini menggantikan kontrak tahap fondasi (register publik, tabel `users`, role huruf kecil).

- Base URL lokal: `http://127.0.0.1:8000/api`
- Semua route ada di `routes/api/*.php` dan dimuat dari `routes/web.php` dengan prefix `/api` serta middleware `web` (sesi cookie + CSRF).
- Kirim `Accept: application/json`. Body JSON memakai `Content-Type: application/json`; unggah file memakai `multipart/form-data`.

## 1. Sesi, CSRF, dan peran aktif

1. `GET /api/csrf-token` → simpan cookie sesi dan `data.csrf_token`.
2. `POST /api/auth/login` dengan header `X-CSRF-TOKEN`. Respons berisi `csrf_token` baru (sesi diregenerasi). Pakai token baru ini untuk request berikutnya.
3. Semua POST/PUT/PATCH/DELETE wajib membawa cookie yang sama dan header `X-CSRF-TOKEN`.
4. Satu akun boleh punya beberapa peran (mis. GURU + WALI_KELAS). Saat login, peran aktif = peran dengan prioritas tertinggi (urutan `config/siakad.php`). Ganti lewat `POST /api/auth/switch-role`. **Middleware `role:` memeriksa peran aktif**, bukan gabungan semua peran.
5. Bila `must_change_password = true`, semua endpoint selain `/auth/me`, `/auth/password/change`, dan `/auth/logout` mengembalikan `403` dengan `code: MUST_CHANGE_PASSWORD`.

Frontend lintas origin membutuhkan konfigurasi CORS + `SESSION_DOMAIN` (belum disiapkan; saat ini diasumsikan satu origin).

## 2. Bentuk respons

```jsonc
// sukses
{ "data": { ... }, "message": "Data berhasil dibuat." }

// daftar (pagination; ?page=, ?per_page= 1–100, default 20)
{ "data": [ ... ], "meta": { "current_page": 1, "per_page": 20, "total": 3, "last_page": 1 }, "message": null }

// error
{ "message": "Penjelasan untuk pengguna.", "code": "KODE_MESIN", "errors": { "field": ["..."] } }
```

`errors` hanya ada bila relevan (validasi/sel ditolak). Frontend sebaiknya membaca **status HTTP + `code`**, bukan isi kalimat.

| HTTP | Kode umum | Arti |
|---|---|---|
| 401 | `UNAUTHENTICATED`, `INVALID_CREDENTIALS` | Belum login / kredensial salah |
| 403 | `ROLE_FORBIDDEN`, `FORBIDDEN`, `MUST_CHANGE_PASSWORD`, `ACCOUNT_NOT_ACTIVATED`, `ACCOUNT_DISABLED`, `ACCOUNT_INACTIVE`, `ROLE_HIERARCHY`, `ROLE_CLI_ONLY`, `SELF_MANAGEMENT`, `LAST_SUPERADMIN`, `NOT_PENGAMPU`, `NOT_WALI_KELAS`, `STUDENT_FORBIDDEN`, `RAPOR_NOT_PUBLISHED`, `PROFILE_NOT_LINKED` | Tidak berwenang |
| 404 | `NOT_FOUND`, `IMPORT_TOKEN_INVALID`, `FILE_MISSING` | Data/endpoint tidak ada |
| 409 | `DUPLICATE`, `PROFILE_ALREADY_LINKED`, `PRESENSI_A1_UNAVAILABLE` | Konflik |
| 419 | `CSRF_MISMATCH` | Token CSRF/sesi tidak valid |
| 422 | `VALIDATION_ERROR`, `BUSINESS_RULE`, `LOCKED`, `TP_LIMIT`, `NILAI_DITOLAK`, `PAKET_BELUM_LENGKAP`, `PAKET_BELUM_SIAP`, `PAKET_STATUS`, `PAKET_KOSONG`, `ROLE_AUTOMATIC`, `ROLE_CONFLICT`, `PROFILE_REQUIRED`, `TEMPLATE_MISSING`, `NO_ACTIVE_SEMESTER`, `CHILD_REQUIRED` | Input/aturan bisnis ditolak |
| 429 | `TOO_MANY_REQUESTS` | Rate limit |

## 3. Peran

| Kode | Label UI | Catatan |
|---|---|---|
| `SUPERADMIN` | Super Admin | Hanya dibuat via `php artisan siakad:create-superadmin` |
| `ADMIN` | Operator | Data master. Tidak boleh mengelola akun ADMIN/SUPERADMIN |
| `KURIKULUM` | Admin Kurikulum | |
| `KESISWAAN` | Admin Kesiswaan | Ekskul |
| `WALI_KELAS` | Wali Kelas | Otomatis dari penetapan wali kelas; tidak bisa dipilih manual |
| `GURU` | Guru Mapel | |
| `BUDAYA_POSITIF` | Guru BK | Disiapkan untuk A3; belum ada endpoint |
| `SISWA` | Siswa | Tidak boleh digabung peran lain |
| `ORANG_TUA` | Orang Tua | |

Peran yang boleh dikelola tiap peran aktif diatur di `config/siakad.php` → `role_manageable` (default: SUPERADMIN semua kecuali SUPERADMIN/WALI_KELAS; ADMIN kosong).

## 4. Endpoint

Notasi akses: peran aktif yang diizinkan middleware. Pembatasan data (rombel yang diampu, kelas yang diwalikan, anak tertaut) diterapkan di `PenilaianAccess` / `RaporAccess`.

### 4.1 Auth (semua pengguna)

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/csrf-token` | | Publik |
| POST | `/auth/login` | `username`, `password` | Throttle 5/menit per (username, IP). 403 bila `belum_aktif`/`nonaktif` |
| GET | `/auth/me` | | Data akun, peran, peran aktif, `must_change_password`, `csrf_token` |
| POST | `/auth/logout` | | Sesi dihapus; respons berisi `csrf_token` baru |
| POST | `/auth/password/change` | `password_lama`, `password_baru`, `password_baru_confirmation` | Min. 10, huruf besar+kecil, angka, simbol |
| POST | `/auth/switch-role` | `peran` | Hanya peran yang dimiliki |
| GET | `/notifikasi` | `belum_dibaca=1` | Pagination + `jumlah_belum_dibaca` |
| POST | `/notifikasi/{id}/baca` | | Hanya milik sendiri |
| POST | `/notifikasi/baca-semua` | | |
| GET | `/referensi/agama` | | |

Tidak ada `POST /auth/register` dan tidak ada lupa-sandi mandiri. Reset sandi dilakukan pengelola akun.

### 4.2 Kelola akun — SUPERADMIN, ADMIN (dibatasi `role_manageable`)

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/roles` | | Daftar peran + `dapat_diberikan`, `otomatis` |
| GET | `/akun` | `q`, `role`, `status_akun` | Tiap baris berisi `dapat_dikelola` |
| POST | `/akun` | `username`, `nama`, `email?`, `password?`, `status_akun?` (`aktif`/`belum_aktif`), `must_change_password?`, `roles[]`, `id_guru?`, `id_siswa?`, `id_orang_tua?` | Peran GURU/SISWA/ORANG_TUA wajib menautkan profil. Bila `password` kosong → dibuat acak dan dikembalikan **sekali** di `data.password_awal` |
| GET | `/akun/{id}` | | |
| PUT | `/akun/{id}` | `username?`, `nama?`, `email?` | |
| PUT | `/akun/{id}/peran` | `roles[]`, `id_guru?`… | Ganti peran manual; WALI_KELAS dipertahankan; sesi target diputus |
| PATCH | `/akun/{id}/status` | `status_akun`, `alasan` (wajib bila nonaktif) | SUPERADMIN terakhir tidak bisa dinonaktifkan |
| POST | `/akun/{id}/reset-password` | | `data.password_sementara` sekali tampil; wajib ganti saat login |
| POST | `/akun/massal` | `jenis` (`siswa`/`guru`/`orang_tua`), `ids?[]`, `format?` (`json`/`xlsx`) | Akun untuk profil tanpa akun. Username: NISN (fallback NIS) / NIP (fallback `gttNNNN`) / `ortu<NISN anak><a\|i\|w>`. Kredensial sekali tampil |
| GET | `/audit-log` | `aksi`, `nama_entitas`, `entitas_id`, `id_user`, `dari`, `sampai` | |

Akun sendiri tidak bisa diubah dari menu ini (`SELF_MANAGEMENT`).

### 4.3 Operator (ADMIN) — data master

Baca juga diizinkan untuk KURIKULUM dan KESISWAAN: `GET /master/sekolah`, `/master/siswa`, `/master/siswa/{id}`, `/master/guru`, `/master/guru/{id}`, `/master/kelas`, `/master/kelas/{id}/anggota`.

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/master/dashboard` | | Jumlah siswa, guru, kelas, akun belum aktif, siswa/guru tanpa akun, siswa tanpa kelas |
| GET/PUT | `/master/sekolah` | `npsn` (8 digit), `nama`, `alamat`, `kota`, `telepon`, `email`, `kepala_sekolah`, `nip_kepala_sekolah` | |
| GET | `/master/siswa` | `q`, `status`, `id_kelas`, `id_semester`, `id_agama`, `jenis_kelamin`, `tanpa_kelas`, `tanpa_akun` | |
| POST/PUT | `/master/siswa`, `/master/siswa/{id}` | `nis`, `nisn?` (10 digit), `nama`, `jenis_kelamin?`, `id_agama?`, `tempat_lahir?`, `tanggal_lahir?`, `alamat?`, `tahun_masuk?`, `status?` | |
| DELETE | `/master/siswa/{id}` | | Hanya bila belum punya riwayat kelas/akun |
| GET | `/master/siswa-impor/template` | | Unduh xlsx |
| POST | `/master/siswa-impor/pratinjau` | `file` (xlsx/xls/csv ≤ 5 MB) | Tidak menyimpan. `data.token`, `valid`, `gagal[{baris, errors}]`, `contoh` |
| POST | `/master/siswa-impor/simpan` | `token` | Menyimpan baris valid (cek ulang); token sekali pakai, 30 menit |
| POST/PUT | `/master/guru`, `/master/guru/{id}` | `status_kepegawaian` (`ASN`/`GTT`), `nip` (wajib untuk ASN), `nama`, … | |
| POST/PUT/DELETE | `/master/kelas`, `/master/kelas/{id}` | `id_tahun_ajaran` (hanya POST), `nama`, `tingkat` (`X`/`XI`/`XII`) | Fase otomatis (X=E, XI/XII=F) |
| POST | `/master/kelas/{id}/anggota` | `id_siswa[]`, `id_semester?` | Satu siswa satu kelas per semester; hasil `ditambahkan`, `gagal{}` |
| POST | `/master/anggota-kelas/{id}/pindah` | `id_kelas`, `alasan` | Tingkat & TA sama; tercatat di `mutasi_kelas` |
| DELETE | `/master/anggota-kelas/{id}` | | Ditolak bila sudah ada nilai |
| GET/POST/PUT | `/master/orang-tua`, `/master/orang-tua/{id}` | `nama`, `jenis_kelamin?`, `no_telepon?`, `alamat?` | |
| POST | `/master/siswa/{id}/orang-tua` | `id_orang_tua`, `hubungan` (`Ayah`/`Ibu`/`Wali`) | |
| DELETE | `/master/siswa/{id}/orang-tua/{idOrangTua}` | | |

### 4.4 Admin Kurikulum

Baca juga untuk ADMIN & KESISWAAN: `GET /kurikulum/tahun-ajaran`, `GET /kurikulum/mapel`.

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/kurikulum/dashboard` | | Rombel tanpa guru/TP, status periode & paket, kelas siap dikunci |
| POST | `/kurikulum/tahun-ajaran` | `nama` (`2026/2027`) | Otomatis membuat semester GANJIL/GENAP + periode STS/SAS |
| POST | `/kurikulum/semester/{id}/aktifkan` | | Hanya satu semester & satu TA aktif |
| POST/PUT | `/kurikulum/mapel`, `/kurikulum/mapel/{id}` | `kode`, `nama`, `kelompok?`, `urutan_rapor?`, `id_agama?` (mapel agama), `kktp_default?`, `deskripsi_kuat?`, `deskripsi_penguatan?`, `aktif?` | Template deskripsi memakai `:tp` |
| GET | `/kurikulum/rombel` | `id_semester`, `id_mapel`, `id_guru`, `id_kelas`, `q` | |
| POST | `/kurikulum/rombel` | `id_semester`, `id_mapel`, `nama`, `id_guru[]?` | |
| GET/PUT/DELETE | `/kurikulum/rombel/{id}` | `nama` | Detail: guru, TP, kelas asal, peserta. Hapus hanya bila tanpa TP & peserta |
| PUT | `/kurikulum/rombel/{id}/guru` | `id_guru[]` | Guru pengampu |
| POST | `/kurikulum/rombel/{id}/peserta` | `id_riwayat_kelas[]` atau `id_kelas[]` | Mapel agama hanya siswa seagama; semester harus sama |
| DELETE | `/kurikulum/rombel/{id}/peserta/{idSiswaRombel}` | | Ditolak bila sudah ada nilai |
| GET | `/kurikulum/rombel/{id}/rekap` | | Rekap nilai rombel |
| GET | `/kurikulum/wali-kelas` | `id_semester?` | |
| PUT | `/kurikulum/kelas/{id}/wali` | `id_guru`, `id_semester?` | Memberi WALI_KELAS otomatis (guru wajib punya akun) |
| DELETE | `/kurikulum/wali-kelas/{id}` | | Mencabut WALI_KELAS bila tidak menjadi wali lagi |
| GET | `/kurikulum/periode` | `id_semester?` | STS & SAS semester (dibuat otomatis) |
| PUT | `/kurikulum/periode/{id}` | `tgl_buka`, `tgl_tutup`, `tingkat[]?` | Status → `dibuka` (efektif mengikuti tanggal) |
| POST | `/kurikulum/periode/{id}/tutup` | | Tutup input sekarang |
| GET | `/kurikulum/periode/{id}/ringkasan` | | Per kelas: wali, mapel siap/total, status, daftar paket |
| POST | `/kurikulum/periode/{id}/kelas/{idKelas}/kunci` | | Semua paket kelas harus `disetujui` |
| POST | `/kurikulum/periode/{id}/kunci-semua-siap` | | |
| POST | `/kurikulum/paket/{id}/buka-koreksi` | `catatan` (wajib) | `dikunci` → `perlu_revisi` + `koreksi=true` |
| PUT | `/kurikulum/tp/{id}/kktp` | `kktp` (null = default mapel) | |
| GET | `/kurikulum/dkn` | `id_kelas`, `id_periode` | DKN kelas |
| GET | `/kurikulum/dkn/ekspor` | `id_kelas`, `id_periode`, `format` (`xlsx`/`pdf`) | |
| GET/POST/PUT | `/kurikulum/template-rapor`, `/kurikulum/template-rapor/{id}` | `jenis`, `nama`, `view` (whitelist), `judul?`, `tempat_cetak?`, `tampilkan_deskripsi?`, `aktif?` | Satu aktif per jenis |
| GET | `/kurikulum/rapor` | `id_kelas`, `id_periode` | Status nilai + versi rapor per siswa |
| POST | `/kurikulum/rapor/cetak` | `id_periode`, `id_riwayat_kelas` | |
| POST | `/kurikulum/rapor/cetak-kelas` | `id_periode`, `id_kelas`, `format` (`pdf`/`zip`) | Unduhan |
| GET | `/kurikulum/rapor/riwayat-cetak` | `id_kelas`, `id_semester?` | |
| GET | `/kurikulum/rapor/{id}/unduh` | `pratinjau?` (boolean) | `pratinjau=1` menampilkan PDF tersimpan secara inline |
| POST | `/kurikulum/rapor/terbitkan` | `id_kelas`, `id_periode` | Versi terbaru tiap siswa → `terbit`; notifikasi siswa & ortu |
| POST | `/kurikulum/rapor/{id}/cabut` | `alasan` | |

### 4.5 Guru Mapel (hanya rombel yang diampu)

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/guru/dashboard` | | Rombel diampu, jumlah siswa/TP, progres input, status paket |
| GET | `/guru/periode` | `id_semester?` | |
| GET | `/guru/rombel`, `/guru/rombel/{id}` | `id_semester?` | |
| GET | `/guru/rombel/{id}/tp` | | `maks`, `terkunci`, tiap TP: `kktp`, `jumlah_nilai`, `dapat_dihapus`, `tp_sas` |
| POST | `/guru/rombel/{id}/tp` | `deskripsi`, `kode?`, `hanya_sts?` | Maks. 10 (`TP_LIMIT`) |
| PUT | `/guru/tp/{id}` | `deskripsi?`, `kode?`, `hanya_sts?` | |
| DELETE | `/guru/tp/{id}` | | Ditolak bila ada nilai; urutan dirapatkan |
| PUT | `/guru/rombel/{id}/tp-urutan` | `id_tp[]` (semua TP, urutan baru) | |
| POST | `/guru/rombel/{id}/tp-salin` | `id_tp[]`, `id_rombel_tujuan[]` | Mapel sama, rombel yang juga diampu; menjadi baris baru |
| GET | `/guru/rombel/{id}/nilai` | `id_periode`, `id_kelas?` | Grid: TP, peserta, nilai per sel + `dapat_diedit`, ringkasan |
| POST | `/guru/rombel/{id}/nilai/pratinjau` | `id_periode`, `items[{id_siswa_rombel, id_tp, nilai}]` | Validasi tempel Excel tanpa menyimpan |
| PUT | `/guru/rombel/{id}/nilai` | sama | Simpan draf. `nilai: null` mengosongkan sel. Maks. 1000 sel |
| GET | `/guru/rombel/{id}/rekap`, `/guru/rombel/{id}/rekap/ekspor` | | Rekap rombel + xlsx |
| GET | `/guru/rombel/{id}/paket` | `id_periode` | Status & kelengkapan per kelas asal + riwayat |
| POST | `/guru/rombel/{id}/paket/kirim` | `id_periode`, `id_kelas`, `konfirmasi_belum_lengkap?` | Belum lengkap tanpa konfirmasi → 422 `PAKET_BELUM_LENGKAP` |

### 4.6 Wali Kelas (hanya kelas yang diwalikan)

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/wali/dashboard` | | Kelas, jumlah siswa, ringkasan status paket per periode |
| GET | `/wali/periode` | | |
| GET | `/wali/paket` | `id_periode` | Daftar mapel kelas + status |
| GET | `/wali/paket/{id}` | | Nilai per siswa/TP, kelengkapan, riwayat |
| POST | `/wali/paket/{id}/setujui` | `catatan?` | `terkirim` → `disetujui` |
| POST | `/wali/paket/{id}/revisi` | `catatan` (wajib) | `terkirim`/`disetujui` → `perlu_revisi` |
| GET | `/wali/pelengkap` | `id_kelas`, `id_periode` | Presensi, catatan, kokurikuler, ekskul, `status_presensi_a1` |
| PUT | `/wali/pelengkap` | `id_kelas`, `id_periode`, `items[{id_riwayat_kelas, sakit?, izin?, alpha?, catatan_wali?, kokurikuler?}]` | Ditolak bila rapor kelas sudah terbit |
| POST | `/wali/pelengkap/tarik-presensi` | `id_kelas`, `id_periode`, `dari`, `sampai` | Dari tabel `presensi` A1; 409 bila belum ada |
| GET | `/wali/dkn`, `/wali/dkn/ekspor` | `id_kelas`, `id_periode`, `format?` | |
| GET/POST | `/wali/rapor`, `/wali/rapor/cetak`, `/wali/rapor/cetak-kelas`, `/wali/rapor/riwayat-cetak`, `/wali/rapor/{id}/unduh` | sama dengan Kurikulum | Cetak hanya bila semua nilai kelas dikunci |

### 4.7 Kesiswaan

| Method | Path | Body / query | Keterangan |
|---|---|---|---|
| GET | `/kesiswaan/dashboard` | | |
| GET/POST | `/kesiswaan/ekskul` | `nama`, `id_pembina?`, `aktif?` | |
| PUT/DELETE | `/kesiswaan/ekskul/{id}` | | Hapus hanya bila tanpa peserta |
| GET | `/kesiswaan/ekskul/{id}/peserta` | `id_semester?` | |
| POST | `/kesiswaan/ekskul/{id}/peserta` | `id_riwayat_kelas[]` | |
| PUT | `/kesiswaan/ekskul/{id}/predikat` | `items[{id_peserta_ekskul, predikat (SB/B/K/null), catatan?}]` | Tampil hanya di rapor semester |
| DELETE | `/kesiswaan/peserta-ekskul/{id}` | | |

### 4.8 Siswa & Orang Tua (data diri/anak sendiri)

Orang tua dengan lebih dari satu anak wajib mengirim `?id_siswa=`; anak yang tidak tertaut → 403.

| Method | Path | Keterangan |
|---|---|---|
| GET | `/portal/anak` | Siswa: dirinya; orang tua: daftar anak |
| GET | `/portal/beranda` | Kelas, rata-rata, jumlah TP perlu penguatan, rapor terbit terbaru |
| GET | `/portal/nilai?id_semester=` | Nilai per mapel (STS, SAS, akhir, kelengkapan, perlu penguatan) |
| GET | `/portal/nilai/{idRombel}` | Detail per TP: nilai, KKTP, `perlu_penguatan` |
| GET | `/portal/rapor` | Riwayat rapor **terbit** + versi |
| GET | `/portal/rapor/{id}/unduh` | Hanya rapor terbit milik sendiri/anak. `?pratinjau=1` menampilkan PDF secara inline. |
| GET | `/portal/analitik/tren` | Tren nilai antarsemester |

### 4.9 Analitik — KURIKULUM, WALI_KELAS, GURU

Cakupan: `id_rombel` **atau** `id_kelas`, plus `id_periode`. Kurikulum semua; wali hanya kelasnya; guru hanya rombel yang diampu.

| Method | Path | Isi |
|---|---|---|
| GET | `/analitik/sebaran` | Per mapel: `n`, `rerata`, `simpangan_baku` (populasi), `min`, `maks`, `median`, `histogram` |
| GET | `/analitik/ketuntasan` | Persen tuntas per mapel (nilai ≥ KKTP mapel) dan per TP |
| GET | `/analitik/early-warning` | Siswa dengan TP < KKTP dan/atau nilai turun dua asesmen berturut-turut; `tingkat` sedang/tinggi |
| GET | `/analitik/siswa/{id}/tren` | Tren siswa (guru: hanya mapel yang diampu) |

## 5. Aturan nilai yang berlaku

- Nilai 0–100, maks. 2 desimal. `null`/tidak ada record = belum diisi (bukan 0).
- `nilai_tp.id_periode` = periode tempat nilai diisi. Nilai yang diisi di STS tidak bisa diubah dari periode SAS.
- **Nilai STS** = rata-rata nilai TP yang diisi pada periode STS.
- **Nilai SAS** = nilai TP dengan `urutan` terbesar.
- **Nilai akhir semester** = rata-rata semua nilai TP rombel. Dihitung, tidak disimpan (`NilaiService`).
- TP "STS saja" (`hanya_sts`) hanya bisa diisi di periode STS.
- Sel bisa diedit bila periode `dibuka` (atau paket dibuka koreksinya), paket kelas `draf`/`perlu_revisi`, dan tingkat kelas ikut periode.
- Kelengkapan paket STS: semua siswa punya nilai untuk TP "STS saja" + TP yang sudah dinilai di periode STS. Paket SAS: semua TP rombel.
- Status paket: `draf → terkirim → disetujui → dikunci`; `terkirim/disetujui → perlu_revisi` (wali, catatan wajib); `dikunci → perlu_revisi` (buka koreksi Kurikulum, alasan wajib). Riwayat di `verifikasi_nilai` (append-only).
- Periode menjadi `dikunci` otomatis bila semua paketnya dikunci. Struktur TP terkunci bila periode SAS dikunci.
- Rapor hanya bisa dicetak bila semua paket kelas pada periode itu `dikunci`. Data berubah → versi baru; versi lama tetap. Siswa/ortu hanya bisa mengunduh versi `terbit`.
- Penguncian memeriksa kelengkapan nilai terkini pada semua paket yang disetujui. Paket belum lengkap dapat dikembalikan untuk revisi dan tidak dapat dikunci.
- Rapor STS menyimpan nilai per TP yang masuk periode STS. Saat penerbitan, sistem memeriksa penguncian kelas, isi draf, templat aktif, dan keberadaan berkas PDF. Cetak kembali bila data draf sudah berubah.
