@extends('layouts.app')

@section('title', 'Data Guru')

@push('styles')
    @vite(['resources/js/operator-guru.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="guruPage(JSON.parse(document.getElementById('data-guru').textContent))">
    <x-ui.json-data id="data-guru" :data="['guru' => $guru->items(), 'mapel' => $mapel]" />

    <x-ui.page-header title="Data Guru" role="Operator" subtitle="Guru ASN dan GTT memakai akun yang sama untuk seluruh modul.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="download" size="lg" :href="route('data-guru.format')">Unduh Format Excel</x-ui.button>
            <x-ui.button icon="upload" size="lg" x-on:click="$dispatch('open-modal', 'impor')">Impor Excel</x-ui.button>
            <x-ui.button variant="secondary" icon="plus" size="lg" x-on:click="tambah()">Tambah Guru</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-link label="Status" param="status" :options="['' => 'Semua', 'ASN' => 'ASN', 'GTT' => 'GTT']" />
        <x-ui.filter-link label="Mapel" param="mapel" :options="['' => 'Semua'] + array_combine($mapel, $mapel)" />
        <x-ui.badge tone="info" class="!px-2.5 !py-1 !text-[13px]">{{ $asn }} ASN</x-ui.badge>
        <x-ui.badge tone="violet" class="!px-2.5 !py-1 !text-[13px]">{{ $gtt }} GTT</x-ui.badge>
        <x-ui.search-form placeholder="Cari nama atau NIP…" class="ml-auto w-[240px]" />
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="px-5 pt-4 pb-3">
            <h2 class="text-[15px] font-bold text-ink">Daftar Guru</h2>
            <p class="text-xs text-muted">{{ $guru->total() }} guru · urut nama A–Z</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="w-[56px] px-5 py-3">No</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">NIP</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Peran</th>
                        <th class="px-3 py-3">Mapel diampu</th>
                        <th class="px-3 py-3">Akun</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(g, i) in guru" :key="g.id_guru">
                        <tr>
                            <td class="px-5 py-3 text-ink-2" x-text="{{ max(0, $guru->firstItem() - 1) }} + i + 1"></td>
                            <td class="px-3 py-3 font-semibold text-ink" x-text="g.nama"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="g.nip ?? '—'"></td>
                            <td class="px-3 py-3"><span :class="$store.ref.badge(g.status === 'ASN' ? 'info' : 'violet')" x-text="g.status"></span></td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="p in g.peran" :key="p"><span :class="$store.ref.peranBadge(p)" x-text="p"></span></template>
                                </div>
                            </td>
                            <td class="px-3 py-3"><span class="rounded-md border border-line px-1.5 py-0.5 text-xs font-semibold text-ink-2" x-text="g.mapel"></span></td>
                            <td class="px-3 py-3">
                                <span :class="$store.ref.badge({ aktif: 'success', belum: 'warning', nonaktif: 'neutral' }[g.akun])"
                                      x-text="{ aktif: 'Aktif', belum: 'Belum dibuat', nonaktif: 'Nonaktif' }[g.akun]"></span>
                            </td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-1.5">
                                    <x-ui.icon-button icon="pencil" label="Ubah data guru" x-on:click="ubah(g)" />
                                    <x-ui.icon-button icon="key" label="Buat akun atau reset sandi" x-on:click="akun(g)" />
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!guru.length"><td colspan="8" class="px-5 py-10 text-center text-sm text-muted">Tidak ada guru yang cocok dengan filter.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="text-xs text-muted">NIP hanya untuk guru ASN</p>
            <x-ui.pager :paginator="$guru" satuan="guru" />
        </div>
    </x-ui.card>

    <x-ui.impor-modal judul="Impor Data Guru" satuan="guru" :hasil="$impor" :format-url="route('data-guru.format')" />

    {{-- Tambah / Ubah guru --}}
    <x-ui.modal name="guru" width="max-w-[480px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="editId ? 'Ubah Data Guru' : 'Tambah Guru'"></h2>
        <p class="mt-1 text-[13px] text-muted">Akun guru dapat dibuat setelah data tersimpan.</p>
        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <x-ui.field label="Nama lengkap & gelar" for="gr-nama" required error="error.nama"><x-ui.input id="gr-nama" x-model="form.nama" error="error.nama" /></x-ui.field>
            <div class="flex flex-wrap items-end gap-3">
                <x-ui.field label="NIP" for="gr-nip" error="error.nip" class="min-w-0 flex-1">
                    <x-ui.input id="gr-nip" x-model="form.nip" error="error.nip" inputmode="numeric" maxlength="18" x-bind:disabled="form.status === 'GTT'" x-bind:placeholder="form.status === 'GTT' ? 'Tidak diperlukan untuk GTT' : '18 digit'" />
                </x-ui.field>
                <div>
                    <p class="mb-1.5 text-[13px] font-semibold text-ink">Status</p>
                    <x-ui.segmented :options="['ASN' => 'ASN', 'GTT' => 'GTT']" model="form.status" label="Status kepegawaian" size="sm" />
                </div>
            </div>
            <x-ui.field label="Mapel diampu" for="gr-mapel"><x-ui.select id="gr-mapel" x-model="form.mapel" :options="$mapel" /></x-ui.field>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'guru')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
