<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 15px; margin: 0; text-align: center; }
        h2 { font-size: 12px; margin: 14px 0 4px; }
        .kop { text-align: center; border-bottom: 2px solid #111; padding-bottom: 6px; margin-bottom: 10px; }
        .kop small { display: block; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 3px 5px; text-align: left; }
        table.identitas td { border: 0; padding: 1px 3px; }
        .tengah { text-align: center; }
        .halaman { page-break-after: always; }
        .halaman:last-child { page-break-after: auto; }
        .kecil { font-size: 9px; color: #555; }
        .kotak { border: 1px solid #444; padding: 5px; min-height: 28px; }
    </style>
</head>
<body>
@foreach($items as $item)
    @php($d = $item['data'])
    <div class="halaman">
        <div class="kop">
            <h1>{{ $template->nama ?: 'LAPORAN HASIL BELAJAR' }}</h1>
            <strong>{{ $sekolah->nama }}</strong>
            <small>{{ $sekolah->alamat }}</small>
            @if($template->identitas_cetak)
                <small>{{ $template->identitas_cetak }}</small>
            @endif
        </div>

        <table class="identitas">
            <tr><td width="18%">Nama</td><td width="2%">:</td><td width="40%">{{ $d['siswa']['nama'] }}</td>
                <td width="14%">Kelas</td><td width="2%">:</td><td>{{ $d['kelas']['nama'] }} ({{ $d['kelas']['tingkat_label'] }})</td></tr>
            <tr><td>NIS / NISN</td><td>:</td><td>{{ $d['siswa']['nis'] }} / {{ $d['siswa']['nisn'] ?? '-' }}</td>
                <td>Semester</td><td>:</td><td>{{ $d['semester']['nama'] }}</td></tr>
            <tr><td>Periode</td><td>:</td><td>{{ $d['periode']['jenis'] }}</td>
                <td>Tahun ajaran</td><td>:</td><td>{{ $d['semester']['tahun_ajaran'] ?? '-' }}</td></tr>
        </table>

        <h2>A. Nilai Mata Pelajaran</h2>
        <table>
            <thead>
                <tr><th width="6%" class="tengah">No</th><th>Mata pelajaran</th><th width="16%" class="tengah">Nilai akhir</th><th width="16%" class="tengah">TP terisi</th></tr>
            </thead>
            <tbody>
            @forelse($d['mapel'] as $i => $m)
                <tr>
                    <td class="tengah">{{ $i + 1 }}</td>
                    <td>{{ $m['mapel'] }}</td>
                    <td class="tengah">{{ $m['nilai_akhir'] === null ? '-' : number_format($m['nilai_akhir'], 2, ',', '') }}</td>
                    <td class="tengah">{{ $m['jumlah_tp_terisi'] }}/{{ $m['jumlah_tp'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="tengah">Tidak ada data nilai.</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>B. Ekstrakurikuler</h2>
        <table>
            <thead><tr><th width="6%" class="tengah">No</th><th>Kegiatan</th><th width="16%" class="tengah">Predikat</th></tr></thead>
            <tbody>
            @forelse($d['ekskul'] as $i => $e)
                <tr><td class="tengah">{{ $i + 1 }}</td><td>{{ $e['nama'] }}</td><td class="tengah">{{ $e['predikat'] }}</td></tr>
            @empty
                <tr><td colspan="3" class="tengah">-</td></tr>
            @endforelse
            </tbody>
        </table>

        <h2>C. Ketidakhadiran</h2>
        <table>
            <tr>
                <th class="tengah">Sakit</th><th class="tengah">Izin</th><th class="tengah">Tanpa keterangan</th>
            </tr>
            <tr>
                <td class="tengah">{{ $d['kehadiran']['sakit'] ?? '-' }}</td>
                <td class="tengah">{{ $d['kehadiran']['izin'] ?? '-' }}</td>
                <td class="tengah">{{ $d['kehadiran']['alpha'] ?? '-' }}</td>
            </tr>
        </table>

        @if(!empty($d['kokurikuler']))
            <h2>D. Kokurikuler</h2>
            <div class="kotak">{{ $d['kokurikuler'] }}</div>
        @endif

        <h2>Catatan Wali Kelas</h2>
        <div class="kotak">{{ $d['catatan_wali'] ?: '-' }}</div>

        <p class="kecil">Versi dokumen {{ $item['versi'] }}</p>
    </div>
@endforeach
</body>
</html>
