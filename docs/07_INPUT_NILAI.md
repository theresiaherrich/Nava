# Kontrak Input Nilai

Aturan alokasi dan kunci lintas periode terbaru ada di `09_PENYELARASAN_TP_STS_SAS.md`.
Input memakai posisi TP terurut, bukan flag legacy. Format body tetap sama.

POST `/api/rombel/{record}/nilai/pratinjau-tempel` dan PUT
`/api/rombel/{record}/nilai` menerima body yang sama:

```json
{
  "periode_id": 1,
  "items": [
    {"siswa_rombel_id": 11, "tp_id": 21, "skor": 0},
    {"siswa_rombel_id": 11, "tp_id": 22, "skor": null}
  ]
}
```

## Pemetaan Excel

Ambil roster dan TP dari GET `/api/rombel/{record}/nilai?periode_id=...`.
Frontend menyimpan ID peserta pada setiap baris dan ID TP pada setiap kolom.
Untuk setiap sel yang ditempel, gunakan ID baris dan kolom tujuan, bukan nama
siswa, nomor absen, atau posisi dalam hasil query database. `siswa_rombel_id`
adalah ID keanggotaan rombel, bukan `siswa_id`. Hanya gunakan kolom dalam
`tp_dapat_diisi`. Tolak tempelan yang melampaui baris/kolom tujuan.

Frontend memecah teks clipboard pada tab (kolom) dan newline (baris), lalu
menghasilkan `items`. API ini menerima sel yang sudah dipetakan, bukan teks
clipboard atau file Excel. Sel kosong dikirim sebagai `null`; angka nol tetap
`0`. Middleware Laravel juga mengubah string kosong menjadi `null`.
Desimal memakai titik, maksimal dua angka di belakang titik, rentang 0–100.
Desimal koma harus dikonversi frontend secara eksplisit sebelum dikirim.
Field `skor` wajib hadir. Maksimal 1.000 sel per request; pasangan peserta–TP
tidak boleh berulang. `items` wajib list dengan indeks 0, 1, 2, dan seterusnya;
objek dengan key nama atau indeks yang melompat ditolak. Jangan mengirim sel
di luar area tempelan.

Struktur dan tipe sel diperiksa terlebih dahulu. ID peserta dan TP unik
kemudian diambil secara berkelompok, dibatasi pada rombel tujuan. Tidak ada
query `exists` per sel untuk peserta/TP. ID yang tidak ada maupun berasal dari
rombel lain menghasilkan 422 dengan lokasi field, misalnya
`errors.items.2.tp_id`, tanpa membocorkan data rombel lain.

## Pratinjau dan penyimpanan

Respons pratinjau sukses: `data.periode_id`, `data.items`, dan `data.jumlah_sel`.
Urutan item dipertahankan. Pratinjau tidak menyimpan nilai, paket, atau audit,
dan tidak mengambil lock database. Jika ada kesalahan, seluruh request ditolak
dengan 422; kesalahan field memakai `errors`, kesalahan aturan bisnis memakai
`message`. Format respons sukses tetap sama seperti di atas; PUT mengembalikan
`data.tersimpan` dan `data.waktu`.

Setelah pengguna memeriksa pratinjau, kirim body tersebut ke PUT. PUT mengecek
ulang seluruh aturan dalam transaksi karena periode/paket mungkin sudah berubah.
Pratinjau bukan jaminan bahwa request PUT berikutnya pasti diterima.
Middleware session tetap berlaku; penyimpanan session dapat menulis database
jika aplikasi menggunakan session driver database. Jaminan tanpa tulis di sini
berlaku untuk logika pratinjau penilaian, bukan middleware autentikasi aplikasi.

## CSRF dan login session

Endpoint berada di `routes/web.php` dan memakai cookie session Laravel.
Gunakan cookie jar yang sama untuk semua langkah (Postman menyimpan cookie
otomatis; frontend memakai `credentials: 'include'` jika diperlukan).
Kirim `Accept: application/json` dan `Content-Type: application/json` untuk body JSON.

1. GET `/api/csrf-token`. Simpan cookie session dan nilai `csrf_token` respons.
2. POST `/api/login` dengan body `{"login":"username_atau_email","password":"password"}`.
   Kirim cookie tersebut dan header `X-CSRF-TOKEN` berisi token dari langkah 1.
3. Setelah login berhasil, gunakan cookie terbaru dan `csrf_token` dari respons
   login untuk request berikutnya. Session dapat diperbarui saat login.
4. GET daftar nilai, lalu POST pratinjau dan PUT simpan. POST/PUT tetap membawa
   cookie session dan header `X-CSRF-TOKEN`. Token CSRF bukan pengganti login.
5. Jika session kedaluwarsa, ulangi pengambilan CSRF dan login.

| HTTP | Arti dan tindakan |
|---|---|
| 401 | Belum login/session tidak berlaku, atau kredensial login salah. Login kembali. |
| 403 | Akun tidak aktif, role tidak diizinkan, atau guru bukan pengampu. Periksa penugasan/hak akses. |
| 419 | CSRF hilang/tidak cocok atau session berubah. Ambil CSRF pada session yang sama dan ulangi alur bila perlu. |
| 422 | Struktur/skor/ID tidak valid atau aturan periode/paket menolak pengisian. Periksa `errors` dan `message`. |

CSRF dapat diperiksa sebelum autentikasi, sehingga POST/PUT tanpa CSRF valid
dapat menghasilkan 419 walaupun pengguna juga belum login. Jika memakai
frontend lintas origin, cookie, domain, dan konfigurasi CORS aplikasi harus sesuai.

## Batasan dan verifikasi

Tidak ada perubahan skema. Dump SQL `siakad_sma1` tidak ditemukan di workspace;
kesesuaian dump dengan migration belum dapat diverifikasi.
Penguncian diperiksa untuk periode input dan paket SAS yang bergantung pada skor.
Karena skor TP tidak memiliki `periode_id`, TP yang dipakai STS sekaligus SAS
berbagi skor antarperiode. Koreksi STS kini ditolak jika paket SAS terkait sudah
dikirim/disetujui/dikunci; belum ada snapshot historis. Tim perlu memutuskan apakah skor
dibagikan antarperiode, dipisahkan, atau hasil final disimpan sebagai snapshot.

### Opsi kebijakan lintas STS/SAS untuk tim

| Opsi | Dampak dan konsekuensi |
|---|---|
| Skor TP bersama | Pertahankan satu skor per TP–peserta. Perubahan perlu diblokir jika skor digunakan paket final di periode mana pun. Koreksi harus melibatkan semua periode terdampak. |
| Skor terpisah per periode | STS dan SAS dapat memiliki skor berbeda untuk TP yang sama. Membutuhkan perubahan skema, query, dan aturan migrasi data yang disepakati. |
| Snapshot hasil final | Skor kerja dapat berkembang, tetapi hasil periode yang sudah final disalin ke arsip yang tidak berubah. Membutuhkan mekanisme finalisasi dan versi koreksi. |

Rekomendasi: sepakati dulu apakah skor TP secara akademik memang sama pada
STS/SAS. Jika sama, pertimbangkan perlindungan lintas periode untuk skor kerja
serta snapshot untuk laporan final. Jika penilaiannya berbeda, pertimbangkan
skor terpisah. Jangan hanya menambahkan `periode_id` tanpa aturan pemindahan
data dan koreksi. Tidak ada opsi ini yang diimplementasikan pada perubahan ini.

Pengujian `tests/Feature/PenilaianTest.php` dijalankan pada 14 November 2026
dengan SQLite `:memory:`: **18 PASS, 0 FAIL, 161 assertions**.
Perintah: `php vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result tests/Feature/PenilaianTest.php`.
Percobaan pertama menghasilkan 18 error karena encryption key testing kosong;
test kini memakai key sementara di konfigurasi memori, tanpa mengubah environment.
File ini menggunakan `RefreshDatabase`, yang menjalankan migration secara internal.
Hook `beforeRefreshingDatabase()` menghentikan test sebelum migration jika
environment bukan testing atau koneksi bukan SQLite in-memory tanpa URL override.
Jangan jalankan terhadap database aktif. Hasil ini tidak membuktikan perilaku
konkurensi dan penguncian baris MySQL atau menyelesaikan risiko lintas STS/SAS.
