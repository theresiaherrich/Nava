# Panduan setup backend SIAKAD A2

Backend Laravel modul akademik (nilai, rapor, analitik) untuk SMA Negeri 1 Kota Malang. Stack: **Laravel 13, PHP 8.3+, MySQL 8 (Laragon)**. API berbasis sesi cookie + CSRF, prefix `/api`. Kontrak lengkap di `02_KONTRAK_API.md`.

## 1. Alat

| Alat | Fungsi |
|---|---|
| Laragon | PHP + MySQL di Windows |
| DBeaver | Melihat isi MySQL (opsional) |
| Composer | Dependency PHP |
| Postman | Uji endpoint (`docs/SIAKAD_A2.postman_collection.json`) |

Pastikan ekstensi PHP `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `dom`, `xml`, `gd`, `zip` aktif (`php -m`). `pdo_sqlite` dipakai tes otomatis; `gd`/`zip` dipakai Excel/PDF/ZIP rapor.

## 2. Pasang proyek

```bash
git checkout fix/backend-konsolidasi
composer install
cp .env.example .env        # PowerShell: Copy-Item .env.example .env
php artisan key:generate
```

> **Composer gagal SSL ("curl error 60")?** Jaringan/antivirus memakai sertifikat yang tidak ada di `cacert.pem` PHP. Jangan mematikan verifikasi. Ekspor root CA Windows ke file PEM lalu set `COMPOSER_CAFILE` ke file itu, atau minta file `cacert.pem` terbaru ke admin jaringan. Untuk git: `git -c http.sslBackend=schannel fetch`.

## 3. Database

Buat database **khusus** (jangan memakai database tim yang berisi data):

```sql
CREATE DATABASE siakad_a2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Isi `.env`:

```dotenv
DB_DATABASE=siakad_a2
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database

# Hanya development: sandi semua akun contoh dari seeder.
# Min. 10 karakter, huruf besar, kecil, angka, simbol. Jangan di-commit.
SEED_DEFAULT_PASSWORD=
```

Jalankan:

```bash
php artisan migrate:fresh --seed     # menghapus SEMUA tabel di database .env!
```

Tanpa `SEED_DEFAULT_PASSWORD`, seeder hanya membuat data referensi (peran, agama). Dengan sandi terisi dan `APP_ENV=local`, seeder membuat data contoh:

| Username | Peran | Catatan |
|---|---|---|
| `superadmin` | SUPERADMIN | |
| `operator` | ADMIN | |
| `kurikulum` | KURIKULUM | |
| `kesiswaan` | KESISWAAN | |
| `budi` | GURU | Matematika X (rombel campuran X-A + X-B), PAI X |
| `sari` | GURU + WALI_KELAS | Wali X-B; Bahasa Indonesia X, PAK X |
| `andi`, `bunga`, `citra` | SISWA | Andi & Bunga di X-B, Citra di X-A |
| `hendra` | ORANG_TUA | Ayah dari Andi |

Periode STS semester ganjil 2026/2027 sudah dibuka, dan Matematika X sudah punya 4 TP dengan nilai TP 1–2.

## 4. Produksi / server bersama

Jangan menjalankan seeder contoh. Buat Super Admin pertama:

```bash
php artisan migrate --force
php artisan db:seed --force            # hanya peran & agama
php artisan siakad:create-superadmin   # interaktif; tidak ada sandi bawaan
```

Perintah menolak bila SUPERADMIN sudah ada (pakai `--force` bila memang perlu menambah). Super Admin kemudian membuat akun Operator/Kurikulum/Kesiswaan dan akun massal guru/siswa/orang tua dari menu akun. Akun baru wajib mengganti sandi saat login pertama.

## 5. Jalankan dan uji

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

`GET http://127.0.0.1:8000/` → `{"application":"SIAKAD A2","status":"ok"}`.

Postman: import `docs/SIAKAD_A2.postman_collection.json`, isi variabel `username` dan `password`. Jalankan **00 Auth → CSRF** lalu **Login**; script collection menyimpan token CSRF otomatis. Gunakan host `127.0.0.1` secara konsisten (cookie `localhost` berbeda).

Untuk uji Postman lokal tanpa CSRF bisa di-set `SIAKAD_DEV_SKIP_CSRF=true`; ini hanya aktif bila `APP_ENV=local`. Jangan dipakai di server bersama.

## 6. Tes otomatis

```bash
php artisan test            # SQLite in-memory, tidak menyentuh MySQL
vendor/bin/pint             # format kode sebelum commit
```

## 7. Struktur kode

| Folder | Isi |
|---|---|
| `routes/api/*.php` | Route per modul: `auth`, `akun`, `master`, `kurikulum`, `penilaian`, `rapor`, `ekskul`, `notifikasi`, `analitik` |
| `app/Http/Controllers/{Auth,Akun,Master,Kurikulum,Guru,Wali,Rapor,Ekskul,Portal,Analitik}` | Controller tipis per peran |
| `app/Http/Requests/*` | Validasi (FormRequest) |
| `app/Services` | Logika bisnis + transaksi (`NilaiService` = satu sumber rumus nilai) |
| `app/Services/PenilaianAccess.php`, `RaporAccess.php`, `app/Support/AcademicAccess.php` | Aturan akses data |
| `config/siakad.php` | Peran, `role_manageable`, kebijakan sandi, KKTP default, template deskripsi, view rapor |
| `resources/views/rapor`, `dkn` | Template PDF |

## 8. Masalah umum

| Gejala | Penyebab |
|---|---|
| 419 `CSRF_MISMATCH` | Token dari sesi lain; ambil ulang `/api/csrf-token` dan pakai token dari respons login |
| 403 `ROLE_FORBIDDEN` | Peran aktif salah; `POST /api/auth/switch-role` |
| 403 `MUST_CHANGE_PASSWORD` | Ganti sandi dulu lewat `/api/auth/password/change` |
| 422 `NO_ACTIVE_SEMESTER` | Kurikulum belum mengaktifkan semester |
| `Isi SEED_DEFAULT_PASSWORD` saat seeding | Isi variabel itu di `.env` |
