@extends('layouts.app')

@section('title', 'Rapor Saya')

@push('styles')
    @vite(['resources/js/siswa.js'])
@endpush

@section('content')
<div class="px-4 pt-5 pb-28 md:px-7 md:pt-7 md:pb-10" x-data="raporSaya(JSON.parse(document.getElementById('data-rapor').textContent))">
    <x-ui.json-data id="data-rapor" :data="['rapor' => $rapor, 'dokumen' => $dokumen, 'jenis' => $jenis]" />

    <x-ui.page-header title="Rapor Saya" role="Siswa" subtitle="Rapor resmi yang sudah diterbitkan oleh sekolah.">
        <x-slot:actions>
            <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="jenis" label="Jenis rapor" size="sm" />
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 lg:grid-cols-[301px_minmax(0,1fr)]">
        {{-- Rapor tersedia --}}
        <x-ui.card class="!p-3">
            <h2 class="px-1 pt-1 pb-2.5 text-[15px] font-bold text-ink">Rapor tersedia</h2>
            <ul class="space-y-2" role="listbox" aria-label="Rapor tersedia">
                @foreach ($rapor as $x)
                    <li>
                        <button type="button" role="option" :aria-selected="jenis === @js($x['jenis'])" x-on:click="jenis = @js($x['jenis'])"
                                class="relative w-full rounded-control border px-3 py-2.5 text-left"
                                :class="jenis === @js($x['jenis'])
                                    ? 'border-primary-soft bg-primary-faint before:absolute before:inset-y-0 before:left-0 before:w-[3px] before:rounded-l-control before:bg-primary'
                                    : '{{ $x['terbit'] ? 'border-line hover:bg-surface-2' : 'border-dashed border-line bg-surface-2/60' }}'">
                            <span class="block text-sm font-bold" :class="jenis === @js($x['jenis']) ? 'text-link' : '{{ $x['terbit'] ? 'text-ink' : 'text-muted' }}'">{{ $x['label'] }} · {{ $x['periode'] }}</span>
                            <span class="mt-1.5 flex items-center gap-2">
                                @if ($x['terbit'])
                                    <x-ui.badge tone="success" icon="check">Terbit {{ $x['terbit'] }}</x-ui.badge><span class="text-xs text-muted">Versi {{ $x['versi'] }}</span>
                                @else
                                    <x-ui.badge>Belum terbit</x-ui.badge>
                                @endif
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        {{-- Pratinjau --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <template x-if="r.terbit">
                <div>
                    <div class="flex flex-wrap items-center gap-3 border-b border-line-soft px-4 py-3">
                        <p class="text-[13px] text-muted">Pratinjau rapor · <strong class="text-ink" x-text="`Halaman ${lembar} dari ${jumlahLembar}`"></strong><span x-show="jenis === 'sts'" x-text="` · ${judul}`"></span></p>
                        <div x-show="jenis === 'semester'" class="flex items-center gap-2">
                            <x-ui.badge>2 halaman</x-ui.badge>
                            <div role="group" aria-label="Lompat ke halaman" class="inline-flex items-center rounded-control border border-line p-0.5">
                                <template x-for="n in [1, 2]" :key="n">
                                    <button type="button" x-on:click="keLembar(n)" :aria-pressed="lembar === n" x-text="`Halaman ${n}`"
                                            class="rounded-[6px] px-2.5 py-1 text-[13px]" :class="lembar === n ? 'bg-primary-faint font-medium text-link' : 'text-muted hover:text-ink'"></button>
                                </template>
                            </div>
                        </div>
                        <div class="ml-auto flex gap-2.5">
                            <x-ui.button variant="secondary" icon="printer" x-on:click="window.print()">Cetak</x-ui.button>
                            <x-ui.button icon="download" x-on:click="unduh()">Unduh PDF</x-ui.button>
                        </div>
                    </div>
                    <div class="relative max-h-[880px] overflow-auto bg-neutral-soft px-4 py-6" x-ref="area" id="area-cetak" x-on:scroll.debounce.100ms="pantauLembar($el)">
                        <div class="w-max min-w-full"><x-rapor.dokumen doc="doc" /></div>
                    </div>
                </div>
            </template>
            <template x-if="!r.terbit">
                <div class="flex flex-col items-center px-6 py-24 text-center">
                    <span class="relative flex size-28 items-center justify-center rounded-full bg-neutral-soft text-subtle">
                        <x-ui.icon name="calendar" :size="44" :stroke="1.4" />
                        <span class="absolute right-0 bottom-1 flex size-9 items-center justify-center rounded-full border border-line bg-surface text-ink-2"><x-ui.icon name="lock" :size="15" /></span>
                    </span>
                    <p class="mt-5 text-lg font-bold text-ink" x-text="`${r.label} belum diterbitkan`"></p>
                    <p class="mt-2 max-w-[420px] text-sm text-muted" x-text="`${r.label} ${r.periode} akan tersedia setelah diterbitkan oleh sekolah (${r.jenis === 'semester' ? 'setelah 18 Desember 2026' : 'setelah nilai dikunci'}).`"></p>
                    <x-ui.button variant="secondary" icon="file-text" class="mt-5" x-show="r.jenis === 'semester' && rapor.sts.terbit" x-on:click="jenis = 'sts'">Lihat Rapor STS</x-ui.button>
                </div>
            </template>
        </x-ui.card>
    </div>

    <p class="mt-4 flex items-center gap-1.5 text-[13px] text-muted"><x-ui.icon name="info" :size="14" />Jika ada data yang tidak sesuai, hubungi wali kelas.</p>
</div>
<x-siswa.bottom-nav />
@endsection
