@extends('layouts.app')

@section('title', 'Beranda Akademik')

@section('content')
@vite(['resources/css/beranda-akademik.css', 'resources/js/beranda-akademik.js'])

@php
    $siswa          = $siswa          ?? null;
    $statusSts      = $statusSts      ?? ['terbit' => false];
    $statusSemester = $statusSemester ?? ['terbit' => false];
    $mapels         = collect($mapels ?? []);
    $totalMapel     = $totalMapel     ?? 0;
    $tercapai       = $tercapai       ?? 0;
    $perluPenguatan = $perluPenguatan ?? 0;
    $rataRata       = $rataRata       ?? null;
    $namaPerlu      = $namaPerlu      ?? '';
    $tanggalHariIni = $tanggalHariIni ?? '-';

    $namaSiswa = $siswa['nama'] ?? 'Siswa';
    $namaDepan = explode(' ', $namaSiswa)[0];
    $inisial   = collect(explode(' ', $namaSiswa))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');

    $stsTerbit      = (bool) ($statusSts['terbit'] ?? false);
    $semesterTerbit = (bool) ($statusSemester['terbit'] ?? false);
@endphp

<div class="ba-page">

    {{-- HEADER --}}
    <header class="ba-head">
        <div>
            <p class="ba-eyebrow">SMA Negeri 1 Kota Malang · Siswa</p>
            <h1 class="ba-title">Halo, {{ $namaDepan }} 👋</h1>
            <p class="ba-subtitle">Lihat rapor dan capaian belajarmu yang sudah diterbitkan sekolah.</p>
        </div>

        <div class="ba-date">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
            </svg>
            <span>Hari ini: <strong>{{ $tanggalHariIni }}</strong></span>
        </div>
    </header>

    {{-- BARIS ATAS: PROFIL + BANNER + STAT --}}
    <div class="ba-top">
        <section class="ba-card ba-profile">
            <div class="ba-profile-main">
                <div class="ba-avatar">{{ $inisial }}</div>
                <div>
                    <p class="ba-name">{{ $namaSiswa }}</p>
                    <p class="ba-nis">NIS {{ $siswa['nis'] ?? '-' }} · NISN {{ $siswa['nisn'] ?? '-' }}</p>
                </div>
            </div>

            <div class="ba-tags">
                <span class="ba-tag is-blue">Kelas {{ $siswa['kelas'] ?? '-' }}</span>
                <span class="ba-tag is-teal">Fase {{ $siswa['fase'] ?? '-' }}</span>
            </div>

            <p class="ba-wali">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 12 0v1"/>
                </svg>
                Wali kelas: {{ $siswa['wali_kelas'] ?? '-' }}
            </p>
        </section>

        <div class="ba-right">
            @if ($stsTerbit)
                <section class="ba-banner is-success">
                    <span class="ba-banner-icon">✓</span>
                    <div class="ba-banner-body">
                        <p class="ba-banner-title">{{ $statusSts['nama'] ?? 'Rapor STS' }} sudah terbit</p>
                        <p class="ba-banner-text">Diterbitkan {{ $statusSts['tanggal'] ?? '-' }}{{ ! empty($statusSts['waktu']) ? ', ' . $statusSts['waktu'] : '' }}.</p>
                    </div>
                    <a href="{{ route('rapor-saya.index') }}" class="ba-btn-primary">Lihat rapor →</a>
                </section>
            @else
                <section class="ba-banner is-info">
                    <span class="ba-banner-icon">◷</span>
                    <div class="ba-banner-body">
                        <p class="ba-banner-title">Belum ada rapor yang diterbitkan untuk semester ini.</p>
                        <p class="ba-banner-text">{{ $statusSts['nama'] ?? 'Rapor STS' }} akan muncul di sini setelah diterbitkan oleh sekolah.</p>
                    </div>
                </section>
            @endif

            <div class="ba-stats">
                <div class="ba-stat">
                    <p class="ba-stat-label">Rata-rata Rapor STS</p>
                    <p class="ba-stat-value">{{ $rataRata !== null ? number_format($rataRata, 1, ',', '.') : '—' }}</p>
                    <p class="ba-stat-note">{{ $totalMapel > 0 ? 'dari ' . $totalMapel . ' mata pelajaran' : 'belum ada rapor terbit' }}</p>
                </div>

                <div class="ba-stat">
                    <p class="ba-stat-label">Mapel tercapai KKTP</p>
                    <p class="ba-stat-value is-ok">
                        {{ $totalMapel > 0 ? $tercapai : '—' }}
                        @if ($totalMapel > 0)<small>dari {{ $totalMapel }}</small>@endif
                    </p>
                    <p class="ba-stat-note">{{ $totalMapel > 0 ? 'semua TP mencapai KKTP' : 'belum ada rapor terbit' }}</p>
                </div>

                <div class="ba-stat">
                    <p class="ba-stat-label">Perlu penguatan</p>
                    <p class="ba-stat-value {{ $perluPenguatan > 0 ? 'is-warn' : '' }}">
                        {{ $totalMapel > 0 ? $perluPenguatan : '—' }}
                        @if ($totalMapel > 0)<small>mapel</small>@endif
                    </p>
                    <p class="ba-stat-note">{{ $namaPerlu !== '' ? $namaPerlu : 'belum ada rapor terbit' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS BAWAH: STATUS RAPOR + CAPAIAN --}}
    <div class="ba-bottom">
        <section class="ba-card ba-status">
            <h2 class="ba-panel-title">Status Rapor Semester Ini</h2>

            <div class="ba-status-list">
                <div class="ba-status-item">
                    <span class="ba-status-icon">▤</span>
                    <div class="ba-status-body">
                        <p class="ba-status-name">{{ $statusSts['nama'] ?? 'Rapor STS' }}</p>
                        <p class="ba-status-sub">{{ $statusSts['keterangan'] ?? '' }}</p>
                    </div>
                    @if ($stsTerbit)
                        <span class="ba-chip ok">✓ Terbit · {{ $statusSts['tanggal'] ?? '-' }}</span>
                        <a href="{{ route('rapor-saya.index') }}" class="ba-link">Buka</a>
                    @else
                        <span class="ba-chip">Belum terbit</span>
                    @endif
                </div>

                <div class="ba-status-item">
                    <span class="ba-status-icon">▤</span>
                    <div class="ba-status-body">
                        <p class="ba-status-name">{{ $statusSemester['nama'] ?? 'Rapor Semester' }}</p>
                        <p class="ba-status-sub">{{ $statusSemester['keterangan'] ?? '' }}</p>
                    </div>
                    @if ($semesterTerbit)
                        <span class="ba-chip ok">✓ Terbit · {{ $statusSemester['tanggal'] ?? '-' }}</span>
                        <a href="{{ route('rapor-saya.index') }}" class="ba-link">Buka</a>
                    @else
                        <span class="ba-chip">Belum terbit</span>
                    @endif
                </div>
            </div>
        </section>

        <section class="ba-card ba-capaian">
            <div class="ba-panel-head">
                <h2 class="ba-panel-title">Capaian per Mapel (Rapor STS)</h2>
                @if ($mapels->isNotEmpty())
                    <a href="{{ route('nilai-per-mapel.index') }}" class="ba-link">Lihat semua mapel →</a>
                @endif
            </div>

            @if ($mapels->isEmpty())
                <div class="ba-empty">
                    <div class="ba-empty-icon">▦</div>
                    <p class="ba-empty-title">Belum ada nilai</p>
                    <p class="ba-empty-text">Nilai akan muncul setelah rapor STS diterbitkan oleh sekolah.</p>
                </div>
            @else
                <div class="ba-mapel-list">
                    @foreach ($mapels as $m)
                        @php
                            $warn  = ($m['status'] ?? 'tercapai') !== 'tercapai';
                            $nilai = (int) ($m['nilai'] ?? 0);
                        @endphp
                        <div class="ba-mapel-row">
                            <span class="ba-mapel-name">{{ $m['nama'] ?? '-' }}</span>
                            <span class="ba-mapel-score {{ $warn ? 'is-warn' : '' }}">{{ $nilai }}</span>
                            <div class="ba-bar">
                                <div class="ba-bar-fill {{ $warn ? 'is-warn' : '' }}" style="width: {{ min(100, max(0, $nilai)) }}%"></div>
                            </div>
                            <span class="ba-chip {{ $warn ? 'warn' : 'ok' }}">{{ $warn ? 'Perlu penguatan' : 'Tercapai' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <p class="ba-footnote">🔒 Nilai yang ditampilkan hanya nilai resmi yang sudah diterbitkan. Nilai yang masih diproses guru tidak ditampilkan.</p>

</div>
@endsection