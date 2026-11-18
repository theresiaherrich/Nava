<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Kumpulan Nilai</title>
    <style>
        body { font: 9pt DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: .5pt solid #222; padding: 4pt; }
    </style>
</head>
<body>
    <h1>Daftar Kumpulan Nilai {{ $dkn['periode']['jenis'] }} · {{ $dkn['kelas']['nama'] }}</h1>
    <table>
        <thead><tr><th>NIS</th><th>Nama</th>
            @foreach ($dkn['mapel'] as $m) <th>{{ $m['mapel'] }}</th> @endforeach
            <th>Rata-rata</th><th>Peringkat</th>
        </tr></thead>
        <tbody>
            @foreach ($dkn['siswa'] as $row)
                <tr><td>{{ $row['siswa']['nis'] }}</td><td>{{ $row['siswa']['nama'] }}</td>
                    @foreach ($dkn['mapel'] as $m) <td>{{ $row['nilai'][$m['id_rombel']] ?? '–' }}</td> @endforeach
                    <td>{{ $row['rata_rata'] ?? '–' }}</td><td>{{ $row['peringkat'] ?? '–' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
