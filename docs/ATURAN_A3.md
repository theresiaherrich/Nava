# Aturan Modul A3 (Peminatan & Bimbingan Konseling)

SIAKAD SMA Negeri 1 Malang. Dokumen ini adalah acuan untuk Antigravity / developer.
Jika desain (PDF) bertabrakan dengan aturan di sini, **aturan di sini yang menang**.

## 1. Sumber acuan
- Desain: `A3-Administrator.pdf`, `A3-BK.pdf`, `A3-kelas 10.pdf`, `A3-kelas 11.pdf`, `A3-kelas 12.pdf`
- Skema database final: `siakad_sma1_skema.sql` (hasil export database `siakad_sma1` yang sudah di-patch)
- Skema lama (hanya untuk perbandingan, JANGAN dipakai): `nava_lengkap_LAMA.sql`

## 2. Database
- Database yang dipakai: `siakad_sma1`. Tabel sudah ada.
- **Jangan** menjalankan `migrate`, `migrate:fresh`, atau `db:wipe`.
- **Jangan** membuat migration untuk tabel yang sudah ada. Migration lama dipindah ke `database/_old`.
- Model Eloquent memetakan tabel yang ada: set `$table`, `$primaryKey` (bukan `id`), dan `$timestamps` sesuai skema.
- Tabel login adalah `user` (bukan `users`): `username` atau `guru.nip`, `role`, `aktif`, `must_change_password`.
- Aksi penting dicatat ke `log_aktivitas`.

## 3. Role
| Role | Keterangan |
|---|---|
| `SISWA` | Jenjang ditentukan dari `riwayat_kelas -> kelas.tingkat` pada semester aktif (`semester.aktif = 1`) |
| `GURU_BK` | Akun `user` + profil di tabel `guru`. **Tidak ada tabel `guru_bk`.** |
| `ADMIN` | Kelola master data A3 |

Kartu "Guru BK Kelas X" di halaman Pengajuan BK siswa diambil dari tabel `penugasan_bk` (guru, semester, tingkat, `jadwal_layanan`).

## 4. Aturan per jenjang
| Jenjang | Tes RIASEC | Hasil |
|---|---|---|
| Kelas X | Boleh, **berkali-kali**, kapan saja selama semester aktif | 5 rekomendasi **mata pelajaran satuan** (bukan paket) |
| Kelas XI | **Tidak boleh** (403). Hanya melihat hasil dan riwayat tes saat kelas X | Read-only |
| Kelas XII | Boleh, **berkali-kali**, kapan saja selama semester aktif | 5 rekomendasi **program studi** + prospek profesi |

- **Tidak ada jadwal/periode tes serentak.** Tidak ada tabel, model, controller, atau halaman Periode Tes. Info "periode aktif" di dashboard = semester aktif.
- `sesi_tes.tingkat` hanya `X` atau `XII` dan harus sama dengan tingkat kelas siswa saat tes.
- Hasil terkini = sesi dengan `status = 'selesai'` dan `waktu_selesai` terbaru.
- Riwayat = semua sesi selesai siswa, lintas semester (lewat `riwayat_kelas`).
- Setiap tes baru membuat sesi baru dan set rekomendasi baru. Riwayat lama tetap tersimpan.

## 5. Tes RIASEC
- **12 soal aktif** (2 per dimensi) untuk kelas X dan XII. Skala Likert 1-5.
- Satu soal per halaman, progres "01 dari 12". Jawaban unik per `(sesi, soal)`.
- `skor_mentah` = jumlah nilai dimensi. `skor_normal` = `(mentah - n) / (4n) * 100`, n = jumlah soal dimensi itu.
- `kode_holland` = 3 dimensi dengan `skor_normal` tertinggi (tie-break urutan R, I, A, S, E, C).
- **Tidak ada julukan dimensi** ("Si Penjelajah", dst.). Pakai `dimensi_riasec.nama` + `deskripsi`.

## 6. Rekomendasi
- Kecocokan kandidat = `(3*skor(huruf1) + 2*skor(huruf2) + 1*skor(huruf3)) / 6`, dari `kode_riasec` 3 huruf milik kandidat.
- Kelas X: kandidat = `mapel` dengan `is_pilihan = 1`, `aktif = 1`, `status_validasi = 'tervalidasi'`. Simpan 5 teratas ke `rekomendasi_sepuluh`.
- Kelas XII: kandidat = `program_studi` aktif dan tervalidasi. Simpan 5 teratas ke `rekomendasi_dua_belas`.
- Peringkat 1-5, `skor` dalam persen, `alasan` berupa teks. Simpan dalam satu transaksi.
- Prospek profesi kelas XII lewat tabel `prodi_profesi` (many-to-many prodi dan profesi).
- Mapel mengacu ke tabel `mapel` (bukan daftar mapel terpisah). `mapel.id_dimensi` diisi otomatis dari huruf pertama `kode_riasec`. `kode_riasec` disimpan tanpa tanda hubung (mis. `ASC`).
- Jumlah mapel pilihan yang tervalidasi minimal 5, supaya rekomendasi bisa menghasilkan 5 mapel.

## 7. Pengajuan BK (tabel `pengajuan_bk`)
- Alur status: `menunggu -> diproses -> dijadwalkan -> selesai`, atau `ditolak`.
- `balasan` WAJIB untuk `selesai` dan `ditolak`.
- `dijadwalkan` WAJIB punya `tanggal_bimbingan` dan `jam_bimbingan`.
- `deskripsi` 20-500 karakter. `topik` disimpan sebagai teks (daftar topik diatur di config aplikasi).
- `id_guru` = Guru BK yang menangani. Tampil sebagai "BK {id_pengajuan}".
- Hanya siswa pemilik dan Guru BK yang boleh melihat.
- Riwayat Bimbingan = pengajuan berstatus `selesai` atau `ditolak` (tidak ada tabel terpisah).

## 8. Validasi profil RIASEC
- Mapel dan prodi punya `status_validasi` (`menunggu`, `tervalidasi`, `perlu_revisi`), `catatan_validasi`, `divalidasi_oleh`, `waktu_validasi`.
- Yang memvalidasi adalah Guru BK. Hanya yang `tervalidasi` masuk rekomendasi.

## 9. Dihitung, bukan disimpan
Total siswa, sudah tes, belum tes (siswa kelas X/XII tanpa sesi selesai), distribusi minat, persentase kesesuaian, dan tipe dominan dihitung dari data yang ada.

## 10. Perbedaan desain vs aturan (aturan menang)
- Halaman/menu **Periode Tes** di admin dihapus. Kartu "Periode Tes Aktif" diganti info semester aktif.
- Julukan dimensi di halaman Hasil Tes kelas X dihilangkan.
- "6 pertanyaan" di desain kelas XII adalah placeholder; yang benar 12 soal.
- Baris periode "Kelas XI" di admin tidak berlaku (XI tidak tes).
- Link "Lupa kata sandi?" belum punya tabel token reset; di luar cakupan sampai diputuskan.
