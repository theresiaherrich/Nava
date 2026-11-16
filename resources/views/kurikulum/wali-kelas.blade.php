@extends('layouts.app')

@section('title', 'Penetapan Wali Kelas')

@push('styles')
    @vite(['resources/js/kurikulum-wali-kelas.js'])
@endpush

@section('content')
<div class="flex min-h-[calc(100vh-64px)] flex-col"
     x-data="waliKelas({ kelas: @js($kelas), guru: @js($guru), perubahan: @js((object) $perubahan) })">
    <div class="flex-1 px-7 pt-7 pb-10">
        <x-ui.page-header title="Penetapan Wali Kelas" role="Kurikulum"
                          subtitle="Satu kelas memiliki satu wali kelas pada setiap semester." />

        <x-ui.alert tone="warning" x-show="tanpaWali.length" x-cloak>
            <x-slot:title><span x-text="`${tanpaWali.length} kelas belum memiliki wali kelas.`"></span></x-slot:title>
            <span x-text="`Kelas ${tanpaWali.map((k) => k.kelas).join(', ')} belum dapat memverifikasi nilai dan mencetak rapor sampai wali kelas ditetapkan.`"></span>
        </x-ui.alert>

        <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
            <x-ui.filter-select label="Semester" model="semester" :options="['Ganjil 2026/2027', 'Genap 2025/2026']" />
            <x-ui.filter-select label="Tingkat" model="tingkat" :options="['Kelas X', 'Kelas XI', 'Kelas XII']" />
            <x-ui.search-input placeholder="Cari kelas atau guru…" x-model="cari" class="ml-auto w-[220px]" />
        </x-ui.card>

        <x-ui.card :padding="false" class="mt-4 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr>
                            <th class="px-5 py-3">Kelas</th>
                            <th class="px-3 py-3">Tingkat</th>
                            <th class="px-3 py-3">Jumlah Siswa</th>
                            <th class="px-3 py-3">Wali Kelas</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="w-[110px] px-5 py-3"><span class="sr-only">Perubahan</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="k in tersaring" :key="k.id_kelas">
                            <tr :class="!k.wali ? 'bg-warning-faint/70' : (k.wali !== k.awal ? 'bg-primary-faint' : '')">
                                <td class="px-5 py-2.5 font-bold text-ink" x-text="k.kelas"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="k.tingkat"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="k.jumlah_siswa"></td>
                                <td class="px-3 py-2">
                                    <label class="relative block w-[300px]">
                                        <span class="sr-only" x-text="`Wali kelas ${k.kelas}`"></span>
                                        <select x-model="k.wali"
                                                class="h-9 w-full appearance-none rounded-control border bg-surface pr-9 pl-3 text-[13px] focus:ring-2 focus:outline-none"
                                                :class="k.wali ? 'border-line text-ink focus:border-primary focus:ring-primary-soft' : 'border-warning-strong/70 text-subtle focus:ring-warning-soft'">
                                            <option value="" :selected="!k.wali">Pilih wali kelas</option>
                                            <template x-for="g in guru" :key="g">
                                                <option :value="g" :selected="k.wali === g" :disabled="!!waliDi(g, k.kelas)"
                                                        x-text="waliDi(g, k.kelas) ? `${g} (wali ${waliDi(g, k.kelas)})` : g"></option>
                                            </template>
                                        </select>
                                        <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
                                    </label>
                                </td>
                                <td class="px-3 py-2.5">
                                    <span class="rounded-md px-2 py-0.5 text-xs font-semibold"
                                          :class="k.wali ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning'"
                                          x-text="k.wali ? 'Ditetapkan' : 'Belum'"></span>
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <span x-show="k.wali !== k.awal" class="inline-flex items-center gap-1.5 text-xs font-semibold text-link">
                                        <span class="size-1.5 rounded-full bg-primary"></span>Diubah
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!tersaring.length">
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-muted">Tidak ada kelas yang cocok.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="flex items-center gap-1.5 border-t border-line-soft px-5 py-3 text-xs text-muted">
                <x-ui.icon name="info" :size="13" />Satu guru hanya dapat menjadi wali untuk satu kelas.
            </p>
        </x-ui.card>
    </div>

    {{-- Bilah perubahan belum disimpan --}}
    <div x-show="diubah.length" x-cloak x-transition.opacity
         class="sticky bottom-0 z-20 flex flex-wrap items-center justify-between gap-3 border-t border-line bg-surface px-7 py-3 shadow-[0_-4px_16px_rgb(15_23_42/0.06)]"
         role="region" aria-label="Perubahan belum disimpan">
        <p class="flex items-center gap-2 text-sm">
            <span class="size-2 rounded-full bg-primary"></span>
            <strong class="font-bold text-link" x-text="`${diubah.length} perubahan belum disimpan`"></strong>
            <span class="text-[13px] text-muted" x-text="`· ${diubah.map((k) => k.kelas).join(', ')}`"></span>
        </p>
        <div class="flex gap-2.5">
            <x-ui.button variant="secondary" x-on:click="batalkan()">Batalkan</x-ui.button>
            <x-ui.button x-on:click="simpan()">Simpan Perubahan</x-ui.button>
        </div>
    </div>
</div>
@endsection
