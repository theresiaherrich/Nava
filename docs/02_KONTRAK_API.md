# Kontrak backend tahap fondasi

Base URL lokal: `http://127.0.0.1:8000/api`.

Semua endpoint mengembalikan JSON. Auth memakai **cookie session**, bukan bearer token. Semua POST/PUT/PATCH memerlukan CSRF dan cookie dari session yang sama. Ambil token melalui GET `/csrf-token`. Perbarui token setelah login; ambil token kembali setelah logout. GET autentikasi dan GET data tidak membutuhkan CSRF header, tetapi tetap membutuhkan cookie.

## Endpoint autentikasi dan admin

| Method | Path | Akses | Body/hasil penting |
|---|---|---|---|
| GET | `/csrf-token` | Publik | `csrf_token` untuk session saat ini |
| POST | `/register` | Publik, rate limit | name, username, email, password, password_confirmation; 201 akun pending |
| POST | `/login` | Publik, rate limit | login (username/email), password; 200 data user + roles + CSRF baru |
| GET | `/me` | Login aktif | data akun dan roles; tanpa password/hash |
| POST | `/logout` | Login aktif | 200; session dibatalkan |
| GET | `/admin/roles` | Admin | Daftar role |
| GET | `/admin/users` | Admin | Pagination, optional `is_active=0/1` |
| PATCH | `/admin/users/{user}/access` | Admin | roles[], is_active, alasan; seluruh role diganti dengan daftar ini |
| PUT | `/admin/users/{user}/profile` | Admin | jenis siswa/guru, profil_id, alasan |
| GET | `/admin/logs` | Admin | Audit log dipaginasi, terbaru dahulu |

Rate limit: maksimal 5 request login/menit per identitas yang dinormalisasi, dan 20 request/menit per IP. Register dibatasi 5 request/menit per IP. Request login yang berhasil juga dihitung. Identitas email dan username memiliki bucket sendiri; pembatas IP tetap berlaku. Rate limit akun global dapat mengganggu login akun yang sedang diserang; sesuaikan kebijakan operasional saat deployment.

Register menerima field profil akun saja. `role`, `roles`, `role_id`, `is_active`, `user_id` ditolak. Field lain tidak disimpan. Daftar tanpa role dan nonaktif adalah keadaan yang disengaja; bukan otomatis menjadi siswa. Akun admin pertama dibuat melalui terminal.

## Data master

Untuk setiap resource berikut tersedia:

- GET `/{resource}`: daftar 20 per halaman, optional `q`, `page`.
- POST `/{resource}`: tambah record; 201.
- GET `/{resource}/{id}`: detail; 200 atau 404.
- PUT `/{resource}/{id}`: ubah record, kirim seluruh field wajib; 200.

Semua endpoint master hanya untuk **admin atau kurikulum**. `q` mencari kolom `nama`. `page` minimum 1. Tidak ada endpoint DELETE.

| Resource | Field wajib | Field opsional | Aturan penting |
|---|---|---|---|
| `tahun-ajaran` | nama, aktif | — | `YYYY/YYYY`, akhir = awal+1; nama unik |
| `semester` | tahun_ajaran_id, nama, aktif | — | ganjil/genap; kombinasi tahun+nama unik; tahun tidak boleh diganti |
| `kelas` | semester_id, nama, tingkat, fase | — | tingkat 10/11/12; E untuk X, F untuk XI/XII; semester tidak boleh diganti |
| `mapel` | kode, nama, aktif | — | kode unik |
| `siswa` | nis, nama, aktif | nisn, agama | nis unik, nisn null atau 10 digit unik; user_id dilarang |
| `guru` | nama, status_kepegawaian, aktif | nip | nip null atau unik; user_id dilarang |

Batas panjang: nama 255 (kelas 50), kode mapel 30, NIS/NIP 30, agama/status kepegawaian 50. Nilai boolean dalam JSON gunakan `true`/`false`; parameter query gunakan 1/0. Validasi null dan required tersedia pada class Request terkait. Untuk field nullable pada PUT, kirim `null` bila ingin mengosongkannya.

## Sekolah dan penempatan kelas

| Method | Path | Body atau filter | Aturan |
|---|---|---|---|
| GET | `/sekolah` | — | `data: null` bila belum diisi |
| PUT | `/sekolah` | npsn, nama, alamat, nama_kepala_sekolah | Satu sekolah ID 1; NPSN 8 digit |
| GET | `/siswa-kelas` | optional semester_id, kelas_id, page | Menyertakan data siswa dan kelas |
| POST | `/siswa-kelas` | siswa_id, kelas_id | Siswa aktif; semester diturunkan dari kelas; satu siswa per semester |

Akses tetap admin/kurikulum. Pengaitan `users` ke `siswa/guru` hanya melalui endpoint admin, bukan lewat mass assignment di endpoint master. Proses link tidak mengaktifkan akun dan tidak memberi role secara otomatis. Role harus cocok terlebih dahulu.

## Bentuk respons

Detail/create/update:

```json
{"data":{"id":1,"kode":"IF","nama":"Informatika","aktif":true}}
```

Daftar memakai paginator Laravel, bukan `{data: {data: ...}}`:

```json
{
  "current_page":1,
  "data":[{"id":1,"kode":"IF","nama":"Informatika","aktif":true}],
  "last_page":1,
  "per_page":20,
  "total":1
}
```

Ada field pagination tambahan dari Laravel. Registrasi mengembalikan `message` dan hanya `data.id`; login menambahkan `csrf_token`.

Contoh validasi:

```json
{
  "message":"...",
  "errors":{"email":["..."]}
}
```

Pesan validasi bawaan Laravel dapat berbahasa Inggris karena paket terjemahan Indonesia belum ditambahkan. Frontend sebaiknya membaca kode status dan key `errors`, bukan membandingkan isi kalimat.

| Status | Makna |
|---|---|
| 200 / 201 | Berhasil / record dibuat |
| 401 | Belum terautentikasi atau login gagal |
| 403 | Role tidak sesuai atau akun session sudah nonaktif |
| 404 | ID/resource tidak ditemukan |
| 419 | Token CSRF/session tidak valid |
| 422 | Input/aturan bisnis ditolak |
| 409 | Konflik constraint unik atau profil sudah tertaut |
| 429 | Rate limit |

## Batas akses tahap ini

| Role | Akun sendiri/login | Kelola master | Kelola akun/role/audit |
|---|---|---|---|
| admin | Ya | Ya | Ya |
| kurikulum | Ya | Ya | Tidak |
| guru | Ya | Belum ada endpoint scoped guru | Tidak |
| wali_kelas | Ya | Belum ada endpoint scoped wali kelas | Tidak |
| siswa | Ya | Tidak | Tidak |
| wali_siswa | Ya | Tidak | Tidak |
| kepala_sekolah | Ya | Belum ada dashboard pimpinan | Tidak |

Untuk fitur berikutnya, middleware role saja tidak cukup. Guru harus dibatasi ke rombel yang ditugaskan; siswa ke datanya sendiri; wali siswa ke anak yang relasinya sudah diverifikasi. Rencana ada di roadmap.
