@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
@vite(['resources/css/data-siswa.css', 'resources/js/data-siswa.js'])

@php
    $siswa       = collect($siswa ?? []);
    $totalSiswa  = $totalSiswa  ?? 0;
    $tingkat     = $tingkat     ?? 'semua';
    $kelas       = $kelas       ?? 'semua';
    $agama       = $agama       ?? 'semua';
    $q           = $q           ?? '';
    $page        = $page        ?? 1;
    $lastPage    = $lastPage    ?? 1;
    $daftarKelas = collect($daftarKelas ?? []);
    $daftarAgama = collect($daftarAgama ?? []);

    $opsiTingkat = ['semua' => 'Tingkat: Semua', 'X' => 'Tingkat: X', 'XI' => 'Tingkat: XI', 'XII' => 'Tingkat: XII'];
    $opsiKelas   = ['semua' => 'Kelas asal: Semua'] + $daftarKelas->mapWithKeys(fn ($k) => [$k => 'Kelas asal: ' . $k])->all();
    $opsiAgama   = ['semua' => 'Agama: Semua'] + $daftarAgama->mapWithKeys(fn ($a) => [$a => 'Agama: ' . $a])->all();
@endphp

<div class="ds-page">

    {{-- HEADER --}}
    <header class="ds-head">
        <div>
            <p class="ds-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="ds-title">Data Siswa</h1>
            <p class="ds-subtitle">Data induk siswa yang dipakai seluruh modul SIAKAD.</p>
        </div>

        <div class="ds-head-actions">
            <a href="{{ route('data-siswa.format') }}" class="ds-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v3h16v-3"/></svg>
                Unduh Format Excel
            </a>
            <button type="button" class="ds-btn-primary" data-mk-open="ds-impor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M4 17v3h16v-3"/></svg>
                Impor Excel
            </button>
            <button type="button" class="ds-btn-primary ds-btn-outline" data-mk-open="ds-tambah">+ Tambah Siswa</button>
        </div>
    </header>

    @if (session('status'))
        <div class="mk-alert">{{ session('status') }}</div>
    @endif

    {{-- FILTER --}}
    <div class="ds-card ds-filter-card">
        <form method="GET" action="{{ route('data-siswa.index') }}" class="ds-filter" data-filter-form>
            <x-dropdown name="tingkat" :value="$tingkat" :options="$opsiTingkat" autosubmit />
            <x-dropdown name="kelas"   :value="$kelas"   :options="$opsiKelas"   autosubmit />
            <x-dropdown name="agama"   :value="$agama"   :options="$opsiAgama"   autosubmit />

            <div class="ds-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, NIS, atau NISN..." class="ds-search" data-search>
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <section class="ds-card ds-table-card">
        <div class="ds-table-head">
            <h2 class="ds-panel-title">Daftar Siswa</h2>
            <p class="ds-note">{{ $totalSiswa }} siswa aktif · urut nama A–Z</p>
        </div>

        <div class="ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th class="is-center">No</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>L/P</th>
                        <th>Agama</th>
                        <th>Kelas Asal</th>
                        <th>Orang Tua</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($siswa as $i => $s)
                        <tr>
                            <td class="is-center ds-muted">{{ ($page - 1) * 10 + $i + 1 }}</td>
                            <td class="ds-muted">{{ $s['nis'] ?? '-' }}</td>
                            <td class="ds-muted">{{ $s['nisn'] ?? '-' }}</td>
                            <td class="ds-bold">{{ $s['nama'] ?? '-' }}</td>
                            <td>{{ $s['lp'] ?? '-' }}</td>
                            <td>{{ $s['agama'] ?? '-' }}</td>
                            <td><span class="ds-kelas">{{ $s['kelas_asal'] ?? '-' }}</span></td>
                            <td>
                                @if (($s['orang_tua'] ?? 'belum') === 'terhubung')
                                    <span class="ds-chip ok">Terhubung</span>
                                @else
                                    <span class="ds-chip warn">Belum</span>
                                @endif
                            </td>
                            <td class="is-right">
                                <div class="ds-actions">
                                    <button type="button" class="ds-icon-btn" aria-label="Ubah" title="Ubah">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </button>
                                    <button type="button" class="ds-icon-btn" aria-label="Lihat" title="Lihat">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="ds-empty">Belum ada data siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ds-foot">
            <span class="ds-note">ⓘ Kelas asal = kelas administratif siswa pada semester ini.</span>

            <div class="ds-foot-right">
                <span class="ds-note">{{ $siswa->count() }} dari {{ $totalSiswa }} siswa</span>

                @if ($lastPage > 1)
                    <nav class="ds-pager">
                        <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                           class="ds-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                        @for ($i = 1; $i <= $lastPage; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                               class="ds-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                           class="ds-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                    </nav>
                @endif
            </div>
        </div>
    </section>

</div>

@include('components.modal-impor', [
    'id'        => 'ds-impor',
    'judul'     => 'Impor Data Siswa',
    'aksi'      => route('data-siswa.impor'),
    'formatUrl' => route('data-siswa.format'),
])

@include('components.modal-tambah', [
    'id'     => 'ds-tambah',
    'judul'  => 'Tambah Siswa',
    'aksi'   => route('data-siswa.store'),
    'fields' => [
        ['name' => 'nis',        'label' => 'NIS',           'required' => true],
        ['name' => 'nisn',       'label' => 'NISN',          'required' => true, 'placeholder' => '10 digit'],
        ['name' => 'nama',       'label' => 'Nama lengkap',  'required' => true, 'full' => true],
        ['name' => 'lp',         'label' => 'L/P', 'type' => 'select', 'required' => true,
         'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
        ['name' => 'agama',      'label' => 'Agama',         'required' => true],
        ['name' => 'kelas_asal', 'label' => 'Kelas asal',    'required' => true, 'full' => true, 'placeholder' => 'contoh: X-B'],
    ],
])

@include('components.modal-kit')
@endsection