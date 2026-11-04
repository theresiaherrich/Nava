# Catatan perubahan dari ZIP asal

## Dasar proyek

Input: `Nava-main (1).zip`, berisi skeleton Laravel dengan framework constraint `^13.17` dan dependency DomPDF/Excel. Composer lock asal memuat Laravel 13.38.0. Versi framework dipertahankan pada 13.38.0; tidak dibuat proyek baru dengan versi framework berbeda.

## Perubahan aplikasi

- Menambahkan dua migration; migration bawaan user/cache/jobs dipertahankan.
- Menambahkan username, status aktif, roles/pivot, dan audit.
- Memperbarui model User dengan hash password dan multi-role.
- Menambahkan FormRequest, middleware active/role/JSON, controller auth/admin/master.
- Menambahkan perintah admin interaktif dan role seeder idempoten.
- Menambahkan model dan endpoint akademik awal.
- Menambahkan test dan collection; default test memakai SQLite sementara.
- `.env.example` diarahkan ke MySQL lokal, timezone aplikasi Asia/Jakarta.
- Script Composer setup/dev disesuaikan untuk backend tanpa build npm.
- File frontend bawaan (welcome Blade, CSS/JS, package npm) tidak disertakan pada paket backend; route root sekarang JSON. Paket bukan patch untuk menimpa UI tim yang sudah berkembang.

## Dependency

Instruksi `AGENTS.md` di ZIP asal meminta Laravel Boost dipasang sebelum pengerjaan. Boost ditambahkan sebagai dependency development beserta dependency pendukungnya. Boost bukan autentikasi aplikasi dan tidak diperlukan untuk memakai endpoint.

Audit awal menemukan advisory pada Symfony Mime bawaan. Dependency tersebut diperbarui ke rilis patch yang tidak terkena advisory, dengan dependency resolution ditargetkan PHP 8.3 melalui `config.platform.php`. `composer.lock` akhir disertakan agar tim menginstal versi yang sama. Jalankan `composer check-platform-reqs` pada PHP Laragon yang sebenarnya; platform Composer tidak menginstal PHP untukmu.

Tidak menggunakan Spatie Permission karena ERD sudah memiliki `roles` dan `role_user`. Tidak memasang Sanctum karena tahap ini memakai session MVC satu origin.

## Hal yang sengaja belum disediakan

- Tidak ada public role picker atau auto-claim siswa berdasarkan NIS saja.
- Tidak ada akun/password produksi bawaan.
- Belum ada fitur ganti/lupa password atau verifikasi email. Tabel reset bawaan belum digunakan oleh endpoint.
- Belum ada policy per rombel/wali kelas; endpoint data pada tahap ini hanya admin/kurikulum. Policy objek wajib ditambahkan saat membuka modul untuk guru/siswa/wali.
- Belum ada DELETE master, mutasi siswa, atau penggantian profil yang sudah tertaut.
- Belum ada jaminan satu tahun/semester aktif global.
- Belum ada perhitungan nilai/rapor. Formula harus dikonfirmasi dengan sekolah.
- Belum ada CORS untuk frontend beda origin.
- Belum diuji pada MySQL Windows, MariaDB, atau production.

## Integrasi ke repository tim

Gunakan branch terpisah, review perubahan `User`, `bootstrap/app.php`, `AppServiceProvider`, `routes/web.php`, seeder, dan `.env.example`. Jangan mengganti `.env` teman dengan `.env` milikmu. Jalankan `composer install` dan migration setelah perubahan disepakati. Jika database tim sudah berisi tabel dengan nama yang sama dari implementasi lain, selaraskan migration terlebih dahulu; jangan menjalankan dua definisi tabel yang bertabrakan.
