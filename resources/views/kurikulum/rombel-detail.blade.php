@extends('layouts.app')

@section('title', $rombel['nama'].' · Detail Rombel')

@push('styles')
    @vite(['resources/js/kurikulum-rombel-detail.js'])
@endpush

@php
    $kelasTeks = implode(' dan ', $rombel['kelas_asal']);
    if (count($rombel['kelas_asal']) > 2) {
        $kelasTeks = implode(', ', array_slice($rombel['kelas_asal'], 0, -1)).' dan '.last($rombel['kelas_asal']);
    }
    $subjudul = $rombel['jenis'] === 'campuran'
        ? "Rombel campuran {$rombel['mapel']} untuk siswa {$kelasTeks}."
        : "Rombel reguler {$rombel['mapel']} untuk siswa {$kelasTeks}.";
@endphp

@section('content')
<div class="px-7 pt-6 pb-10"
     x-data="rombelDetail({
        rombel: @js($rombel),
        calonPeserta: @js($calonPeserta),
        calon: @js($calonPengampu),
        bukaModal: @js(request('modal') === 'tambah-peserta'),
     })">
    <x-ui.page-header :title="$rombel['nama']" role="Kurikulum" :subtitle="$subjudul"
                      :back="['href' => route('rombel.index'), 'label' => 'Rombel & Pengampu']">
        <x-slot:actions>
            <x-ui.badge tone="outline" class="!text-[13px]">{{ $rombel['mapel'] }}</x-ui.badge>
            @if ($rombel['jenis'] === 'campuran')
                <x-ui.badge tone="violet" class="!text-[13px]">Campuran</x-ui.badge>
            @else
                <x-ui.badge class="!text-[13px]">Reguler</x-ui.badge>
            @endif
            <x-ui.badge tone="outline" class="!text-[13px]">{{ $rombel['semester'] }}</x-ui.badge>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Ringkasan pengampu --}}
    <x-ui.card class="flex flex-wrap items-center gap-x-6 gap-y-4 !py-4">
        <div class="flex items-center gap-3">
            <span class="flex size-9 items-center justify-center rounded-full text-xs font-bold text-white"
                  :class="rombel.guru ? warnaAvatar(rombel.guru) : 'bg-subtle'" x-text="rombel.guru ? inisial(rombel.guru) : '?'"></span>
            <div>
                <p class="text-[13px] font-bold text-ink" x-text="rombel.guru ?? 'Belum ada pengampu'"></p>
                <p class="text-xs text-muted" x-text="rombel.guru ? `Guru pengampu · ${rombel.guru_status}` : 'Pilih guru pengampu'"></p>
            </div>
            <x-ui.button variant="secondary" size="sm" class="ml-3"
                         x-on:click.stop="bukaPicker(rombel.id_rombel, rombel.mapel, $el)"
                         x-text="rombel.guru ? 'Ganti pengampu' : 'Pilih pengampu'">Ganti pengampu</x-ui.button>
        </div>
        <div class="hidden h-10 w-px bg-line md:block"></div>
        <div>
            <p class="text-xs text-muted">Tujuan pembelajaran</p>
            <p class="text-[15px] font-bold text-ink">{{ $rombel['jumlah_tp'] }} TP</p>
        </div>
        <div class="hidden h-10 w-px bg-line md:block"></div>
        <div>
            <p class="text-xs text-muted">Status nilai STS</p>
            <x-ui.badge tone="warning" class="mt-1">Sedang diisi</x-ui.badge>
        </div>
        <div class="hidden h-10 w-px bg-line md:block"></div>
        <div>
            <p class="text-xs text-muted">Peserta</p>
            <p class="text-[15px] font-bold text-ink"><span x-text="rombel.peserta.length"></span> siswa</p>
        </div>
    </x-ui.card>

    {{-- Kelas asal --}}
    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <template x-for="k in rombel.kelas_asal_detail" :key="k.kelas">
            <x-ui.card class="flex items-center gap-3.5 !py-4">
                <span class="flex size-10 items-center justify-center rounded-control text-[13px] font-bold" :class="warnaKelas(k.kelas)" x-text="k.kelas"></span>
                <div>
                    <p class="text-sm font-bold text-ink" x-text="`${k.kelas} · ${jumlahKelas(k.kelas)} siswa`"></p>
                    <p class="text-xs text-muted" x-text="`Wali: ${k.wali}`"></p>
                </div>
            </x-ui.card>
        </template>
    </div>

    {{-- Peserta --}}
    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
            <div>
                <h2 class="text-[15px] font-bold text-ink">Peserta</h2>
                <p class="text-xs text-muted" x-text="`${rombel.peserta.length} siswa dari ${rombel.kelas_asal.length} kelas asal`"></p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <div role="radiogroup" aria-label="Filter kelas asal" class="inline-flex items-center rounded-control bg-neutral-soft p-0.5">
                    <template x-for="(teks, nilai) in opsiKelas" :key="nilai">
                        <button type="button" role="radio" :aria-checked="filterKelas === nilai" x-on:click="filterKelas = nilai" x-text="teks"
                                class="rounded-[6px] px-3 py-1.5 text-[13px] font-medium"
                                :class="filterKelas === nilai ? 'bg-surface text-link shadow-card' : 'text-muted hover:text-ink'"></button>
                    </template>
                </div>
                <x-ui.search-input placeholder="Cari peserta…" x-model="cari" class="w-[170px]" />
                <x-ui.button icon="plus" x-on:click="bukaTambah()">Tambah Peserta</x-ui.button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="w-[60px] px-5 py-3">No</th>
                        <th class="px-3 py-3">NIS</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">Kelas Asal</th>
                        <th class="w-[70px] px-5 py-3"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(s, i) in barisHalaman" :key="s.nis">
                        <tr>
                            <td class="px-5 py-3 text-ink-2" x-text="(page - 1) * perPage + i + 1"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="s.nis"></td>
                            <td class="px-3 py-3 font-semibold text-ink" x-text="s.nama"></td>
                            <td class="px-3 py-3"><span class="rounded-md px-2 py-0.5 text-xs font-semibold" :class="warnaKelas(s.kelas_asal)" x-text="s.kelas_asal"></span></td>
                            <td class="px-5 py-2 text-right">
                                <x-ui.icon-button icon="trash" tone="danger" label="Keluarkan dari rombel" x-on:click="konfirmasiKeluarkan(s)" />
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!barisHalaman.length">
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-muted">Tidak ada peserta yang cocok.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Nilai setiap siswa masuk ke rapor kelas asalnya.</p>
            <div class="flex items-center gap-3">
                <span class="text-xs text-muted" x-text="rentang"></span>
                <x-ui.pagination page="page" pages="halaman" />
            </div>
        </div>
    </x-ui.card>

    <x-ui.guru-picker />

    {{-- Konfirmasi keluarkan peserta --}}
    <x-ui.modal name="keluarkan" width="max-w-[420px]">
        <x-slot:icon>
            <span class="flex size-11 items-center justify-center rounded-full bg-danger-soft text-danger"><x-ui.icon name="trash" :size="18" /></span>
        </x-slot:icon>
        <h2 class="text-lg font-bold text-ink" x-text="keluarkanTarget ? `Keluarkan ${keluarkanTarget.nama}?` : ''"></h2>
        <p class="mt-2 text-sm text-ink-2" x-text="`Siswa tidak lagi tercatat sebagai peserta ${rombel.nama}. Nilai yang sudah diisi tetap tersimpan.`"></p>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'keluarkan')">Batal</x-ui.button>
            <x-ui.button variant="danger" size="lg" x-on:click="keluarkan()">Keluarkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- 4.09 Tambah peserta --}}
    <x-ui.modal name="tambah-peserta" width="max-w-[600px]">
        <h2 class="pr-10 text-lg font-bold text-ink">Tambah Peserta — {{ $rombel['nama'] }}</h2>
        <p class="mt-1 text-[13px] text-muted">Pilih siswa dari kelas asal yang terdaftar di rombel ini.</p>

        <div class="mt-4 flex flex-wrap gap-2.5">
            <x-ui.filter-select label="Kelas asal" model="tambah.kelas" :options="$rombel['kelas_asal']" class="w-[180px] [&>button]:w-full [&>button]:justify-between" />
            <x-ui.search-input placeholder="Cari nama atau NIS…" x-model="tambah.cari" class="w-[330px] max-w-full" />
        </div>

        <ul class="mt-4 space-y-2" aria-label="Calon peserta">
            <template x-for="s in calonTampil" :key="s.nis">
                <li>
                    <label class="flex items-center gap-3 rounded-control border px-3 py-2.5"
                           :class="s.terdaftar ? 'cursor-not-allowed border-line bg-surface-2' : (tambah.dipilih.includes(s.nis) ? 'cursor-pointer border-primary/50 bg-primary-faint' : 'cursor-pointer border-line hover:bg-surface-2')">
                        <input type="checkbox" class="size-4 accent-primary" :checked="s.terdaftar || tambah.dipilih.includes(s.nis)"
                               :disabled="s.terdaftar" x-on:change="toggle(s)">
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13px] font-semibold" :class="s.terdaftar ? 'text-subtle' : 'text-ink'" x-text="s.nama"></span>
                            <span class="block text-xs text-muted" x-text="`NIS ${s.nis} · ${s.kelas_asal}`"></span>
                        </span>
                        <span x-show="s.terdaftar" class="rounded-md bg-neutral-soft px-2 py-0.5 text-xs font-semibold text-neutral">Sudah terdaftar</span>
                        <span x-show="!s.terdaftar && s.rombel_lain" class="inline-flex items-center gap-1 rounded-md bg-warning-soft px-2 py-0.5 text-xs font-semibold text-warning">
                            <x-ui.icon name="alert" :size="12" /> <span x-text="`Sudah di ${s.rombel_lain}`"></span>
                        </span>
                    </label>
                </li>
            </template>
            <li x-show="!calonTampil.length" class="py-6 text-center text-sm text-muted">Tidak ada siswa yang cocok.</li>
        </ul>

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-line-soft pt-4">
            <span class="text-[13px] font-semibold text-link" x-text="`${tambah.dipilih.length} siswa dipilih`"></span>
            <div class="flex gap-2.5">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'tambah-peserta')">Batal</x-ui.button>
                <x-ui.button size="lg" x-bind:disabled="!tambah.dipilih.length" x-on:click="simpanPeserta()"
                             x-text="`Tambahkan ${tambah.dipilih.length} Siswa`">Tambahkan Siswa</x-ui.button>
            </div>
        </div>
    </x-ui.modal>
</div>
@endsection
