@extends('layouts.app')

@section('title', 'Dashboard Kelas '.$kelas)

@push('styles')
    @vite(['resources/css/dashboard-kelas.css', 'resources/js/dashboard-kelas.js'])
@endpush

@section('content')
@php
    // Label & warna status (tampilan saja; data dari App\Support\Dummy\DashboardWaliKelasDummy).
    $statusLabel = [
        'menunggu' => 'Menunggu verifikasi',
        'disetujui' => 'Disetujui',
        'sedang_diisi' => 'Sedang diisi',
        'belum_tp' => 'Belum ada TP',
        'dikembalikan' => 'Dikembalikan',
        'terkunci' => 'Terkunci',
    ];
    $legenda = ['disetujui' => 'Disetujui', 'menunggu' => 'Menunggu', 'sedang_diisi' => 'Sedang diisi', 'belum_tp' => 'Belum ada TP', 'dikembalikan' => 'Dikembalikan'];
    $persen = fn ($a, $b) => $b > 0 ? round($a / $b * 100) : 0;
    $stat = $statistik;
    $perHalaman = 11;

    $ic = [
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'chev' => '<path d="m6 9 6 6 6-6"/>',
        'warn' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'pencil' => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/>',
        'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'printer' => '<path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>',
    ];
    $svg = fn (string $n, string $c = 'dkl-icon') => '<svg class="'.$c.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($ic[$n] ?? '').'</svg>';
    $ikonTindakan = ['verifikasi' => ['check', 'verify'], 'tp_kosong' => ['warn', 'warn'], 'pelengkap' => ['pencil', 'note']];
    $pesanPelengkapBelumAda = 'Halaman Pelengkap Rapor belum tersedia.';
@endphp

<div class="dkl-page">

{{-- Judul --}}
<header class="dkl-head">
<div>
<p class="dkl-eyebrow">SMA Negeri 1 Kota Malang · Wali Kelas</p>
<h1 class="dkl-title">Dashboard Kelas {{ $kelas }}</h1>
<p class="dkl-subtitle">Pantau kelengkapan nilai seluruh mata pelajaran kelas Anda sebelum rapor dicetak.</p>
</div>
<p class="dkl-date">
{!! $svg('calendar') !!}
<span>Hari ini · <strong>{{ $tanggal_hari_ini }}</strong></span>
</p>
</header>

{{-- Banner periode --}}
@if ($periode['status'] === 'dikunci')
<div class="dkl-banner is-success">
<span class="dkl-banner__icon">{!! $svg('lock') !!}</span>
<div class="dkl-banner__body">
<p class="dkl-banner__title">{{ $periode['judul'] }}</p>
<p class="dkl-banner__text">{{ $periode['keterangan'] }}</p>
</div>
<a href="{{ $tautan['cetak'] }}" class="dkl-banner__cta">Cetak rapor {!! $svg('arrow') !!}</a>
</div>
@else
<div class="dkl-banner">
<span class="dkl-banner__icon">{!! $svg('calendar') !!}</span>
<div class="dkl-banner__body">
<p class="dkl-banner__title">{{ $periode['judul'] }}</p>
<p class="dkl-banner__text">
Pengisian nilai ditutup {{ $periode['tutup'] }}.
@if ($stat['menunggu_verifikasi'] > 0)
{{ $stat['menunggu_verifikasi'] }} mata pelajaran menunggu verifikasi Anda.
@endif
</p>
</div>
<a href="{{ $tautan['verifikasi'] }}" class="dkl-banner__link">Periksa sekarang {!! $svg('arrow') !!}</a>
</div>
@endif

{{-- Kartu statistik --}}
<div class="dkl-stats">
<div class="dkl-card dkl-stat">
<p class="dkl-label">Jumlah siswa</p>
<p class="dkl-stat__value">{{ $stat['siswa']['total'] }}</p>
<p class="dkl-note">{{ $stat['siswa']['laki_laki'] }} laki-laki · {{ $stat['siswa']['perempuan'] }} perempuan</p>
</div>
<div class="dkl-card dkl-stat">
<p class="dkl-label">Mapel sudah dikirim</p>
<p class="dkl-stat__value">{{ $stat['mapel_dikirim'] }}<small> / {{ $stat['mapel_total'] }}</small></p>
@php $pDikirim = $persen($stat['mapel_dikirim'], $stat['mapel_total']); @endphp
<div class="dkl-bar dkl-bar--stat" role="progressbar" aria-label="Mapel sudah dikirim" aria-valuenow="{{ $pDikirim }}" aria-valuemin="0" aria-valuemax="100">
<div @class(['dkl-bar__fill', 'is-green' => $pDikirim >= 100, 'is-blue' => $pDikirim < 100]) style="width: {{ $pDikirim }}%"></div>
</div>
</div>
<div class="dkl-card dkl-stat">
<p class="dkl-label">Menunggu verifikasi</p>
<p @class(['dkl-stat__value', 'is-warn' => $stat['menunggu_verifikasi'] > 0])>{{ $stat['menunggu_verifikasi'] }}</p>
<p class="dkl-note">{{ $stat['menunggu_verifikasi'] > 0 ? 'perlu diperiksa' : 'semua sudah diperiksa' }}</p>
</div>
<div class="dkl-card dkl-stat">
<p class="dkl-label">Pelengkap rapor</p>
<p class="dkl-stat__value">{{ $stat['catatan_wali_terisi'] }}<small> / {{ $stat['siswa']['total'] }}</small></p>
<p class="dkl-note">catatan wali terisi</p>
</div>
</div>

{{-- Stepper --}}
<section class="dkl-card dkl-steps-card" aria-labelledby="dkl-langkah">
<h2 class="dkl-panel-title" id="dkl-langkah">Langkah menuju Rapor STS</h2>
<ol class="dkl-steps">
@foreach ($langkah as $i => $l)
<li class="dkl-step is-{{ $l['state'] }}" @if ($l['state'] === 'aktif') aria-current="step" @endif>
<span class="dkl-step__dot">
@if ($l['state'] === 'selesai')
{!! $svg('check') !!}<span class="dkl-sr">Selesai:</span>
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

{{-- Tabel kelengkapan mata pelajaran --}}
<section class="dkl-card dkl-table-card" data-mapel data-per-page="{{ $perHalaman }}" aria-labelledby="dkl-mapel">
<div class="dkl-table-head">
<div>
<h2 class="dkl-panel-title" id="dkl-mapel">Kelengkapan Mata Pelajaran</h2>
<p class="dkl-note">{{ count($mapel) }} mapel · Kelas {{ $kelas }}</p>
</div>
<div class="dkl-filter">
<label class="dkl-select-wrap">
<span class="dkl-sr">Filter status</span>
<select class="dkl-select" data-mapel-status>
<option value="">Semua status</option>
@foreach ($statusLabel as $val => $lbl)
@continue($val === 'terkunci' && $periode['status'] !== 'dikunci')
<option value="{{ $val }}">{{ $lbl }}</option>
@endforeach
</select>
{!! $svg('chev', 'dkl-icon dkl-select__chev') !!}
</label>
<label class="dkl-search">
{!! $svg('search') !!}
<span class="dkl-sr">Cari mapel</span>
<input type="search" placeholder="Cari mapel..." autocomplete="off" data-mapel-search>
</label>
</div>
</div>

<div class="dkl-table-wrap">
<table class="dkl-table">
<thead>
<tr>
<th scope="col">Mata Pelajaran</th>
<th scope="col">Guru Pengampu</th>
<th scope="col">TP</th>
<th scope="col">Nilai Terisi</th>
<th scope="col">Status</th>
<th scope="col">Diperbarui</th>
<th scope="col" class="is-right">Aksi</th>
</tr>
</thead>
<tbody>
{{-- Catatan: keluaran HTML view ini sengaja tanpa indentasi agar halaman tetap < 64 KB,
     karena `php artisan serve` (php -S) di Windows memotong respons yang lebih besar. --}}
@foreach ($mapel as $i => $m)
@php
    $p = $persen($m['terisi'], $m['total']);
    $warnaBar = $m['status'] === 'sedang_diisi' ? 'is-orange' : ($p > 0 ? 'is-green' : '');
    $periksa = $m['status'] === 'menunggu';
@endphp
<tr data-row data-status="{{ $m['status'] }}" data-search="{{ mb_strtolower($m['mapel'].' '.$m['guru'].' '.$m['keterangan']) }}"@if ($i >= $perHalaman) hidden @endif>
<td><p class="dkl-mapel">{{ $m['mapel'] }}</p><p class="dkl-mapel__sub">{{ $m['keterangan'] }}</p>@if ($m['catatan'])<p class="dkl-mapel__sub is-italic">{{ $m['catatan'] }}</p>@endif</td>
<td class="dkl-guru">{{ $m['guru'] }}</td>
<td class="dkl-nowrap">{{ $m['tp'] > 0 ? $m['tp'].' TP · '.$m['sts'].' STS' : '0 TP' }}</td>
<td><div class="dkl-fill"><div class="dkl-bar"><div class="dkl-bar__fill {{ $warnaBar }}" style="width:{{ $p }}%"></div></div><span>{{ $m['terisi'] }}/{{ $m['total'] }}</span></div></td>
<td><span class="dkl-badge is-{{ $m['status'] }}">@if ($m['status'] === 'terkunci'){!! $svg('lock', 'dkl-icon dkl-badge__icon') !!}@endif{{ $statusLabel[$m['status']] ?? $m['status'] }}</span></td>
<td class="dkl-muted dkl-nowrap">{{ $m['diperbarui'] ?? '—' }}</td>
<td class="is-right"><a href="{{ $m['url'] }}" class="dkl-btn {{ $periksa ? 'dkl-btn--primary' : 'dkl-btn--outline' }}" aria-label="{{ $periksa ? 'Periksa' : 'Lihat' }} nilai {{ $m['mapel'] }}">{{ $periksa ? 'Periksa' : 'Lihat' }}</a></td>
</tr>
@endforeach
<tr class="dkl-empty" data-empty hidden>
<td colspan="7">Tidak ada mapel yang cocok dengan filter.</td>
</tr>
</tbody>
</table>
</div>

<div class="dkl-table-foot">
<p class="dkl-note" data-mapel-info aria-live="polite">Menampilkan {{ min($perHalaman, count($mapel)) }} dari {{ count($mapel) }} mapel</p>
<ul class="dkl-legend" aria-label="Keterangan warna status">
@foreach ($legenda as $val => $lbl)
<li><span class="dkl-legend__dot is-{{ $val }}"></span>{{ $lbl }}</li>
@endforeach
</ul>
<nav class="dkl-pager" aria-label="Halaman tabel mapel" data-mapel-pager></nav>
</div>
</section>

{{-- Kolom kanan --}}
<aside class="dkl-side">

{{-- Perlu tindakan --}}
<section class="dkl-card" aria-labelledby="dkl-tindakan">
<div class="dkl-panel-head">
<h2 class="dkl-panel-title" id="dkl-tindakan">Perlu tindakan</h2>
<span @class(['dkl-count', 'is-zero' => ! $perlu_tindakan])>{{ count($perlu_tindakan) }}</span>
</div>
@forelse ($perlu_tindakan as $t)
@php [$ikon, $tone] = $ikonTindakan[$t['jenis']] ?? ['check', 'verify']; @endphp
<div class="dkl-todo">
<span class="dkl-todo__icon is-{{ $tone }}">{!! $svg($ikon) !!}</span>
<div class="dkl-todo__body">
<p class="dkl-todo__title">{{ $t['judul'] }}</p>
<p class="dkl-note">{{ $t['keterangan'] }}</p>
</div>
@if ($t['aksi'] && $t['url'])
<a href="{{ $t['url'] }}" class="dkl-todo__link">{{ $t['aksi'] }}</a>
@elseif ($t['aksi'])
<button type="button" class="dkl-todo__link" disabled title="{{ $pesanPelengkapBelumAda }}">{{ $t['aksi'] }}</button>
@endif
</div>
@empty
<p class="dkl-done">{!! $svg('check-circle') !!} Semua tindakan selesai</p>
@endforelse
</section>

{{-- Pelengkap rapor --}}
<section class="dkl-card" aria-labelledby="dkl-pelengkap">
<h2 class="dkl-panel-title" id="dkl-pelengkap">Pelengkap Rapor</h2>
@php
                    $catatan = $pelengkap['catatan_wali'];
                    $ekskul = $pelengkap['ekskul'];
                    $warna = fn ($a, $b) => $persen($a, $b) >= 100 ? 'is-green' : null;
@endphp
<div class="dkl-pl">
<div class="dkl-pl__row"><span>Catatan wali kelas</span><strong>{{ $catatan['terisi'] }}/{{ $catatan['total'] }}</strong></div>
<div class="dkl-bar"><div class="dkl-bar__fill {{ $warna($catatan['terisi'], $catatan['total']) ?? 'is-orange' }}" style="width: {{ $persen($catatan['terisi'], $catatan['total']) }}%"></div></div>
</div>
<div class="dkl-pl">
<div class="dkl-pl__row">
<span>Kehadiran (dari Presensi A1)</span>
@if ($pelengkap['kehadiran_tersedia'])
<span class="dkl-badge is-disetujui">Tersedia</span>
@else
<span class="dkl-badge is-belum_tp">Belum tersedia</span>
@endif
</div>
</div>
<div class="dkl-pl">
<div class="dkl-pl__row"><span>Predikat ekskul (dari Kesiswaan)</span><strong>{{ $ekskul['terisi'] }}/{{ $ekskul['total'] }}</strong></div>
<div class="dkl-bar"><div class="dkl-bar__fill {{ $warna($ekskul['terisi'], $ekskul['total']) ?? 'is-blue' }}" style="width: {{ $persen($ekskul['terisi'], $ekskul['total']) }}%"></div></div>
</div>
@if ($tautan['pelengkap'])
<a href="{{ $tautan['pelengkap'] }}" class="dkl-btn dkl-btn--outline dkl-btn--block">Lengkapi pelengkap rapor</a>
@else
<button type="button" class="dkl-btn dkl-btn--outline dkl-btn--block" disabled title="{{ $pesanPelengkapBelumAda }}">Lengkapi pelengkap rapor</button>
@endif
</section>

{{-- Status rapor STS --}}
<section class="dkl-card" aria-labelledby="dkl-rapor">
<h2 class="dkl-panel-title" id="dkl-rapor">Status Rapor STS</h2>
<div class="dkl-rapor">
<span @class(['dkl-rapor__icon', 'is-siap' => $status_rapor['bisa_cetak']])>{!! $svg($status_rapor['bisa_cetak'] ? 'check' : 'lock') !!}</span>
<div>
<p @class(['dkl-todo__title', 'is-success' => $status_rapor['bisa_cetak']])>{{ $status_rapor['judul'] }}</p>
<p class="dkl-note">{{ $status_rapor['keterangan'] }}</p>
</div>
</div>
@if ($status_rapor['bisa_cetak'])
<a href="{{ $tautan['cetak'] }}" class="dkl-btn dkl-btn--print dkl-btn--block is-siap">{!! $svg('printer') !!} Cetak rapor kelas</a>
@else
<button type="button" class="dkl-btn dkl-btn--print dkl-btn--block" disabled>{!! $svg('printer') !!} Cetak rapor kelas</button>
@endif
</section>
</aside>
</div>
</div>
@endsection
