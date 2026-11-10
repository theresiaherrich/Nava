@extends('layouts.app')

@section('title', 'Kelola Akun & Peran')

@section('content')
@vite(['resources/css/akun-peran.css', 'resources/js/akun-peran.js'])

@php
    $stats     = $stats     ?? [];
    $akun      = collect($akun ?? []);
    $peranMap  = $peranMap  ?? [];
    $peranOpsi = $peranOpsi ?? [];
    $peran     = $peran     ?? 'semua';
    $status    = $status    ?? 'semua';
    $q         = $q         ?? '';
    $page      = $page      ?? 1;
    $lastPage  = $lastPage  ?? 1;
    $totalAkun = $totalAkun ?? 0;

    $persenOrtu = ($stats['akun_ortu'] ?? 0) > 0
        ? ($stats['ortu_terhubung'] / $stats['akun_ortu']) * 100
        : 0;
@endphp

<div class="ak-page">

    {{-- HEADER --}}
    <header class="ak-head">
        <div>
            <p class="ak-eyebrow">SMA Negeri 1 Kota Malang · Operator</p>
            <h1 class="ak-title">Kelola Akun & Peran</h1>
            <p class="ak-subtitle">Atur akun pengguna dan peran yang dimiliki.</p>
        </div>
        <button type="button" class="ak-btn-primary" data-buka-modal>+ Tambah Akun</button>
    </header>

    {{-- STATISTIK --}}
    <div class="ak-stats">
        <div class="ak-card">
            <p class="ak-label">Total akun</p>
            <p class="ak-value">{{ $stats['total'] ?? 0 }}</p>
            <p class="ak-note">{{ $stats['total_aktif'] ?? 0 }} aktif · {{ $stats['total_nonaktif'] ?? 0 }} nonaktif</p>
        </div>

        <div class="ak-card">
            <p class="ak-label">Akun guru</p>
            <p class="ak-value">{{ $stats['akun_guru'] ?? 0 }}</p>
            <p class="ak-note {{ ($stats['guru_belum_akun'] ?? 0) > 0 ? 'is-warn' : '' }}">
                {{ $stats['guru_belum_akun'] ?? 0 }} guru belum punya akun
            </p>
        </div>

        <div class="ak-card">
            <p class="ak-label">Akun orang tua</p>
            <p class="ak-value">{{ $stats['akun_ortu'] ?? 0 }}</p>
            <div class="ak-bar"><div class="ak-bar-fill" style="width: {{ $persenOrtu }}%"></div></div>
            <p class="ak-note">terhubung ke {{ $stats['ortu_terhubung'] ?? 0 }} siswa</p>
        </div>
    </div>

    {{-- DAFTAR AKUN --}}
    <section class="ak-card ak-table-card">
        <div class="ak-table-head">
            <div>
                <h2 class="ak-panel-title">Daftar Akun</h2>
                <p class="ak-note">{{ $totalAkun }} akun</p>
            </div>

            <form method="GET" action="{{ route('akun-peran.index') }}" class="ak-filter" data-filter-form>
                <select name="peran" class="ak-select" data-auto-submit>
                    <option value="semua" @selected($peran === 'semua')>Peran: Semua</option>
                    @foreach ($peranMap as $kode => $info)
                        <option value="{{ $kode }}" @selected($peran === $kode)>Peran: {{ $info['label'] }}</option>
                    @endforeach
                </select>

                <select name="status" class="ak-select" data-auto-submit>
                    <option value="semua" @selected($status === 'semua')>Status: Semua</option>
                    <option value="aktif" @selected($status === 'aktif')>Status: Aktif</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Status: Nonaktif</option>
                </select>

                <div class="ak-search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama atau username..." class="ak-search" data-search>
                </div>
            </form>
        </div>

        <div class="ak-table-wrap">
            <table class="ak-table">
                <thead>
                    <tr>
                        <th class="is-center">No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th>Terakhir login</th>
                        <th class="is-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($akun as $i => $a)
                        <tr>
                            <td class="is-center ak-muted">{{ ($page - 1) * 10 + $i + 1 }}</td>
                            <td class="ak-bold">{{ $a['nama'] ?? '-' }}</td>
                            <td class="ak-muted">{{ $a['username'] ?? '-' }}</td>
                            <td>
                                <div class="ak-roles">
                                    @foreach ($a['peran'] ?? [] as $kode)
                                        @php $info = $peranMap[$kode] ?? null; @endphp
                                        @if ($info)
                                            <span class="ak-role tone-{{ $info['tone'] }}">{{ $info['label'] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if (($a['status'] ?? 'nonaktif') === 'aktif')
                                    <span class="ak-chip ok">Aktif</span>
                                @else
                                    <span class="ak-chip">Nonaktif</span>
                                @endif
                            </td>
                            <td class="ak-muted">{{ $a['terakhir_login'] ?? '—' }}</td>
                            <td class="is-right">
                                <div class="ak-actions">
                                    <button type="button" class="ak-icon-btn" title="Ubah" aria-label="Ubah">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </button>
                                    <button type="button" class="ak-icon-btn" title="Reset password" aria-label="Reset password">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.7-8.7M16 7l3 3"/></svg>
                                    </button>
                                    <button type="button" class="ak-icon-btn" title="Aktif / nonaktif" aria-label="Aktif atau nonaktif">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="7" width="20" height="10" rx="5"/><circle cx="16" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="ak-empty">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ak-foot">
            <span class="ak-note">ⓘ Satu akun dapat memiliki lebih dari satu peran.</span>

            <div class="ak-foot-right">
                <span class="ak-note">{{ $akun->count() }} dari {{ $totalAkun }} akun</span>

                @if ($lastPage > 1)
                    <nav class="ak-pager">
                        <a href="{{ $page > 1 ? request()->fullUrlWithQuery(['page' => $page - 1]) : '#' }}"
                           class="ak-page-btn {{ $page <= 1 ? 'is-disabled' : '' }}">‹</a>
                        @for ($i = 1; $i <= $lastPage; $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}"
                               class="ak-page-btn {{ $i === $page ? 'is-active' : '' }}">{{ $i }}</a>
                        @endfor
                        <a href="{{ $page < $lastPage ? request()->fullUrlWithQuery(['page' => $page + 1]) : '#' }}"
                           class="ak-page-btn {{ $page >= $lastPage ? 'is-disabled' : '' }}">›</a>
                    </nav>
                @endif
            </div>
        </div>
    </section>

</div>

{{-- MODAL TAMBAH AKUN --}}
<div data-modal class="ak-modal" aria-hidden="true">
    <div class="ak-modal-box" role="dialog" aria-modal="true" aria-labelledby="ak-modal-title">
        <div class="ak-modal-head">
            <div>
                <h3 id="ak-modal-title" class="ak-modal-title">Tambah Akun</h3>
                <p class="ak-note">Akun dipakai untuk masuk ke seluruh modul SIAKAD.</p>
            </div>
            <button type="button" class="ak-close" data-tutup-modal aria-label="Tutup">×</button>
        </div>

        <form method="POST" action="#" data-form-akun novalidate>
            @csrf

            <div class="ak-form-grid">
                <div class="ak-field">
                    <label for="ak-nama">Nama lengkap <span class="ak-req">*</span></label>
                    <input id="ak-nama" type="text" name="nama" value="{{ old('nama') }}" placeholder="Nama lengkap">
                </div>

                <div class="ak-field">
                    <label for="ak-username">Username <span class="ak-req">*</span></label>
                    <input id="ak-username" type="text" name="username" value="{{ old('username') }}"
                           class="{{ $errors->has('username') ? 'is-error' : '' }}">
                    @error('username')
                        <p class="ak-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="ak-field">
                    <label for="ak-email">Email (opsional)</label>
                    <input id="ak-email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@sman1malang.sch.id">
                </div>

                <div class="ak-field">
                    <label for="ak-password">Password awal <span class="ak-req">*</span></label>
                    <div class="ak-password">
                        <input id="ak-password" type="password" name="password" value="">
                        <button type="button" class="ak-link-btn" data-buat-password>Buat otomatis</button>
                    </div>
                </div>
            </div>

            <div class="ak-field ak-field-full">
                <span class="ak-label-strong">Peran <span class="ak-req">*</span></span>
                <div class="ak-check-grid">
                    @foreach ($peranMap as $kode => $info)
                        <label class="ak-check">
                            <input type="checkbox" name="peran[]" value="{{ $kode }}"
                                   data-peran-check="{{ $kode }}"
                                   @checked(in_array($kode, old('peran', []), true))>
                            <span>{{ $info['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="ak-guru-box" data-guru-box hidden>
                <div class="ak-field">
                    <label for="ak-nip">NIP (opsional)</label>
                    <input id="ak-nip" type="text" name="nip" value="{{ old('nip') }}" placeholder="18 digit NIP">
                    <p class="ak-note">Muncul karena peran Guru dipilih.</p>
                </div>

                <div class="ak-field">
                    <span class="ak-label-strong">Status</span>
                    <div class="ak-segment">
                        <label><input type="radio" name="status_guru" value="asn" @checked(old('status_guru', 'asn') === 'asn')><span>ASN</span></label>
                        <label><input type="radio" name="status_guru" value="gtt" @checked(old('status_guru') === 'gtt')><span>GTT</span></label>
                    </div>
                </div>
            </div>

            <div class="ak-modal-foot">
                <button type="button" class="ak-btn" data-tutup-modal>Batal</button>
                <button type="submit" class="ak-btn-primary">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>
@endsection