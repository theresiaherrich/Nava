@extends('layouts.app')

@section('title', 'Dashboard Kelas')

@section('content')
@vite(['resources/css/dashboard-kelas.css', 'resources/js/dashboard-kelas.js'])

@php
    $pengguna          = $pengguna ?? ['nama' => 'Pengguna', 'peran' => '', 'inisial' => null];
    $kelas             = $kelas ?? '-';
    $tahunAjaran       = $tahunAjaran ?? '';
    $daftarTahunAjaran = $daftarTahunAjaran ?? [];
    $tanggalHariIni    = $tanggalHariIni ?? '-';
    $notifikasi        = $notifikasi ?? [];
    $mapel             = collect($mapel ?? []);
    $periode           = $periode ?? null;
    $stats             = $stats ?? [];
    $langkah           = $langkah ?? [];
    $perluTindakan     = $perluTindakan ?? [];
    $pelengkap         = $pelengkap ?? null;
    $statusRapor       = $statusRapor ?? ['bisa_cetak' => false, 'judul' => '', 'keterangan' => ''];
    $linkVerifikasi    = $linkVerifikasi ?? '#';
    $linkPelengkap     = $linkPelengkap ?? '#';

    $statusLabel = [
        'menunggu'     => 'Menunggu verifikasi',
        'disetujui'    => 'Disetujui',
        'sedang_diisi' => 'Sedang diisi',
        'belum_tp'     => 'Belum ada TP',
        'dikembalikan' => 'Dikembalikan',
    ];
    $legenda = [
        'disetujui'    => 'Disetujui',
        'menunggu'     => 'Menunggu',
        'sedang_diisi' => 'Sedang diisi',
        'belum_tp'     => 'Belum ada TP',
        'dikembalikan' => 'Dikembalikan',
    ];

    $persen = fn ($a, $b) => $b > 0 ? round($a / $b * 100) : 0;

    $ic = [
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'chev'     => '<path d="m6 9 6 6 6-6"/>',
        'left'     => '<path d="m15 18-6-6 6-6"/>',
        'right'    => '<path d="m9 18 6-6-6-6"/>',
        'warn'     => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'pencil'   => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/>',
        'lock'     => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'printer'  => '<path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>',
    ];
    $svg = fn (string $n, string $c = 'dkl-icon') => '<svg class="'.$c.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($ic[$n] ?? '').'</svg>';
    $toneIcon = ['verify' => 'check', 'warn' => 'warn', 'note' => 'pencil'];
@endphp

<x-topbar
    :name="$pengguna['nama']"
    :role="$pengguna['peran']"
    :initials="$pengguna['inisial']"
    :academic-year="$tahunAjaran"
    :academic-years="$daftarTahunAjaran"
    :notifications="$notifikasi"
/>

<div class="dkl-page">

    {{-- HEADER HALAMAN --}}
    <header class="dkl-head">
        <div>
            <p class="dkl-eyebrow">SMA Negeri 1 Kota Malang · Wali Kelas</p>
            <h1 class="dkl-title">Dashboard Kelas {{ $kelas }}</h1>
            <p class="dkl-subtitle">Pantau kelengkapan nilai seluruh mata pelajaran kelas Anda sebelum rapor dicetak.</p>
        </div>
        <div class="dkl-date">
            {!! $svg('calendar') !!}
            <span>Hari ini · <strong>{{ $tanggalHariIni }}</strong></span>
        </div>
    </header>

    {{-- BANNER PERIODE --}}
    @if ($periode)
        <div class="dkl-banner">
            <span class="dkl-banner__icon">{!! $svg('calendar') !!}</span>
            <div class="dkl-banner__body">
                <p class="dkl-banner__title">{{ $periode['nama'] }}</p>
                <p class="dkl-banner__text">
                    Pengisian nilai ditutup {{ $periode['tutup'] }}.
                    @if (($stats['menunggu'] ?? 0) > 0)
                        {{ $stats['menunggu'] }} mata pelajaran menunggu verifikasi Anda.
                    @endif
                </p>
            </div>
            <a href="{{ $linkVerifikasi }}" class="dkl-banner__link">Periksa sekarang {!! $svg('arrow') !!}</a>
        </div>
    @endif

    {{-- KARTU RINGKASAN --}}
    <div class="dkl-stats">
        <div class="dkl-card dkl-stat">
            <p class="dkl-label">Jumlah siswa</p>
            <p class="dkl-stat__value">{{ $stats['siswa']['total'] ?? 0 }}</p>
            <p class="dkl-note">{{ $stats['siswa']['laki'] ?? 0 }} laki-laki · {{ $stats['siswa']['perempuan'] ?? 0 }} perempuan</p>
        </div>
        <div class="dkl-card dkl-stat">
            <p class="dkl-label">Mapel sudah dikirim</p>
            <p class="dkl-stat__value">{{ $stats['mapel_dikirim'] ?? 0 }}<small> / {{ $stats['mapel_total'] ?? 0 }}</small></p>
            <div class="dkl-bar dkl-bar--stat">
                <div class="dkl-bar__fill is-blue" style="width: {{ $persen($stats['mapel_dikirim'] ?? 0, $stats['mapel_total'] ?? 0) }}%"></div>
            </div>
        </div>
        <div class="dkl-card dkl-stat">
            <p class="dkl-label">Menunggu verifikasi</p>
            <p class="dkl-stat__value is-warn">{{ $stats['menunggu'] ?? 0 }}</p>
            <p class="dkl-note">perlu diperiksa</p>
        </div>
        <div class="dkl-card dkl-stat">
            <p class="dkl-label">Pelengkap rapor</p>
            <p class="dkl-stat__value">{{ $stats['pelengkap'] ?? 0 }}<small> / {{ $stats['siswa']['total'] ?? 0 }}</small></p>
            <p class="dkl-note">catatan wali terisi</p>
        </div>
    </div>

    {{-- STEPPER --}}
    <section class="dkl-card dkl-steps-card">
        <h2 class="dkl-panel-title">Langkah menuju Rapor STS</h2>
        <ol class="dkl-steps">
            @foreach ($langkah as $i => $l)
                <li class="dkl-step is-{{ $l['state'] }}">
                    <span class="dkl-step__dot">
                        @if ($l['state'] === 'selesai')
                            {!! $svg('check') !!}
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="dkl-step__text">
                        <span class="dkl-step__title">{{ $l['judul'] }}</span>
                        <span class="dkl-step__ket">{{ $l['keterangan'] }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </section>

    <div class="dkl-grid">

        {{-- TABEL KELENGKAPAN MATA PELAJARAN --}}
        <section class="dkl-card dkl-table-card" data-mapel>
            <div class="dkl-table-head">
                <div>
                    <h2 class="dkl-panel-title">Kelengkapan Mata Pelajaran</h2>
                    <p class="dkl-note">{{ $mapel->count() }} mapel · Kelas {{ $kelas }}</p>
                </div>
                <div class="dkl-filter">
                    <label class="dkl-select-wrap">
                        <span class="dkl-sr">Filter status</span>
                        <select class="dkl-select" data-mapel-status>
                            <option value="">Semua status</option>
                            @foreach ($statusLabel as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                        {!! $svg('chev', 'dkl-icon dkl-select__chev') !!}
                    </label>
                    <label class="dkl-search">
                        {!! $svg('search') !!}
                        <span class="dkl-sr">Cari mapel</span>
                        <input type="search" placeholder="Cari mapel..." data-mapel-search>
                    </label>
                </div>
            </div>

            <div class="dkl-table-wrap">
                <table class="dkl-table">
                    <thead>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <th>Guru Pengampu</th>
                            <th>TP</th>
                            <th>Nilai Terisi</th>
                            <th>Status</th>
                            <th>Diperbarui</th>
                            <th class="is-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mapel as $m)
                            @php
                                $p    = $persen($m['terisi'], $m['total']);
                                $fill = $p >= 100 ? 'is-green' : ($p > 0 ? 'is-orange' : '');
                            @endphp
                            <tr data-row data-status="{{ $m['status'] }}"
                                data-search="{{ mb_strtolower($m['nama'].' '.$m['guru'].' '.$m['kelompok']) }}">
                                <td>
                                    <p class="dkl-mapel">{{ $m['nama'] }}</p>
                                    <p class="dkl-mapel__sub">{{ $m['kelompok'] }}</p>
                                    @if ($m['catatan'])
                                        <p class="dkl-mapel__sub">{{ $m['catatan'] }}</p>
                                    @endif
                                </td>
                                <td class="dkl-guru">{{ $m['guru'] }}</td>
                                <td class="dkl-nowrap">
                                    @if ($m['tp'] > 0)
                                        {{ $m['tp'] }} TP · {{ $m['sts'] }} STS
                                    @else
                                        0 TP
                                    @endif
                                </td>
                                <td>
                                    <div class="dkl-fill">
                                        <div class="dkl-bar"><div class="dkl-bar__fill {{ $fill }}" style="width: {{ $p }}%"></div></div>
                                        <span>{{ $m['terisi'] }}/{{ $m['total'] }}</span>
                                    </div>
                                </td>
                                <td><span class="dkl-badge is-{{ $m['status'] }}">{{ $statusLabel[$m['status']] ?? $m['status'] }}</span></td>
                                <td class="dkl-muted dkl-nowrap">{{ $m['diperbarui'] ?? '—' }}</td>
                                <td class="is-right">
                                    {{-- TODO (backend): tautkan ke detail mapel, mis. route('verifikasi-nilai.show', $m['id']) --}}
                                    @if ($m['status'] === 'menunggu')
                                        <a href="{{ $linkVerifikasi }}" class="dkl-btn dkl-btn--primary">Periksa</a>
                                    @else
                                        <a href="{{ $linkVerifikasi }}" class="dkl-btn dkl-btn--outline">Lihat</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="dkl-empty" data-empty hidden>
                            <td colspan="7">Tidak ada mapel yang cocok dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="dkl-table-foot">
                <p class="dkl-note" data-mapel-info>Menampilkan {{ min(11, $mapel->count()) }} dari {{ $mapel->count() }} mapel</p>
                <ul class="dkl-legend">
                    @foreach ($legenda as $val => $lbl)
                        <li><span class="dkl-legend__dot is-{{ $val }}"></span>{{ $lbl }}</li>
                    @endforeach
                </ul>
                <nav class="dkl-pager" aria-label="Halaman tabel mapel" data-mapel-pager></nav>
            </div>
        </section>

        {{-- KOLOM KANAN --}}
        <aside class="dkl-side">

            {{-- Perlu tindakan --}}
            <section class="dkl-card">
                <div class="dkl-panel-head">
                    <h2 class="dkl-panel-title">Perlu tindakan</h2>
                    <span class="dkl-count">{{ count($perluTindakan) }}</span>
                </div>
                @forelse ($perluTindakan as $t)
                    <div class="dkl-todo">
                        <span class="dkl-todo__icon is-{{ $t['tone'] }}">{!! $svg($toneIcon[$t['tone']] ?? 'check') !!}</span>
                        <div class="dkl-todo__body">
                            <p class="dkl-todo__title">{{ $t['judul'] }}</p>
                            <p class="dkl-note">{{ $t['keterangan'] }}</p>
                        </div>
                        @if ($t['aksi'])
                            <a href="{{ $t['url'] }}" class="dkl-todo__link">{{ $t['aksi'] }}</a>
                        @endif
                    </div>
                @empty
                    <p class="dkl-note">Semua beres. Tidak ada tindakan yang perlu dilakukan.</p>
                @endforelse
            </section>

            {{-- Pelengkap rapor --}}
            @if ($pelengkap)
                <section class="dkl-card">
                    <h2 class="dkl-panel-title">Pelengkap Rapor</h2>

                    <div class="dkl-pl">
                        <div class="dkl-pl__row">
                            <span>Catatan wali kelas</span>
                            <strong>{{ $pelengkap['catatan']['terisi'] }}/{{ $pelengkap['catatan']['total'] }}</strong>
                        </div>
                        <div class="dkl-bar"><div class="dkl-bar__fill is-orange" style="width: {{ $persen($pelengkap['catatan']['terisi'], $pelengkap['catatan']['total']) }}%"></div></div>
                    </div>

                    <div class="dkl-pl">
                        <div class="dkl-pl__row">
                            <span>Kehadiran <span class="dkl-muted">(dari Presensi A1)</span></span>
                            @if ($pelengkap['kehadiran'])
                                <span class="dkl-badge is-disetujui">Tersedia</span>
                            @else
                                <span class="dkl-badge is-belum_tp">Belum tersedia</span>
                            @endif
                        </div>
                    </div>

                    <div class="dkl-pl">
                        <div class="dkl-pl__row">
                            <span>Predikat ekskul <span class="dkl-muted">(dari Kesiswaan)</span></span>
                            <strong>{{ $pelengkap['ekskul']['terisi'] }}/{{ $pelengkap['ekskul']['total'] }}</strong>
                        </div>
                        <div class="dkl-bar"><div class="dkl-bar__fill is-blue" style="width: {{ $persen($pelengkap['ekskul']['terisi'], $pelengkap['ekskul']['total']) }}%"></div></div>
                    </div>

                    <a href="{{ $linkPelengkap }}" class="dkl-btn dkl-btn--outline dkl-btn--block">Lengkapi pelengkap rapor</a>
                </section>
            @endif

            {{-- Status rapor STS --}}
            <section class="dkl-card">
                <h2 class="dkl-panel-title">Status Rapor STS</h2>
                <div class="dkl-rapor">
                    <span class="dkl-rapor__icon">{!! $svg('lock') !!}</span>
                    <div>
                        <p class="dkl-todo__title">{{ $statusRapor['judul'] }}</p>
                        <p class="dkl-note">{{ $statusRapor['keterangan'] }}</p>
                    </div>
                </div>
                {{-- TODO (backend): arahkan ke route cetak rapor kelas bila bisa_cetak = true --}}
                <button type="button" class="dkl-btn dkl-btn--print dkl-btn--block" @disabled(! $statusRapor['bisa_cetak'])>
                    {!! $svg('printer') !!} Cetak rapor kelas
                </button>
            </section>
        </aside>
    </div>
</div>
@endsection
