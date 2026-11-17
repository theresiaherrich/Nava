@extends('layouts.app')

@section('title', 'Arsip & Versi Rapor')

@push('styles')
    @vite(['resources/js/kurikulum-rapor.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="arsipRapor(JSON.parse(document.getElementById('data-arsip').textContent))">
    <x-ui.json-data id="data-arsip" :data="['arsip' => $arsip, 'dokumen' => $dokumen]" />

    <x-ui.page-header title="Arsip & Versi Rapor" role="Kurikulum" subtitle="Setiap versi rapor menyimpan data final saat dibuat. Cetak ulang versi lama memakai data lama." />

    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-select label="Tahun ajaran" model="tahun" :options="['2026/2027', '2025/2026']" />
        <x-ui.filter-select label="Semester" model="semester" :options="['Ganjil', 'Genap']" />
        <x-ui.filter-select label="Jenis rapor" model="jenisFilter" :options="['Semua', 'Rapor STS', 'Rapor Semester']" />
        <x-ui.filter-select label="Kelas" model="kelas" :options="$kelas" />
        <x-ui.search-input placeholder="Cari nama siswa…" x-model="cari" class="ml-auto w-[200px]" />
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1040px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="px-5 py-3">Nama Siswa</th><th class="px-3 py-3">Kelas</th><th class="px-3 py-3">Jenis Rapor</th><th class="px-3 py-3">Versi</th>
                        <th class="px-3 py-3">Dibuat</th><th class="px-3 py-3">Oleh</th><th class="px-3 py-3">Alasan</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <template x-for="r in barisHalaman" :key="r.id">
                    <tbody class="border-b border-line-soft">
                        <tr :class="buka === r.id && 'bg-violet-soft/30'">
                            <td class="px-5 py-3">
                                <button type="button" x-on:click="buka = buka === r.id ? null : r.id" :aria-expanded="buka === r.id" class="flex items-center gap-2 font-semibold text-ink">
                                    <x-ui.icon name="chevron-right" :size="13" class="text-muted transition-transform" x-bind:class="buka === r.id && 'rotate-90'" />
                                    <span x-text="r.nama"></span>
                                </button>
                            </td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.kelas"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.jenis === 'sts' ? 'Rapor STS' : 'Rapor Semester'"></td>
                            <td class="px-3 py-3"><span class="rounded-md px-2 py-0.5 text-xs font-semibold" :class="r.versi[0].v > 1 ? 'bg-violet-soft text-violet' : 'border border-line text-ink'" x-text="r.versi[0].label"></span></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.dibuat"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.oleh"></td>
                            <td class="px-3 py-3" :class="r.alasan ? 'text-ink' : 'text-subtle'" x-text="r.alasan ?? '—'"></td>
                            <td class="px-3 py-3"><span :class="$store.ref.badge(r.status === 'terbit' ? 'success' : 'neutral')" x-text="r.status === 'terbit' ? 'Terbit' : 'Arsip'"></span></td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-2">
                                    <x-ui.button variant="secondary" size="sm" x-on:click="lihat(r)">Pratinjau</x-ui.button>
                                    <x-ui.button variant="secondary" size="sm" icon="download" x-on:click="unduh(r)">Unduh</x-ui.button>
                                </div>
                            </td>
                        </tr>
                        <tr x-show="buka === r.id" x-cloak class="bg-violet-soft/20">
                            <td colspan="9" class="px-5 pt-1 pb-4">
                                <p class="mb-2 pl-6 text-[11px] font-bold text-violet uppercase" x-text="`Riwayat versi · ${r.nama}`"></p>
                                <ul class="space-y-2 pl-6">
                                    <template x-for="v in r.versi" :key="v.v">
                                        <li class="flex flex-wrap items-center gap-3 rounded-card border border-line bg-surface px-4 py-3">
                                            <span class="size-2 rounded-full" :class="v.status === 'terbit' ? 'bg-violet' : 'bg-subtle'"></span>
                                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold" :class="v.v > 1 ? 'bg-violet-soft text-violet' : 'border border-line text-ink'" x-text="v.label"></span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block text-[13px] font-bold text-ink" x-text="`${v.status === 'terbit' ? 'Terbit' : 'Dibuat'} ${v.waktu}`"></span>
                                                <span class="block text-xs text-muted" x-text="v.ket"></span>
                                            </span>
                                            <span :class="$store.ref.badge(v.status === 'terbit' ? 'success' : 'neutral')" x-text="v.status === 'terbit' ? 'Terbit' : 'Arsip'"></span>
                                            <x-ui.button variant="secondary" size="sm" icon="download" x-on:click="unduh(r, v.label)">Unduh ulang</x-ui.button>
                                        </li>
                                    </template>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </template>
            </table>
            <p x-show="!barisHalaman.length" class="px-5 py-10 text-center text-sm text-muted">Tidak ada arsip yang cocok dengan filter.</p>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="lock" :size="13" />Versi lama tidak dapat diubah.</p>
            <div class="flex items-center gap-3"><span class="text-xs text-muted" x-text="rentang"></span><x-ui.pagination page="page" pages="halaman" /></div>
        </div>
    </x-ui.card>

    <x-ui.modal name="pratinjau" width="max-w-[640px]">
        <template x-if="pratinjau">
            <div>
                <h2 class="pr-10 text-lg font-bold text-ink" x-text="`${pratinjau.nama} · ${pratinjau.jenis === 'sts' ? 'Rapor STS' : 'Rapor Semester'} ${pratinjau.versi[0].label}`"></h2>
                <p class="mt-1 text-[13px] text-muted">Snapshot data final saat versi dibuat — tidak berubah walau nilai diperbarui.</p>
                <div class="mt-4 max-h-[70vh] overflow-auto rounded-card bg-neutral-soft px-2 py-5">
                    <x-rapor.dokumen doc="dokumen[pratinjau.id]" />
                </div>
            </div>
        </template>
    </x-ui.modal>
</div>
@endsection
