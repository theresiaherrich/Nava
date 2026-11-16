# Catatan perubahan — konsolidasi backend (`fix/backend-konsolidasi`)

Dasar: `dev_backend` @ `ffb691d`. Branch itu berisi hasil merge dua implementasi paralel yang belum diselesaikan: 16 file gagal parse, tiga konvensi tabel bercampur, dan migrasi yang membuat tabel yang sama dua kali. Akibatnya `migrate:fresh` dan `route:list` gagal.

## Konvensi yang dipilih

- Tabel akun `user` (PK `id_user`), semua PK `id_<tabel>`, FK `id_<tabel>`.
- Peran `roles.kode` huruf besar; multi-peran lewat `role_user`; **peran aktif** di sesi.
- API sesi cookie + CSRF, prefix `/api`, route per modul di `routes/api/*.php`.
- Satu middleware peran (`role:`), satu status akun (`account.active`), plus `password.changed`.
- Respons `{ data, message }`, daftar `{ data, meta, message }`, error `{ message, code, errors? }`.

## Dihapus

| Yang dihapus | Alasan / pengganti |
|---|---|
| Migrasi `2026_10_30_*`, `2026_10_31_*`, `2026_11_10_*`, `2026_11_14_*` | Diganti 4 migrasi bersih tanpa tabel dobel (lihat `03_ROADMAP_ERD.md`) |
| Tabel `users`, `siswa_kelas`, `predikat`, `kesiswaan`, `password_reset_tokens` | `user`, `riwayat_kelas`; predikat jadi kolom enum `peserta_ekskul.predikat`; peran KESISWAAN menggantikan tabel `kesiswaan`; tidak ada lupa-sandi mandiri |
| `POST /register`, `POST /auth/register`, `RegisterRequest`, status akun `pending`, persetujuan akun | Tidak ada registrasi publik. Akun dibuat pengelola (`/akun`, `/akun/massal`) atau artisan |
| `POST /auth/password/forgot`, `/auth/password/reset`, `PasswordResetService` | Tanpa kanal email/WA, token reset tidak aman dibagikan. Reset lewat `/akun/{id}/reset-password` |
| Controller root (`SiswaController`, `GuruController`, `KelasController`, `MapelController`, `SekolahController`, `TahunAjaranController`, `SemesterController`, `WaliKelasController`, `RombelController`, `TpController`, `NilaiController`, `PaketNilaiController`, `PeriodeNilaiController`, `RekapNilaiController`, `CetakRaporController`, `PelengkapRaporController`, `PesertaEkskulController`, `PresensiRingkasanController`, `EkskulController`, `SiswaKelasController`, `UserAccessController`, `AuthController`), `Api/*`, `Admin/*`, `Penilaian/*`, `Master/*` lama | Logikanya dipindah ke namespace baru per peran (lihat bawah) |
| Middleware `EnsureActive`, `RequireRole` | `EnsureAccountActive`, `EnsureRole` (peran aktif) |
| `siakad:create-admin` | `siakad:create-superadmin` |
| `AlokasiTpService` (STS = floor(n/2) TP pertama) dan flag `masuk_sts`/`is_sas` | Diganti aturan spesifikasi: nilai menyimpan `id_periode`; STS = nilai yang diisi pada periode STS; SAS = TP urutan terbesar; mode "STS saja" per TP (`hanya_sts`). `docs/09` diarsipkan |
| Tes lama (`SiakadTest`, `SiakadAuthTest`, `PenilaianTest`, `RekapNilaiTest`, `AlokasiTpTest`, `PaketNilaiSiwaTest`, `RaporSiwaTest`, `RombelTest`, `TpTest`) | Ditulis ulang untuk konvensi baru (lihat "Tes") |
| `resources/views/welcome.blade.php`, `rapor/pdf.blade.php` | Root `/` mengembalikan JSON; view rapor baru `rapor/sts`, `rapor/semester`, `dkn/pdf` |

## Dipindah (logika lama → tempat baru)

| Logika lama | Sekarang |
|---|---|
| `PenilaianAccess`, `AcademicAccess`, `RaporAccess` | `App\Services\PenilaianAccess`, `App\Support\AcademicAccess` (peran aktif, profil, cakupan), `App\Services\RaporAccess` |
| `AccountService`, `AuditService` | Ditulis ulang: hierarki peran, akun massal, sinkron WALI_KELAS, penghapusan field sensitif |
| `PeriodeService` (ringkasan, kunci kelas, kunci semua siap, buka koreksi) | `App\Services\PeriodeService` + jadwal/tingkat/tutup + notifikasi |
| `NilaiController` (validasi item, pratinjau tempel, kunci baris) | `InputNilaiService` + `Guru\NilaiController` |
| `PaketNilaiController` (kirim/setujui/revisi/kunci) | `PaketService`, `Guru\PaketController`, `Wali\VerifikasiController`, `Kurikulum\PeriodeController` |
| `RekapNilaiService` + `RekapNilaiExport` | `DknService` + `DknExport` (DKN per kelas) dan `RekapRombelExport` (rekap guru) |
| `RaporService` + `CetakRaporController` (snapshot, versi, PDF privat) | `RaporService` + `Rapor\RaporController` (+ terbit/cabut, ZIP/PDF gabungan) |
| `PelengkapRaporController`, `PresensiRingkasanController` | `PelengkapService` + `Wali\PelengkapController` (tarik dari tabel `presensi` A1 bila ada) |
| `EkskulController`, `PesertaEkskulController` | `Ekskul\EkskulController` |
| `TpController` (batas 10, salin) | `TpService` + `Guru\TpController` (urutan rapat, geser, salin sebagai baris baru) |

## Ditambah

- **Auth & peran:** SUPERADMIN, `config('siakad.role_manageable')`, penjaga akun sendiri & SUPERADMIN terakhir, `must_change_password` global, throttle login per (username, IP), audit semua aksi akun/peran, akun massal dengan kredensial sekali tampil, `switch-role`.
- **Operator:** dashboard, impor siswa Excel dua langkah (pratinjau + laporan baris gagal), pindah kelas + `mutasi_kelas`, orang tua ↔ anak (`orang_tua_siswa`), filter/pencarian.
- **Kurikulum:** dashboard; TA otomatis membuat semester & periode; satu semester aktif; mapel agama (`id_agama`), urutan rapor, KKTP default, template deskripsi; KKTP per TP; periode per semester dengan tingkat peserta; tutup periode; template rapor (whitelist view); terbitkan/cabut rapor.
- **Guru:** dashboard dengan progres; urutan TP bisa digeser; mode "STS saja"; status `perlu_revisi`; konfirmasi kirim belum lengkap; rekap rombel + ekspor.
- **Wali:** dashboard; verifikasi dengan catatan wajib & riwayat; pelengkap rapor; DKN kelas + ekspor Excel/PDF; cetak satu kelas (PDF gabungan/ZIP); riwayat cetak.
- **Kesiswaan:** dashboard, CRUD ekskul, peserta per semester, predikat SB/B/K.
- **Siswa/Orang tua:** portal beranda, nilai per mapel, detail per TP + "perlu penguatan", rapor terbit + versi, pilih anak.
- **Notifikasi:** tabel `notifikasi` + endpoint list/baca/baca semua; pemicu nilai dikirim, revisi, disetujui, periode dibuka/diubah/ditutup, koreksi dibuka, rapor terbit.
- **Analitik:** tren siswa, sebaran nilai (rerata, simpangan baku, min/maks, median, histogram), ketuntasan per mapel/TP, early warning.
- **Seeder dev:** 1 SUPERADMIN, 1 ADMIN, 1 KURIKULUM, 1 KESISWAAN, 2 GURU (Sari = wali X-B), 3 SISWA, 1 ORANG_TUA; mapel, rombel campuran, TP, periode STS dibuka, ekskul, template. Sandi dari `SEED_DEFAULT_PASSWORD`.

## Tes

`php artisan test`: **62 tes, 390 assertion, semua lulus** (SQLite in-memory). Berkas: `AuthTest`, `AkunTest`, `MasterDataTest`, `PenilaianTest`, `RaporTest`, `EkskulNotifikasiTest`, `AnalitikTest`.

Wajib dari spesifikasi:

| Skenario | Tes |
|---|---|
| Login / logout / must-change-password | `AuthTest::test_login_berhasil_*`, `test_logout_mengakhiri_sesi`, `test_wajib_ganti_sandi_memblokir_endpoint_lain` |
| SUPERADMIN membuat ADMIN | `AkunTest::test_superadmin_membuat_akun_admin` |
| ADMIN gagal membuat ADMIN/SUPERADMIN | `AkunTest::test_admin_gagal_membuat_admin_atau_superadmin` |
| Batas 10 TP | `PenilaianTest::test_batas_sepuluh_tp_per_rombel` |
| Kunci nilai menolak edit | `PenilaianTest::test_alur_kirim_setujui_kunci_dan_nilai_terkunci_menolak_edit` |
| Wali hanya melihat kelasnya | `RaporTest::test_wali_hanya_melihat_kelasnya`, `PenilaianTest::test_wali_wajib_catatan_*` |
| Siswa tidak bisa membuka rapor siswa lain | `RaporTest::test_siswa_tidak_bisa_membuka_rapor_siswa_lain_dan_rapor_belum_terbit` |
| Rapor belum terbit tidak bisa diunduh siswa | idem |

## Verifikasi

- `php -l` seluruh 182 file PHP: lulus. `composer dump-autoload`: tanpa warning.
- `php artisan migrate:fresh --seed` di MySQL 8.4 (Laragon): lulus.
- `php artisan route:list`: 140 route `/api`, tanpa duplikat method+URI, semua controller/method ada.
- `vendor/bin/pint`: lulus.
