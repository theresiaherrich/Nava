# Hasil pengujian dan checklist lokal

Tanggal: 30 Oktober 2026.

## Pengujian yang benar-benar dijalankan

Lingkungan: Linux, PHP 8.5.12, Laravel 13.38.0. Database feature test: SQLite in-memory. Database HTTP: file SQLite sementara terpisah. Tidak memakai data sekolah asli.

| Pemeriksaan | Hasil |
|---|---|
| PHPUnit melalui `php artisan test` | 22 tes lulus, 96 assertions; 20 tes fitur baru + 2 tes bawaan |
| Flow HTTP dari definisi collection | 32 request sesuai status yang diharapkan |
| Pemeriksaan cookie/CSRF tambahan | 9 request sesuai harapan; pergantian token dan cookie saat login diverifikasi |
| Migration dari database kosong | Lulus di SQLite |
| Rollback dua migration tambahan dan migrate ulang | Lulus di SQLite |
| Formatter Laravel Pint | Lulus setelah format |
| `composer validate --no-check-publish` | Valid |
| `composer audit` | Tidak menemukan advisory pada dependency akhir saat pemeriksaan |
| Route list | 38 endpoint dengan prefix `/api` |

Flow collection dijalankan melalui klien HTTP Python dengan cookie jar dan substitusi variabel yang setara, menggunakan definisi request dari file collection. Aplikasi desktop Postman dan JavaScript sandbox Postman tidak dibuka di lingkungan ini; import dan script collection tetap perlu dicoba di Postman pada laptopmu.

SQLite membuktikan perilaku yang diuji, tetapi tidak menggantikan pemeriksaan MySQL. MySQL/Laragon Windows, browser frontend, email, PDF, impor Excel, dan deployment production belum diuji. Audit dependency juga bukan audit keamanan menyeluruh.

## Ringkasan pemeriksaan keamanan HTTP

1. GET me sebelum login → 401.
2. POST login tanpa CSRF → 419.
3. GET CSRF → 200 dan cookie tersimpan.
4. Login dengan token+cookie → 200; token/session berubah.
5. POST memakai CSRF lama setelah login → 419.
6. POST master menggunakan token baru → 201.
7. GET me sesudah login → 200.
8. Logout → 200.
9. GET me sesudah logout → 401.

## Checklist manual di MySQL Laragon

- [ ] Koneksi DBeaver berhasil pada host/port/user yang sama dengan `.env`.
- [ ] `composer check-platform-reqs` berhasil.
- [ ] `php artisan migrate --seed` berhasil pada database latihan kosong.
- [ ] `php artisan migrate:status` seluruh migration Ran.
- [ ] Admin pertama berhasil dibuat lewat command.
- [ ] Register membuat user nonaktif tanpa role.
- [ ] Login pending gagal; admin menyetujui; login sesudah persetujuan berhasil.
- [ ] Role siswa mendapat 403 untuk daftar semua siswa dan endpoint admin.
- [ ] Master tersimpan dengan ID yang tepat; data duplikat ditolak.
- [ ] Penempatan kedua pada semester yang sama ditolak.
- [ ] MySQL menampilkan foreign key dan unique constraint dengan benar.
- [ ] Password tidak tampil di respons API atau log aktivitas.
- [ ] Request perubahan data tanpa CSRF ditolak; logout memutus akses.
- [ ] Pengecekan ulang setelah restart Laragon berhasil.

Gunakan database latihan untuk alur collection. Tahun `2026/2027` dan kelas `X-1` dalam flow pertama harus belum ada. Jika mengulang pada database yang sama, ambil ID record existing, isi variables collection, dan lewati create untuk record itu. Jangan menghapus database tim hanya agar collection kembali hijau.

## Query verifikasi DBeaver (read-only)

```sql
SELECT id, name, username, email, is_active FROM users ORDER BY id;

SELECT u.username, r.name AS role_name
FROM users u
JOIN role_user ru ON ru.user_id = u.id
JOIN roles r ON r.id = ru.role_id
ORDER BY u.id, r.name;

SELECT sk.id, s.nis, s.nama AS siswa, k.nama AS kelas,
       sk.semester_id, k.semester_id AS semester_kelas
FROM siswa_kelas sk
JOIN siswa s ON s.id = sk.siswa_id
JOIN kelas k ON k.id = sk.kelas_id;

-- Harus 0 baris.
SELECT *
FROM siswa_kelas sk
JOIN kelas k ON k.id = sk.kelas_id
WHERE sk.semester_id <> k.semester_id;

-- Harus 0 baris.
SELECT siswa_id, semester_id, COUNT(*) AS jumlah
FROM siswa_kelas
GROUP BY siswa_id, semester_id
HAVING COUNT(*) > 1;

SELECT user_id, aksi, nama_entitas, entitas_id, alasan, created_at
FROM log_aktivitas
ORDER BY id DESC;
```

Untuk melihat constraint MySQL:

```sql
SHOW CREATE TABLE siswa_kelas;
SHOW CREATE TABLE role_user;
```

Jangan menggunakan SELECT password untuk dibagikan sebagai screenshot bukti. Bukti tugas cukup identitas dummy, role, status, response, dan hasil test.
