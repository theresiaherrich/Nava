@extends('layouts.app')

@section('title', 'Nilai per Mapel')

@section('content')
@vite(['resources/css/nilai-per-mapel.css', 'resources/js/nilai-per-mapel.js'])

@php
    $periodeAktif   = $periodeAktif   ?? 'sts';
    $q              = $q              ?? '';
    $semesterTerbit = $semesterTerbit ?? false;
    $mapels         = $mapels         ?? collect();
    $totalMapel     = $totalMapel     ?? 0;
    $page           = $page           ?? 1;
    $lastPage       = $lastPage       ?? 1;

    $urlSts      = route('nilai-per-mapel.index', array_filter(['periode' => 'sts', 'q' => $q]));
    $urlSemester = route('nilai-per-mapel.index', array_filter(['periode' => 'semester', 'q' => $q]));

    $toneKategori = ['Wajib' => 'blue', 'Bahasa Asing' => 'purple', 'Agama' => 'green'];
    $labelPeriode = $periodeAktif === 'semester' ? 'Semester' : 'STS';
@endphp

<div class="np-page">

    <header class="np-head">
        <div>
            <p class="np-eyebrow">SMA Negeri 1 Kota Malang · Siswa</p>
            <h1 class="np-title">Nilai per Mata Pelajaran</h1>
            <p class="np-subtitle">Ringkasan nilai resmi dari Rapor {{ $labelPeriode }} Ganjil 2026/2027</p>
        </div>

        <div class="np-toolbar">
            <div class="np-tabs">
                <a href="{{ $urlSts }}" class="np-tab {{ $periodeAktif === 'sts' ? 'is-active' : '' }}">Rapor STS</a>

                @if ($semesterTerbit)
                    <a href="{{ $urlSemester }}" class="np-tab {{ $periodeAktif === 'semester' ? 'is-active' : '' }}">Rapor Semester</a>
                @else
                    <span class="np-tab is-disabled" tabindex="0" aria-disabled="true" data-tab-disabled>
                        Rapor Semester
                        <span class="np-tooltip">Belum terbit</span>
                    </span>
                @endif
            </div>

            <form method="GET" action="{{ route('nilai-per-mapel.index') }}" class="np-search" data-search-form>
                <input type="hidden" name="periode" value="{{ $periodeAktif }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari mapel..." data-search>
            </form>
        </div>
    </header>

    @if ($mapels->isEmpty())
        <div class="np-empty">
            <p class="np-empty-title">Belum ada nilai mata pelajaran</p>
            <p class="np-empty-text">
                @if ($q !== '')
                    Tidak ada mapel yang cocok dengan pencarian "{{ $q }}".
                @else
                    Nilai akan muncul setelah rapor {{ $labelPeriode }} diterbitkan oleh sekolah.
                @endif
            </p>
        </div>
    @else
        <div class="np-grid">
            @foreach ($mapels as $m)
                @php
                    $status = $m['status'] ?? 'tercapai';
                    $isWarn = $status !== 'tercapai';
                    $nilai  = (int) ($m['nilai'] ?? 0);
                    $tone   = $toneKategori[$m['kategori'] ?? ''] ?? 'sky';
                    $huruf  = mb_strtoupper(mb_substr($m['nama'] ?? '-', 0, 1));
                    $href   = isset($m['id']) ? route('nilai-per-mapel.show', $m['id']) : '#';
                @endphp

                <article class="np-card">
                    <div class="np-card-head">
                        <div class="np-avatar t-{{ $tone }}">{{ $huruf }}</div>
                        <div>
                            <p class="np-card-name">{{ $m['nama'] ?? '-' }}</p>
                            <p class="np-card-kategori">{{ $m['kategori'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="np-score-row">
                        <span class="np-score {{ $isWarn ? 'is-warn' : '' }}">{{ $nilai }}</span>
                        <span class="np-chip {{ $isWarn ? 'warn' : 'ok' }}">{{ $isWarn ? 'Perlu penguatan' : 'Tercapai' }}</span>
                    </div>

                    <div class="np-bar">
                        <div class="np-bar-fill {{ $isWarn ? 'is-warn' : '' }}" style="width: {{ min(100, max(0, $nilai)) }}%"></div>
                    </div>

                    <div class="np-card-foot">
                        <span>KKTP {{ $m['kktp'] ?? '-' }}</span>
                        <a href="{{ $href }}" class="np-link">Lihat detail ›</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="np-foot">
            <span class="np-count">Menampilkan {{ $mapels->count() }} dari {{ $totalMapel }} mata pelajaran</span>

            @if ($lastPage > 1)
                <nav class="np-pager" aria-label="Halaman">
                    <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                       class="np-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}" aria-label="Sebelumnya">‹</a>

                    @for ($i = 1; $i <= $lastPage; $i++)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                           class="np-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                    @endfor

                    <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                       class="np-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}" aria-label="Berikutnya">›</a>
                </nav>
            @endif
        </div>
    @endif

</div>
@endsection