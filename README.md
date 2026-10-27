# Nava: SIAKAD Terpadu

Aplikasi web administrasi sekolah (SIAKAD Terpadu) yang dibangun dengan **Laravel** sebagai **fullstack**: backend (logika bisnis, routing, autentikasi, otorisasi) dan frontend (tampilan Blade) berada dalam satu repository dan satu aplikasi.

Aplikasi ini mencakup empat modul di atas satu basis data terpadu:

- **Presensi dan kehadiran**
- **Akademik dan nilai**, dengan fokus pada pengelolaan nilai berbasis Tujuan Pembelajaran (TP) dan ekspor rapor otomatis (Rapor STS, Rapor Semester, serta rekap nilai/DKN)
- **Kedisiplinan dan bimbingan konseling (BK)**
- **Portal informasi** untuk orang tua dan siswa

Pengguna dibagi ke dalam lima peran dengan hak akses berbeda: **Admin**, **Guru**, **Wali Kelas**, **Orang Tua**, dan **Siswa**.

> Repository ini dikembangkan sebagai proyek Capstone Project. Konteks lengkap kebutuhan dan rancangan ada di dokumen proposal.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Arsitektur](#arsitektur)
- [Struktur Direktori](#struktur-direktori)
- [Persyaratan](#persyaratan)
- [Instalasi dan Setup](#instalasi-dan-setup)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun dan Peran](#akun-dan-peran)
- [Menjalankan Pengujian](#menjalankan-pengujian)
- [Alur Kerja Git](#alur-kerja-git)
- [Troubleshooting](#troubleshooting)
- [Tim Pengembang](#tim-pengembang)

---

## Fitur Utama

| Modul | Fitur |
|---|---|
| Autentikasi dan hak akses | Login per peran, pembatasan menu dan halaman berdasarkan role |
| Data master | Siswa, guru (ASN dan GTT), kelas, mata pelajaran, pembagian mengajar, wali kelas (impor CSV/Excel) |
| Tujuan Pembelajaran | Guru menulis TP per mata pelajaran, kelas, dan semester (maksimal 10 TP per semester) |
| Input nilai per TP | Tabel menyerupai lembar kerja, dukungan tempel dari Excel, penyimpanan otomatis, nilai SAS tercatat sebagai TP terakhir |
| Periode dan penguncian | Admin membuka dan menutup periode STS/SAS per tingkat, nilai yang dikunci tidak dapat diubah guru |
| Verifikasi | Wali kelas menyetujui atau meminta revisi nilai per mata pelajaran |
| Rapor otomatis | Pembentukan Rapor STS dan Rapor Semester dari templat Blade, pratinjau, dan unduh PDF per siswa atau per kelas |
| Ekspor | Rekap nilai (DKN) ke Excel, riwayat rapor yang dapat dicetak ulang |
| Presensi | Pencatatan kehadiran harian dan rekap bulanan |
| Kedisiplinan dan BK | Pencatatan pelanggaran, poin, dan catatan bimbingan konseling |
| Portal | Halaman orang tua dan siswa untuk melihat nilai per TP, rapor digital, presensi, dan kedisiplinan |

---

## Teknologi

**Backend dan frontend (fullstack):**

- PHP dan **Laravel** (routing, controller, validasi, autentikasi, otorisasi)
- **Laravel Blade** sebagai templat antarmuka yang dirender di server
- **JavaScript** ringan untuk interaksi tabel nilai (navigasi keyboard, tempel dari Excel, simpan otomatis)
- **Vite** untuk bundling aset frontend (default Laravel)
- **Tailwind CSS** / CSS sesuai konfigurasi proyek

**Data dan pustaka pendukung:**

- **MySQL** sebagai basis data relasional
- **Eloquent ORM** dan migrasi Laravel
- **laravel-dompdf** untuk membentuk PDF rapor dari templat Blade
- **Laravel Excel** untuk ekspor rekap nilai (DKN) ke Excel
- **PHPUnit** untuk pengujian unit dan fitur

---

## Arsitektur

Aplikasi berjalan sebagai satu aplikasi Laravel dengan pola **MVC**:

```
Browser  ──HTTPS──>  Laravel
                     ├── Routes & Middleware (autentikasi, otorisasi role)
                     ├── Controllers (logika per modul)
                     ├── Services (perhitungan nilai rapor, pembentukan PDF)
                     ├── Models (Eloquent)  ──>  MySQL
                     └── Views (Blade)  ──>  HTML ke browser
```

Perhitungan nilai rapor dan pembentukan berkas PDF dilakukan di sisi server, sehingga hasil cetak konsisten dan peramban pengguna tetap ringan.

---

## Struktur Direktori

Struktur standar Laravel dengan beberapa tambahan berikut. Sesuaikan jika ada perbedaan di repository:

```
.
├── app/
│   ├── Http/Controllers/     # Controller per modul
│   ├── Http/Middleware/      # Pengecekan peran dan akses
│   ├── Models/               # Model Eloquent
│   ├── Policies/             # Aturan hak akses per resource
│   └── Services/             # Logika nilai dan rapor
├── database/
│   ├── migrations/           # Skema basis data
│   └── seeders/              # Data awal (peran, akun contoh, data master)
├── resources/
│   ├── views/                # Templat Blade (termasuk templat rapor)
│   ├── js/                   # JavaScript ringan untuk tabel nilai
│   └── css/
├── routes/
│   └── web.php               # Rute aplikasi
├── tests/                    # Pengujian PHPUnit
└── .env.example              # Contoh konfigurasi lingkungan
```

---

## Persyaratan

Pastikan perangkat sudah terpasang:

- PHP 8.2 atau lebih baru, beserta ekstensi yang umum dipakai Laravel (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `zip`)
- [Composer](https://getcomposer.org/)
- Node.js (disarankan versi LTS) dan npm
- MySQL 8.0 atau MariaDB
- Git

**Pengguna Windows:** disarankan menjalankan proyek di **WSL2** (Ubuntu) agar perintah dan perilaku PHP, Composer, dan Node.js konsisten dengan lingkungan produksi.

---

## Instalasi dan Setup

### 1. Clone repository

```bash
git clone https://github.com/theresiaherrich/Nava.git
cd Nava
```

### 2. Instal dependensi PHP

```bash
composer install
```

### 3. Instal dependensi frontend

```bash
npm install
```

### 4. Siapkan file lingkungan

```bash
cp .env.example .env
php artisan key:generate
```

Di Windows PowerShell, gunakan `copy .env.example .env` jika `cp` tidak tersedia.

### 5. Atur koneksi basis data

Buka berkas `.env` lalu sesuaikan bagian database:

```env
APP_NAME=Nava
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nava
DB_USERNAME=root
DB_PASSWORD=
```

Buat basis data kosong dengan nama yang sama seperti `DB_DATABASE`, misalnya:

```sql
CREATE DATABASE nava CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Jalankan migrasi dan data awal

```bash
php artisan migrate
php artisan db:seed
```

Perintah `db:seed` membuat peran dan akun contoh untuk pengujian lokal. Jangan jalankan seeder ini di lingkungan produksi.

### 7. Buat tautan penyimpanan (opsional, untuk berkas unggahan dan rapor)

```bash
php artisan storage:link
```

---

## Menjalankan Aplikasi

Jalankan dua proses secara bersamaan di dua terminal terpisah:

**Terminal 1: server Laravel**

```bash
php artisan serve
```

**Terminal 2: bundling frontend (mode pengembangan)**

```bash
npm run dev
```

Buka aplikasi di [http://localhost:8000](http://localhost:8000).

Untuk build aset produksi:

```bash
npm run build
```

---

## Akun dan Peran

| Peran | Akses utama |
|---|---|
| **Admin** | Data master, akun guru, pembagian mengajar, periode dan penguncian nilai, nilai ekstrakurikuler, pengaturan templat rapor |
| **Guru** | Mengelola TP, input nilai per TP, melihat rekap kelas yang diampu, presensi |
| **Wali Kelas** | Verifikasi nilai, rekap DKN kelas, cetak dan unduh rapor kelasnya, catatan kedisiplinan |
| **Orang Tua** | Portal: nilai per TP, rapor digital, presensi, kedisiplinan anak (hanya lihat) |
| **Siswa** | Portal: nilai per TP, unduh rapor, presensi, dan catatan kedisiplinan pribadi (hanya lihat) |

Akun contoh untuk setiap peran dibuat oleh seeder. Ganti kata sandi akun contoh sebelum digunakan di lingkungan mana pun selain lokal.

---

## Menjalankan Pengujian

```bash
php artisan test
```

Pengujian mencakup:

- **Unit test:** fungsi perhitungan nilai rapor, aturan penguncian, dan batas 10 TP per semester
- **Feature test:** autentikasi, hak akses per peran, alur input nilai, dan pembentukan rapor
- **Black box test:** skenario pengujian fungsional sesuai dokumen proposal

---

## Alur Kerja Git

### Aturan dasar

- Branch `main` berisi kode yang siap dipakai. Jangan langsung push ke `main`.
- Kerjakan setiap fitur atau perbaikan pada branch terpisah, misalnya `feature/input-nilai-tp` atau `fix/rapor-pdf`.
- Buka **pull request** ke `main` dan minta minimal satu anggota lain meninjau sebelum digabungkan.
- Pastikan `php artisan test` lulus sebelum membuat pull request.
- Jangan commit berkas `.env`, `vendor/`, atau `node_modules/`. Berkas ini sudah diabaikan oleh `.gitignore`.

### Menambahkan README ke repository

Setelah clone, folder `Nava` akan terbentuk. Letakkan `README.md` di folder root, yaitu folder yang sejajar dengan `composer.json` dan `artisan`. Jika repository sudah punya `README.md`, timpa berkas lama dengan yang baru.

```bash
git clone https://github.com/theresiaherrich/Nava.git
cd Nava

# salin README.md ke folder Nava, lalu cek statusnya
git status

git checkout -b docs/readme
git add README.md
git commit -m "docs: tambah README proyek dengan setup dan panduan git"
git push -u origin docs/readme
```

Setelah itu, buka GitHub, pilih **Compare & pull request**, lalu gabungkan ke `main` setelah ditinjau.

### Perintah git harian

```bash
# ambil perubahan terbaru dari GitHub sebelum mulai bekerja
git checkout main
git pull origin main

# buat branch baru untuk pekerjaanmu
git checkout -b feature/nama-fitur

# lihat berkas yang berubah
git status
git diff

# masukkan perubahan ke staging, lalu commit
git add nama-berkas.php        # atau git add . untuk semua berkas
git commit -m "feat(nilai): tambah input nilai per TP"

# kirim branch ke GitHub
git push -u origin feature/nama-fitur

# setelah branch lain digabung, perbarui branch kerjamu dari main
git checkout main
git pull origin main
git checkout feature/nama-fitur
git merge main                 # atau: git rebase main
```

Penjelasan singkat:

- **`git clone`**: mengunduh seluruh repository ke komputer.
- **`git pull`**: mengambil perubahan dari GitHub dan menggabungkannya ke branch lokal. Lakukan ini setiap kali mulai bekerja.
- **`git add`**: memilih berkas yang akan di-commit (staging).
- **`git commit`**: menyimpan snapshot perubahan beserta pesan penjelasan.
- **`git push`**: mengirim commit lokal ke GitHub.

### Cara menulis commit yang baik

Commit yang baik adalah commit yang **kecil**, **fokus pada satu tujuan**, dan **pesannya menjelaskan apa yang berubah dan mengapa**.

Gunakan format berikut (Conventional Commits):

```
<tipe>(<lingkup opsional>): <deskripsi singkat>

<penjelasan opsional, jika perubahan perlu konteks>
```

Tipe yang disarankan:

| Tipe | Kapan dipakai | Contoh |
|---|---|---|
| `feat` | Menambah fitur | `feat(rapor): tambah ekspor PDF per kelas` |
| `fix` | Memperbaiki bug | `fix(nilai): cegah nilai TP tersimpan saat periode terkunci` |
| `docs` | Mengubah dokumentasi | `docs: perbarui langkah setup di README` |
| `refactor` | Mengubah struktur kode tanpa mengubah perilaku | `refactor(services): pisahkan perhitungan rata-rata TP` |
| `test` | Menambah atau memperbaiki pengujian | `test(auth): tambah uji akses ditolak untuk role siswa` |
| `style` | Format kode, spasi, titik koma (tanpa mengubah logika) | `style: rapikan indentasi view presensi` |
| `chore` | Pekerjaan rutin, konfigurasi, dependensi | `chore: perbarui laravel-dompdf ke versi 3.1` |

Aturan praktis:

1. **Gunakan bahasa imperatif** dan kalimat pendek: "tambah", "perbaiki", "hapus", bukan "menambahkan" atau "sudah ditambahkan".
2. **Batasi judul sekitar 50 sampai 72 karakter**, tanpa titik di akhir.
3. **Satu commit, satu tujuan.** Jika pesanmu mengandung kata "dan", pertimbangkan untuk memecahnya menjadi dua commit.
4. **Jelaskan alasannya di badan commit** bila perubahan tidak jelas dari kode saja.
5. **Jangan commit kode yang rusak.** Jalankan `php artisan test` sebelum commit.
6. **Jangan commit berkas rahasia** seperti `.env` atau kata sandi.

Contoh perbandingan:

| Kurang baik | Lebih baik |
|---|---|
| `update` | `fix(presensi): perbaiki rekap bulanan yang menghitung hari libur` |
| `fix bug` | `fix(auth): arahkan siswa ke dashboard setelah login` |
| `selesai semua` | `feat(nilai): tambah verifikasi nilai oleh wali kelas` |
| `coba-coba rapor` | `refactor(rapor): gunakan templat Blade terpisah untuk STS dan Semester` |

Contoh commit dengan badan penjelasan:

```bash
git commit -m "fix(nilai): hitung rata-rata TP dengan benar

Sebelumnya nilai SAS ikut dihitung dua kali karena TP terakhir
sudah tercatat sebagai SAS. Sekarang nilai SAS hanya dihitung
sebagai TP terakhir."
```

### Ringkasan alur

1. `git pull origin main` untuk mengambil kode terbaru.
2. `git checkout -b feature/nama-fitur` untuk membuat branch.
3. Kerjakan fitur, lalu jalankan `php artisan test`.
4. `git add` dan `git commit` dengan pesan yang jelas.
5. `git push -u origin feature/nama-fitur`.
6. Buka pull request di GitHub dan minta peninjauan.

---

## Troubleshooting

**`No application encryption key has been specified`**
Jalankan `php artisan key:generate`.

**`SQLSTATE[HY000] [1049] Unknown database`**
Basis data belum dibuat. Buat basis data sesuai `DB_DATABASE` di `.env`, lalu jalankan `php artisan migrate`.

**Halaman tampil tanpa gaya (CSS hilang)**
Jalankan `npm run dev` atau `npm run build`, lalu muat ulang halaman.

**Perubahan konfigurasi `.env` tidak terbaca**
Jalankan `php artisan config:clear`.

**Gambar atau berkas rapor tidak tampil**
Pastikan `php artisan storage:link` sudah dijalankan.

**Ekstensi PHP tidak ditemukan saat `composer install`**
Aktifkan ekstensi yang disebutkan pada bagian [Persyaratan](#persyaratan) di `php.ini`.