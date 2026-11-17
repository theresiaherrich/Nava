@extends('layouts.app')

@section('title', 'Nilai per Mapel')

@push('styles')
    @vite(['resources/js/siswa.js'])
@endpush

@section('content')
<div class="px-4 pt-5 pb-28 md:px-7 md:pt-7 md:pb-10" x-data="nilaiSiswa(JSON.parse(document.getElementById('data-nilai').textContent))">
    <x-ui.json-data id="data-nilai" :data="['nilai' => $nilai]" />

    <x-ui.page-header title="Nilai per Mata Pelajaran" role="Siswa" :subtitle="$nilai ? 'Ringkasan nilai resmi dari Rapor STS Ganjil 2026/2027.' : 'Nilai resmi tampil setelah rapor diterbitkan sekolah.'">
        <x-slot:actions>
            <div class="inline-flex items-center rounded-control bg-neutral-soft p-0.5" role="group" aria-label="Periode rapor">
                <span class="rounded-[6px] bg-surface px-2.5 py-1 text-xs font-medium text-link shadow-card" aria-current="true">Rapor STS</span>
                <span class="group relative">
                    <button type="button" disabled aria-describedby="tip-semester" class="cursor-not-allowed rounded-[6px] px-2.5 py-1 text-xs font-medium text-subtle">Rapor Semester</button>
                    <span id="tip-semester" role="tooltip" class="pointer-events-none absolute top-full left-1/2 z-20 mt-2 hidden -translate-x-1/2 rounded-md bg-toast px-2.5 py-1.5 text-xs whitespace-nowrap text-white group-hover:block">Belum terbit</span>
                </span>
            </div>
            <x-ui.search-input placeholder="Cari mapel…" x-model="cari" class="w-full sm:w-[200px]" />
        </x-slot:actions>
    </x-ui.page-header>

    @if (! $nilai)
        <x-ui.card :padding="false"><x-ui.empty-state icon="table" title="Belum ada nilai" description="Nilai akan muncul setelah rapor diterbitkan sekolah." /></x-ui.card>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <template x-for="m in kartu" :key="m.id">
                <x-ui.card class="flex flex-col">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-control text-base font-bold"
                              :class="{ 'Bahasa Asing': 'bg-violet-soft text-violet', Agama: 'bg-success-soft text-success' }[m.kelompok] ?? 'bg-primary-soft text-link'" x-text="m.mapel.replace(/^Pend\. /, '')[0]" aria-hidden="true"></span>
                        <div class="min-w-0"><p class="truncate text-[15px] font-bold text-ink" x-text="m.mapel"></p><p class="text-xs text-muted" x-text="m.kelompok"></p></div>
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-2">
                        <p class="text-[32px] leading-none font-bold" :class="m.tercapai ? 'text-ink' : 'text-warning-strong'" x-text="m.nilai"></p>
                        <span :class="$store.ref.badge(m.tercapai ? 'success' : 'warning')" x-text="m.tercapai ? 'Tercapai' : 'Perlu penguatan'"></span>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" :aria-label="`Nilai ${m.mapel}`" :aria-valuenow="m.nilai" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full" :class="m.tercapai ? 'bg-success-strong' : 'bg-warning-strong'" :style="`width: ${m.nilai}%`"></div>
                    </div>
                    <div class="mt-auto flex items-center justify-between pt-5 text-xs">
                        <span class="text-muted">KKTP {{ $kktp }}</span>
                        <a :href="@js(route('nilai-per-mapel.show', '__ID__')).replace('__ID__', m.id)" class="inline-flex items-center gap-0.5 text-[13px] font-semibold text-link hover:underline" :aria-label="`Lihat detail nilai ${m.mapel}`">Lihat detail <x-ui.icon name="chevron-right" :size="13" /></a>
                    </div>
                </x-ui.card>
            </template>
        </div>
        <x-ui.card x-show="!kartu.length" x-cloak :padding="false"><x-ui.empty-state icon="search" title="Mapel tidak ditemukan" description="Coba kata kunci lain." class="!py-12" /></x-ui.card>

        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[13px] text-muted" x-text="`Menampilkan ${kartu.length} dari ${tersaring.length} mata pelajaran`"></p>
            <x-ui.pagination page="page" pages="halaman" x-show="halaman > 1" />
        </div>
    @endif
</div>
<x-siswa.bottom-nav />
@endsection
