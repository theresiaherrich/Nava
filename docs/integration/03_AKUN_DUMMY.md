# Akun dummy A2

Semua profil orang di bawah adalah fiktif. Dataset memakai tahun ajaran 2026/2027 Semester Ganjil. Seeder hanya berjalan di lingkungan `local` atau `testing` dan membaca `SEED_DEFAULT_PASSWORD`. Gunakan sandi uji lokal dengan minimal 10 karakter, huruf besar, huruf kecil, angka, dan simbol. Simpan di variabel proses seperti di panduan setup, atau di `.env` lokal yang diabaikan Git.

| Username | Peran | Data terkait |
|---|---|---|
| `superadmin` | Super Admin | Pengelolaan akses tingkat sistem |
| `operator` | Operator (`ADMIN`) | Data master |
| `kurikulum` | Admin Kurikulum | Jadwal STS/SAS, penguncian, penerbitan |
| `kesiswaan` | Admin Kesiswaan | Predikat ekstrakurikuler |
| `budi` | Guru | Matematika X dan PAI X |
| `sari` | Wali Kelas dan Guru | Wali X-B, pengampu Bahasa Indonesia X dan PAK X |
| `wali_xa` | Wali Kelas dan Guru | Wali X-A |
| `andi`, `bunga` | Siswa | X-B, NIS 1001 dan 1002 |
| `citra` | Siswa | X-A, NIS 1003. Wajib ganti sandi pada login pertama. TP2 Matematika STS kosong. |
| `siswa_a2_04` s.d. `siswa_a2_11` | Siswa | X-B, NIS 1004 s.d. 1011 |
| `siswa_a2_12` | Siswa | X-A, NIS 1012 |
| `hendra` | Orang Tua | Ayah Andi, tanpa tautan ke Bunga atau Citra |

X-B memiliki 10 siswa dan nilai contoh lengkap pada 4 rombel. X-A menyediakan skenario belum lengkap. Empat TP Matematika dan TP mapel lain memiliki contoh draf STS dan SAS, tanpa paket yang sudah dikirim. Seeder membuka STS saat belum dijadwalkan. Jadwalkan SAS dari Admin Kurikulum saat pengujian. Kegiatan Pramuka memuat contoh predikat SB, B, dan K.

`php artisan db:seed` dapat diulang pada database demo. Akun yang sudah dibuat mempertahankan hash sandi dan status ganti sandi. Agar pengujian login pertama dapat diulang dari awal, buat schema demo baru.
