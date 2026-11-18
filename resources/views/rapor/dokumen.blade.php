<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor {{ $sts ? 'STS' : 'Semester' }}</title>
    <style>
        @page { margin: 28pt 36pt; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #111; }
        h1, header, .center { text-align: center; }
        h1 { font-size: 13pt; margin: 12pt 0 2pt; }
        h2 { font-size: 10pt; margin: 12pt 0 4pt; }
        header { border-bottom: 3pt double #111; padding: 4pt 44pt 10pt; position: relative; }
        header img { width: 36pt; position: absolute; left: 0; top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        th, td { border: .6pt solid #222; padding: 4pt; }
        th { background: #efefef; }
        table.identitas td, table.tanda-tangan td { border: 0; }
        .tanda-tangan td { width: 33%; text-align: center; vertical-align: top; }
        .tanda-tangan p { margin-top: 32pt; }
        .halaman { page-break-after: always; }
        .halaman:last-child { page-break-after: auto; }
        .catatan { border: .6pt solid #444; padding: 5pt; }
        .nomor { width: 5%; }
        .nilai { width: 8%; text-align: center; }
    </style>
</head>
<body>
@foreach ($items as $d)
    @php
        $kolomTp = collect($d['mapel'])->flatMap(fn ($m) => array_keys($m['tp'] ?? []))->unique()->sort()->values();
        $angka = fn ($n) => $n === null ? '–' : rtrim(rtrim(number_format((float) $n, 2, ',', ''), '0'), ',');
    @endphp
    <article class="halaman">
        <header>
            <img src="{{ public_path('images/logo-siakad.png') }}" alt="">
            <div>PEMERINTAH PROVINSI JAWA TIMUR · DINAS PENDIDIKAN</div>
            <strong>{{ mb_strtoupper($d['sekolah']['nama']) }}</strong><br>
            {{ $d['sekolah']['alamat'] }}<br>
            @if ($d['sekolah']['npsn']) NPSN {{ $d['sekolah']['npsn'] }} @endif
        </header>
        <h1>{{ $d['judul'] ?: ($sts ? 'LAPORAN HASIL BELAJAR TENGAH SEMESTER' : 'LAPORAN HASIL BELAJAR') }}</h1>
        <div class="center">Tahun Ajaran {{ $d['semester']['tahun_ajaran'] }}</div>
        <table class="identitas">
            <tr><td>Nama Peserta Didik</td><td>{{ $d['siswa']['nama'] }}</td><td>Kelas</td><td>{{ $d['kelas']['nama'] }} / {{ $d['kelas']['fase'] }}</td></tr>
            <tr><td>NIS / NISN</td><td>{{ $d['siswa']['nis'] }} / {{ $d['siswa']['nisn'] ?? '–' }}</td><td>Semester</td><td>{{ $d['semester']['nama'] }}</td></tr>
        </table>
        <h2>Nilai Mata Pelajaran</h2>
        <table>
            <thead><tr>
                <th class="nomor">No</th><th>Mata Pelajaran</th>
                @foreach ($kolomTp as $urutan) <th class="nilai">TP{{ $urutan }}</th> @endforeach
                <th class="nilai">{{ $sts ? 'Nilai STS' : 'Nilai Akhir' }}</th>
                @unless ($sts) <th>Capaian Kompetensi</th> @endunless
            </tr></thead>
            <tbody>
            @foreach ($d['mapel'] as $mapel)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td><td>{{ $mapel['mapel'] }}</td>
                    @foreach ($kolomTp as $urutan) <td class="nilai">{{ $angka($mapel['tp'][$urutan] ?? null) }}</td> @endforeach
                    <td class="nilai"><strong>{{ $angka($mapel['nilai']) }}</strong></td>
                    @unless ($sts) <td>{{ $mapel['deskripsi'] ?? '–' }}</td> @endunless
                </tr>
            @endforeach
            </tbody>
        </table>
        @unless ($sts)
            @if ($d['kokurikuler'])
                <h2>Kokurikuler</h2><div class="catatan">{{ $d['kokurikuler'] }}</div>
            @endif
            <h2>Ekstrakurikuler</h2>
            <table>
                <thead><tr><th>Kegiatan</th><th>Predikat</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse ($d['ekskul'] as $ekskul)
                        <tr><td>{{ $ekskul['nama'] }}</td><td class="center">{{ $ekskul['predikat'] }}</td><td>{{ $ekskul['catatan'] ?? '–' }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="center">–</td></tr>
                    @endforelse
                </tbody>
            </table>
            <h2>Ketidakhadiran</h2>
            <table><tr>
                <td>Sakit {{ $d['kehadiran']['sakit'] ?? '–' }}</td>
                <td>Izin {{ $d['kehadiran']['izin'] ?? '–' }}</td>
                <td>Tanpa keterangan {{ $d['kehadiran']['alpha'] ?? '–' }}</td>
            </tr></table>
            <h2>Catatan Wali Kelas</h2><div class="catatan">{{ $d['catatan_wali'] ?? '–' }}</div>
        @endunless
        <table class="tanda-tangan">
            <tr>
                <td>Mengetahui,<br>Orang Tua/Wali<p>_________________</p></td>
                @unless ($sts)
                    <td>Mengetahui,<br>Kepala Sekolah<p>{{ $d['sekolah']['kepala_sekolah'] ?? '_________________' }}</p></td>
                @endunless
                <td>{{ $d['tempat_cetak'] }}<br>Wali Kelas<p>{{ $d['wali_kelas']['nama'] ?? '_________________' }}</p></td>
            </tr>
        </table>
        <small>Versi {{ $d['versi'] }}</small>
    </article>
@endforeach
</body>
</html>
