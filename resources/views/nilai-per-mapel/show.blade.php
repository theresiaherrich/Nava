@extends('layouts.app')

@section('title', 'Detail Nilai')

@section('content')
@vite(['resources/css/nilai-per-mapel.css', 'resources/js/nilai-per-mapel.js'])

@php
    $detail      = $detail ?? null;
    $tps         = $detail['tps'] ?? [];
    $tpTotal     = count($tps);
    $tpTercapai  = collect($tps)->where('capaian', 'tercapai')->count();
    $persenTp    = $tpTotal ? round($tpTercapai / $tpTotal * 100) : 0;
    $nilai       = (int) ($detail['nilai'] ?? 0);
    $kktp        = $detail['kktp'] ?? '-';
    $isWarnAkhir = ($detail['status'] ?? 'tercapai') !== 'tercapai';
@endphp

<div class="np-page">

    <a href="{{ route('nilai-per-mapel.index') }}" class="np-back">‹ Nilai per Mapel</a>

    @if (! $detail)
        <div class="np-empty">
            <p class="np-empty-title">Data mapel tidak ditemukan</p>
            <p class="np-empty-text">Mapel ini belum memiliki nilai rapor yang diterbitkan.</p>
        </div>
    @else
        <header class="np-head np-head-detail">
            <div>
                <p class="np-eyebrow">SMA Negeri 1 Kota Malang · Siswa</p>
                <h1 class="np-title">{{ $detail['nama'] ?? '-' }}</h1>
                <p class="np-subtitle">Rincian nilai resmi per tujuan pembelajaran.</p>
            </div>

            <div class="np-meta">
                <span class="np-pill">{{ $detail['kategori'] ?? '-' }}</span>
                <span class="np-pill">Guru: {{ $detail['guru'] ?? '-' }}</span>
                <span class="np-pill is-blue">Rapor STS Ganjil 2026/2027</span>
            </div>
        </header>

        <div class="np-stats">
            <div class="np-stat">
                <p class="np-stat-label">Nilai Rapor STS</p>
                <p class="np-stat-value {{ $isWarnAkhir ? 'is-warn' : '' }}">{{ $nilai }}</p>
                <p class="np-stat-note">nilai akhir mapel</p>
            </div>

            <div class="np-stat">
                <p class="np-stat-label">KKTP</p>
                <p class="np-stat-value">{{ $kktp }}</p>
                <p class="np-stat-note">kriteria ketercapaian TP</p>
            </div>

            <div class="np-stat">
                <p class="np-stat-label">TP tercapai</p>
                <p class="np-stat-value">{{ $tpTercapai }} <small>dari {{ $tpTotal }}</small></p>
                <div class="np-bar np-bar-thin">
                    <div class="np-bar-fill {{ $tpTercapai < $tpTotal ? 'is-warn' : '' }}" style="width: {{ $persenTp }}%"></div>
                </div>
                <p class="np-stat-note">TP yang masuk penilaian STS</p>
            </div>
        </div>

        <section class="np-panel">
            <h2 class="np-panel-title">Nilai per Tujuan Pembelajaran</h2>
            <p class="np-panel-sub">Hanya TP yang masuk penilaian STS yang ditampilkan.</p>

            <div class="np-table-wrap">
                <table class="np-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tujuan Pembelajaran</th>
                            <th class="is-right">Nilai</th>
                            <th class="is-right">KKTP</th>
                            <th class="is-right">Capaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tps as $tp)
                            @php $warnTp = ($tp['capaian'] ?? 'tercapai') !== 'tercapai'; @endphp
                            <tr class="{{ $warnTp ? 'is-warn' : '' }}">
                                <td><span class="np-kode">{{ $tp['kode'] ?? '-' }}</span></td>
                                <td>{{ $tp['tujuan'] ?? '-' }}</td>
                                <td class="is-right np-nilai">{{ $tp['nilai'] ?? '-' }}</td>
                                <td class="is-right">{{ $tp['kktp'] ?? '-' }}</td>
                                <td class="is-right">
                                    <span class="np-chip {{ $warnTp ? 'warn' : 'ok' }}">{{ $warnTp ? 'Perlu penguatan' : 'Tercapai' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="np-table-empty">Belum ada data tujuan pembelajaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="np-panel">
            <h2 class="np-panel-title">Deskripsi Capaian</h2>
            <div class="np-deskripsi">
                {{ $detail['deskripsi'] ?? 'Belum ada deskripsi capaian.' }}
                @if (! empty($detail['deskripsi_penguatan']))
                    <span class="np-warn-text">{{ $detail['deskripsi_penguatan'] }}</span>
                @endif
            </div>
        </section>
    @endif

</div>
@endsection