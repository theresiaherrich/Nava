# Panduan pengujian end-to-end

Buat schema demo kosong dan jalankan setup. Gunakan satu host untuk semua akun. Untuk bekerja paralel, pisahkan profil browser atau keluar sebelum masuk sebagai peran lain. Sari memiliki peran awal Wali Kelas dan dapat memilih Guru dari header.

| Langkah | Akun dan URL | Tindakan dan hasil yang diharapkan |
|---|---|---|
| Login | Semua peran di `/login` | Masuk dengan username dan sandi demo. Halaman tujuan mengikuti peran aktif. Akun `citra` membuka `/ganti-sandi`. Ganti sandi awal, lanjutkan ke portal, lalu coba keluar. |
| Struktur TP | `budi` di `/guru/tp` | Pilih Matematika X. Buat TP dengan deskripsi, simpan, dan muat ulang. Lanjutkan sampai 10 TP untuk memeriksa kuota. Tes browser mengirim permintaan ke server untuk memverifikasi penolakan TP ke-11. Hapus TP tambahan yang masih kosong atau isi nilainya sebelum pengujian SAS. |
| Input nilai | `budi` di `/guru/input-nilai` | Pilih Matematika X, tab STS, dan kelas X-B. Ubah beberapa nilai, termasuk 0 atau desimal. Tunggu pesan tersimpan lalu muat ulang. Nilai di luar 0–100 ditolak. Untuk tempel Excel, mulai dari sel yang dapat diubah dan tinjau daftar sebelum menerapkannya. |
| Kirim dan revisi | `budi`, `sari` sebagai Guru, dan `sari` sebagai Wali Kelas di `/wali-kelas/verifikasi-nilai` | Guru mengirim setiap paket X-B. Budi mengirim Matematika dan PAI. Sari mengirim Bahasa Indonesia dan PAK. Wali membuka rincian, mengembalikan satu paket dengan catatan, dan menyetujui paket setelah guru mengirim ulang. |
| Kunci | `kurikulum` di `/kurikulum/periode-penguncian` | Pilih STS, pastikan paket X-B lengkap dan disetujui, lalu kunci. Nilai terkunci menjadi hanya baca. X-A memuat contoh paket dengan nilai kosong. Jika dikirim melalui API dengan konfirmasi dan disetujui, penguncian tetap harus ditolak. |
| Rapor STS | `sari` sebagai Wali di `/wali-kelas/cetak-rapor` | Pilih STS dan X-B. Bentuk rapor Andi, buka Pratinjau dan Unduh PDF. Uji juga PDF/ZIP satu kelas. Rapor menyertakan identitas, kolom TP yang dinilai pada STS, dan rata-rata STS. |
| Rekap DKN | Wali atau Kurikulum di `/wali-kelas/rekap-nilai` atau `/kurikulum/rekap-nilai` | Pilih X-B serta periode. Baca nilai per mapel dan rata-rata. Unduh dan buka Excel. |
| SAS dan ekskul | `kurikulum` di `/kurikulum/periode-penguncian`, `budi` dan `sari` sebagai Guru, `kesiswaan` di `/kesiswaan/ekskul` | Atur mulai–selesai SAS dalam WIB. Lengkapi semua nilai. Isi SAS pada TP terakhir. Ubah predikat atau catatan Pramuka. Kirim paket X-B, verifikasi, kunci, dan bentuk Rapor Semester. |
| Terbit dan baca rapor | `kurikulum` di `/kurikulum/cetak-rapor`, `andi` di `/siswa/rapor`, `hendra` di `/orang-tua/rapor` | Terbitkan versi yang sudah dibentuk. Andi dan Hendra melihat dan mengunduh versi yang sama. Sebelum terbit, draf hanya tersedia bagi pengelola rapor. |
| Portal nilai | `andi` di `/siswa/nilai`, `hendra` di `/orang-tua/nilai` | Buka Matematika, lihat nilai dan deskripsi TP. Hendra dapat mengakses nilai Andi melalui relasi orang tua. Coba akses rapor siswa lain menggunakan konteks pengujian fiktif, hasil yang diharapkan 403. |

Contoh hitung dari dataset awal Andi. Matematika TP1–TP4 adalah 82, 78, 86, 90. STS adalah `(82 + 78) / 2 = 80`. SAS berasal dari TP4 dengan nilai 90. Nilai semester adalah `(82 + 78 + 86 + 90) / 4 = 84`. Bila guru menambah TP, isi TP terakhir untuk SAS dan perbarui jumlah pembaginya.

## Tes browser berulang

Jalankan pada schema MySQL demo baru setiap kali. Tes mengubah sandi `citra`, nilai, periode, paket, dan status rapor. Pakai sandi awal yang sama dengan seeder. Jalankan server lokal terlebih dahulu, lalu dari terminal kedua jalankan perintah berikut.

```powershell
npm.cmd install --no-save --package-lock=false playwright-core
$env:NAVA_TEST_URL = 'http://127.0.0.1:8000'
$env:NAVA_TEST_CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe'
$env:SEED_DEFAULT_PASSWORD = [Net.NetworkCredential]::new('', (Read-Host 'Sandi akun demo' -AsSecureString)).Password
node tests/browser/a2-e2e.mjs
```

Tes menggabungkan interaksi UI dan permintaan HTTP dengan cookie browser. Pemeriksaan CSRF mengirim token yang salah tanpa metadata origin browser yang sah, sehingga Laravel menjalankan validasi token. PHPUnit melengkapi tes browser dengan cakupan regresi backend.
