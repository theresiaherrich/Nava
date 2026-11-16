# Urutan fitur setelah fondasi

Rancangan ini mengikuti nama tabel pada gambar ERD. Item bertanda **belum** merupakan pekerjaan tahap berikutnya, bukan endpoint yang sudah bisa dipanggil. Tuntaskan setiap tahap sampai test akses dan aturan datanya lulus sebelum pindah tahap.

## 1. Peta cakupan

| Area/tabel ERD | Status dalam paket | Pekerjaan berikutnya |
|---|---|---|
| users, roles, role_user | Sudah | Lupa password, verifikasi email bila dibutuhkan, kebijakan pergantian password/session |
| log_aktivitas | Sudah untuk tindakan fondasi | Tambahkan audit nilai, verifikasi, penerbitan, koreksi |
| sekolah | Sudah, satu mitra | Profil sekolah sesuai data asli |
| tahun_ajaran, semester, kelas, mapel | Sudah | Kebijakan tepat satu periode aktif bila disepakati |
| siswa, guru | Sudah | Impor tervalidasi, koreksi tautan akun dengan audit |
| siswa_kelas | Sudah daftar + tambah | Mutasi kelas dan kenaikan kelas tanpa menghapus riwayat |
| wali_siswa | Belum | Verifikasi relasi wali-anak, hak lihat anak tertentu |
| wali_kelas | Belum | Penugasan guru untuk kelas pada semester tertentu |
| rombel, guru_rombel, siswa_rombel | Belum | Kelompok belajar per mapel, guru pengampu, peserta |
| periode_nilai | Belum | Periode STS/SAS, jadwal buka-tutup dan status |
| tp, kktp | Belum | Tujuan pembelajaran, kriteria dan bobot |
| nilai_tp, v_nilai_semester | Belum | Nilai, kelengkapan, agregasi sesuai kebijakan sekolah |
| paket_nilai, verifikasi_nilai | Belum | Submit, verifikasi, kunci, koreksi |
| ekskul, peserta_ekskul | Belum | Keikutsertaan dan predikat |
| pelengkap_rapor | Belum | Catatan wali, kokurikuler, rekap absensi |
| template_rapor, rapor | Belum | Versi template, snapshot final, PDF, penerbitan |

## 2. Keputusan implementasi yang perlu dipahami

- Register publik menghasilkan akun **pending, tanpa role**. Admin memeriksa identitas lalu memberi role. Jika sekolah menginginkan registrasi tertutup, ganti kebijakan menjadi admin/invitation; jangan membuka pemilihan admin pada form register.
- Tabel user tetap punya `email_verified_at`, `password_reset_tokens`, `sessions` bawaan Laravel. Keberadaan tabel bukan berarti flow reset/verifikasi sudah diimplementasikan.
- Username migrasi nullable untuk kompatibilitas akun lama; semua registrasi baru wajib mengisi username. Akun lama diberi `user_<id>` dan nonaktif sampai diverifikasi.
- `siswa.user_id` dan `guru.user_id` nullable serta unik. Profil akademik boleh ada sebelum akun login dibuat.
- `siswa_kelas.semester_id` dipertahankan sesuai ERD. Ada foreign key gabungan `(kelas_id, semester_id)` ke kelas dan unique `(siswa_id, semester_id)` untuk mencegah semester tidak cocok/penempatan ganda.
- Nama kelas unik per semester. Mapel master terpisah dari rombel yang berubah setiap semester.
- Data sekolah diasumsikan satu mitra. Ini belum sistem multi-sekolah; banyak tabel ERD memang tidak mempunyai `sekolah_id`.
- `aktif` pada tahun ajaran/semester saat ini flag record, belum aturan tunggal lintas record. Jangan menyimpulkan seluruh query otomatis memakai semester aktif; scope periode harus eksplisit.

## 3. Tahap A — penugasan dan rombel

**Tabel:** wali_kelas, rombel, guru_rombel, siswa_rombel.

Urutan kerja:

1. Tambahkan migration rombel dengan FK semester dan mapel. Validasi tingkat, jenis, kelompok rapor, urutan rapor, dan metode nilai sesuai keputusan sekolah.
2. Tambahkan pivot guru_rombel dengan primary/unique gabungan guru_id+rombel_id.
3. Buat penugasan wali kelas. Tentukan apakah satu kelas boleh memiliki satu atau lebih wali; jangan mengasumsikan dari gambar saja.
4. Buat peserta siswa_rombel. Ambil semester/mapel dari rombel dan cek siswa_kelas berasal dari semester yang sama. Kolom redundan harus konsisten.
5. Tambahkan model, relasi, request, policy, service transaksi, controller, route, dan test.

Contoh endpoint yang **direncanakan**: POST `/rombel`, PUT `/rombel/{id}/guru`, POST `/rombel/{id}/peserta`, PUT `/kelas/{id}/wali-kelas`.

Test minimal: guru tidak dapat mengambil penugasan sendiri, peserta beda semester ditolak, peserta duplikat ditolak, guru A tidak boleh mengakses roster rombel B, siswa tidak boleh membaca roster seluruh sekolah.

## 4. Tahap B — periode nilai, TP, dan KKTP

**Tabel:** periode_nilai, tp, kktp.

Tentukan bersama PM/guru sekolah:

- Arti `masuk_sts`, `is_sas`, dan `bobot` pada TP.
- Apakah STS/SAS menggunakan TP yang berbeda atau agregasi yang berbeda.
- Cara nilai dihitung ketika ada TP kosong, remedial, atau siswa tidak mengikuti TP tertentu.
- Rentang nilai, presisi, pembulatan, dan aturan predikat.

Implementasi: `tanggal_buka <= tanggal_tutup`; status dan jam buka diperiksa di backend; TP harus berada pada rombel yang benar; bobot tidak negatif; satu KKTP per TP bila itulah kebijakan yang disepakati. JSON `rentang_predikat` harus tervalidasi strukturnya, bukan diterima bebas.

Test: tanggal terbalik ditolak; TP rombel lain ditolak; perubahan bobot setelah nilai terkunci ditolak; hanya guru yang ditugaskan/kurikulum sesuai kebijakan boleh mengelola TP.

## 5. Tahap C — input nilai

**Tabel:** nilai_tp.

Urutan implementasi satu nilai:

1. Cek user aktif dan role guru.
2. Cek guru terdaftar di `guru_rombel`.
3. Cek `tp` dan `siswa_rombel` berasal dari rombel yang sama.
4. Cek periode masih terbuka dan paket belum dikirim/dikunci sesuai workflow.
5. Validasi nilai sesuai rentang sekolah, misalnya 0–100 jika disepakati.
6. Simpan dengan unique `(tp_id, siswa_rombel_id)`; kolom rombel diturunkan dari relasinya.
7. Set `diubah_oleh` dari user login, bukan request.
8. Simpan perubahan dan audit dalam satu transaksi.

Endpoint rencana: PUT `/rombel/{rombel}/nilai/{siswaRombel}/{tp}`. Jangan hanya mencari NilaiTp berdasarkan ID tanpa memeriksa scope guru/rombel.

Test wajib: nilai minimum/maksimum, di luar rentang, nilai null, guru bukan pengampu, siswa beda rombel, periode tutup, paket terkunci, input bersamaan, dan audit sebelum/sesudah.

## 6. Tahap D — kirim, verifikasi, kunci, dan koreksi

**Tabel:** paket_nilai, verifikasi_nilai.

Usulan alur untuk disepakati: `draft → dikirim → dikunci`; jika tidak valid, pemeriksa mengembalikan ke `perlu_perbaikan` lalu guru mengirim ulang. Setelah dikunci, pembukaan koreksi harus beralasan dan diaudit. Ini usulan state machine, bukan nilai enum final dari ERD.

Aturan minimum:

- Paket mengikat rombel, periode, dan kelas yang benar.
- Submit hanya jika nilai peserta/TP wajib sudah lengkap.
- User pemeriksa harus mempunyai scope penugasan yang cocok, bukan sekadar label role.
- Gunakan transaksi dan `lockForUpdate` untuk submit/lock agar dua request tidak menghasilkan keputusan bertentangan.
- Server mengisi dikirim_oleh, waktu_kirim, dikunci_oleh, waktu_kunci.
- Endpoint edit nilai selalu memeriksa status paket, bukan hanya tombol frontend yang disembunyikan.
- Riwayat verifikasi bersifat append; jangan menimpa keputusan lama.

Test: submit tidak lengkap ditolak, submit ganda idempoten/ditolak dengan jelas, guru tidak mengunci paket sendiri bila bukan wewenangnya, nilai terkunci tidak dapat diedit, koreksi wajib alasan.

## 7. Tahap E — perhitungan nilai semester

**Objek:** `v_nilai_semester` adalah view SQL, bukan tabel CRUD biasa.

Pada ERD, view berisi siswa_rombel_id, siswa_kelas_id, rombel_id, jumlah_tp, jumlah_tp_terisi, nilai_akhir. View belum menampilkan `periode_id`. Sebelum dibuat, pastikan apakah hasil berlaku satu semester penuh atau per STS/SAS. Jika dipakai per periode, tambahkan scope periode atau buat query/service yang menerima periode secara eksplisit; jangan menyamakan hasil STS dengan SAS tanpa aturan.

Jika disepakati bobot TP, rumus dasarnya bisa:

`nilai_akhir = jumlah(nilai × bobot) / jumlah(bobot TP yang berlaku)`

Rumus ini baru benar setelah aturan TP kosong, TP yang berlaku, dan pembulatan disepakati. Jangan otomatis menganggap nilai kosong sama dengan nol. Bedakan nilai `0` yang sah dengan nilai `null` yang belum diisi. Bila denominator 0, kembalikan null/error bisnis yang jelas. Untuk laporan final, kelengkapan wajib dicek sebelum penerbitan.

Test dengan data kecil yang bisa dihitung manual: dua TP berbobot berbeda, nilai nol, nilai kosong, semua bobot nol, dan perbedaan TP STS/SAS. Hasil JSON dan PDF harus konsisten.

## 8. Tahap F — pelengkap dan ekstrakurikuler

**Tabel:** ekskul, peserta_ekskul, pelengkap_rapor.

Pisahkan kewenangan wali kelas dari guru mapel. Wali kelas hanya mengisi kelas yang ditugaskan. `sakit`, `izin`, `alpa` harus bilangan nonnegatif; `sumber_presensi` dan periode harus jelas. Catatan/kokurikuler perlu batas panjang dan tidak boleh menerima HTML bebas bila nanti dirender ke PDF.

Tetapkan apakah peserta ekskul unik per siswa_kelas+ekskul dan bagaimana periode predikatnya direkam. Gambar belum menyertakan periode_nilai pada peserta_ekskul, sehingga perlu keputusan bila predikat berbeda pada STS/SAS.

## 9. Tahap G — rapor dan akses wali

**Tabel:** template_rapor, rapor, wali_siswa.

1. Simpan versi template yang jelas; hanya file template tepercaya yang boleh dirender.
2. Pastikan seluruh paket dan pelengkap yang dibutuhkan sudah final.
3. Buat snapshot `data_final`, bukan laporan yang berubah setiap kali data master diedit.
4. Simpan versi, tanggal rapor, penerbit, waktu terbit, status dan path PDF.
5. Simpan PDF pada disk privat; download melalui endpoint dengan policy, bukan file publik bebas diakses.
6. Siswa hanya boleh mengunduh rapornya sendiri.
7. Wali hanya boleh melihat rapor anak yang `wali_siswa.aktif` dan sudah diverifikasi oleh sekolah.
8. Koreksi menghasilkan versi baru; jangan diam-diam mengubah rapor yang sudah diterbitkan.

Dependency DomPDF dan Excel sudah ada dari proyek awal. Keberadaan package tidak berarti endpoint ekspor/import tersedia. Buat feature, policy, dan tes masing-masing.

Test: siswa A tidak dapat mengunduh rapor B walau mengetahui ID/path, wali belum diverifikasi ditolak, draft tidak tampil sebagai rapor terbit, publikasi ganda tidak membuat versi bentrok, snapshot lama tetap sama setelah nilai dikoreksi.

## 10. Urutan kerja pribadi yang realistis

| Sesi | Fokus | Bukti selesai |
|---|---|---|
| 1 | Setup dan koneksi database | Laravel hidup, migration Ran, DBeaver melihat tabel |
| 2 | Auth + persetujuan + RBAC | Flow Postman folder 01–03 berhasil |
| 3 | Data master dan penempatan | Folder 04 + tes duplikat/relasi berhasil |
| 4 | Rombel dan penugasan | Guru hanya dapat membuka rombel sendiri |
| 5 | TP, KKTP, periode | Aturan perhitungan disepakati dan dites |
| 6 | Input nilai | Edit aman dan lengkap, kasus lintas rombel ditolak |
| 7 | Verifikasi/kunci | State transition dan audit lulus |
| 8 | Pelengkap + rapor | PDF sesuai data final, akses siswa/wali terbatasi |

Ini urutan dependensi, bukan janji semuanya selesai dalam delapan hari. Pecah sesi sesuai jam kerja dan pembagian tim. Mulailah dengan setup hingga login berhasil; belum perlu mengetik semua tabel ERD sekaligus.
