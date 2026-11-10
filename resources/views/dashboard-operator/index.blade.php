@extends('layouts.app')

@section('title', 'Dashboard Operator')

@section('content')
@vite(['resources/css/dashboard-operator.css', 'resources/js/dashboard-operator.js'])

@php
    $namaPengguna   = $namaPengguna   ?? 'Pengguna';
    $sapaan         = $sapaan         ?? 'pagi';
    $tanggalHariIni = $tanggalHariIni ?? '-';
    $stats          = $stats          ?? [];
    $ortu           = $ortu           ?? ['terhubung' => 0, 'total' => 0, 'belum' => 0];
    $perlu          = collect($perlu ?? []);
    $aktivitas      = collect($aktivitas ?? []);

    $persenOrtu = ($ortu['total'] ?? 0) > 0 ? ($ortu['terhubung'] / $ortu['total']) * 100 : 0;
@endphp

<div class="do-page">

    {{-- HEADER --}}
    <header class="do-head">
        <div>
            <p class="do-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="do-title">Selamat {{ $sapaan }}, {{ $namaPengguna }}</h1>
            <p class="do-subtitle">Kelola data induk yang dipakai seluruh modul SIAKAD.</p>
        </div>
        <div class="do-date">{{ $tanggalHariIni }}</div>
    </header>

    {{-- STATISTIK --}}
    <div class="do-stats">
        <div class="do-card">
            <p class="do-label">Akun aktif</p>
            <p class="do-value">{{ $stats['akun_aktif'] ?? 0 }}</p>
            <p class="do-note">guru, siswa, orang tua, staf</p>
        </div>

        <div class="do-card">
            <p class="do-label">Siswa</p>
            <p class="do-value">{{ $stats['siswa'] ?? 0 }}</p>
            <p class="do-note">{{ $stats['siswa_keterangan'] ?? '' }}</p>
        </div>

        <div class="do-card">
            <p class="do-label">Guru</p>
            <p class="do-value">{{ $stats['guru'] ?? 0 }}</p>
            <p class="do-note">{{ $stats['guru_keterangan'] ?? '' }}</p>
        </div>

        <div class="do-card">
            <p class="do-label">Orang tua terhubung</p>
            <p class="do-value">{{ $ortu['terhubung'] ?? 0 }} <small>/ {{ $ortu['total'] ?? 0 }}</small></p>
            <div class="do-bar"><div class="do-bar-fill" style="width: {{ $persenOrtu }}%"></div></div>
            @if (($ortu['belum'] ?? 0) > 0)
                <p class="do-note is-warn"><strong>{{ $ortu['belum'] }}</strong> belum terhubung</p>
            @endif
        </div>
    </div>

    <div class="do-grid">

        {{-- KIRI --}}
        <div class="do-left">

            <section class="do-card">
                <div class="do-panel-head">
                    <h2 class="do-panel-title">Perlu ditindaklanjuti</h2>
                    <span class="do-count">{{ $perlu->count() }}</span>
                </div>

                @forelse ($perlu as $item)
                    <div class="do-todo">
                        <span class="do-todo-icon tone-{{ $item['tone'] ?? 'blue' }}">●</span>
                        <span class="do-todo-text">{{ $item['judul'] ?? '-' }}</span>
                        <a href="{{ $item['url'] ?? '#' }}" class="do-link">{{ $item['aksi'] ?? 'Lihat' }} →</a>
                    </div>
                @empty
                    <p class="do-empty">Tidak ada yang perlu ditindaklanjuti.</p>
                @endforelse
            </section>

            <section class="do-card">
                <h2 class="do-panel-title">Aksi cepat</h2>
                <div class="do-quick">
                    <a href="#" class="do-quick-item">
                        <span class="do-quick-title">Impor siswa</span>
                        <span class="do-quick-sub">Dari berkas Excel</span>
                    </a>
                    <a href="#" class="do-quick-item">
                        <span class="do-quick-title">Impor guru</span>
                        <span class="do-quick-sub">ASN & GTT</span>
                    </a>
                    <a href="#" class="do-quick-item">
                        <span class="do-quick-title">Tambah akun</span>
                        <span class="do-quick-sub">Satu per satu</span>
                    </a>
                    <a href="#" class="do-quick-item">
                        <span class="do-quick-title">Hubungkan orang tua</span>
                        <span class="do-quick-sub">Relasi orang tua–anak</span>
                    </a>
                </div>
            </section>

        </div>

        {{-- KANAN --}}
        <section class="do-card">
            <div class="do-panel-head">
                <h2 class="do-panel-title">Aktivitas terbaru</h2>
                <a href="#" class="do-link">Lihat semua</a>
            </div>

            @forelse ($aktivitas as $a)
                <div class="do-activity">
                    <span class="do-activity-icon tone-{{ $a['tone'] ?? 'gray' }}">•</span>
                    <div class="do-activity-body">
                        <p class="do-activity-title">{{ $a['judul'] ?? '-' }}</p>
                        <p class="do-activity-detail">{{ $a['detail'] ?? '' }}</p>
                    </div>
                    <span class="do-activity-time">{{ $a['waktu'] ?? '' }}</span>
                </div>
            @empty
                <p class="do-empty">Belum ada aktivitas.</p>
            @endforelse
        </section>

    </div>

</div>
@endsection