# Panduan backend SIAKAD A2 — Wildatus Sakinah

Paket ini melanjutkan kerangka Laravel dari `Nava-main (1).zip` dan mengacu pada ERD `siakad_a2_rancangan.png`. Urutan belajar: setup → database → register/login → role → data master → penempatan kelas → testing → fitur nilai dan rapor.

**Cakupan kode yang sudah dibuat:** register, login email/username, logout, akun saya, persetujuan akun oleh admin, multi-role, pengaitan akun dengan profil siswa/guru, audit aktivitas, data sekolah, tahun ajaran, semester, kelas, mata pelajaran, siswa, guru, dan penempatan siswa ke kelas.

**Belum diimplementasikan:** rombel mapel, penugasan guru/wali kelas, TP, KKTP, input dan verifikasi nilai, wali siswa, ekstrakurikuler, rapor/PDF, impor Excel, lupa password, verifikasi email. Rencana pembangunannya ada di `03_ROADMAP_ERD.md`. Jadi ini fondasi backend tahap pertama, bukan seluruh aplikasi SIAKAD selesai.

## 1. Pahami alat yang digunakan

| Alat | Tugasnya |
|---|---|
| Laragon | Menjalankan PHP dan server database di Windows |
| MySQL | Menyimpan data sebenarnya |
| DBeaver | Menghubungkan dan melihat/memeriksa MySQL; bukan pengganti MySQL |
| Composer | Mengunduh dependency PHP/Laravel |
| Laravel | Menangani route, validasi, login, aturan akses, dan database |
| Postman | Mengirim request tanpa perlu membuat halaman frontend |
| VS Code | Membaca dan mengedit kode |

Paket sumbermu memakai Laravel `^13.17` dengan PHP `^8.3`. Gunakan PHP **8.3 atau yang kompatibel dengan composer.lock**, misalnya PHP 8.5. Jalankan `composer check-platform-reqs` untuk memastikan paket terpasang cocok. Pengujian paket ini dilakukan dengan PHP 8.5.12. Jangan menurunkan proyek ke Laravel 10/11 hanya agar cocok dengan tutorial lama.

Arsitektur tetap Laravel MVC: model mengelola entitas, controller menerima request, dan view nanti dikerjakan frontend. Karena yang diminta backend, controller saat ini mengembalikan JSON. URL memakai awalan `/api`, tetapi route sengaja ditulis di `routes/web.php` untuk menggunakan session cookie dan proteksi CSRF. Ini bukan bearer-token API. Jangan memindahkannya ke `api.php` tanpa mendesain ulang autentikasinya.

## 2. Siapkan Laragon

1. Pasang/buka Laragon di Windows. Gunakan instalasi dan lisensi yang sesuai perangkatmu.
2. Pastikan tersedia PHP 8.3+ dan MySQL. Bila yang dipakai MariaDB, pilih driver MariaDB di DBeaver dan jalankan pemeriksaan migrasi sendiri; paket ini belum diuji pada MariaDB.
3. Pilih versi PHP melalui menu Laragon jika ada beberapa versi. Nama menu bisa berbeda antarversi.
4. Klik **Start All**. Pastikan MySQL benar-benar berjalan.
5. Buka **Terminal** dari Laragon supaya executable PHP yang dipakai sesuai Laragon.
6. Jalankan satu per satu:

```bat
php -v
composer -V
where php
php --ini
php -m
```

Hasil yang diharapkan: versi PHP benar, Composer bisa dijalankan, dan path PHP menunjuk ke instalasi yang kamu gunakan. Jika PHP tidak dikenali atau masih versi lama, pilih PHP yang tepat, tutup terminal lama, lalu buka terminal Laragon lagi.

Ekstensi yang perlu diperiksa antara lain `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `curl`, `dom`, `xml`, `gd`, dan `zip`. SQLite dipakai untuk tes otomatis terisolasi; MySQL dipakai aplikasi lokalmu. Dependency Excel/PDF yang sudah ada di ZIP asal juga membutuhkan beberapa ekstensi. Composer akan menyebutkan yang kurang. Aktifkan pada `php.ini` yang ditampilkan `php --ini`, kemudian restart layanan/terminal sesuai kebutuhan.

## 3. Letakkan proyek

Ekstrak paket sehingga lokasi `artisan` adalah:

```text
C:\laragon\www\siakad-a2-backend\artisan
```

Buka folder `siakad-a2-backend` di VS Code. Jangan sampai hasil ekstrak menjadi folder bertingkat yang membuat terminal berada satu tingkat di atas `artisan`.

Untuk awal, gunakan salinan paket ini sebagai proyek lokal baru. Jika menggabungkan ke repository tim yang sudah berubah, buat branch dan bandingkan file terlebih dahulu. Paket ini berasal dari ZIP yang dilampirkan, bukan dari HEAD terbaru repository tim.

Di terminal Laragon berbasis Command Prompt:

```bat
cd /d C:\laragon\www\siakad-a2-backend
composer install
```

Jika terminalmu PowerShell, gunakan:

```powershell
Set-Location C:\laragon\www\siakad-a2-backend
composer install
```

**Alasannya:** ZIP tidak menyertakan folder `vendor`. `composer install` mengisi folder itu sesuai versi pada `composer.lock`. Hindari `composer update` untuk setup biasa karena dapat mengubah versi dependency tim. Tidak perlu `npm install`, Breeze, ataupun `php artisan install:api` untuk paket backend ini.

## 4. Hubungkan DBeaver ke MySQL

1. Buka DBeaver → **Database → New Database Connection**.
2. Pilih **MySQL**.
3. Isi:

| Field | Nilai |
|---|---|
| Host | `127.0.0.1` |
| Port | `3306`, atau port MySQL pada Laragonmu |
| Database | `mysql` sementara, atau kosong jika driver mengizinkan |
| Username | Akun MySQL lokalmu, biasanya `root` |
| Password | Password MySQL lokalmu; kosong hanya jika memang belum disetel |

4. Klik **Test Connection**. Jika diminta mengunduh driver, izinkan.
5. Setelah berhasil, buka SQL Editor dan jalankan:

```sql
CREATE DATABASE IF NOT EXISTS siakad_a2
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

6. Ubah koneksi agar database aktifnya `siakad_a2`; reconnect/refresh.

**Alasannya:** MySQL menyimpan datanya, sedangkan DBeaver hanya alat untuk mengaksesnya. Cukup buat database kosong di DBeaver. Struktur tabel akan dibuat migration Laravel sehingga seluruh anggota tim memiliki struktur yang konsisten.

Jika Test Connection gagal, cek MySQL hidup, port benar, dan username/password benar. Jangan mengubah file Laravel untuk memperbaiki koneksi DBeaver yang salah.

## 5. Atur `.env`

Di terminal pada folder proyek:

```bat
copy .env.example .env
php artisan key:generate
```

`copy` juga tersedia sebagai alias di PowerShell. Alternatif PowerShell eksplisit: `Copy-Item .env.example .env`.

Buka `.env`, sesuaikan bagian ini:

```dotenv
APP_NAME="SIAKAD A2"
APP_ENV=local
APP_KEY=nilai_otomatis_dari_key_generate
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siakad_a2
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Jangan mengganti `APP_KEY` yang sudah diisi oleh perintah tadi dengan teks contoh. Isi `DB_PASSWORD` dengan password sebenarnya jika ada. Kredensial database Laravel harus sama dengan koneksi DBeaver. Gunakan `false` untuk secure cookie hanya karena percobaan lokal ini memakai HTTP; pada deployment HTTPS gunakan `true`.

Jalankan:

```bat
php artisan config:clear
composer check-platform-reqs
```

**Alasannya:** `.env` menyimpan konfigurasi lokal dan `APP_KEY` digunakan untuk enkripsi aplikasi, termasuk cookie. File `.env` tidak untuk dikirim ke repository. Jangan mengganti key berulang-ulang setelah aplikasi sudah dipakai.

## 6. Buat tabel dan role

```bat
php artisan migrate --seed
php artisan migrate:status
```

Hasil yang diharapkan: seluruh migration berstatus **Ran**. Refresh daftar tabel di DBeaver. Akan ada tabel infrastruktur Laravel dan tabel domain berikut:

- `users`, `roles`, `role_user`, `log_aktivitas`;
- `sekolah`, `tahun_ajaran`, `semester`, `kelas`, `mapel`;
- `siswa`, `guru`, `siswa_kelas`.

`RoleSeeder` membuat `admin`, `kurikulum`, `guru`, `wali_kelas`, `siswa`, `wali_siswa`, `kepala_sekolah`. Nama role ini asumsi implementasi untuk diselaraskan dengan tim; ERD yang kamu kirim belum menetapkan daftar nilainya. Satu user boleh memiliki beberapa role.

`php artisan db:seed --class=RoleSeeder` aman dijalankan ulang: role tidak digandakan. Seeder tidak membuat password admin bawaan.

**Jangan gunakan `migrate:fresh` pada database berisi data yang ingin disimpan**, karena perintah itu menghapus semua tabel. Untuk perubahan berikutnya, buat migration baru dan jalankan `migrate`.

Jika diterapkan ke database yang sudah punya user bawaan, migration memberi username `user_<id>` dan status nonaktif. Admin baru harus dibuat pada langkah berikut. Ini tidak otomatis menebak role akun lama.

## 7. Buat admin pertama

```bat
php artisan siakad:create-admin
```

Isi nama, username, email, password, dan konfirmasi. Password minimal 12 karakter dengan huruf besar/kecil, angka, simbol; maksimal 72 karakter. Saat diketik, password disembunyikan.

**Alasannya:** kalau semua akun hanya bisa dibuat melalui register lalu harus disetujui, siapa yang menyetujui admin pertama? Perintah terminal ini menyelesaikannya. Perintah hanya membuat akun baru; username/email yang sudah dipakai akan ditolak, bukan diam-diam dinaikkan menjadi admin.

Catat kredensial admin di tempat pribadi untuk testing. Tidak ada kombinasi `admin/admin123` di dalam source code.

## 8. Jalankan backend

```bat
php artisan serve --host=127.0.0.1 --port=8000
```

Biarkan terminal ini terbuka. Buka terminal kedua untuk menjalankan perintah Artisan lain.

Buka `http://127.0.0.1:8000/`. Hasilnya JSON dengan `application: SIAKAD A2` dan `status: ok`.

Kita memakai `artisan serve` agar alamat pengujian seragam; MySQL tetap berasal dari Laragon. Gunakan host `127.0.0.1` secara konsisten di `.env`, Postman, dan browser. Cookie `localhost` berbeda dari cookie `127.0.0.1`. Apache Laragon tidak wajib dipakai untuk jalur ini. Jika nanti memakai domain `.test`, document root harus folder `public`, lalu sesuaikan seluruh URL dan cookie pengujiannya.

## 9. Uji register–login dengan Postman

Import `docs/SIAKAD_A2.postman_collection.json` melalui **Import** di Postman. Pada variables collection, isi `admin_login`, `admin_password`, serta `applicant_password` dengan nilai lokalmu. Password pendaftar harus mengikuti aturan di atas. Variabel `base_url` sudah `http://127.0.0.1:8000`.

Collection memiliki empat folder berurutan. Jalankan request satu per satu untuk belajar. Script collection menyimpan CSRF baru dari respons login dan menyimpan ID hasil create. Cookie harus tetap aktif di Postman. Jangan bagikan collection hasil ekspor jika variabelnya sudah berisi password pribadi.

### 9.1 Ambil CSRF sebelum request yang mengubah data

```http
GET /api/csrf-token
Accept: application/json
```

Respons 200:

```json
{"csrf_token":"token_dari_server"}
```

Postman menyimpan cookie session dari respons ini. Untuk POST/PUT/PATCH berikutnya gunakan header:

```http
Accept: application/json
Content-Type: application/json
X-CSRF-TOKEN: {{csrf_token}}
```

**Alasannya:** cookie mengenali session, sedangkan token CSRF memastikan request perubahan data berasal dari session yang sesuai. Keduanya harus dikirim. Tidak ada header `Authorization: Bearer` untuk paket ini.

### 9.2 Register calon pengguna

```http
POST /api/register
```

Body → raw → JSON:

```json
{
  "name": "Wilda Demo",
  "username": "wilda_demo",
  "email": "wilda.demo@example.test",
  "password": "GantiDenganPassword12!",
  "password_confirmation": "GantiDenganPassword12!"
}
```

Respons 201 berisi pesan menunggu persetujuan dan `data.id`. Simpan ID sebagai `applicant_id` jika menguji manual. Jangan mengirim `role`, `roles`, `role_id`, atau `is_active` saat register; akan ditolak 422.

Di DBeaver, cek:

```sql
SELECT id, name, username, email, is_active FROM users;
SELECT user_id, role_id FROM role_user;
```

Akun pendaftar baru memiliki `is_active = 0` dan belum punya role. Tidak otomatis dibuatkan baris siswa/guru. Password di tabel user adalah hash, bukan teks asli.

### 9.3 Coba login sebelum disetujui

```http
POST /api/login
```

```json
{"login":"wilda_demo","password":"GantiDenganPassword12!"}
```

Hasil yang benar: **401**. Ini memang aturan bisnis, bukan error instalasi. Respons sengaja tidak mengungkap secara spesifik apakah email ada atau password yang salah.

### 9.4 Login sebagai admin

Gunakan endpoint login yang sama dan kredensial dari `siakad:create-admin`:

```json
{"login":"username_adminmu","password":"password_adminmu"}
```

Hasil 200 berisi data akun, roles, dan `csrf_token` baru. Login mengganti session ID dan token CSRF; gunakan token baru untuk request selanjutnya. Collection menangani pergantian ini secara otomatis.

```http
GET /api/me
GET /api/admin/users?is_active=0
```

Hasil: akun admin saat ini dan daftar akun menunggu persetujuan. Nilai `is_active` pada query gunakan `0`/`1`, bukan teks `false`/`true`.

### 9.5 Setujui akun pendaftar

```http
PATCH /api/admin/users/{{applicant_id}}/access
```

```json
{
  "roles": ["siswa"],
  "is_active": true,
  "alasan": "Identitas sudah diverifikasi oleh admin sekolah"
}
```

Respons 200. Untuk guru yang sekaligus wali kelas, admin dapat memilih `roles: ["guru", "wali_kelas"]`. Admin tidak boleh mengubah akses akun dirinya sendiri lewat endpoint ini. Perubahan akses tercatat pada `log_aktivitas`.

Persetujuan ini merupakan verifikasi manual admin, bukan verifikasi email otomatis. Akun siswa pada tahap fondasi ini hanya dapat login dan membaca `/api/me`; akses data akademik milik sendiri baru dibuat bersama modul nilai/rapor.

### 9.6 Logout, lalu login sebagai siswa

```http
POST /api/logout
```

Setelah logout, ambil CSRF lagi dengan `GET /api/csrf-token`, kemudian login memakai pendaftar tadi. Hasilnya sekarang 200.

- `GET /api/me` → 200.
- `GET /api/admin/users` → 403.
- `GET /api/siswa` → 403.

**Alasannya:** bisa login tidak berarti boleh membuka semua data. Tabel siswa berisi data seluruh siswa; role siswa tidak boleh membaca daftar ini.

## 10. Uji fitur data master

Logout akun siswa → ambil CSRF baru → login admin kembali. Admin dan kurikulum dapat mengelola data master. Collection menyediakan folder alur data ini.

Urutan create penting karena ada foreign key. Contoh ID berikut hanya ilustrasi; gunakan ID yang benar dari responsmu.

### 10.1 Identitas sekolah

`PUT /api/sekolah`

```json
{"npsn":"12345678","nama":"SMA Demo","alamat":"Alamat contoh","nama_kepala_sekolah":"Kepala Sekolah Demo"}
```

Aplikasi diasumsikan untuk satu mitra; endpoint ini mengisi/memperbarui baris sekolah ID 1. Jangan gunakan NPSN contoh sebagai data asli.

### 10.2 Tahun ajaran

`POST /api/tahun-ajaran`

```json
{"nama":"2026/2027","aktif":true}
```

`2026/2028` ditolak. Simpan `data.id` sebagai `tahun_ajaran_id`.

### 10.3 Semester

`POST /api/semester`

```json
{"tahun_ajaran_id":1,"nama":"ganjil","aktif":true}
```

Nilai nama: `ganjil` atau `genap`. Semester dengan tahun ajaran dan nama yang sama tidak boleh ganda. `aktif` pada fondasi ini adalah status record; belum ada aturan global yang memaksa hanya satu semester aktif.

### 10.4 Kelas

`POST /api/kelas`

```json
{"semester_id":1,"nama":"X-1","tingkat":10,"fase":"E"}
```

Untuk SMA, kelas X menggunakan fase E dan XI/XII fase F. Nama kelas unik per semester. Pada semester berikutnya, buat record kelas baru; jangan mengganti semester pada kelas lama.

### 10.5 Mata pelajaran

`POST /api/mapel`

```json
{"kode":"IF","nama":"Informatika","aktif":true}
```

`kode` harus unik.

### 10.6 Profil siswa

`POST /api/siswa`

```json
{"nis":"S001","nisn":"0012345678","nama":"Wilda Demo","agama":null,"aktif":true}
```

NIS/NISN ditulis sebagai string, agar nol di depan tidak hilang. NIS wajib; NISN opsional, tetapi bila diisi harus 10 digit dan unik. `user_id` tidak boleh diisi melalui endpoint master.

### 10.7 Hubungkan profil siswa dengan akun login

Hanya admin:

`PUT /api/admin/users/{{applicant_id}}/profile`

```json
{"jenis":"siswa","profil_id":1,"alasan":"Identitas akun cocok dengan data induk siswa"}
```

Gunakan ID siswa dari langkah sebelumnya. Akun harus sudah memiliki role `siswa`. Profil tidak boleh direbut dari user lain. Satu akun maksimal terhubung dengan satu profil siswa; profil guru mempunyai batas yang sama.

Jika ada tautan yang salah, jangan menukar `user_id` sembarangan. Alur koreksi tautan dengan verifikasi identitas perlu dirancang dan diuji sebagai fitur admin berikutnya; endpoint awal ini sengaja menolak penimpaan tautan yang sudah ada.

### 10.8 Profil guru

`POST /api/guru`

```json
{"nama":"Guru Demo","nip":null,"status_kepegawaian":"Honorer","aktif":true}
```

NIP boleh kosong untuk guru yang tidak memiliki NIP. Jika guru perlu login, daftarkan akun, setujui dengan role `guru`, lalu hubungkan profil menggunakan endpoint admin dengan `jenis: guru`.

### 10.9 Masukkan siswa ke kelas

`POST /api/siswa-kelas`

```json
{"siswa_id":1,"kelas_id":1}
```

Server mengambil `semester_id` dari kelas. Klien dilarang mengirim semester sendiri. Siswa nonaktif ditolak dan satu siswa hanya boleh masuk satu kelas pada satu semester. Coba kirim dua kali: request kedua harus 422. Constraint database juga melindungi konsistensinya.

### 10.10 Baca, ubah, dan arsipkan

Contoh:

```http
GET /api/siswa
GET /api/siswa?q=Wilda
GET /api/siswa/1
GET /api/siswa-kelas?kelas_id=1
GET /api/mapel?page=2
PUT /api/mapel/1
```

Body PUT berisi seluruh field wajib, bukan hanya yang berubah. Untuk mengarsipkan mapel, kirim `kode`, `nama`, dan `aktif: false`. Tidak tersedia DELETE master di paket ini; relasi akademik tidak boleh hilang begitu saja. Kelas tidak memiliki kolom aktif di ERD. Mutasi kelas/pindah siswa belum dibuat; jangan menghapus riwayat agar pindah kelas terlihat berhasil.

Daftar master dipaginasi 20 data. Baca `data`, `current_page`, `last_page`, dan `total` dari respons. Detail lengkap ada di `02_KONTRAK_API.md`.

## 11. Jalankan testing otomatis

Pada terminal kedua di folder proyek:

```bat
php artisan config:clear
php artisan test
```

`phpunit.xml` mengatur database `sqlite` dengan `:memory:`. Artinya tes membuat database sementara, bukan memakai `siakad_a2` di MySQL. Pastikan `pdo_sqlite` aktif. Jangan mengubah konfigurasi test menjadi database aplikasi yang berisi data nyata karena tes memakai `RefreshDatabase`.

Tes mencakup hash password, status pending, penolakan eskalasi role, login email/username, logout, login gagal, pembatasan login, 401/403, persetujuan admin, multi-role, audit, duplikat master, fase kelas, konsistensi semester, penempatan siswa, pengaitan profil, dan pembuatan admin.

**CSRF tidak dibuktikan hanya dengan feature test Laravel**, karena middleware CSRF biasanya dilewati dalam lingkungan test. Gunakan Postman untuk memastikan POST tanpa token ditolak. Paket ini juga telah diuji melalui server HTTP lokal untuk skenario cookie, rotasi token saat login, token lama ditolak, dan logout. Hasil aktual ada di `04_HASIL_PENGUJIAN.md`.

Untuk cek di MySQL lokal:

1. Pastikan `.env` menunjuk `siakad_a2`, bukan database lain.
2. Jalankan migration, lalu flow Postman register → approve → login → master → penempatan.
3. Refresh data di DBeaver setelah setiap langkah utama.
4. Coba input invalid dan duplikat, pastikan respons 422/409 serta tidak ada record tambahan.
5. Gunakan query verifikasi di dokumen hasil pengujian.

## 12. Cara membaca dan membangun kode ini bertahap

Kalau ingin belajar dengan mengetik sendiri, baca file dalam urutan berikut. **Jangan menjalankan generator lagi di paket siap pakai**, karena file berikut sudah tersedia.

| Urutan | File | Hal yang dipelajari |
|---|---|---|
| 1 | `database/migrations/2026_10_30_000001_add_siakad_access_tables.php` | Kolom akun, role, pivot, audit |
| 2 | `app/Models/User.php` dan `Role.php` | Password hash dan relasi many-to-many |
| 3 | `database/seeders/RoleSeeder.php` | Mengisi role awal tanpa duplikat |
| 4 | `app/Console/Commands/CreateAdmin.php` | Bootstrap admin tanpa password bawaan |
| 5 | `app/Http/Requests/RegisterRequest.php` | Validasi dan pelarangan pilihan role |
| 6 | `app/Http/Requests/LoginRequest.php` | Identitas login dinormalisasi |
| 7 | `app/Http/Controllers/AuthController.php` | Register, attempt login, regenerate session, logout |
| 8 | `app/Http/Middleware/EnsureActive.php`, `RequireRole.php` | Akun aktif dan otorisasi |
| 9 | `bootstrap/app.php`, `routes/web.php` | Alias middleware dan endpoint |
| 10 | `app/Http/Controllers/UserAccessController.php` | Persetujuan dan pengaitan profil oleh admin |
| 11 | Migration akademik, model, request, controller master | Menambah fitur di atas auth |
| 12 | `SiswaKelasController.php` | Aturan lintas tabel dan transaksi |
| 13 | `tests/Feature/SiakadTest.php` | Membuktikan perilaku yang diharapkan |

Contoh perintah generator **untuk modul baru**, misalnya rombel pada tahap selanjutnya:

```bat
php artisan make:model Rombel -m
php artisan make:request StoreRombelRequest
php artisan make:controller RombelController
php artisan make:policy RombelPolicy --model=Rombel
php artisan make:test RombelTest
```

Urutannya: tulis aturan bisnis → migration → model/relasi → request validation → policy → controller/service → route → test. `make:model` hanya menghasilkan kerangka; hubungan, aturan, dan otorisasi tetap harus diisi.

## 13. Error yang paling mungkin muncul

| Gejala | Penyebab umum | Tindakan |
|---|---|---|
| `Could not open input file: artisan` | Terminal bukan di root proyek | Masuk folder yang berisi `artisan` |
| `vendor/autoload.php` tidak ada | Dependency belum terpasang | `composer install` |
| Versi PHP/extension tidak cocok | PHP CLI berbeda dari versi yang dibutuhkan | `where php`, `php -v`, `php --ini`; sesuaikan Laragon |
| `Access denied for user` | Username/password DB salah | Samakan `.env` dan DBeaver, `config:clear` |
| `Connection refused` | MySQL mati atau salah port | Start MySQL, periksa port |
| `Unknown database` | Database belum dibuat | Jalankan CREATE DATABASE di DBeaver |
| Tabel `sessions`/`cache` tidak ada | Migration belum dijalankan | `php artisan migrate --seed` |
| 419 | CSRF kosong/kedaluwarsa atau cookie beda host | GET CSRF lagi, gunakan cookie yang sama, perbarui token setelah login |
| 401 | Belum login, salah kredensial, atau akun belum disetujui | Cek flow persetujuan, login ulang |
| 403 | Role tidak punya akses atau akun dinonaktifkan | Cek role/status; jangan menghapus middleware |
| 422 | Input tidak lolos validasi | Baca object `errors` di respons |
| 409 | Konflik/duplikat pada constraint atau profil sudah tertaut | Periksa record yang sudah ada |
| 429 | Terlalu banyak percobaan | Tunggu jendela rate limit; jangan menonaktifkannya |
| `could not find driver` saat test | `pdo_sqlite` belum aktif | Aktifkan SQLite di PHP CLI |
| Perubahan `.env` belum terbaca | Konfigurasi tercache | `php artisan config:clear` |

## 14. Cara kerja dengan frontend dan tim

Kirim ke frontend kontrak endpoint, contoh request, status error, dan kebutuhan cookie/CSRF. Untuk MVC satu aplikasi, view Blade dapat memakai `@csrf`; ketika login/logout, view perlu memuat ulang token. Jika menggunakan fetch dari origin yang sama, kirim cookie session dan header CSRF yang benar.

Jika frontend ternyata aplikasi React/Vue pada domain/port terpisah, sepakati arsitekturnya lebih dahulu. Paket saat ini mengasumsikan **satu origin**; belum menyediakan konfigurasi CORS/stateful SPA. Jangan menaruh token akses buatan sendiri di localStorage untuk mengakali cookie. Pertimbangkan Sanctum SPA setelah kebutuhan frontend jelas.

Simpan migration dan `composer.lock` di repository tim. Buat branch fitur sesuai kebiasaan tim. Untuk fitur baru, sertakan request Postman dan tes akses, bukan hanya screenshot respons sukses.

## Referensi resmi

- Laravel 13 — persyaratan server: https://laravel.com/docs/13.x/deployment
- Laravel 13 — autentikasi: https://laravel.com/docs/13.x/authentication
- Laravel 13 — middleware: https://laravel.com/docs/13.x/middleware
- Laravel 13 — HTTP testing: https://laravel.com/docs/13.x/http-tests
- Laragon — quick start: https://laragon.org/docs/quick-start
- DBeaver — create connection: https://dbeaver.com/docs/dbeaver/Create-Connection/

Panduan disiapkan 30 Oktober 2026. Nama dan nilai contoh adalah data dummy.
