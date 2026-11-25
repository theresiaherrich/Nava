# Testing Lokal SIAKAD (semua peran)

Panduan ini untuk mencoba web SIAKAD di laptop dengan satu akun demo untuk setiap peran. Akun demo hanya dibuat saat `APP_ENV=local`.

## 1. Menjalankan aplikasi

1. Buka **Laragon**, lalu klik **Start All** (MySQL harus berjalan).
2. Pastikan `.env` berisi minimal:

   ```dotenv
   APP_ENV=local
   APP_URL=http://localhost:8000
   DB_DATABASE=siakad_a2          # database hasil `php artisan migrate`, bukan database rancangan
   NAVA_DEMO_PASSWORD=            # isi sendiri, min. 12 karakter; dipakai SEMUA akun demo
   ```

   Pertama kali saja: `composer install`, `npm install`, `php artisan key:generate`, lalu `php artisan migrate --seed`.

3. Jalankan dua terminal:

   ```bash
   php artisan serve      # http://localhost:8000
   npm run dev            # aset Vite (CSS/JS); atau sekali saja: npm run build
   ```

4. Buka **http://localhost:8000/login**.

## 2. Membuat akun demo

Isi `NAVA_DEMO_PASSWORD` di `.env`, lalu jalankan:

```bash
php artisan db:seed --class=DemoAccountSeeder
```

- Aman dijalankan berulang. Sandi, status aktif, dan peran setiap akun dikembalikan ke kondisi demo.
- Seeder hanya berjalan di `APP_ENV=local`. Di lingkungan lain seeder dilewati.
- `php artisan db:seed` (tanpa `--class`) juga memanggil seeder ini di local, setelah data contoh (`DevSeeder`: kelas, guru, siswa, nilai).
- **Jangan** memakai `php artisan migrate:fresh` di database yang datanya ingin disimpan, karena perintah itu menghapus semua tabel.

## 3. Akun demo

Semua akun memakai sandi yang sama, yaitu nilai `NAVA_DEMO_PASSWORD`. Semua akun aktif dan tidak wajib ganti sandi.

| Username | Peran | Halaman pertama setelah login |
|---|---|---|
| `superadmin` | Super Admin | `/superadmin/dashboard` |
| `operator` | Operator (`ADMIN`) | `/operator/dashboard` |
| `kurikulum` | Admin Kurikulum | `/kurikulum/dashboard` |
| `kesiswaan` | Admin Kesiswaan | `/kesiswaan/dashboard` |
| `guru_mapel` | Guru Mapel | `/guru/dashboard` (ikut mengampu Matematika X bila data contoh ada) |
| `wali_kelas` | Wali Kelas (+ Guru Mapel) | `/wali-kelas/dashboard`, wali kelas **X-B** |
| `guru_bk` | Guru BK / Budaya Positif | `/bk/dashboard` |
| `siswa_demo` | Siswa | `/siswa/beranda`, anggota kelas **X-B** (NIS `DEMO-001`) |
| `ortu_demo` | Orang Tua | `/orang-tua/beranda`, orang tua `siswa_demo` |

Catatan:
- Setelah login, `/dashboard` mengarahkan ke halaman pertama peran aktif.
- Akun dengan dua peran (`wali_kelas`) masuk sebagai Wali Kelas. Untuk pindah ke peran Guru Mapel, klik **Masuk sebagai Guru Mapel** di menu Akademik sidebar.
- `wali_kelas` menggantikan Sari sebagai wali X-B. Akun data contoh lain (`budi`, `sari`, `andi`, dst.) tetap ada dengan sandi `SEED_DEFAULT_PASSWORD`, atau `NAVA_DEMO_PASSWORD` bila kosong.
- Tidak ada pendaftaran akun mandiri. Akun dibuat oleh Superadmin/Operator. "Lupa kata sandi" juga ditangani Operator.

## 4. URL halaman per peran

Diambil dari `php artisan route:list`. Halaman yang perannya tidak cocok akan ditolak (403).

### A2 – Akademik

| Peran | URL |
|---|---|
| Super Admin | `/superadmin/dashboard` |
| Operator | `/operator/dashboard`, `/operator/data-master` |
| Admin Kurikulum | `/kurikulum/dashboard`, `/kurikulum/data-akademik`, `/kurikulum/periode-penguncian`, `/kurikulum/rekap-nilai`, `/kurikulum/cetak-rapor` |
| Admin Kesiswaan | `/kesiswaan/dashboard`, `/kesiswaan/ekskul` |
| Guru Mapel | `/guru/dashboard`, `/guru/tp`, `/guru/input-nilai` |
| Wali Kelas | `/wali-kelas/dashboard`, `/wali-kelas/verifikasi-nilai`, `/wali-kelas/rekap-nilai`, `/wali-kelas/cetak-rapor` |
| Guru BK | `/bk/dashboard` |
| Siswa | `/siswa/beranda`, `/siswa/nilai`, `/siswa/nilai/{rombel}`, `/siswa/rapor` |
| Orang Tua | `/orang-tua/beranda`, `/orang-tua/nilai`, `/orang-tua/nilai/{rombel}`, `/orang-tua/rapor` |
| Semua peran | `/ganti-sandi` |

### A1 – Presensi (menu "Presensi" di sidebar)

| Peran | URL |
|---|---|
| Super Admin / Operator | `/presensi/admin`, `/presensi/admin/riwayat` |
| Admin Kurikulum | `/presensi/kurikulum` |
| Guru Mapel / Wali Kelas | `/presensi/guru`, `/presensi/kelola` |
| Guru BK | `/presensi/budaya-positif`, `/presensi/budaya-positif/admin-presensi` |
| Siswa | `/presensi/siswa`, `/presensi/siswa/qr`, `/presensi/siswa/riwayat` |

## 5. Uji otomatis

```bash
php artisan test --filter=DemoAccountTest
```

Tes ini login dengan setiap akun demo lewat `/api/auth/login`. Untuk setiap akun, tes memastikan `/dashboard` diarahkan ke halaman yang benar, menu sidebar sesuai peran, dan semua halaman menu serta API dashboard mengembalikan 200 (tanpa 403/404).

## 6. Masalah umum

| Gejala | Penyebab / solusi |
|---|---|
| "Terjadi gangguan pada server" saat login | Database belum dimigrasi atau `DB_DATABASE` salah. Detail ada di `storage/logs/laravel.log`. |
| `Isi NAVA_DEMO_PASSWORD di .env` | Isi sandi minimal 12 karakter, lalu jalankan `php artisan config:clear`. |
| "Nama pengguna atau kata sandi salah." | Sandi tidak sama dengan `NAVA_DEMO_PASSWORD`. Jalankan ulang `DemoAccountSeeder` setelah mengganti sandi. |
| Tampilan tanpa CSS | `npm run dev` belum berjalan (atau jalankan `npm run build`). |
