@extends('layouts.app')

@section('title', $m['mapel'].' · Nilai')

@php
    $tercapaiTp = collect($m['tp'])->filter(fn ($t) => $t['nilai'] >= $kktp)->count();
    $jumlahTp = count($m['tp']);
@endphp

@section('content')
<div class="px-4 pt-5 pb-28 md:px-7 md:pt-7 md:pb-10">
    <x-ui.page-header :title="$m['mapel']" role="Siswa" subtitle="Rincian nilai resmi per tujuan pembelajaran." :back="['href' => route('nilai-per-mapel.index'), 'label' => 'Nilai per Mapel']">
        <x-slot:actions>
            <x-ui.badge tone="outline" class="!text-[13px]">{{ $m['kelompok'] }}</x-ui.badge>
            <x-ui.badge tone="outline" class="!text-[13px]">Guru: {{ $m['guru'] }}</x-ui.badge>
            <x-ui.badge tone="info" class="!text-[13px]">Rapor STS Ganjil 2026/2027</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Nilai Rapor STS" :value="$m['nilai']" hint="nilai akhir mapel" :tone="$m['tercapai'] ? 'ink' : 'warning'" />
        <x-ui.stat-card label="KKTP" :value="$kktp" hint="kriteria ketercapaian TP" />
        <x-ui.card>
            <p class="text-[13px] text-muted">TP tercapai</p>
            <p class="mt-2 text-[30px] leading-none font-bold {{ $tercapaiTp === $jumlahTp ? 'text-success' : 'text-warning-strong' }}">{{ $tercapaiTp }}<span class="ml-1 text-lg font-semibold text-ink-2">dari {{ $jumlahTp }}</span></p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="TP tercapai" aria-valuenow="{{ $tercapaiTp }}" aria-valuemin="0" aria-valuemax="{{ $jumlahTp }}">
                <div class="h-full rounded-full {{ $tercapaiTp === $jumlahTp ? 'bg-success-strong' : 'bg-warning-strong' }}" style="width: {{ $jumlahTp ? $tercapaiTp / $jumlahTp * 100 : 0 }}%"></div>
            </div>
            <p class="mt-2 text-xs text-muted">TP yang masuk penilaian STS</p>
        </x-ui.card>
    </div>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="px-5 pt-4 pb-3"><h2 class="text-[15px] font-bold text-ink">Nilai per Tujuan Pembelajaran</h2><p class="text-xs text-muted">Hanya TP yang masuk penilaian STS yang ditampilkan.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr><th class="w-20 px-5 py-3">Kode</th><th class="px-3 py-3">Tujuan Pembelajaran</th><th class="w-24 px-3 py-3">Nilai</th><th class="w-20 px-3 py-3">KKTP</th><th class="w-36 px-5 py-3 text-right">Capaian</th></tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    @foreach ($m['tp'] as $t)
                        @php $ok = $t['nilai'] >= $kktp; @endphp
                        <tr @class(['bg-warning-faint/60' => ! $ok])>
                            <td class="px-5 py-3 font-bold text-link">{{ $t['kode'] }}</td>
                            <td class="px-3 text-ink">{{ $t['deskripsi'] }}</td>
                            <td class="px-3 text-[15px] font-bold {{ $ok ? 'text-ink' : 'text-warning-strong' }}">{{ $t['nilai'] }}</td>
                            <td class="px-3 text-ink-2">{{ $kktp }}</td>
                            <td class="px-5 text-right"><x-ui.badge :tone="$ok ? 'success' : 'warning'">{{ $ok ? 'Tercapai' : 'Perlu penguatan' }}</x-ui.badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <x-ui.card class="mt-4">
        <h2 class="text-[15px] font-bold text-ink">Deskripsi Capaian</h2>
        <p class="mt-3 rounded-control border border-line bg-surface-2 px-4 py-3 text-sm text-ink">
            {{ $m['deskripsi'] }}@if ($m['penguatan']) <strong class="font-semibold text-warning">{{ $m['penguatan'] }}</strong>@endif
        </p>
    </x-ui.card>
</div>
<x-siswa.bottom-nav />
@endsection
