# Audit integrasi A2 Nava

Audit dilakukan pada 18 November 2026 di workspace Laragon. `git fetch origin --prune` berhasil. Branch lokal `integration/a2-e2e` dibuat dari `origin/dev_backend`. Sebelum pindah branch, status Git mencatat `.env.testing` dan `hortlog -sne --all`. Keduanya dipertahankan. Aturan ignore backend menandai `.env.testing` sebagai berkas lokal. Branch bersama dan remote tetap pada commit awal audit.

| Referensi saat fetch | Commit | Keputusan |
|---|---|---|
| `origin/dev_backend` | `09074a2` | Basis integrasi. Konsolidasi model, migrasi, aturan nilai, dan API ada pada `a1210e8` sampai `076219a`. |
| `origin/dev_frontend` | `2c7d8a3` | Berbeda 29 commit di sisi backend dan 30 di sisi frontend. Ambil fondasi Vite/Tailwind/Alpine, komponen UI, logo, login, halaman TP dan tabel nilai. |
| `origin/dev` | `7984450` | Leluhur backend yang berisi perubahan README. |
| `origin/feature/a2-auth-admin-periode` | `2034eec` | Leluhur backend. Skema dan implementasi aktif berasal dari konsolidasi setelah merge tersebut. |
| `origin/feature/a2-penyelarasan-tp-sts-sas` | `6fc093b` | Leluhur backend dan branch lokal awal. Alokasi otomatis setengah TP dari iterasi ini sudah diarsipkan. |
| `origin/feature/a2-rombel`, `a2-tp`, `a2-input-nilai`, `a2-dkn`, `a2-siwa` | `f31397f`, `63967f4`, `43560d1`, `c4a98ab`, `39a7ae2` | Alur akademik terkait sudah memiliki padanan terkonsolidasi. Templat PDF dari riwayat `a2-siwa` menjadi referensi bersama templat rapor frontend. |
| `origin/feature/fe-rapor` dan `origin/fe-nadya` | `7e3200d`, `ec7adb4` | UI pendahulu untuk verifikasi, input, periode, dan rapor. Rujukan visual utama tersedia di `origin/dev_frontend:docs/design`. |
| `origin/feature/fe-portal-ortu` | `2b31420` | Commit terpisah dengan tampilan portal dan sumber data demo sendiri. Portal akademik terhubung ke API konsolidasi. |
| `origin/feature/fe-dashboard-wali-kelas`, `origin/feature/fe-periode-penguncian`, `origin/feature/tahap-a-d` | `6ae4351`, `8b31c69`, `9eddbbc` | Tidak ada commit unik pada perbandingan terhadap cabang utama terkait. |

Tidak ada ref `origin/login`. Implementasi frontend ada di riwayat `d572ad6` dan UI login diperbarui di `2c7d8a3`. Sumber akun final menggunakan `user.id_user`, `roles` dan `role_user`. Tab dan halaman terikat peran aktif pada sesi Laravel. Rute tulis memakai middleware backend dan validasi CSRF pada origin aplikasi. Endpoint API dan Blade memakai satu sesi, satu layanan aturan nilai, dan satu database. Spatie, migrasi `users`, serta mode autentikasi demo frontend dikeluarkan dari rekonsiliasi kode.

`docs/02_KONTRAK_API.md` adalah kontrak kanonis. `03_ROADMAP_ERD.md` menjelaskan struktur, `05_CATATAN_PERUBAHAN.md` mencatat penggantian implementasi lama, dan `01_PANDUAN_DARI_NOL.md` menjadi sumber setup backend. Periode STS dikaitkan dengan asal nilai. Rata-rata semester memasukkan nilai TP terakhir tepat satu kali. Migrasi backend konsolidasi dipakai pada instalasi kosong. Lihat panduan setup untuk batas upgrade dari skema lama.

## Cakupan perubahan

| Berkas atau folder | Perubahan |
|---|---|
| `routes/web.php`, `routes/frontend.php`, `bootstrap/app.php`, `app/Support/WebNavigation.php` dan middleware | Halaman autentikasi dan dashboard per peran, pembatasan role, penggantian sandi awal, kebijakan cache, dan CSRF. |
| `app/Http/Controllers/Web/GuruPageController.php`, `resources/views/guru/`, `resources/js/guru-*.js` | Integrasi halaman TP dan tabel nilai frontend ke data rombel, mutasi, verifikasi per kelas asal, status sel, dan perhitungan. |
| `resources/views/academic/`, `resources/js/academic.js`, `resources/js/siakad-api.js` | Master baca, verifikasi, periode, penguncian, DKN, rapor, portal siswa/orang tua, dan predikat ekskul. |
| `resources/views/layouts/`, `resources/views/components/ui/`, `resources/views/auth/`, dan aset Vite | Identitas visual frontend, komponen, form login dan sesi. |
| `app/Services/PeriodeService.php`, `app/Services/RaporService.php`, controller rapor/portal, `resources/views/rapor/` dan `resources/views/dkn/` | Uji ulang kelengkapan saat penguncian. Snapshot STS memuat nilai per TP. Versi draf diperiksa kembali saat terbit. PDF privat tersedia untuk pratinjau dan unduhan. |
| `DevSeeder`, `DevPenilaianSeeder`, `config/filesystems.php` | Data fiktif relasional dan opsi penyimpanan PDF khusus pengujian. |
| `tests/browser/`, `tests/Feature/`, `docs/integration/` | Skenario pengujian, hasil aktual, serta panduan Windows dan Laragon. |

Rincian fitur dan cakupan pengujian tersedia di [hasil pengujian](05_HASIL_PENGUJIAN.md) dan [tindak lanjut](06_KETERBATASAN_DAN_TINDAK_LANJUT.md).
