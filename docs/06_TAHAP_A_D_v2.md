# Tahap A–D (v2): rombel, wali, TP, nilai, paket

Aturan TP/input/paket terkini dijelaskan di `09_PENYELARASAN_TP_STS_SAS.md`.

Lihat panduan lengkap di artifact "Panduan Backend SIAKAD A2". Ringkasan cepat:

- Migrasi baru: `2026_10_31_000001_create_rombel_tables.php` dan `2026_10_31_000002_create_penilaian_tables.php`.
- Endpoint baru ada di `routes/web.php` pada blok "Tahap A–D (v2)".
- Aturan akses terpusat di `app/Services/PenilaianAccess.php`.
- Test: `tests/Feature/PenilaianTest.php` (`php artisan test --filter=PenilaianTest`).
- Migrasi harus dijalankan pada database latihan dulu: `php artisan migrate`.
