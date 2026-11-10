@extends('layouts.app')

@section('title', 'Dashboard Kurikulum')

@section('content')
@vite(['resources/css/dashboard-kurikulum.css', 'resources/js/dashboard-kurikulum.js'])

@php
    $namaPengguna   = $namaPengguna   ?? 'Pengguna';
    $sapaan         = $sapaan         ?? 'pagi';
    $tanggalHariIni = $tanggalHariIni ?? '-';
    $periode        = $periode        ?? null;
    $stats          = $stats          ?? [];
    $progres        = collect($progres ?? []);
    $kelasRows      = collect($kelasRows ?? []);
    $tingkat        = $tingkat        ?? 'semua';
    $q              = $q              ?? '';
    $page           = $page           ?? 1;
    $lastPage       = $lastPage       ?? 1;
    $totalKelas     = $totalKelas     ?? 0;
    $perlu          = collect($perlu ?? []);
    $statusRapor    = $statusRapor    ?? ['total' => 0, 'siap' => 0, 'terkunci' => 0, 'terbit' => 0, 'belum_siap' => 0];

    $toneVerif = ['selesai' => 'ok', 'berjalan' => 'info', 'belum' => 'muted'];
    $labelVerif = ['selesai' => 'Selesai', 'berjalan' => 'Berjalan', 'belum' => 'Belum'];
    $toneKunci = ['terkunci' => 'ok', 'koreksi' => 'purple', 'terbuka' => 'muted'];
    $labelKunci = ['terkunci' => 'Terkunci', 'koreksi' => 'Koreksi', 'terbuka' => 'Terbuka'];
    $labelRapor = ['belum_dibuat' => 'Belum dibuat', 'pdf_dibuat' => 'PDF dibuat', 'terbit' => 'Terbit'];

    $persenSiap = ($statusRapor['total'] ?? 0) > 0 ? ($statusRapor['siap'] / $statusRapor['total']) * 100 : 0;
    $persenTerkunci = ($statusRapor['total'] ?? 0) > 0 ? ($statusRapor['terkunci'] / $statusRapor['total']) * 100 : 0;
@endphp

<div class="dk-page">

    {{-- HEADER --}}
    <header class="dk-head">
        <div>
            <p class="dk-eyebrow">SMA Negeri 1 Kota Malang · Kurikulum</p>
            <h1 class="dk-title">Selamat {{ $sapaan }}, {{ $namaPengguna }}</h1>
            <p class="dk-subtitle">Pantau progres penilaian dan rapor seluruh kelas.</p>
        </div>
        <div class="dk-date">
            <span>{{ $tanggalHariIni }}</span>
        </div>
    </header>

    {{-- BANNER PERIODE --}}
    @if ($periode)
        <div class="dk-banner">
            <span class="dk-banner-icon">▦</span>
            <div>
                <p class="dk-banner-title">{{ $periode['nama'] ?? 'Periode aktif' }}</p>
                <p class="dk-banner-text">{{ $periode['keterangan'] ?? '' }}</p>
            </div>
        </div>
    @endif

    {{-- STATISTIK --}}
    <div class="dk-stats">
        <div class="dk-card">
            <p class="dk-label">Siswa aktif</p>
            <p class="dk-stat-value">{{ $stats['siswa_aktif'] ?? 0 }}</p>
            <p class="dk-note">{{ $stats['siswa_keterangan'] ?? '' }}</p>
        </div>
        <div class="dk-card">
            <p class="dk-label">Guru</p>
            <p class="dk-stat-value">{{ $stats['guru'] ?? 0 }}</p>
            <p class="dk-note">{{ $stats['guru_keterangan'] ?? '' }}</p>
        </div>
        <div class="dk-card">
            <p class="dk-label">Rombel tanpa pengampu</p>
            <p class="dk-stat-value is-warn">{{ $stats['rombel_tanpa_pengampu'] ?? 0 }}</p>
            <p class="dk-note">{{ $stats['rombel_keterangan'] ?? '' }}</p>
        </div>
    </div>

    {{-- PROGRES PENILAIAN STS --}}
    <section class="dk-card dk-progres">
        <h2 class="dk-panel-title">Progres Penilaian STS</h2>

        @foreach ($progres as $p)
            <div class="dk-progres-row">
                <p class="dk-progres-kelas">Kelas {{ $p['tingkat'] }}</p>

                <div class="dk-progres-item">
                    <div class="dk-progres-head"><span>Terisi</span><strong>{{ $p['terisi'] }}%</strong></div>
                    <div class="dk-bar"><div class="dk-bar-fill is-blue" style="width: {{ $p['terisi'] }}%"></div></div>
                </div>

                <div class="dk-progres-item">
                    <div class="dk-progres-head"><span>Terverifikasi</span><strong>{{ $p['terverifikasi'] }}%</strong></div>
                    <div class="dk-bar"><div class="dk-bar-fill is-purple" style="width: {{ $p['terverifikasi'] }}%"></div></div>
                </div>

                <div class="dk-progres-item">
                    <div class="dk-progres-head"><span>Terkunci</span><strong>{{ $p['terkunci'] }}%</strong></div>
                    <div class="dk-bar"><div class="dk-bar-fill is-green" style="width: {{ $p['terkunci'] }}%"></div></div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- BARIS BAWAH --}}
    <div class="dk-grid">

        {{-- TABEL STATUS PER KELAS --}}
        <section class="dk-card">
            <div class="dk-table-head">
                <div>
                    <h2 class="dk-panel-title">Status per Kelas</h2>
                    <p class="dk-note">STS Ganjil 2026/2027</p>
                </div>

                <form method="GET" action="{{ route('dashboard-kurikulum.index') }}" class="dk-filter" data-filter-form>
                    <select name="tingkat" class="dk-select" data-auto-submit>
                        <option value="semua" @selected($tingkat === 'semua')>Tingkat: Semua</option>
                        <option value="X" @selected($tingkat === 'X')>Tingkat: Kelas X</option>
                        <option value="XI" @selected($tingkat === 'XI')>Tingkat: Kelas XI</option>
                        <option value="XII" @selected($tingkat === 'XII')>Tingkat: Kelas XII</option>
                    </select>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari kelas..." class="dk-search" data-search>
                </form>
            </div>

            <div class="dk-table-wrap">
                <table class="dk-table">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Mapel disetujui</th>
                            <th>Verifikasi</th>
                            <th>Kunci</th>
                            <th>Rapor</th>
                            <th class="is-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kelasRows as $k)
                            @php
                                $total   = (int) ($k['mapel_total'] ?? 0);
                                $disetuj = (int) ($k['mapel_disetujui'] ?? 0);
                                $pct     = $total > 0 ? ($disetuj / $total) * 100 : 0;
                                $vKey    = $k['verifikasi'] ?? 'belum';
                                $kKey    = $k['kunci'] ?? 'terbuka';
                                $rKey    = $k['rapor'] ?? 'belum_dibuat';
                            @endphp
                            <tr>
                                <td class="dk-bold">{{ $k['kelas'] ?? '-' }}</td>
                                <td>{{ $k['wali_kelas'] ?? '-' }}</td>
                                <td>
                                    <div class="dk-mini">
                                        <span class="dk-mini-text">{{ $disetuj }}/{{ $total }}</span>
                                        <div class="dk-bar dk-bar-thin"><div class="dk-bar-fill is-green" style="width: {{ $pct }}%"></div></div>
                                    </div>
                                </td>
                                <td><span class="dk-chip {{ $toneVerif[$vKey] ?? 'muted' }}">{{ $labelVerif[$vKey] ?? '-' }}</span></td>
                                <td><span class="dk-chip {{ $toneKunci[$kKey] ?? 'muted' }}">{{ $labelKunci[$kKey] ?? '-' }}</span></td>
                                <td><span class="dk-chip muted">{{ $labelRapor[$rKey] ?? '-' }}</span></td>
                                <td class="is-right">
                                    <a href="{{ $k['url'] ?? '#' }}" class="dk-btn">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="dk-empty">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="dk-pager-row">
                <span class="dk-note">{{ $kelasRows->count() }} dari {{ $totalKelas }} kelas</span>

                @if ($lastPage > 1)
                    <nav class="dk-pager">
                        <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                           class="dk-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                        @for ($i = 1; $i <= $lastPage; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                               class="dk-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                           class="dk-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                    </nav>
                @endif
            </div>
        </section>

        {{-- KOLOM KANAN --}}
        <div class="dk-side">

            <section class="dk-card">
                <div class="dk-panel-head">
                    <h2 class="dk-panel-title">Perlu ditindaklanjuti</h2>
                    <span class="dk-count">{{ $perlu->count() }}</span>
                </div>

                @forelse ($perlu as $item)
                    <div class="dk-todo">
                        <span class="dk-todo-icon tone-{{ $item['tone'] ?? 'blue' }}">●</span>
                        <span class="dk-todo-text">{{ $item['judul'] ?? '-' }}</span>
                        <a href="{{ $item['url'] ?? '#' }}" class="dk-link">{{ $item['aksi'] ?? 'Lihat' }}</a>
                    </div>
                @empty
                    <p class="dk-empty-small">Tidak ada yang perlu ditindaklanjuti.</p>
                @endforelse
            </section>

            <section class="dk-card">
                <h2 class="dk-panel-title">Aksi cepat</h2>
                <div class="dk-quick">
                    <a href="#" class="dk-quick-item">Atur periode</a>
                    <a href="#" class="dk-quick-item">Rombel & pengampu</a>
                    <a href="#" class="dk-quick-item">Cetak rapor</a>
                    <a href="#" class="dk-quick-item">Terbitkan rapor</a>
                </div>
            </section>

            <section class="dk-card">
                <h2 class="dk-panel-title">Status Rapor STS</h2>
                <p class="dk-note">{{ $statusRapor['siap'] }} kelas siap diunci · {{ $statusRapor['terkunci'] }} terkunci · {{ $statusRapor['terbit'] }} terbit</p>

                <div class="dk-stacked">
                    <div class="dk-stacked-fill is-blue" style="width: {{ $persenSiap }}%"></div>
                    <div class="dk-stacked-fill is-green" style="width: {{ $persenTerkunci }}%"></div>
                </div>

                <div class="dk-legend">
                    <span><i class="dot is-blue"></i> Siap diunci {{ $statusRapor['siap'] }}</span>
                    <span><i class="dot is-green"></i> Terkunci {{ $statusRapor['terkunci'] }}</span>
                    <span><i class="dot is-teal"></i> Terbit {{ $statusRapor['terbit'] }}</span>
                    <span><i class="dot is-gray"></i> Belum siap {{ $statusRapor['belum_siap'] }}</span>
                </div>
            </section>

        </div>
    </div>

</div>
@endsection