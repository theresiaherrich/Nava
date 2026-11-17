@extends('layouts.app')

@section('title', 'Beranda Akademik')

@php
    use App\Support\Format;

    $sts = $rapor['sts'];
    $r = $ringkasan;
@endphp

@section('content')
<div class="px-4 pt-5 pb-28 md:px-7 md:pt-7 md:pb-10">
    <x-ui.page-header :title="'Halo, '.$profil['panggilan'].' 👋'" role="Siswa" subtitle="Lihat rapor dan capaian belajarmu yang sudah diterbitkan sekolah." class="[&_p.text-sm]:max-sm:hidden">
        <x-slot:actions><x-ui.today :tanggal="$hariIni" class="max-sm:hidden" /></x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 lg:grid-cols-[minmax(0,340px)_minmax(0,1fr)]">
        {{-- Profil --}}
        <x-ui.card class="max-sm:!p-4">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-teal-600 text-base font-bold text-white sm:size-14 sm:text-xl" aria-hidden="true">{{ $profil['inisial'] }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-base font-bold text-ink sm:text-lg">{{ $profil['nama'] }}</p>
                    <p class="truncate text-xs text-muted sm:text-[13px]">NIS {{ $profil['nis'] }}<span class="max-sm:hidden"> · NISN {{ $profil['nisn'] }}</span><span class="sm:hidden"> · Wali: {{ preg_replace('/,.*$/', '', $profil['wali']) }}</span></p>
                </div>
                <div class="flex flex-col items-end gap-1 sm:hidden">
                    <x-ui.badge tone="info">{{ $profil['kelas'] }}</x-ui.badge><x-ui.badge tone="violet">Fase {{ $profil['fase'] }}</x-ui.badge>
                </div>
            </div>
            <div class="mt-4 flex gap-2 max-sm:hidden">
                <x-ui.badge tone="info" class="!text-[13px]">Kelas {{ $profil['kelas'] }}</x-ui.badge><x-ui.badge tone="violet" class="!text-[13px]">Fase {{ $profil['fase'] }}</x-ui.badge>
            </div>
            <p class="mt-4 flex items-center gap-2 border-t border-line-soft pt-4 text-[13px] text-muted max-sm:hidden">
                <x-ui.icon name="user-star" :size="15" />Wali kelas: <strong class="font-bold text-ink">{{ $profil['wali'] }}</strong>
            </p>
        </x-ui.card>

        <div class="flex min-w-0 flex-col gap-4">
            {{-- Banner rapor --}}
            @if ($terbit)
                <div class="flex items-center gap-3.5 rounded-card border border-success/30 bg-success-soft/60 px-4 py-3.5 sm:px-5" role="status">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-success shadow-card max-sm:bg-transparent max-sm:shadow-none"><x-ui.icon name="check-circle" :size="16" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-success"><span class="max-sm:hidden">Rapor STS {{ $sts['periode'] === 'Ganjil 2026/2027' ? 'Ganjil 2026/2027' : $sts['periode'] }} sudah terbit</span><span class="sm:hidden">Rapor STS sudah terbit</span></p>
                        <p class="text-xs text-ink-2 sm:text-[13px]"><span class="max-sm:hidden">Diterbitkan </span>{{ $sts['terbit'] }}, {{ $sts['jam'] }}.</p>
                    </div>
                    <x-ui.button :href="route('rapor-saya.index')">Lihat rapor<x-ui.icon name="arrow-right" :size="14" class="max-sm:hidden" /></x-ui.button>
                </div>
            @else
                <div class="flex items-center gap-3.5 rounded-card border border-line bg-neutral-soft/70 px-5 py-3.5" role="status">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-ink-2 shadow-card"><x-ui.icon name="clock" :size="16" /></span>
                    <div><p class="text-sm font-bold text-ink">Belum ada rapor yang diterbitkan untuk semester ini.</p><p class="text-[13px] text-ink-2">Rapor STS Ganjil 2026/2027 akan muncul di sini setelah diterbitkan oleh sekolah.</p></div>
                </div>
            @endif

            {{-- Statistik --}}
            <div class="grid gap-3 sm:grid-cols-3 sm:gap-4">
                @php
                    $stat = $r ? [
                        ['Rata-rata Rapor STS', Format::angka($r['rata'], 1), null, "dari {$r['jumlah']} mata pelajaran", 'text-ink'],
                        ['Mapel tercapai KKTP', $r['tercapai'], "dari {$r['jumlah']}", 'semua TP mencapai KKTP', 'text-success'],
                        ['Perlu penguatan', count($r['penguatan']), 'mapel', implode(' · ', $r['penguatan']), 'text-warning-strong'],
                    ] : array_map(fn ($l) => [$l, '—', null, 'belum ada rapor terbit', 'text-ink'], ['Rata-rata Rapor STS', 'Mapel tercapai KKTP', 'Perlu penguatan']);
                @endphp
                @foreach ($stat as [$label, $nilai, $satuan, $ket, $warna])
                    <x-ui.card class="flex items-center justify-between gap-3 max-sm:!px-4 max-sm:!py-3.5 sm:flex-col sm:items-start sm:justify-start sm:gap-0">
                        <div class="sm:contents">
                            <p class="text-[13px] text-muted">{{ $label }}</p>
                            <p class="text-xs text-muted sm:order-last sm:mt-1.5">{{ $ket }}</p>
                        </div>
                        <p class="text-[26px] leading-tight font-bold sm:mt-2 sm:text-[30px] {{ $warna }}">
                            {{ $nilai }}@if ($satuan)<span class="ml-1 text-lg font-semibold max-sm:hidden">{{ $satuan }}</span>@if ($satuan !== 'mapel')<span class="sm:hidden">/{{ $r['jumlah'] }}</span>@endif @endif
                        </p>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-4 grid items-start gap-4 lg:grid-cols-[minmax(0,440px)_minmax(0,1fr)]">
        {{-- Status rapor --}}
        <x-ui.card class="max-sm:!p-4">
            <h2 class="text-[15px] font-bold text-ink">Status Rapor<span class="max-sm:hidden"> Semester Ini</span></h2>
            <ul class="mt-3 divide-y divide-line-soft">
                @foreach ($rapor as $x)
                    <li class="flex items-center gap-3 py-3">
                        <span class="flex size-9 shrink-0 items-center justify-center rounded-control max-sm:hidden {{ $x['terbit'] ? 'bg-success-soft text-success' : 'bg-neutral-soft text-ink-2' }}"><x-ui.icon name="file-text" :size="16" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold {{ $x['terbit'] ? 'text-ink' : 'text-ink-2' }}">{{ $x['label'] }} Ganjil</p>
                            @unless ($x['terbit'])<p class="text-xs text-muted max-sm:hidden">{{ $x['jadwal'] }}</p>@endunless
                        </div>
                        @if ($x['terbit'])
                            <x-ui.badge tone="success" icon="check">Terbit · {{ preg_replace('/ \d{4}$/', '', $x['terbit']) }}<span class="max-sm:hidden"> {{ substr($x['terbit'], -4) }}</span></x-ui.badge>
                            <a href="{{ route('rapor-saya.index', ['jenis' => $x['jenis']] + request()->only('state')) }}" class="text-[13px] font-semibold text-link hover:underline max-sm:hidden">Buka</a>
                        @else
                            <x-ui.badge>Belum terbit</x-ui.badge>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        {{-- Capaian per mapel --}}
        <x-ui.card class="max-sm:hidden">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-[15px] font-bold text-ink">Capaian per Mapel (Rapor STS)</h2>
                @if ($terbit)<a href="{{ route('nilai-per-mapel.index') }}" class="inline-flex items-center gap-1 text-[13px] font-semibold text-link hover:underline">Lihat semua mapel <x-ui.icon name="arrow-right" :size="13" /></a>@endif
            </div>
            @if ($terbit)
                <ul class="mt-3 divide-y divide-line-soft">
                    @foreach ($capaian as $m)
                        <li>
                            <a href="{{ route('nilai-per-mapel.show', $m['id']) }}" class="grid grid-cols-[minmax(0,1fr)_32px_minmax(80px,160px)_minmax(0,120px)] items-center gap-3 py-2.5 hover:bg-surface-2/60">
                                <span class="truncate text-sm font-bold text-ink">{{ $m['mapel'] }}</span>
                                <span class="text-right text-[15px] font-bold {{ $m['tercapai'] ? 'text-ink' : 'text-warning-strong' }}">{{ $m['nilai'] }}</span>
                                <span class="h-1.5 overflow-hidden rounded-full bg-line" aria-hidden="true"><span class="block h-full rounded-full {{ $m['tercapai'] ? 'bg-success-strong' : 'bg-warning-strong' }}" style="width: {{ $m['nilai'] }}%"></span></span>
                                <span class="text-right"><x-ui.badge :tone="$m['tercapai'] ? 'success' : 'warning'">{{ $m['tercapai'] ? 'Tercapai' : 'Perlu penguatan' }}</x-ui.badge></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <x-ui.empty-state icon="table" title="Belum ada nilai" description="Nilai akan muncul setelah rapor diterbitkan sekolah." class="!py-12" />
            @endif
        </x-ui.card>
    </div>

    <p class="mt-4 flex items-center gap-2 rounded-card border border-line bg-neutral-soft/60 px-4 py-3 text-[13px] text-ink-2 max-sm:hidden">
        <x-ui.icon name="lock" :size="14" />Nilai yang ditampilkan hanya nilai resmi yang sudah diterbitkan. Nilai yang masih diproses guru tidak ditampilkan.
    </p>
</div>
<x-siswa.bottom-nav />
@endsection
