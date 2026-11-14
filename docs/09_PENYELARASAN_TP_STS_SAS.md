# Penyelarasan TP, Input Nilai, Paket Nilai, dan DKN

Aturan ini menggantikan pemilihan berdasarkan flag pada panduan lama.
Tidak ada migration, pembaruan massal flag, pemindahan skor, atau perubahan
rumus rata-rata/format Excel. Kuota tetap maksimal 10 TP **per rombel**.

## Satu sumber aturan

`AlokasiTpService` menerima seluruh TP rombel, mengurutkan `nomor_urut`, lalu:

- STS untuk input: `floor(total/2)` TP pertama.
- SAS untuk input: semua TP setelah batas STS.
- STS untuk DKN/paket: bagian STS.
- SAS untuk DKN/paket: **seluruh TP**, termasuk skor dari STS.

Pembagian memakai posisi pada daftar, bukan `nomor_urut <= batas` atau ID.
Nomor `[1,3,5,7,10]` menghasilkan STS `[1,3]`, SAS `[5,7,10]`.
Ini nomor, bukan ID: respons `tp_dapat_diisi` tetap berisi ID TP aktual.

| Total | Input STS | Input SAS | TP wajib DKN/paket SAS |
|---|---|---|---|
| 1 | 0 | 1 | 1 |
| 2 | 1 | 1 | 2 |
| 3 | 1 | 2 | 3 |
| 4 | 2 | 2 | 4 |
| 5 | 2 | 3 | 5 |
| 6 | 3 | 3 | 6 |
| 7 | 3 | 4 | 7 |
| 8 | 4 | 4 | 8 |
| 9 | 4 | 5 | 9 |
| 10 | 5 | 5 | 10 |

Untuk ID terurut `[1,2,3,4,5]`, input STS `[1,2]`, SAS `[3,4,5]`.
Skor `[80,90,70,85,95]`: DKN STS `85.000000`, SAS `84.000000`.
Skor 0 sah. Null/record tidak ada menahan kelengkapan dan nilai akhir.
Presisi enam desimal DKN tetap sama. Jika bagian STS kosong (total 1), nilai
STS null dengan alasan `tidak_ada_tp`; pengiriman paket STS ditolak 422,
bukan membuat paket kosong/final buatan. SAS tetap dapat diisi/dikirim.

## Flag legacy dan frontend

`masuk_sts` dan `is_sas` tetap ada pada request/model/respons untuk kompatibilitas.
Keduanya **tidak menentukan hak input atau TP DKN/paket**. Tidak ada pembaruan
otomatis ke flag yang sudah tersimpan. `is_sas` tetap penanda opsional satu TP
terakhir, bukan penanda seluruh bagian SAS. Validasi create/update menolak
penanda yang lebih dari satu atau bukan TP terakhir. Jika menambah TP setelah
TP bertanda, penanda lama harus diperbaiki secara eksplisit sebelum tambah;
server tidak memindahkannya diam-diam. Data legacy yang sudah tidak sesuai
tetap dibaca, tetapi tidak dapat diperbanyak lewat salin yang tidak valid.

Frontend wajib memakai `data.tp_dapat_diisi` pada GET Input Nilai untuk sel
editable, bukan menafsirkan flag. GET tetap menampilkan semua TP/skor, sehingga
skor STS terlihat pada SAS namun tidak dapat diedit dari periode SAS.
Pratinjau dan PUT memeriksa alokasi yang sama; hasil pratinjau tetap bukan
jaminan penyimpanan jika struktur/status berubah sebelum PUT.
Metadata TP DKN STS adalah bagian STS; metadata SAS seluruh TP.

## Pembekuan dan kunci lintas periode

Perubahan TP (tambah/update termasuk deskripsi/hapus/salin ke tujuan) ditolak jika:

- Rombel memiliki skor non-null pada TP mana pun, termasuk 0.
- Ada paket rombel berstatus dikirim/disetujui/dikunci.
- Ada periode terkunci dalam semester rombel. Ini penjagaan konservatif lintas
  tingkat karena rombel dapat berisi beberapa kelas asal.

Jika belum ada skor dan paket masih draft/perlu_perbaikan, perubahan struktur
dapat mengubah alokasi dinamis. Record nilai null saja tidak membekukan struktur.
Salin hanya ke tujuan kosong, mengunci sumber dan tujuan dengan urutan ID,
memvalidasi 1–10 TP sumber, nomor unik/rentang, serta penanda SAS legacy.
Sumber bernilai boleh disalin definisinya karena sumber/skornya tidak diubah.
Ketentuan lintas mapel/semester salin tetap mengikuti endpoint existing;
tidak ada kebijakan baru yang memaksa kesamaan mapel/semester.

Input/submit/perubahan status paket mengambil kunci rombel terlebih dahulu.
Input mengunci periode semester dengan urutan ID tetap untuk menyelaraskan
penguncian global periode. Struktur TP juga memeriksa periode di bawah lock.
Perubahan status paket memegang kunci rombel, periode, lalu paket.

Edit STS ditolak bila paket SAS rombel/kelas terkait sudah dikirim, disetujui,
atau dikunci; SAS menggunakan skor STS tersebut. Paket kelas lain tidak
memblokir kelas yang diedit. Periode SAS terkunci untuk semester/tingkat terkait
juga memblokir koreksi STS. Paket/periode STS terkunci tidak menghalangi input
bagian SAS selama periode SAS terbuka dan paket SAS masih dapat diisi.

Tidak ada pembukaan kunci otomatis. Koreksi hanya bisa dilakukan ketika semua
paket yang bergantung pada skor tersebut berada pada status editable dan periode
input terbuka. Mekanisme membuka periode global yang sudah terkunci belum ada.
Approve/kunci paket memeriksa ulang kelengkapan, termasuk seluruh TP untuk SAS,
sehingga paket lama yang tidak lengkap tidak dapat dilanjutkan ke final.

## Dampak data lama dan keterbatasan

Perubahan aturan baca ini dapat mengubah daftar editable/hasil STS legacy jika
flag lama berbeda dari pembagian berdasarkan posisi. Tidak ada cara menentukan
hasil historis sebelumnya tanpa snapshot. Sebelum penerapan ke data bernilai
atau paket final, tim perlu membandingkan alokasi lama/baru dan hasil DKN secara
read-only, meninjau selisih, serta menyetujui rencana koreksi. Implementasi ini
tidak menyimpan versi alokasi dan tidak memperbarui data demo/utama.

DKN tetap rekap kerja; kelengkapan bukan bukti final. Status paket tetap dikirim
dalam respons. Penguncian mencegah penulisan melalui endpoint, bukan snapshot
historis yang kebal terhadap perubahan SQL di luar aplikasi. Jangan mengosongkan
skor atau membuka paket hanya untuk melewati pembekuan struktur.
Ekspor besar tetap memuat data ke memori seperti sebelumnya.

## Verifikasi Postman (manual, lingkungan latihan terisolasi)

Gunakan server latihan yang disiapkan tim, bukan database utama/shared demo.
Jangan mengubah data historis untuk mencoba langkah ini. Login session dan CSRF
mengikuti `07_INPUT_NILAI.md`; sertakan cookie serta header CSRF pada POST/PUT.

1. Kurikulum: buat rombel latihan/peserta/guru pengampu dan 5 TP dengan nomor
   1–5 melalui endpoint existing, sebelum ada nilai. Flag boleh false; `is_sas`
   jika dipakai hanya pada TP terakhir. Catat ID yang dikembalikan.
2. Guru: GET `/api/rombel/{id}/nilai?periode_id={sts}` lalu `{sas}`. Cocokkan
   `tp_dapat_diisi`: dua ID pertama vs tiga ID terakhir.
3. POST `/api/rombel/{id}/nilai/pratinjau-tempel` dan PUT `/api/rombel/{id}/nilai`
   dengan `periode_id` dan `items[{siswa_rombel_id,tp_id,skor}]` seperti Input Nilai.
   STS isi 80/90, SAS isi 70/85/95. Coba TP bagian lain: harus 422.
4. GET DKN kedua periode: 85 dan 84. Unduh Excel dan cocokkan hasilnya.
5. Pada data latihan baru yang belum lengkap, coba kirim SAS walau bagian SAS
   terisi tetapi STS kosong: 422. Nilai 0 dihitung terisi; null tetap tidak lengkap.
6. Kirim–setujui–kunci paket SAS melalui role yang berwenang. Coba pratinjau/PUT
   koreksi STS: 422. Pastikan skor tidak berubah.
7. Coba tambah/ubah/hapus TP rombel bernilai: 422. Uji perubahan struktur hanya
   pada rombel baru tanpa skor/paket final; pastikan alokasi berubah sesuai jumlah.

## Test otomatis

`AlokasiTpTest` mencakup 1–10 TP, flag legacy salah, nomor berlubang, GET/pratinjau/
PUT, contoh 85/84, kelengkapan SAS, null/0, pembekuan, salin, kuota dan kunci
lintas periode. Fixture test lama disesuaikan ketika ekspektasinya mengandalkan
flag manual; perhitungan/keamanan Excel dan akses tetap diuji.
Semua test RefreshDatabase memiliki pengaman environment testing + SQLite
`:memory:` tanpa URL override. Tidak ada migration manual atau seeder MySQL.

```powershell
php vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result tests/Feature/AlokasiTpTest.php tests/Feature/RekapNilaiTest.php tests/Feature/PenilaianTest.php
```

SQLite membuktikan aturan fungsional, bukan konkurensi row-lock MySQL. Uji
konkurensi selanjutnya harus menggunakan MySQL testing terisolasi dengan akun
yang tidak punya izin ke database utama/demo.

Hasil verifikasi 15 November 2026: **71 PASS, 0 FAIL, 1.050 assertions**
(19 kasus penyelarasan, 34 kasus regresi DKN, 18 regresi Input Nilai).
Sintaks PHP seluruh file terkait, Pint, pendaftaran route, dan `git diff --check`
lulus. Pengujian awal menemukan ekspektasi test lama berdasarkan flag, yang
disesuaikan ke alokasi posisi, serta satu fixture deskripsi TP yang terlalu
pendek. Tidak ada konfigurasi environment atau migration yang diubah.
