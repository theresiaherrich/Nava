@props(['fitur' => null])

@php
    $user = auth()->user();
    $role = strtoupper($user?->role ?? '');
    $map = [];

    if (in_array($role, ['GURU', 'BK'])) {
        $peran = 'BK';
        $map = [
            'bk.rekap-tes*'  => 'Rekap Tes RIASEC',
            'bk.pengajuan*'  => 'Pengajuan BK',
            'bk.riwayat*'    => 'Riwayat Bimbingan',
            'bk.profil*'     => 'Profil Guru BK',
        ];
    } elseif ($role === 'ADMIN') {
        $peran = 'ADMIN';
    } else {
        $siswa = \App\Models\Siswa::where('id_user', $user?->id_user)->first();
        $namaKelas = $siswa?->riwayatKelasAktif?->kelas?->nama;
        $tingkat = $namaKelas ? \Illuminate\Support\Str::before($namaKelas, '-') : null;
        $peran = $tingkat ? 'KELAS ' . $tingkat : 'SISWA';
        $map = [
            'siswa.tes*'           => 'Tes RIASEC',
            'siswa.hasil*'         => 'Hasil & Rekomendasi',
            'siswa.mapel-pilihan*' => 'Mapel Pilihan',
            'siswa.riwayat-tes*'   => 'Riwayat Tes',
            'siswa.pengajuan-bk*'  => 'Pengajuan BK',
        ];
    }

    if (! $fitur) {
        foreach ($map as $pola => $nama) {
            if (request()->routeIs($pola)) { $fitur = $nama; break; }
        }
    }

    $label = collect(['SMA Negeri 1 Kota Malang', $peran, $fitur])->filter()->implode(' · ');
@endphp

<p {{ $attributes->merge(['class' => 'text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1']) }}>{{ $label }}</p>