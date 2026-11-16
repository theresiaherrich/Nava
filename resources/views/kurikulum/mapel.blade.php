@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@push('styles')
    @vite(['resources/js/kurikulum-mapel.js'])
@endpush

@php
    // Tata letak dua kolom sesuai desain 4.03.
    $kolom = [['wajib', 'pilihan'], ['bahasa_asing', 'mulok', 'agama']];
@endphp

@section('content')
<div class="px-7 pt-7 pb-10"
     x-data="mataPelajaran({ mapel: @js($mapel), kelompok: @js($kelompok), bukaModal: @js(request('modal') === 'mapel') })">
    <x-ui.page-header title="Mata Pelajaran" role="Kurikulum"
                      subtitle="Daftar mata pelajaran per fase sesuai Kurikulum Merdeka." class="mb-3">
        <x-slot:actions>
            <x-ui.button icon="plus" size="lg" x-on:click="tambah()">Tambah Mapel</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div role="tablist" aria-label="Fase" class="flex gap-6">
            @foreach (['E' => 'Fase E (Kelas X)', 'F' => 'Fase F (Kelas XI & XII)'] as $kode => $teks)
                <button type="button" role="tab" :aria-selected="fase === '{{ $kode }}'" x-on:click="fase = '{{ $kode }}'"
                        class="border-b-2 px-0.5 pt-1 pb-2 text-sm"
                        :class="fase === '{{ $kode }}' ? 'border-primary font-semibold text-link' : 'border-transparent text-muted hover:text-ink'">
                    {{ $teks }}
                </button>
            @endforeach
        </div>
        <x-ui.search-input placeholder="Cari mapel…" x-model="cari" class="w-[220px]" />
    </div>

    <div class="grid items-start gap-4 xl:grid-cols-2">
        @foreach ($kolom as $daftarKelompok)
            <div class="flex flex-col gap-4">
                @foreach ($daftarKelompok as $kode)
                    <x-ui.card :padding="false" class="overflow-hidden" x-show="daftar('{{ $kode }}').length">
                        <div class="flex items-center justify-between gap-3 px-5 pt-5 pb-3">
                            <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink">
                                {{ $kelompok[$kode] }}
                                <span class="rounded-full bg-primary-soft px-2 text-xs font-bold text-link" x-text="daftar('{{ $kode }}').length"></span>
                            </h2>
                            @if ($kode === 'bahasa_asing')
                                <x-ui.badge tone="violet">Identitas sekolah</x-ui.badge>
                            @elseif ($kode === 'agama')
                                <span class="text-xs text-muted">Rombel campuran per agama</span>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[480px] text-left text-[13px]">
                                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                                    <tr>
                                        <th class="w-[96px] px-5 py-2.5">Kode</th>
                                        <th class="px-3 py-2.5">Nama</th>
                                        <th class="w-[116px] px-3 py-2.5">Jumlah rombel</th>
                                        <th class="w-[80px] px-3 py-2.5">Status</th>
                                        <th class="w-[56px] px-5 py-2.5"><span class="sr-only">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line-soft">
                                    <template x-for="m in daftar('{{ $kode }}')" :key="m.id_mapel">
                                        <tr>
                                            <td class="px-5 py-3 whitespace-nowrap text-muted" x-text="m.kode"></td>
                                            <td class="px-3 py-3 font-semibold text-ink" x-text="m.nama"></td>
                                            <td class="px-3 py-3 text-ink-2" x-text="m.jumlah_rombel"></td>
                                            <td class="px-3 py-3">
                                                <span x-show="m.aktif"><x-ui.badge tone="success">Aktif</x-ui.badge></span>
                                                <span x-show="!m.aktif"><x-ui.badge>Nonaktif</x-ui.badge></span>
                                            </td>
                                            <td class="px-5 py-2 text-right">
                                                <x-ui.icon-button icon="pencil" label="Ubah mapel" x-on:click="ubah(m)" />
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
        @endforeach
    </div>

    <p x-show="!Object.keys(kelompok).some((k) => daftar(k).length)" x-cloak class="mt-10 text-center text-sm text-muted">
        Tidak ada mapel yang cocok dengan pencarian.
    </p>

    {{-- 4.04 Tambah / Edit Mapel --}}
    <x-ui.modal name="mapel" width="max-w-[480px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="editId ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran'"></h2>
        <p class="mt-1 text-[13px] text-muted">Mapel baru langsung dapat dipakai untuk membuat rombel.</p>

        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <div class="grid grid-cols-2 gap-3.5">
                <x-ui.field label="Kode" for="mapel-kode" required error="error.kode">
                    <input id="mapel-kode" x-model="form.kode" class="h-10 rounded-control border px-3 text-sm text-ink focus:ring-2 focus:outline-none"
                           :class="error.kode ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'">
                </x-ui.field>
                <x-ui.field label="Nama" for="mapel-nama" required error="error.nama">
                    <input id="mapel-nama" x-model="form.nama" class="h-10 rounded-control border px-3 text-sm text-ink focus:ring-2 focus:outline-none"
                           :class="error.nama ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'">
                </x-ui.field>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-[13px] font-semibold text-ink">Fase<span class="text-danger"> *</span></span>
                <x-ui.segmented :options="['E' => 'E', 'F' => 'F']" model="form.fase" label="Fase" size="sm" />
            </div>

            <x-ui.field label="Kelompok" for="mapel-kelompok" required>
                <div class="relative">
                    <select id="mapel-kelompok" x-model="form.kelompok"
                            class="h-10 w-full appearance-none rounded-control border border-line bg-surface px-3 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                        @foreach ($kelompok as $kode => $teks)
                            <option value="{{ $kode }}">{{ $teks === 'Mata Pelajaran Wajib' ? 'Wajib' : ($teks === 'Mata Pelajaran Pilihan' ? 'Pilihan' : $teks) }}</option>
                        @endforeach
                    </select>
                    <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
                </div>
            </x-ui.field>

            <label class="flex cursor-pointer items-center gap-3 rounded-control border border-line px-3 py-2.5">
                <button type="button" role="switch" :aria-checked="form.aktif" x-on:click="form.aktif = !form.aktif"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="form.aktif ? 'bg-primary' : 'bg-line'">
                    <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow transition-transform" :class="form.aktif && 'translate-x-4'"></span>
                    <span class="sr-only">Aktif</span>
                </button>
                <span>
                    <span class="block text-[13px] font-semibold text-ink">Aktif</span>
                    <span class="block text-xs text-muted">Mapel muncul di daftar pembuatan rombel</span>
                </span>
            </label>

            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'mapel')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan Mapel</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
