@props(['nilai', 'maks' => 16, 'label' => null])

{{-- Bar kecil "16/16" di tabel status kelas: hijau = lengkap, biru = hampir, oranye = tertinggal. --}}
@php
    $persen = $maks ? round($nilai / $maks * 100) : 0;
    $warna = $persen >= 100 ? 'bg-success-strong' : ($persen >= 75 ? 'bg-primary' : 'bg-warning-strong');
@endphp
<div {{ $attributes->class('flex items-center gap-2') }}>
    <div class="h-1 w-14 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $nilai }}" aria-valuemin="0" aria-valuemax="{{ $maks }}" @if ($label) aria-label="{{ $label }}" @endif>
        <div class="h-full rounded-full {{ $warna }}" style="width: {{ $persen }}%"></div>
    </div>
    <span class="text-xs text-ink-2">{{ $label ?? "$nilai/$maks" }}</span>
</div>
