{{--
    resources/views/rekap-nilai/index.blade.php
    Stylesheet: resources/css/rekap-nilai.css

    Variabel yang diharapkan dari Controller:
    - $rombels       : koleksi rombel (nama, jumlah_siswa, jumlah_tp, kelengkapan, rata_rata, status, bisa_lihat)
    - $statistik     : jumlah_siswa, rata_rata, rata_rata_selisih, kelengkapan, kktp, di_bawah_kktp, rombel_aktif
    - $siswaList     : LengthAwarePaginator berisi objek (nama, nisn, tp[5], sts, sas, semester, capaian)
    - $daftarRombel  : koleksi rombel untuk dropdown filter (id, label)
    - $semesterLabel : contoh "Semester Ganjil 2026/2027"
    - $kktp          : nilai KKTP (default 75)
    - session('toast'): ['type' => success|error|info|warning, 'message' => '...']
--}}

@extends('layouts.app')

@section('title', 'Rekap Nilai (DKN)')

@push('styles')
    @vite(['resources/css/rekap-nilai.css', 'resources/js/rekap-nilai.js'])
@endpush

@php
    $tab       = request('tab', 'ringkasan');     // ringkasan | siswa
    $periode   = request('periode', 'semester');  // sts | semester
    $kktp      = $kktp ?? 75;
    $statusMap = [
        'belum-mulai'   => 'Belum mulai',
        'sedang-diisi'  => 'Sedang diisi',
        'belum-ada-tp'  => 'Belum ada TP',
        'terkirim'      => 'Terkirim',
        'perlu-revisi'  => 'Perlu revisi',
        'disetujui'     => 'Disetujui',
        'terkunci'      => 'Terkunci',
    ];
    $capaianMap = [
        'tercapai'        => 'Tercapai',
        'perlu-penguatan' => 'Perlu penguatan',
    ];
    $toastIcon = [
        'success' => '✓',
        'error'   => '!',
        'info'    => 'i',
        'warning' => '!',
    ];
@endphp

@section('content')
<div class="rn-page">

    {{-- ================= HEADER ================= --}}
    <header class="rn-header">
        <div>
            <p class="rn-eyebrow">SMA Negeri 1 Kota Malang</p>
            <h1 class="rn-title">Rekap Nilai (DKN)</h1>
            <p class="rn-subtitle">Pantau kelengkapan dan ringkasan nilai rombel yang Anda ampu.</p>
        </div>

        <details class="rn-dropdown">
            <summary class="rn-btn">
                <svg class="rn-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
                </svg>
                Ekspor Excel
                <svg class="rn-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </summary>

            <div class="rn-dropdown-menu">
                <a class="rn-dropdown-item" href="{{ route('rekap-nilai.export', ['scope' => 'rombel']) }}">
                    <span class="rn-xls">X</span>
                    <span>
                        <strong>DKN rombel ini ({{ $statistik->rombel_aktif ?? '-' }})</strong>
                        <small>Satu sheet · {{ $statistik->jumlah_siswa ?? 0 }} siswa</small>
                    </span>
                </a>
                <a class="rn-dropdown-item" href="{{ route('rekap-nilai.export', ['scope' => 'semua']) }}">
                    <span class="rn-xls">X</span>
                    <span>
                        <strong>DKN semua rombel saya</strong>
                        <small>{{ $rombels->count() }} sheet · {{ $rombels->sum('jumlah_siswa') }} siswa</small>
                    </span>
                </a>
                <div class="rn-dropdown-foot">Format .xlsx</div>
            </div>
        </details>
    </header>

    {{-- ================= TABS ================= --}}
    <nav class="rn-tabs">
        <a href="?tab=ringkasan&periode={{ $periode }}" class="rn-tab {{ $tab === 'ringkasan' ? 'is-active' : '' }}">Ringkasan rombel</a>
        <a href="?tab=siswa&periode={{ $periode }}"     class="rn-tab {{ $tab === 'siswa' ? 'is-active' : '' }}">Per siswa</a>
    </nav>

    {{-- ================= TAB 1: RINGKASAN ROMBEL ================= --}}
    @if ($tab === 'ringkasan')
        <section class="rn-card">
            <div class="rn-card-head">
                <div>
                    <h2 class="rn-card-title">Ringkasan rombel yang Anda ampu</h2>
                    <p class="rn-card-sub">{{ $rombels->count() }} rombel · Matematika · {{ $semesterLabel }}</p>
                </div>

                <div class="rn-period">
                    <span>Periode:</span>
                    <div class="rn-toggle" role="group" aria-label="Periode">
                        <a href="?tab=ringkasan&periode=sts"      class="{{ $periode === 'sts' ? 'is-active' : '' }}">STS</a>
                        <a href="?tab=ringkasan&periode=semester" class="{{ $periode === 'semester' ? 'is-active' : '' }}">Semester</a>
                    </div>
                </div>
            </div>

            <div class="rn-table-wrap">
                <table class="rn-table">
                    <thead>
                        <tr>
                            <th>Rombel</th>
                            <th>Siswa</th>
                            <th>TP</th>
                            <th>Kelengkapan</th>
                            <th>Rata-rata</th>
                            <th>Status</th>
                            <th class="rn-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rombels as $r)
                            @php
                                $bisaLihat = $r->bisa_lihat ?? !in_array($r->status, ['belum-ada-tp', 'belum-mulai']);
                                $warnaBar  = $r->kelengkapan >= 100 ? 'is-green' : ($r->kelengkapan == 0 ? 'is-empty' : '');
                            @endphp
                            <tr>
                                <td>
                                    <span class="rn-strong">{{ $r->nama }}</span>
                                    <span class="rn-muted"> · Matematika</span>
                                </td>
                                <td>{{ $r->jumlah_siswa }}</td>
                                <td>{{ $r->jumlah_tp }} TP</td>
                                <td>
                                    <div class="rn-progress">
                                        <div class="rn-progress-track">
                                            <span class="rn-progress-fill {{ $warnaBar }}" style="--w: {{ $r->kelengkapan }}%"></span>
                                        </div>
                                        <span class="rn-progress-value">{{ $r->kelengkapan }}%</span>
                                    </div>
                                </td>
                                <td class="rn-strong">{{ $r->rata_rata ?? '—' }}</td>
                                <td>
                                    <span class="rn-badge rn-badge--{{ $r->status }}">{{ $statusMap[$r->status] ?? $r->status }}</span>
                                </td>
                                <td class="rn-actions">
                                    <a href="{{ route('rekap-nilai.show', $r->id) }}"
                                       class="rn-btn rn-btn-sm {{ $bisaLihat ? '' : 'is-disabled' }}">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="rn-center rn-muted">Belum ada rombel yang Anda ampu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- ================= TAB 2: PER SISWA ================= --}}
    @if ($tab === 'siswa')

        {{-- Statistik --}}
        <div class="rn-stats">
            <div class="rn-stat">
                <div class="rn-stat-label">Jumlah siswa</div>
                <div class="rn-stat-value">{{ $statistik->jumlah_siswa ?? 0 }}</div>
                <div class="rn-stat-note">{{ $statistik->rombel_aktif ?? '-' }} · Matematika</div>
            </div>

            <div class="rn-stat">
                <div class="rn-stat-label">Rata-rata rombel</div>
                <div class="rn-stat-value">{{ $statistik->rata_rata ?? '—' }}</div>
                @if (!empty($statistik->rata_rata_selisih))
                    <div class="rn-stat-note is-up">+{{ $statistik->rata_rata_selisih }} dari semester lalu</div>
                @else
                    <div class="rn-stat-note">belum ada nilai</div>
                @endif
            </div>

            <div class="rn-stat">
                <div class="rn-stat-label">Kelengkapan nilai</div>
                <div class="rn-stat-value">{{ $statistik->kelengkapan ?? 0 }}%</div>
                <div class="rn-progress-track">
                    <span class="rn-progress-fill" style="--w: {{ $statistik->kelengkapan ?? 0 }}%"></span>
                </div>
            </div>

            <div class="rn-stat">
                <div class="rn-stat-label">Di bawah KKTP</div>
                <div class="rn-stat-value">{{ $statistik->di_bawah_kktp ?? '—' }}</div>
                <div class="rn-stat-note">{{ isset($statistik->di_bawah_kktp) ? 'KKTP ' . $kktp : 'belum ada nilai' }}</div>
            </div>
        </div>

        @if ($siswaList->isEmpty() && ($statistik->kelengkapan ?? 0) == 0)
            {{-- Empty state: rombel belum punya nilai --}}
            <div class="rn-filterbar">
                <div class="rn-filter-left">
                    <select class="rn-select" aria-label="Pilih rombel">
                        @foreach ($daftarRombel as $opt)
                            <option value="{{ $opt->id }}" @selected(($statistik->rombel_id ?? null) == $opt->id)>Rombel: {{ $opt->label }}</option>
                        @endforeach
                    </select>
                    <div class="rn-period">
                        <span>Periode:</span>
                        <div class="rn-toggle" role="group" aria-label="Periode">
                            <a href="?tab=siswa&periode=sts"      class="{{ $periode === 'sts' ? 'is-active' : '' }}">STS</a>
                            <a href="?tab=siswa&periode=semester" class="{{ $periode === 'semester' ? 'is-active' : '' }}">Semester</a>
                        </div>
                    </div>
                </div>
                <div class="rn-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                    <input type="search" placeholder="Cari nama atau NISN..." aria-label="Cari siswa">
                </div>
            </div>

            <div class="rn-empty">
                <div class="rn-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/>
                    </svg>
                </div>
                <h3 class="rn-empty-title">Belum ada nilai untuk direkap</h3>
                <p class="rn-empty-text">Buat TP dan isi nilai terlebih dahulu.</p>
                <a href="{{ route('tp.index') }}" class="rn-btn rn-btn-primary">
                    <svg class="rn-btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/>
                    </svg>
                    Ke Kelola TP
                </a>
            </div>
        @else
            {{-- Filter bar --}}
            <div class="rn-filterbar">
                <div class="rn-filter-left">
                    <select class="rn-select" aria-label="Pilih rombel">
                        @foreach ($daftarRombel as $opt)
                            <option value="{{ $opt->id }}" @selected(($statistik->rombel_id ?? null) == $opt->id)>Rombel: {{ $opt->label }}</option>
                        @endforeach
                    </select>
                    <div class="rn-period">
                        <span>Periode:</span>
                        <div class="rn-toggle" role="group" aria-label="Periode">
                            <a href="?tab=siswa&periode=sts"      class="{{ $periode === 'sts' ? 'is-active' : '' }}">STS</a>
                            <a href="?tab=siswa&periode=semester" class="{{ $periode === 'semester' ? 'is-active' : '' }}">Semester</a>
                        </div>
                    </div>
                </div>
                <div class="rn-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                    <input type="search" placeholder="Cari nama atau NISN..." aria-label="Cari siswa">
                </div>
            </div>

            {{-- Tabel per siswa --}}
            <section class="rn-card">
                <div class="rn-table-wrap">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>Nama siswa</th>
                                <th class="rn-center">TP1</th>
                                <th class="rn-center">TP2</th>
                                <th class="rn-center">TP3</th>
                                <th class="rn-center">TP4</th>
                                <th class="rn-center">TP5</th>
                                <th class="rn-center rn-col-sas">TP6 · SAS</th>
                                <th class="rn-center">Nilai STS</th>
                                <th class="rn-center rn-col-semester">Nilai Semester</th>
                                <th>Capaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($siswaList as $s)
                                <tr>
                                    <td>
                                        <div class="rn-student">
                                            <span class="rn-strong">{{ $s->nama }}</span>
                                            <span class="rn-nisn">{{ $s->nisn }}</span>
                                        </div>
                                    </td>

                                    @foreach ($s->tp as $nilai)
                                        <td class="rn-center">
                                            <span class="{{ $nilai < $kktp ? 'rn-pill' : '' }}">{{ $nilai }}</span>
                                        </td>
                                    @endforeach

                                    <td class="rn-center rn-col-sas">
                                        <span class="{{ $s->sas < $kktp ? 'rn-pill' : '' }}">{{ $s->sas }}</span>
                                    </td>

                                    <td class="rn-center">
                                        <span class="{{ $s->sts < $kktp ? 'rn-pill' : '' }}">{{ number_format($s->sts, 1, ',', '.') }}</span>
                                    </td>

                                    <td class="rn-center rn-col-semester rn-strong">
                                        <span class="{{ $s->semester < $kktp ? 'rn-pill' : '' }}">{{ number_format($s->semester, 1, ',', '.') }}</span>
                                    </td>

                                    <td>
                                        <span class="rn-badge rn-badge--{{ $s->capaian }}">{{ $capaianMap[$s->capaian] ?? $s->capaian }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer: legenda & pagination --}}
                <div class="rn-footer">
                    <span class="rn-legend">
                        <span class="rn-legend-dot"></span>
                        Oranye = di bawah KKTP ({{ $kktp }}) · Nilai STS = rata-rata TP1–TP3 (separuh TP teratas)
                    </span>

                    <div class="rn-pager">
                        <span>{{ $siswaList->firstItem() }}–{{ $siswaList->lastItem() }} dari {{ $siswaList->total() }}</span>

                        <nav class="rn-pager-nav" aria-label="Halaman">
                            @if ($siswaList->onFirstPage())
                                <span class="rn-page-btn is-disabled">‹</span>
                            @else
                                <a class="rn-page-btn" href="{{ $siswaList->previousPageUrl() }}">‹</a>
                            @endif

                            @foreach ($siswaList->getUrlRange(1, $siswaList->lastPage()) as $page => $url)
                                <a href="{{ $url }}" class="rn-page-btn {{ $page === $siswaList->currentPage() ? 'is-current' : '' }}">{{ $page }}</a>
                            @endforeach

                            @if ($siswaList->hasMorePages())
                                <a class="rn-page-btn" href="{{ $siswaList->nextPageUrl() }}">›</a>
                            @else
                                <span class="rn-page-btn is-disabled">›</span>
                            @endif
                        </nav>
                    </div>
                </div>
            </section>
        @endif
    @endif

    {{-- ================= TOAST ================= --}}
    @if (session('toast'))
        <div class="rn-toast rn-toast--{{ session('toast.type', 'success') }}" role="status">
            <span class="rn-toast-icon" aria-hidden="true">{{ $toastIcon[session('toast.type', 'success')] ?? '✓' }}</span>
            <span class="rn-toast-text">{{ session('toast.message') }}</span>
            @if (session('toast.action'))
                <a class="rn-toast-action" href="{{ session('toast.url', '#') }}">{{ session('toast.action') }}</a>
            @endif
        </div>
    @endif

</div>
@endsection