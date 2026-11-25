<?php

return [
    /*
    | Peran (roles.kode => label UI). Urutan = prioritas peran aktif default saat login.
    */
    'roles' => [
        'SUPERADMIN' => 'Super Admin',
        'ADMIN' => 'Operator',
        'KURIKULUM' => 'Admin Kurikulum',
        'KESISWAAN' => 'Admin Kesiswaan',
        'WALI_KELAS' => 'Wali Kelas',
        'GURU' => 'Guru Mapel',
        'BUDAYA_POSITIF' => 'Guru BK',
        'SISWA' => 'Siswa',
        'ORANG_TUA' => 'Orang Tua',
    ],

    // Hanya lewat `php artisan siakad:create-superadmin`.
    'role_khusus_cli' => ['SUPERADMIN'],

    // Diberikan/dicabut otomatis lewat penetapan wali kelas; tidak bisa dipilih manual.
    'role_otomatis' => ['WALI_KELAS'],

    /*
    | Peran yang boleh DIKELOLA (dibuat, diubah, dinonaktifkan, reset sandi, diberi peran)
    | oleh tiap peran aktif. Akun target hanya boleh dikelola bila SEMUA perannya
    | (selain peran otomatis) ada di daftar ini.
    | Contoh bila Operator kelak boleh membuat akun siswa/guru:
    |   'ADMIN' => ['GURU', 'SISWA', 'ORANG_TUA'],
    */
    'role_manageable' => [
        'SUPERADMIN' => ['ADMIN', 'KURIKULUM', 'KESISWAAN', 'GURU', 'BUDAYA_POSITIF', 'SISWA', 'ORANG_TUA'],
        'ADMIN' => [],
    ],

    'password' => [
        'min' => 10,
        'panjang_acak' => 12,
    ],

    // Dipakai seeder development saja. Jangan isi di produksi.
    'seed_default_password' => env('SEED_DEFAULT_PASSWORD'),

    // Sandi akun demo per peran (DemoAccountSeeder, APP_ENV=local saja). Min. 12 karakter.
    'demo_password' => env('NAVA_DEMO_PASSWORD'),
    'demo_password_min' => 12,

    'kktp_default' => 75,

    // :tp diganti daftar deskripsi TP. Bisa ditimpa per mapel (mapel.deskripsi_kuat / deskripsi_penguatan).
    'deskripsi_capaian' => [
        'kuat' => 'Menunjukkan penguasaan yang baik dalam :tp.',
        'penguatan' => 'Perlu penguatan dalam :tp.',
    ],

    // Template rapor hanya boleh memakai view tepercaya berikut.
    'rapor_views' => [
        'rapor.sts' => 'Rapor Tengah Semester',
        'rapor.semester' => 'Rapor Semester',
    ],

    'rapor_disk' => env('SIAKAD_RAPOR_DISK', 'local'),

    'impor' => [
        'maks_baris' => 2000,
        'pratinjau_ttl_menit' => 30,
    ],
];
