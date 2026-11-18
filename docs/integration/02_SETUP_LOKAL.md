# Setup Nava di Windows dan Laragon

Gunakan PHP 8.3+, Composer 2, MySQL 8, dan Node.js yang memenuhi batas versi Vite 8, misalnya Node.js 24. Aktifkan `pdo_mysql`, `pdo_sqlite`, `mbstring`, `dom`, `xml`, `gd`, dan `zip`. Jalankan MySQL di Laragon. Pastikan nomor port yang aktif. Pengujian integrasi ini memakai port 3307.

Jika workspace sudah tersedia, mulai di root Nava. Jika membuat checkout baru, jalankan dari folder kerja Laragon.

```powershell
git clone https://github.com/theresiaherrich/Nava.git
Set-Location Nava
git fetch origin --prune
git switch integration/a2-e2e
```

Branch integrasi tersedia lokal pada workspace tempat pekerjaan ini dilakukan. Untuk checkout di komputer lain, gunakan salinan atau branch yang sudah dibagikan tim.

Di terminal MySQL atau DBeaver, buat schema kosong khusus demo. Bila nama sudah digunakan, pilih nama baru.

```sql
CREATE DATABASE nava_a2_e2e CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```powershell
composer install
composer check-platform-reqs
npm.cmd ci
npm.cmd run build
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    php artisan key:generate
}
```

Untuk satu terminal demo, gunakan variabel proses berikut. `.env` pengguna tetap dipakai sebagai sumber konfigurasi lain, termasuk `APP_KEY` bila sudah tersedia.
Jika belum ada kunci, jalankan `php artisan key:generate` pada `.env` demo yang baru. Perintah pembuatan kunci sementara tersedia di bagian akhir panduan ini.

```powershell
$env:APP_ENV = 'local'
$env:APP_URL = 'http://127.0.0.1:8000'
$env:DB_CONNECTION = 'mysql'
$env:DB_URL = ''
$env:DB_HOST = '127.0.0.1'
$env:DB_PORT = '3307'
$env:DB_DATABASE = 'nava_a2_e2e'
$env:DB_USERNAME = 'root'
$env:DB_PASSWORD = ''
$env:SESSION_DRIVER = 'database'
$env:SESSION_COOKIE = 'nava_a2_e2e_session'
$env:SESSION_DOMAIN = 'null'
$env:SESSION_SECURE_COOKIE = 'false'
$env:SESSION_SAME_SITE = 'lax'
$env:SIAKAD_RAPOR_DISK = 'local'
$env:SIAKAD_PRIVATE_STORAGE = Join-Path (Get-Location) 'storage/app/private/nava_a2_e2e'
$env:SEED_DEFAULT_PASSWORD = [Net.NetworkCredential]::new('', (Read-Host 'Sandi akun demo' -AsSecureString)).Password
php artisan config:clear
php artisan migrate --seed
php artisan migrate:status
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

Buka [halaman login](http://127.0.0.1:8000/login). Pertahankan host `127.0.0.1` pada URL dan sesi. Untuk Laragon Virtual Host, arahkan document root ke `public` dan sesuaikan URL. Pada deployment HTTPS, pakai cookie secure.

Database cabang lama dapat memakai nama tabel, primary key, dan riwayat migrasi berbeda. Jalankan instalasi integrasi pada schema kosong. Pertahankan database lama beserta data. Pemindahan data lama memerlukan pemetaan dan rekonsiliasi tersendiri dengan tim.

Untuk pengujian PHPUnit, gunakan terminal tersendiri. Konfigurasi tes menggunakan SQLite in-memory dan menyiapkan kunci sementara bila `.env.testing` belum memuat kunci.

```powershell
php artisan test
```

Untuk demo HTTP yang memakai `.env` tanpa `APP_KEY`, jalankan perintah berikut di terminal server sebelum migrasi dan menjalankan server.

```powershell
$bytes = [byte[]]::new(32)
[Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($bytes)
$env:APP_KEY = 'base64:' + [Convert]::ToBase64String($bytes)
```
