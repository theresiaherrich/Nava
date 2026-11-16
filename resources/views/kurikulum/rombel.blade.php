@extends('layouts.app')

@section('title', 'Rombel & Pengampu')

@push('styles')
    @vite(['resources/js/kurikulum-rombel.js'])
@endpush

@php
    $inputKelas = 'h-10 w-full rounded-control border bg-surface px-3 text-sm text-ink focus:ring-2 focus:outline-none';
@endphp

@section('content')
<div class="px-7 pt-7 pb-10"
     x-data="rombelPage(JSON.parse(document.getElementById('data-rombel').textContent))">
    <x-ui.json-data id="data-rombel" :data="[
        'rombel' => $rombel,
        'calon' => $calonPengampu,
        'daftarMapel' => $daftarMapel,
        'daftarKelas' => $daftarKelas,
        'bukaModal' => request('modal') === 'buat-rombel',
        'pilihGuru' => request('pilih-guru'),
    ]" />
    <x-ui.page-header title="Rombel & Pembagian Mengajar" role="Kurikulum"
                      subtitle="Atur kelompok belajar setiap mapel dan guru pengampunya.">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="copy" size="lg" x-on:click="salinSemesterLalu()">Salin dari semester lalu</x-ui.button>
            <x-ui.button icon="plus" size="lg" x-on:click="bukaBuat()">Buat Rombel</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="info" title="Rombel campuran">
        Rombel campuran berisi siswa dari beberapa kelas asal. Nilai tetap masuk ke rapor kelas asal masing-masing.
    </x-ui.alert>

    {{-- Ringkasan --}}
    <div class="mt-4 flex flex-wrap items-center gap-2 text-[13px] font-semibold">
        <span class="rounded-md bg-neutral-soft px-2.5 py-1 text-ink-2"><span x-text="ringkasan.total"></span> rombel</span>
        <span class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2.5 py-1 text-success">
            <x-ui.icon name="check" :size="13" :stroke="2.2" /><span x-text="ringkasan.adaPengampu"></span> ada pengampu
        </span>
        <span class="inline-flex items-center gap-1 rounded-md bg-warning-soft px-2.5 py-1 text-warning">
            <x-ui.icon name="alert" :size="13" :stroke="2.2" /><span x-text="ringkasan.tanpaPengampu"></span> tanpa pengampu
        </span>
        <span class="rounded-md bg-violet-soft px-2.5 py-1 text-violet"><span x-text="ringkasan.campuran"></span> rombel campuran</span>
    </div>

    {{-- Filter --}}
    <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-select label="Semester" model="semester" :options="['Ganjil 2026/2027', 'Genap 2025/2026']" />
        <x-ui.filter-select label="Tingkat" model="tingkat" :options="['Kelas X', 'Kelas XI', 'Kelas XII']" />
        <div x-data="{ buka: false }" class="relative" x-on:click.outside="buka = false" x-on:keydown.escape="buka = false">
            <button type="button" x-on:click="buka = !buka" :aria-expanded="buka" aria-haspopup="listbox"
                    class="flex h-9 items-center gap-2 rounded-control border border-line bg-surface px-3 text-[13px] text-muted hover:border-subtle">
                Mapel: <span class="font-medium text-ink" x-text="mapel"></span>
                <x-ui.icon name="chevron-down" :size="14" class="ml-1 text-ink-2" />
            </button>
            <ul x-show="buka" x-cloak role="listbox" aria-label="Mapel"
                class="absolute top-full left-0 z-30 mt-1 max-h-72 min-w-full overflow-y-auto rounded-control border border-line bg-surface py-1 shadow-pop">
                <template x-for="m in daftarMapelFilter" :key="m">
                    <li><button type="button" role="option" :aria-selected="mapel === m" x-on:click="mapel = m; buka = false" x-text="m"
                                class="block w-full px-3 py-2 text-left text-[13px] whitespace-nowrap hover:bg-surface-2"
                                :class="mapel === m ? 'font-semibold text-link' : 'text-ink'"></button></li>
                </template>
            </ul>
        </div>
        <span class="ml-1 text-[13px] text-muted">Jenis</span>
        <x-ui.segmented :options="['semua' => 'Semua', 'reguler' => 'Reguler', 'campuran' => 'Campuran']" model="jenis" label="Jenis rombel" />
        <x-ui.search-input placeholder="Cari rombel atau guru…" x-model="cari" class="ml-auto w-[200px]" />
    </x-ui.card>

    {{-- Tabel rombel --}}
    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="px-5 py-3">Nama Rombel</th>
                        <th class="px-3 py-3">Mapel</th>
                        <th class="px-3 py-3">Jenis</th>
                        <th class="px-3 py-3">Kelas Asal Peserta</th>
                        <th class="px-3 py-3">Peserta</th>
                        <th class="px-3 py-3">Guru Pengampu</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="r in barisHalaman" :key="r.id_rombel">
                        <tr :class="!r.guru && 'bg-warning-faint/60'">
                            <td class="px-5 py-3 font-bold text-ink" x-text="r.nama"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.mapel"></td>
                            <td class="px-3 py-3">
                                <span class="rounded-md px-2 py-0.5 text-xs font-semibold"
                                      :class="r.jenis === 'campuran' ? 'bg-violet-soft text-violet' : 'bg-neutral-soft text-neutral'"
                                      x-text="r.jenis === 'campuran' ? 'Campuran' : 'Reguler'"></span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-1">
                                    <template x-for="k in kelasRingkas(r.kelas_asal)">
                                        <span :class="k === '···' ? 'px-0.5 text-xs text-subtle' : 'rounded-md border border-line px-1.5 py-0.5 text-xs font-semibold text-ink-2'" x-text="k"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.jumlah_peserta"></td>
                            <td class="px-3 py-3">
                                <span x-show="r.guru" class="inline-flex items-center gap-1.5 rounded-full border border-line py-0.5 pr-2.5 pl-0.5 text-xs font-semibold text-ink">
                                    <span class="flex size-5 items-center justify-center rounded-full text-[8px] font-bold text-white"
                                          :class="warnaAvatar(r.guru ?? '')" x-text="inisial(r.guru ?? '')"></span>
                                    <span x-text="namaPendek(r.guru ?? '')"></span>
                                </span>
                                <button type="button" x-show="!r.guru" :data-pilih-guru="r.id_rombel"
                                        x-on:click.stop="bukaPicker(r.id_rombel, r.mapel, $el)"
                                        class="inline-flex items-center gap-1 rounded-full border border-warning-strong/50 bg-warning-faint px-2.5 py-0.5 text-xs font-semibold text-warning hover:bg-warning-soft">
                                    <x-ui.icon name="plus" :size="12" :stroke="2.4" /> Pilih guru
                                </button>
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <a :href="`{{ url('kurikulum/rombel') }}/${r.id_rombel}`"
                                   class="inline-flex h-8 items-center rounded-control border border-line bg-surface px-3 text-[13px] font-semibold text-ink hover:bg-surface-2">Detail</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!barisHalaman.length">
                        <td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Tidak ada rombel yang cocok dengan filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="text-xs text-muted" x-text="`Menampilkan ${barisHalaman.length} dari ${tersaring.length} rombel ${tingkat}`"></p>
            <x-ui.pagination page="page" pages="halaman" />
        </div>
    </x-ui.card>

    <x-ui.guru-picker />

    {{-- 4.07 Buat Rombel --}}
    <x-ui.modal name="buat-rombel" title="Buat Rombel" subtitle="Rombel adalah kelompok belajar satu mapel pada satu semester." width="max-w-[560px]">
        <form class="space-y-4" x-on:submit.prevent="simpanRombel()" novalidate>
            <div class="grid grid-cols-2 gap-3.5">
                <x-ui.field label="Mata pelajaran" for="rb-mapel" required error="error.mapel">
                    <div class="relative">
                        <select id="rb-mapel" x-model="form.mapel" class="{{ $inputKelas }} appearance-none"
                                :class="error.mapel ? 'border-danger' : 'border-line focus:border-primary focus:ring-primary-soft'">
                            <option value="">Pilih mapel</option>
                            <template x-for="m in daftarMapel" :key="m"><option :value="m" x-text="m" :selected="form.mapel === m"></option></template>
                        </select>
                        <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
                    </div>
                </x-ui.field>
                <x-ui.field label="Nama rombel" for="rb-nama" required error="error.nama">
                    <input id="rb-nama" x-model="form.nama" class="{{ $inputKelas }}" placeholder="mis. Mandarin 3"
                           :class="error.nama ? 'border-danger' : 'border-line focus:border-primary focus:ring-primary-soft'">
                </x-ui.field>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-[13px] font-semibold text-ink">Jenis<span class="text-danger"> *</span></span>
                <x-ui.segmented :options="['reguler' => 'Reguler', 'campuran' => 'Campuran']" model="form.jenis" label="Jenis rombel" size="sm" />
            </div>

            <x-ui.field label="Kelas asal peserta" for="rb-kelas" required error="error.kelas" hint="Peserta dipilih dari siswa kelas asal ini">
                <div class="relative" x-data="{ fokus: false }" x-on:click.outside="fokus = false">
                    <div class="flex min-h-10 flex-wrap items-center gap-1.5 rounded-control border bg-surface px-2 py-1.5"
                         :class="error.kelas ? 'border-danger' : 'border-line focus-within:border-primary focus-within:ring-2 focus-within:ring-primary-soft'">
                        <template x-for="k in form.kelas" :key="k">
                            <span class="inline-flex items-center gap-1 rounded-md bg-violet-soft px-2 py-0.5 text-xs font-semibold text-violet">
                                <span x-text="k"></span>
                                <button type="button" x-on:click="hapusKelas(k)" :aria-label="`Hapus ${k}`" class="hover:text-violet-strong">
                                    <x-ui.icon name="x" :size="12" :stroke="2.4" />
                                </button>
                            </span>
                        </template>
                        <input id="rb-kelas" x-model="kelasCari" x-on:focus="fokus = true" placeholder="Tambah kelas…" autocomplete="off"
                               x-on:keydown.enter.prevent="tambahKelas(saranKelas[0])"
                               x-on:keydown.backspace="!kelasCari && form.kelas.length && hapusKelas(form.kelas[form.kelas.length - 1])"
                               class="min-w-24 flex-1 border-0 bg-transparent px-1 text-sm text-ink placeholder:text-subtle focus:outline-none">
                    </div>
                    <ul x-show="fokus && saranKelas.length" x-cloak role="listbox" aria-label="Saran kelas"
                        class="absolute top-full left-0 z-10 mt-1 flex w-full flex-wrap gap-1.5 rounded-control border border-line bg-surface p-2 shadow-pop">
                        <template x-for="k in saranKelas" :key="k">
                            <li><button type="button" role="option" x-on:click="tambahKelas(k)" x-text="k"
                                        class="rounded-md border border-line px-2 py-1 text-xs font-semibold text-ink-2 hover:border-primary hover:text-link"></button></li>
                        </template>
                    </ul>
                </div>
            </x-ui.field>

            <div class="grid grid-cols-2 gap-3.5">
                <x-ui.field label="Guru pengampu" for="rb-guru">
                    <div class="relative">
                        <select id="rb-guru" x-model="form.guru" class="{{ $inputKelas }} appearance-none border-line focus:border-primary focus:ring-primary-soft">
                            <option value="">Pilih nanti</option>
                            <template x-for="g in calon" :key="g.id_guru">
                                <option :value="g.nama" :disabled="g.penuh" :selected="form.guru === g.nama" x-text="`${g.nama} · ${g.status}${g.penuh ? ' (beban penuh)' : ''}`"></option>
                            </template>
                        </select>
                        <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
                    </div>
                </x-ui.field>
                <x-ui.field label="Semester" for="rb-semester">
                    <input id="rb-semester" value="2026/2027 Ganjil" disabled class="{{ $inputKelas }} border-line bg-surface-2 text-muted">
                </x-ui.field>
            </div>

            <p x-show="form.jenis === 'campuran' && form.kelas.length" x-cloak
               class="flex items-center gap-2 rounded-control border border-violet-200 bg-violet-soft/60 px-3 py-2.5 text-[13px] text-violet-strong">
                <x-ui.icon name="info" :size="14" />
                <span x-text="`Nilai siswa tetap masuk ke rapor kelas asal (${catatanKelasAsal}).`"></span>
            </p>

            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'buat-rombel')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Buat Rombel</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
