@props(['jenis', 'nilai'])

{{-- Badge status kelas yang dipakai Dashboard Kurikulum, Periode, dan Rekap DKN (desain 8.02 "Badge status"). --}}
@php
    $peta = [
        'verifikasi' => [
            'selesai' => ['Selesai', 'success', null],
            'berjalan' => ['Berjalan', 'info', null],
            'belum' => ['Belum', 'warning', null],
        ],
        'kunci' => [
            'terbuka' => ['Terbuka', 'neutral', null],
            'terkunci' => ['Terkunci', 'success', 'lock'],
            'koreksi' => ['Koreksi', 'violet', 'unlock'],
        ],
        'rapor' => [
            'belum' => ['Belum dibuat', 'neutral', null],
            'pdf' => ['PDF dibuat', 'info', null],
            'terbit' => ['Terbit', 'success', 'check'],
        ],
        'periode' => [
            'siap' => ['Siap dikunci', 'success', null],
            'terkunci' => ['Terkunci', 'success', 'lock'],
            'menunggu' => ['Menunggu verifikasi', 'info', null],
            'terbuka' => ['Terbuka', 'neutral', null],
            'koreksi' => ['Dibuka untuk koreksi', 'violet', 'unlock'],
        ],
    ];
    [$teks, $tone, $ikon] = $peta[$jenis][$nilai] ?? [$nilai, 'neutral', null];
@endphp

<x-ui.badge :tone="$tone" :icon="$ikon" {{ $attributes }}>{{ $teks }}</x-ui.badge>
