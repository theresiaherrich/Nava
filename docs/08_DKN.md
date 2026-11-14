# DKN: rekap nilai dan ekspor

**Pembaruan penyelarasan:** sumber alokasi kini posisi TP terurut melalui
`AlokasiTpService`, bukan flag manual. Rincian aktif dan dampak data lama ada di
`09_PENYELARASAN_TP_STS_SAS.md`. Audit TP di bawah merupakan catatan sebelum
penyelarasan; bukan deskripsi kemampuan terbaru.

DKN ini membaca data kerja saat ini, bukan arsip nilai final. Tidak ada
perubahan skema, migration, atau penulisan nilai/paket melalui endpoint DKN.
Middleware session tetap mengikuti konfigurasi aplikasi.

## Endpoint

Semua request membutuhkan login session aktif, cookie yang sama dengan login,
dan header `Accept: application/json`. Prosedur login/CSRF ada di `07_INPUT_NILAI.md`.
Ketiga endpoint menggunakan GET, sehingga tidak perlu header CSRF untuk request GET.

| Endpoint | Hasil |
|---|---|
| GET `/api/rekap-nilai?periode_id=1` | Rekap peserta–rombel dengan pagination |
| GET `/api/rekap-nilai/rombel/2?periode_id=1` | Rekap rombel dan metadata TP yang relevan |
| GET `/api/rekap-nilai/export?periode_id=1&scope=semua` | Download `.xlsx`, bukan JSON |

Parameter `periode_id` wajib dan harus ada. Filter opsional: `kelas_id` dan
`rombel_id`. Keduanya harus ID yang ada; hasil tetap dibatasi periode dan akses.
Pada detail, ID rombel di URL menjadi acuan, menggantikan filter `rombel_id`.
JSON mendukung `page` (minimal 1) dan `per_page` (1–100, default 20).
Ekspor wajib `scope=semua`, menerapkan filter kelas/rombel yang sama, dan
mengabaikan pagination untuk pengambilan data (seluruh hasil filter diekspor).
`scope=semua` berarti semua hasil dalam periode/filter yang diizinkan,
bukan seluruh semester atau seluruh database.

## Hak akses

- Admin/kurikulum: semua peserta sesuai periode/filter, termasuk ekspor.
- Guru: peserta pada rombel yang diampu.
- Wali kelas: hanya peserta dari kelas yang menjadi tanggung jawabnya.
- Akun dengan role guru dan wali mendapat gabungan kedua cakupan tersebut.
- Role tambahan siswa/orang tua tidak membatalkan hak guru/admin yang sah.
- Siswa/orang tua tanpa role DKN ditolak; ekspor sementara hanya admin/kurikulum.

Daftar tanpa hasil yang berwenang mengembalikan 200 dengan `data: []`.
Detail rombel di luar penugasan mengembalikan 403, ID rombel tidak ada 404,
periode/filter tidak valid 422, dan belum login 401.
Detail beda semester mengembalikan 422 setelah pemeriksaan akses.

Rombel tidak memiliki kolom tingkat. Tingkat ditentukan dari kelas asal peserta:
peserta harus sesuai semester dan tingkat periode, termasuk semester rombel.
Rombel gabungan tingkat hanya menampilkan peserta tingkat yang diminta;
jika tidak ada peserta yang cocok, hasil kosong, bukan menebak tingkat rombel.

## Kontrak JSON

```json
{
  "data": [{
    "siswa_rombel_id": 11,
    "siswa": {"id": 7, "nis": "00123", "nama": "Siswa"},
    "kelas": {"id": 3, "nama": "X-1"},
    "rombel": {"id": 2, "nama": "Biologi X"},
    "mapel": {"id": 1, "nama": "Biologi"},
    "nilai_tp": [{"tp_id": 21, "nomor_urut": 1, "skor": "0.00"}],
    "jumlah_tp": 1,
    "jumlah_tp_terisi": 1,
    "lengkap": true,
    "status_paket": null,
    "nilai_akhir": "0.000000",
    "alasan_nilai_akhir": null
  }],
  "meta": {
    "periode": {"id": 1, "jenis": "STS", "semester_id": 1, "tingkat": 10},
    "current_page": 1, "per_page": 20, "total": 1, "last_page": 1
  }
}
```

Detail menambahkan `meta.rombel` (`id`, `nama`) dan `meta.tp` berupa daftar
`id`, `nomor_urut`, `deskripsi`, `masuk_sts`, `is_sas`.
Metadata TP tetap tersedia walaupun halaman peserta kosong.
Urutan peserta stabil berdasarkan ID keanggotaan rombel; skor mengikuti nomor TP.
Status paket ada di setiap baris peserta, dibaca berdasarkan rombel + periode +
kelas asal. Paket yang belum ada menghasilkan `null`, bukan draft buatan.

## Aturan nilai dan batasan

- STS: jumlah skor separuh pertama TP terurut (floor jumlah/2), dibagi jumlah
  TP bagian STS tersebut. Flag `masuk_sts` tidak menentukan pemilihan.
- SAS: jumlah skor seluruh TP rombel dibagi jumlah seluruh TP rombel pada semester
  terkait, termasuk TP STS, TP khusus SAS, dan TP tanpa kedua tanda.
  `is_sas` tidak menjadi filter perhitungan DKN SAS.
- Skor JSON berupa string desimal dua digit, atau `null` jika belum diisi.
- Skor 0 sah dan dihitung terisi; record tidak ada dan skor null sama-sama kosong.
- `lengkap` berarti ada TP relevan dan seluruh TP tersebut terisi. Tanpa TP,
  jumlah TP dan terisi 0, `lengkap=false`. Ini indikator kelengkapan, bukan finalisasi.
- Semua status paket boleh ditampilkan; DKN ini belum menjadi ekspor final resmi.
- Jika semua TP relevan terisi, `nilai_akhir` dihitung dan `alasan_nilai_akhir=null`.
  Jika ada TP belum terisi, hasil null dengan alasan `nilai_belum_lengkap`.
  Jika tidak ada TP relevan, hasil null dengan alasan `tidak_ada_tp`.
  Tidak pernah membagi dengan nol atau mengganti nilai kosong menjadi nol.
- Hasil JSON berupa string desimal enam digit; Excel memakai angka dan format
  enam digit desimal. Jumlah dihitung dengan integer satuan 1/100 sesuai presisi
  skor database, lalu dibagi jumlah TP aktual. Pecahan berulang dipotong pada
  enam digit (misalnya 460/6 menjadi `76.666666`). Ini presisi teknis keluaran,
  bukan keputusan pembulatan rapor ke bilangan bulat. Tidak ada bobot tambahan.
  Semua endpoint menggunakan method `nilaiAkhir()` pada service yang sama.
- Nilai TP belum dipisahkan per periode. Risiko perubahan lintas STS/SAS tetap
  berlaku; rekap ini tidak menyelesaikan penguncian atau membuat snapshot.

Contoh: skor TP 1–4 = 80, 90, 70, 100; hanya TP 1–2 masuk STS.
STS = (80+90)/2 = `85.000000`; SAS = (80+90+70+100)/4 = `85.000000`.
Jika TP 4 menjadi 60, STS tetap 85 dan SAS menjadi 75.
Jumlah TP dapat berbeda antar mapel, misalnya 4, 6, 8, 9, atau 10.
Pembagi selalu jumlah TP relevan sebenarnya, bukan konstanta 10.

## Audit batas 10 TP (tindak lanjut terpisah)

### Alokasi TP berbeda dari rumus nilai

Aturan alokasi semester yang dikonfirmasi: urutkan seluruh TP berdasarkan
`nomor_urut`, ambil `floor(total_tp/2)` TP pertama untuk STS; sisanya untuk SAS.
Gunakan posisi dalam daftar terurut, bukan ID database atau nomor yang diasumsikan
selalu berurutan (penghapusan existing dapat meninggalkan celah nomor).

| Total TP | Alokasi STS | Alokasi SAS |
|---|---|---|
| 1 | 0 | 1 |
| 2 | 1 | 1 |
| 3 | 1 | 2 |
| 4 | 2 | 2 |
| 5 | 2 | 3 |
| 6 | 3 | 3 |
| 7 | 3 | 4 |
| 8 | 4 | 4 |
| 9 | 4 | 5 |
| 10 | 5 | 5 |

Contoh 5 TP: alokasi STS adalah TP 1–2; alokasi SAS adalah TP 3–5.
Skor 80, 90, 70, 85, 95 menghasilkan STS 85 dan SAS 84 karena nilai akhir SAS
tetap merata-ratakan **seluruh lima TP**, bukan hanya tiga TP alokasi SAS.
Satu TP menghasilkan STS null (`tidak_ada_tp`) dan SAS memakai satu TP tersebut.

Backend TP existing **belum mengalokasikan otomatis**. `masuk_sts` diterima dari
request, bukan dihitung dari jumlah/urutan. `is_sas` existing bermakna penanda TP
khusus yang dapat diisi pada periode SAS, dibuktikan oleh
`PenilaianAccess::tpDapatDiisi()`. Bahkan `TpController::pastikanSatuSas()`
membatasi paling banyak satu TP bertanda SAS. Pesannya menyebut TP terakhir,
tetapi kodenya tidak memeriksa bahwa TP tersebut benar-benar terakhir.
Ini tidak cocok dengan alokasi SAS baru untuk total >=3 TP.
Tidak ada validasi yang mewajibkan flag eksklusif atau seluruh TP teralokasi.

DKN tetap benar sebagai pembaca flag yang tersimpan: STS berdasarkan `masuk_sts`,
SAS seluruh TP. DKN **tidak menghitung ulang atau menimpa alokasi** berdasarkan
total, sehingga flag lama yang berbeda dari kebijakan baru akan tetap terbaca
apa adanya. Tidak ada inspeksi data MySQL atau klaim bahwa flag aktual sudah
sesuai. Bila dibutuhkan alokasi turunan langsung pada DKN, pemilihan STS dan
metadata TP perlu diselaraskan lewat service; jangan diam-diam mengganti sumber
kebenaran flag hanya pada DKN sementara input/paket masih membaca flag lama.

### Perubahan jumlah TP dan tindak lanjut alokasi

| File/method | Tindak lanjut terpisah |
|---|---|
| `TpController::store/update` dan `TpRequest::rules` | Tambahkan kebijakan alokasi bersama setelah perubahan jumlah/urutan, serta validasi flag sesuai definisi baru. Saat ini penambahan default false dan tidak menyesuaikan TP lama. |
| `TpController::destroy` | Setelah hapus, alokasi tidak dihitung ulang. Tentukan dampak pada batas STS dan nilai yang sudah diisi sebelum menerapkan perubahan. |
| `TpController::salin` | Saat ini menyalin nomor dan flag apa adanya. Validasi sumber, konteks mapel/semester, dan alokasi tujuan; jangan memperbaiki data lama diam-diam. |
| `TpController::pastikanSatuSas` | Selaraskan makna `is_sas`; aturan satu SAS bertentangan dengan alokasi baru yang bisa berisi 2–5 TP. |
| `PenilaianAccess::tpDapatDiisi`, `NilaiController::index/simpan`, `PaketNilaiController::kirim` | Selaraskan hak input per alokasi dan kelengkapan periode; kelengkapan SAS tidak boleh dikira sama dengan kelengkapan nilai akhir DKN. |
| `PenilaianAccess::pastikanRombelBisaDiubah` | Pertahankan perlindungan paket dikirim/disetujui/dikunci. Perubahan alokasi dapat memindahkan TP bernilai melewati batas periode; perlu alur koreksi dan audit yang disepakati. |

Kasus ganjil 3/5/7/9 benar pada pemetaan aturan (SAS mendapat satu TP lebih),
tetapi belum dijamin oleh endpoint TP existing. Penambahan dari 3 ke 4 TP,
misalnya, memindahkan batas STS dari satu menjadi dua TP. Penghapusan juga dapat
memindahkan batas. Karena skor TP dibagikan antarperiode, risiko penguncian lintas
STS/SAS tetap perlu diselesaikan sebelum perubahan flag lama secara massal.

Test DKN menyiapkan fixture alokasi sesuai tabel untuk 1–10 TP di SQLite RAM,
memasukkan TP dengan ID terbalik, lalu memeriksa urutan, kelompok, dan hasil STS/SAS.
Test ini membuktikan pembacaan DKN ketika flag benar, **bukan** implementasi
alokasi otomatis pada controller TP. Tidak ada kode produksi/alokasi yang diubah.

Aturan sekolah maksimal 10 TP per mapel per semester, dengan STS/SAS dihitung
bersama. Audit berikut berdasarkan kode current branch; modul TP tidak diubah.

| Pertanyaan | Hasil audit |
|---|---|
| Jumlah TP bervariasi? | Ya. `TpController::store()` menerima penambahan satu per satu sampai 10; tidak mewajibkan tepat 10. |
| TP ke-11 ditolak? | Ya pada endpoint create existing: count >= 10 ditolak, dan `TpRequest` membatasi nomor 1–10. Hasil ini inspeksi kode, bukan pengujian endpoint TP pada pekerjaan ini. |
| Cakupan kuota? | Per `rombel_id`, bukan gabungan `mapel_id + semester_id`. Dua rombel mapel/semester yang sama dapat memiliki masing-masing 10 record TP. Kuota global belum ditegakkan. |
| TP dibagikan? | Tidak. Setiap rombel memiliki record TP sendiri. Salin menghasilkan ID baru dan perubahan berikutnya tidak tersinkronisasi. |
| STS/SAS kuota terpisah? | Tidak. Count existing menghitung seluruh TP rombel tanpa membedakan tanda. |
| Salin dapat melewati batas? | Tujuan harus kosong; sumber normal <=10 tetap aman. Namun salin tidak memvalidasi ulang jumlah/nomor sumber, sehingga sumber tidak valid dari impor/SQL dapat disalin melebihi 10. Sumber juga tidak dikunci. Existing belum mensyaratkan mapel/semester sumber dan tujuan sama. |
| Penomoran duplikat? | Existing tidak menomori ulang otomatis setelah hapus. Request memeriksa unique per rombel; database juga punya unique `(rombel_id, nomor_urut)`. Duplikat tidak dapat tersimpan, tetapi pertukaran nomor yang masih terpakai ditolak. Database sendiri tidak membatasi nomor <=10. |
| Test batas tersedia? | Belum ditemukan test otomatis create TP ke-11, salin melebihi kuota, atau tabrakan nomor pada branch aktif. Test variasi DKN hanya membuktikan perhitungan, bukan validasi endpoint TP. |

Branch TP A2 yang diperiksa dari ref lokal `origin/feature/a2-tp` (`85a0a97`)
memakai `Controllers/Api/TpController` dan `AcademicAccess`, kolom `id_tp`,
`id_rombel`, `urutan`, `bobot`, serta tabel `kktp`; berbeda dari migration existing
(`id`, `rombel_id`, `nomor_urut`, tanpa bobot/kktp). Route dan kontrak salin juga
berbeda. Branch tersebut menolak create/salin >10 per rombel, tetapi test-nya
hanya akses tamu. Integrasi langsung berisiko konflik route, skema, dan akses.
Penomoran otomatis pada branch tersebut juga perlu diuji terpisah, termasuk
data lama dengan nomor berlubang; pembuatan nomor berdasarkan count+1 dapat
bertabrakan dengan nomor yang masih ada. Tidak ada merge/fetch pada tahap ini.

PR terpisah perlu menyepakati apakah TP merupakan template bersama per
mapel–semester atau definisi mandiri per rombel, menegakkan kuota sesuai keputusan,
memvalidasi sumber salin, dan menambah test batas/duplikasi. Input Nilai dan
kelengkapan Paket Nilai existing masih memakai TP `is_sas` untuk input/kirim SAS,
sedangkan rumus DKN SAS kini memakai seluruh TP. Tim perlu menyelaraskan alur
tersebut lewat pekerjaan terpisah; DKN tidak mengubah atau membuka hak input TP.

## Excel dan efisiensi

Laravel Excel existing digunakan. Satu baris Excel mewakili satu peserta–rombel.
Kolom: ID peserta, NIS, nama, kelas, rombel, mapel, TP 1–10, jumlah TP,
TP terisi, lengkap, status paket, nilai akhir, alasan nilai akhir.
TP di luar periode tetap kosong, skor 0 tetap numerik, dan null menjadi sel kosong.
Semua string disimpan eksplisit sebagai teks, termasuk NIS berawalan nol dan
string yang menyerupai formula. JSON dan Excel memakai service yang sama.

Sebelum membuat file, eksportir memeriksa seluruh nomor TP relevan pada setiap
baris: harus integer, unik per baris peserta–rombel, dan dalam rentang 1–10.
Data yang tidak sesuai membatalkan seluruh ekspor dengan HTTP 422 dan
`errors.tp`, disertai ID rombel untuk diperiksa. Tidak ada skor yang diam-diam
ditimpa oleh nomor duplikat atau disembunyikan sementara ikut nilai akhir.
Pemeriksaan tidak mengubah TP, tidak mengganti rumus, dan tidak membatasi JSON.
TP di luar periode yang tidak digunakan dalam rekap tidak ikut validasi ini.

Service mengambil relasi secara berkelompok dan nilai/paket per kumpulan peserta,
tanpa query per peserta. Pagination dilakukan di database sebelum pembentukan JSON.
Ekspor versi awal memuat seluruh hasil filter ke memori; jika volume meningkat,
pertimbangkan ekspor per chunk/antrian. Tidak ada file ekspor publik permanen.

## Pengujian

`tests/Feature/RekapNilaiTest.php` mencakup akses, multi-role, rombel gabungan,
periode, TP STS/SAS, nol/null, tanpa TP, paket per kelas, pagination, Excel nyata,
konsistensi JSON, keamanan tipe sel Excel, dan jumlah query terhadap penambahan peserta.
Sebelum RefreshDatabase, hook menolak environment selain testing atau koneksi
selain SQLite `:memory:` tanpa URL override. Key enkripsi test hanya di memori.

Jalankan setelah konfigurasi efektif dipastikan aman:

```powershell
php vendor/bin/phpunit --configuration phpunit.xml --do-not-cache-result tests/Feature/RekapNilaiTest.php tests/Feature/PenilaianTest.php
```

SQLite tidak membuktikan perilaku konkurensi/lock MySQL. Tidak ada migration
manual yang diperlukan; RefreshDatabase menjalankan migration internal pada RAM.

Verifikasi awal sebelum pembaruan rumus: 29 PASS, 259 assertions.
Pengujian rumus mencakup variasi 4/6/8/9/10 TP, STS/SAS, skor desimal,
nilai belum lengkap, tanpa TP relevan, isolasi rombel/semester, dan kesamaan Excel.
Hasil verifikasi setelah pembaruan rumus: 36 PASS, 0 FAIL, 403 assertions
(18 kasus DKN dan 18 test regresi Input Nilai), pada konfigurasi efektif
`testing`, `sqlite`, `:memory:`, tanpa URL override dan tanpa cache konfigurasi.
Pemeriksaan sintaks PHP, Pint, tiga route, serta `git diff --check` juga lulus.

Setelah tambahan spesifikasi alokasi 1–10 TP dan contoh 5 TP (STS 85, SAS 84):
**47 PASS, 0 FAIL, 657 assertions** (29 kasus DKN, 18 regresi Input Nilai).
Konfigurasi efektif diperiksa ulang: SQLite `:memory:` tanpa URL override,
environment testing, tanpa cache konfigurasi. Tahap alokasi hanya mengubah
file test DKN dan dokumentasi ini; kode produksi DKN tetap dipertahankan.

Verifikasi defensif sebelum staging: **52 PASS, 0 FAIL, 711 assertions**
(34 kasus DKN, 18 regresi Input Nilai) pada SQLite `:memory:` setelah konfigurasi
efektif diperiksa ulang. Tambahan mencakup nomor 0/11, nomor duplikat, isolasi
guru rombel A dari rombel B, gabungan role dengan cakupan berbeda, serta ekspor
kurikulum dengan filter kelas, rombel, dan gabungan keduanya. Nomor duplikat
disimulasikan pada keluaran service karena constraint unique pada database
normal mencegah penyimpanannya; tidak ada constraint yang diubah untuk test.
Format Excel data valid tetap sama.
