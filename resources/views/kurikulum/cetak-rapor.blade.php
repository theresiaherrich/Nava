@extends('layouts.app')

@section('title', 'Cetak & Ekspor Rapor')

@push('styles')
    @vite(['resources/js/kurikulum-rapor.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="cetakRapor(JSON.parse(document.getElementById('data-cetak').textContent))">
    <x-ui.json-data id="data-cetak" :data="['daftarKelas' => $daftarKelas, 'data' => $data, 'dokumen' => $dokumen, 'bukaKelas' => request('pilih-kelas') === '1']" />

    <x-ui.page-header title="Cetak & Ekspor Rapor" role="Kurikulum" subtitle="Pratinjau, cetak, dan unduh rapor siswa tanpa mengolah ulang dokumen.">
        <x-slot:actions>
            <a href="{{ route('terbitkan-rapor.index') }}" class="inline-flex items-center gap-1 text-[13px] font-semibold text-link hover:underline">Lanjut ke Terbitkan Rapor <x-ui.icon name="arrow-right" :size="13" /></a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="flex flex-wrap items-center gap-x-4 gap-y-2 !py-3 text-[13px]">
        <strong class="text-ink">Kelas {{ $tingkat }}:</strong>
        <span class="flex items-center gap-1.5"><x-ui.badge tone="success" icon="lock">{{ $ringkasan['terkunci'] }}</x-ui.badge>terkunci</span>
        <span class="flex items-center gap-1.5"><x-ui.badge tone="info">{{ $ringkasan['pdf'] }}</x-ui.badge>PDF dibuat</span>
        <span class="flex items-center gap-1.5"><x-ui.badge tone="success">{{ $ringkasan['terbit'] }}</x-ui.badge>terbit</span>
        <span class="ml-auto text-muted">Kunci → Buat PDF → Terbitkan adalah 3 langkah terpisah</span>
    </x-ui.card>

    <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
        {{-- Pilih kelas (dropdown dengan status kunci) --}}
        <div class="relative" x-on:click.outside="pilihKelas = false" x-on:keydown.escape="pilihKelas = false">
            <button type="button" x-on:click="pilihKelas = !pilihKelas" :aria-expanded="pilihKelas" aria-haspopup="listbox"
                    class="flex h-9 w-[200px] items-center justify-between rounded-control border px-3 text-[13px]"
                    :class="pilihKelas ? 'border-primary ring-2 ring-primary-soft' : 'border-line'">
                <span class="text-muted">Kelas: <strong class="text-ink" x-text="data.kelas"></strong></span>
                <x-ui.icon name="chevron-down" :size="14" class="text-ink-2" />
            </button>
            <div x-show="pilihKelas" x-cloak class="absolute top-full left-0 z-30 mt-1 w-[240px] rounded-card border border-line bg-surface p-2 shadow-pop">
                <p class="px-2 pt-1 pb-2 text-[11px] font-bold text-muted uppercase">Kelas {{ $tingkat }}</p>
                <ul role="listbox" aria-label="Kelas">
                    <template x-for="k in daftarKelas" :key="k.kelas">
                        <li>
                            <a :href="`?kelas=${k.kelas}`" role="option" :aria-selected="k.kelas === data.kelas"
                               class="flex items-center gap-2 rounded-control px-2 py-1.5 text-[13px]" :class="k.kelas === data.kelas ? 'bg-primary-faint' : 'hover:bg-surface-2'">
                                <span class="w-4"><x-ui.icon name="check" :size="14" class="text-link" x-show="k.kelas === data.kelas" /></span>
                                <span class="flex-1" :class="k.kunci === 'terkunci' ? 'font-semibold text-ink' : 'text-ink-2'" x-text="`Kelas ${k.kelas}`"></span>
                                <span :class="$store.ref.badge({ terkunci: 'success', koreksi: 'violet', terbuka: 'neutral' }[k.kunci])"
                                      x-text="{ terkunci: 'Terkunci', koreksi: 'Koreksi', terbuka: 'Terbuka' }[k.kunci]"></span>
                            </a>
                        </li>
                    </template>
                </ul>
                <p class="border-t border-line-soft px-2 pt-2 pb-1 text-xs text-muted">Hanya kelas terkunci yang dapat dicetak final.</p>
            </div>
        </div>
        <x-ui.search-input placeholder="Cari nama atau NISN…" x-model="cari" class="w-[220px]" />
        <x-ui.badge tone="success" icon="lock" x-show="data.terkunci" class="!py-1 !text-[13px]"><span x-text="`Nilai dikunci ${data.dikunci}`"></span></x-ui.badge>
        <x-ui.badge tone="warning" icon="unlock" x-show="!data.terkunci" class="!py-1 !text-[13px]">Nilai belum dikunci · draf</x-ui.badge>
        <div class="ml-auto flex gap-2.5">
            <x-ui.button variant="secondary" icon="download" x-on:click="unduh('kelas')">PDF satu kelas</x-ui.button>
            <x-ui.button variant="secondary" icon="download" x-on:click="unduh('semua')">PDF semua kelas terkunci</x-ui.button>
        </div>
    </x-ui.card>

    <div class="mt-4 grid items-start gap-5 xl:grid-cols-[301px_minmax(0,1fr)]">
        {{-- Daftar siswa --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <ul role="listbox" aria-label="Siswa" class="divide-y divide-line-soft">
                <template x-for="s in barisHalaman" :key="s.nis">
                    <li>
                        <button type="button" role="option" :aria-selected="aktif === s.nis" x-on:click="aktif = s.nis"
                                class="relative flex w-full items-center gap-3 px-4 py-3 text-left"
                                :class="aktif === s.nis ? 'bg-primary-faint before:absolute before:inset-y-0 before:left-0 before:w-[3px] before:bg-primary' : 'hover:bg-surface-2'">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-neutral-soft text-[11px] font-bold text-ink-2" x-text="inisial(s.nama)"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[13px] font-bold text-ink" x-text="s.nama"></span>
                                <span class="block text-xs text-muted" x-text="`${s.nisn} · ${s.kelas}`"></span>
                            </span>
                            <span x-show="s.pdf" :class="$store.ref.badge('info')" x-text="s.pdf"></span>
                            <span x-show="!s.pdf" :class="$store.ref.badge('neutral')">Draf</span>
                        </button>
                    </li>
                </template>
                <li x-show="!barisHalaman.length" class="px-4 py-8 text-center text-sm text-muted">Siswa tidak ditemukan.</li>
            </ul>
            <div class="flex items-center justify-between border-t border-line-soft px-4 py-3">
                <span class="text-xs text-muted" x-text="rentang"></span>
                <x-ui.pagination page="page" pages="halaman" />
            </div>
        </x-ui.card>

        {{-- Pratinjau --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center gap-3 border-b border-line-soft px-4 py-3">
                <div>
                    <p class="text-[13px] text-muted">Pratinjau rapor · <strong class="text-ink" x-text="`Halaman 1 dari ${jenis === 'sts' ? 1 : 2}`"></strong> · <span x-text="`${siswaAktif?.nama} · ${data.kelas}`"></span></p>
                    <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="jenis" label="Jenis rapor" class="mt-1.5" />
                </div>
                <div class="ml-auto flex gap-2.5">
                    <x-ui.button variant="secondary" icon="printer" x-on:click="cetak()">Cetak</x-ui.button>
                    <x-ui.button icon="download" x-on:click="unduh('siswa')">Unduh PDF</x-ui.button>
                </div>
            </div>
            <div class="relative max-h-[880px] overflow-auto bg-neutral-soft px-4 py-6" id="area-cetak">
                <p x-show="!data.terkunci" class="mx-auto mb-4 w-[540px] rounded-control border border-orange-200 bg-warning-faint px-3 py-2 text-[13px] text-warning">
                    Pratinjau draf — nilai kelas <span x-text="data.kelas"></span> belum dikunci. Rapor final dapat dicetak setelah dikunci Kurikulum.
                </p>
                <template x-if="doc"><x-rapor.dokumen doc="doc" /></template>
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
