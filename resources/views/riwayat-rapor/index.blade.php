@extends('layouts.app')

@section('title', 'Riwayat Rapor')

@section('content')
@vite(['resources/css/riwayat-rapor.css', 'resources/js/riwayat-rapor.js'])

@php
    $riwayat   = collect($riwayat ?? []);
    $tahunOpsi = collect($tahunOpsi ?? ['semua']);
    $tahun     = $tahun ?? 'semua';

    $adaKoreksi = $riwayat->contains(fn ($r) => (int) ($r['versi'] ?? 1) > 1);
@endphp

<div class="rr-page">

    <header class="rr-head">
        <p class="rr-eyebrow">SMA Negeri 1 Kota Malang · Siswa</p>
        <h1 class="rr-title">Riwayat Rapor</h1>
        <p class="rr-subtitle">Semua rapor yang pernah diterbitkan untukmu.</p>
    </header>

    {{-- FILTER --}}
    <div class="rr-filter">
        <form method="GET" action="{{ route('riwayat-rapor.index') }}" data-filter-form class="rr-filter-form">
            <label for="rr-tahun" class="rr-filter-label">Tahun ajaran</label>
            <div class="rr-select-wrap">
                <select id="rr-tahun" name="tahun" class="rr-select" data-auto-submit>
                    @foreach ($tahunOpsi as $t)
                        <option value="{{ $t }}" @selected($tahun === $t)>
                            {{ $t === 'semua' ? 'Semua' : $t }}
                        </option>
                    @endforeach
                </select>
                <svg class="rr-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <section class="rr-card">
        <div class="rr-table-wrap">
            <table class="rr-table">
                <thead>
                    <tr>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Jenis Rapor</th>
                        <th>Kelas</th>
                        <th>Tanggal Terbit</th>
                        <th>Status</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $r)
                        @php
                            $terbit   = ($r['status'] ?? 'belum_terbit') === 'terbit';
                            $versi    = (int) ($r['versi'] ?? 1);
                            $url      = $r['url'] ?? null;
                            $namaFile = \Illuminate\Support\Str::slug(($r['jenis'] ?? 'rapor') . '-' . ($r['tahun_ajaran'] ?? '')) . '.pdf';
                        @endphp

                        <tr>
                            <td>{{ $r['tahun_ajaran'] ?? '-' }}</td>
                            <td>{{ $r['semester'] ?? '-' }}</td>
                            <td>
                                <span class="{{ ($r['jenis'] ?? '') === 'Rapor STS' ? 'rr-jenis is-strong' : 'rr-jenis' }}">
                                    {{ $r['jenis'] ?? '-' }}
                                </span>
                                @if ($terbit && $versi > 1)
                                    <span class="rr-versi">Versi {{ $versi }} · diperbarui {{ $r['tanggal_update'] ?? '-' }}</span>
                                @endif
                            </td>
                            <td>{{ $r['kelas'] ?? '-' }}</td>
                            <td>{{ $r['tanggal_terbit'] ?? '–' }}</td>
                            <td>
                                @if ($terbit)
                                    <span class="rr-chip ok">✓ Terbit</span>
                                @else
                                    <span class="rr-chip">Belum terbit</span>
                                @endif
                            </td>
                            <td class="is-right">
                                <div class="rr-actions">
                                    @if ($terbit && $url)
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="rr-btn">Lihat</a>
                                        <a href="{{ $url }}" download="{{ $namaFile }}" class="rr-btn rr-btn-primary">Unduh</a>
                                    @else
                                        <span class="rr-btn is-disabled" aria-disabled="true">Lihat</span>
                                        <span class="rr-btn rr-btn-primary is-disabled" aria-disabled="true">Unduh</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="rr-empty">Belum ada riwayat rapor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($adaKoreksi)
            <div class="rr-notice">
                <span class="rr-notice-icon">ⓘ</span>
                Sekolah melakukan koreksi nilai. Versi terbaru yang ditampilkan.
            </div>
        @endif

        <div class="rr-foot">
            🔒 Rapor yang sudah diterbitkan tidak bisa diubah, dan dapat diunduh kapan saja.
        </div>
    </section>

</div>
@endsection