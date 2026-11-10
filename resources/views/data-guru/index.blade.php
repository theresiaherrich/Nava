@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
@vite(['resources/css/data-guru.css', 'resources/js/data-guru.js'])

@php
    $guru        = collect($guru ?? []);
    $totalGuru   = $totalGuru   ?? 0;
    $statistik   = $statistik   ?? ['asn' => 0, 'gtt' => 0];
    $daftarMapel = collect($daftarMapel ?? []);
    $peranMap    = $peranMap    ?? [];
    $status      = $status      ?? 'semua';
    $mapel       = $mapel       ?? 'semua';
    $q           = $q           ?? '';
    $page        = $page        ?? 1;
    $lastPage    = $lastPage    ?? 1;

    $opsiStatus = ['semua' => 'Status: Semua', 'asn' => 'Status: ASN', 'gtt' => 'Status: GTT'];
    $opsiMapel  = ['semua' => 'Mapel: Semua'] + $daftarMapel->mapWithKeys(fn ($m) => [$m => 'Mapel: ' . $m])->all();
@endphp

<div class="dg-page">

    {{-- HEADER --}}
    <header class="dg-head">
        <div>
            <p class="dg-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="dg-title">Data Guru</h1>
            <p class="dg-subtitle">Data guru ASN dan GTT memakai akun yang sama untuk seluruh modul.</p>
        </div>

        <div class="dg-head-actions">
            <a href="{{ route('data-guru.format') }}" class="dg-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v3h16v-3"/></svg>
                Unduh Format Excel
            </a>
            <button type="button" class="dg-btn-primary" data-mk-open="dg-impor">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 15V3m0 0-4 4m4-4 4 4M4 17v3h16v-3"/></svg>
                Impor Excel
            </button>
            <button type="button" class="dg-btn-primary dg-btn-outline" data-mk-open="dg-tambah">+ Tambah Guru</button>
        </div>
    </header>

    @if (session('status'))
        <div class="mk-alert">{{ session('status') }}</div>
    @endif

    {{-- FILTER --}}
    <div class="dg-card dg-filter-card">
        <form method="GET" action="{{ route('data-guru.index') }}" class="dg-filter" data-filter-form>
            <x-dropdown name="status" :value="$status" :options="$opsiStatus" autosubmit />
            <x-dropdown name="mapel"  :value="$mapel"  :options="$opsiMapel"  autosubmit />

            <span class="dg-count-chip asn">{{ $statistik['asn'] }} ASN</span>
            <span class="dg-count-chip gtt">{{ $statistik['gtt'] }} GTT</span>

            <div class="dg-search-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                </svg>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama atau NIP..." class="dg-search" data-search>
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <section class="dg-card dg-table-card">
        <div class="dg-table-head">
            <h2 class="dg-panel-title">Daftar Guru</h2>
            <p class="dg-note">{{ $totalGuru }} guru · urut nama A–Z</p>
        </div>

        <div class="dg-table-wrap">
            <table class="dg-table">
                <thead>
                    <tr>
                        <th class="is-center">No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Status</th>
                        <th>Peran</th>
                        <th>Mapel diampu</th>
                        <th>Akun</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guru as $i => $g)
                        <tr>
                            <td class="is-center dg-muted">{{ ($page - 1) * 10 + $i + 1 }}</td>
                            <td class="dg-bold">{{ $g['nama'] ?? '-' }}</td>
                            <td class="dg-muted">{{ $g['nip'] ?? '—' }}</td>
                            <td>
                                @if (($g['status'] ?? 'gtt') === 'asn')
                                    <span class="dg-chip asn">ASN</span>
                                @else
                                    <span class="dg-chip gtt">GTT</span>
                                @endif
                            </td>
                            <td>
                                <div class="dg-roles">
                                    @foreach ($g['peran'] ?? [] as $kode)
                                        @php $info = $peranMap[$kode] ?? null; @endphp
                                        @if ($info)
                                            <span class="dg-role tone-{{ $info['tone'] }}">{{ $info['label'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td><span class="dg-mapel">{{ $g['mapel'] ?? '-' }}</span></td>
                            <td>
                                @php $akun = $g['akun'] ?? 'belum'; @endphp
                                @if ($akun === 'aktif')
                                    <span class="dg-chip ok">Aktif</span>
                                @elseif ($akun === 'nonaktif')
                                    <span class="dg-chip">Nonaktif</span>
                                @else
                                    <span class="dg-chip warn">Belum dibuat</span>
                                @endif
                            </td>
                            <td class="is-right">
                                <div class="dg-actions">
                                    <button type="button" class="dg-icon-btn" aria-label="Ubah" title="Ubah">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </button>
                                    <button type="button" class="dg-icon-btn" aria-label="Akun" title="Akun">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.7-8.7M16 7l3 3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="dg-empty">Belum ada data guru.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dg-foot">
            <span class="dg-note">ⓘ NIP hanya untuk guru ASN.</span>

            <div class="dg-foot-right">
                <span class="dg-note">{{ $guru->count() }} dari {{ $totalGuru }} guru</span>

                @if ($lastPage > 1)
                    <nav class="dg-pager">
                        <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                           class="dg-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                        @for ($i = 1; $i <= $lastPage; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                               class="dg-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                           class="dg-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                    </nav>
                @endif
            </div>
        </div>
    </section>

</div>

@include('components.modal-impor', [
    'id'        => 'dg-impor',
    'judul'     => 'Impor Data Guru',
    'aksi'      => route('data-guru.impor'),
    'formatUrl' => route('data-guru.format'),
])

@include('components.modal-tambah', [
    'id'     => 'dg-tambah',
    'judul'  => 'Tambah Guru',
    'aksi'   => route('data-guru.store'),
    'fields' => [
        ['name' => 'nama',   'label' => 'Nama lengkap',     'required' => true, 'full' => true, 'placeholder' => 'Nama beserta gelar'],
        ['name' => 'nip',    'label' => 'NIP (khusus ASN)', 'placeholder' => '18 digit'],
        ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true,
         'options' => ['asn' => 'ASN', 'gtt' => 'GTT']],
        ['name' => 'mapel',  'label' => 'Mapel diampu',     'full' => true, 'placeholder' => 'contoh: Matematika'],
    ],
])

@include('components.modal-kit')
@endsection