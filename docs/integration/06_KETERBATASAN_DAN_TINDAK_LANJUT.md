# Keterbatasan dan tindak lanjut

| Area | Kondisi integrasi | Tindak lanjut |
|---|---|---|
| Database dari cabang lama | Instalasi konsolidasi ditujukan ke schema kosong. Database lama mungkin memiliki tabel `users` dengan ID dan pivot berbeda. | Tim backend perlu memetakan akun, relasi dan ID akademik dari salinan database. Uji rekonsiliasi sebelum memigrasikan data yang dipakai tim. |
| UI data master | Daftar tahun, semester, siswa, guru, kelas, rombel, pengampu dan wali kelas membaca API. Pengelolaan master lanjutan, impor, akun massal, dan tautan profil tersedia melalui endpoint backend sesuai peran. | Sambungkan formulir operator dan kurikulum dari `dev_frontend` ke kontrak terkini. |
| Pelengkap dan format rapor | Jadwal, cetak, terbit, DKN, dan predikat ekskul tersedia di UI. Pengisian catatan wali, kokurikuler, presensi manual, perubahan templat, koreksi paket, dan penarikan publikasi masih melalui API. | Sambungkan halaman tambahan Wali Kelas dan Kurikulum. Uji bersama konfigurasi templat sekolah. |
| Desain | Login dan halaman TP serta grid nilai diadaptasi dari frontend. Halaman integrasi lain memakai komponen dan token visual tim. PDF mengikuti struktur kop, tabel, serta tanda tangan templat A2. | Tim frontend dan sekolah perlu meninjau kesesuaian visual seluruh layar dan contoh cetakan resmi. |
| Portal dan lintas modul | Portal terhubung untuk nilai dan rapor. Kehadiran A1, BK A3, minat bakat A4, notifikasi UI, dan analitik UI berada di luar jalur akademik yang disambungkan. | Integrasikan bersama pemilik modul setelah kesepakatan tabel, kontrak, dan hak akses. |
| Operasional | Uji memakai data sintetis, satu instans Laragon, dan Chrome headless. | Tambahkan uji beban konkurensi MySQL, browser lain, alur semester historis, dan pengujian perangkat 390 px. |
