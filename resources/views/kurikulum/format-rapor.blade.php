@extends('layouts.app')

@section('title', 'Format Rapor')

@push('styles')
    @vite(['resources/js/kurikulum-rapor.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="formatRapor(JSON.parse(document.getElementById('data-format').textContent))">
    <x-ui.json-data id="data-format" :data="['pengaturan' => $pengaturan, 'contoh' => $contoh]" />

    <x-ui.page-header title="Format Rapor" role="Kurikulum" subtitle="Atur kop, identitas, dan komponen yang tampil di rapor.">
        <x-slot:actions>
            <span x-show="kotor" x-cloak class="text-[13px] text-muted">Perubahan belum disimpan</span>
            <span x-show="!kotor && tersimpan" x-cloak class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-success"><x-ui.icon name="check-circle" :size="15" /><span x-text="`Tersimpan · ${tersimpan}`"></span></span>
            <x-ui.button size="lg" x-on:click="simpan()">Simpan Format</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid items-start gap-5 xl:grid-cols-[456px_minmax(0,1fr)]">
        {{-- Pengaturan --}}
        <x-ui.card class="space-y-5">
            <h2 class="border-b border-line-soft pb-3 text-[15px] font-bold text-ink">Pengaturan</h2>

            <section class="space-y-3.5">
                <h3 class="text-sm font-bold text-ink">Kop & Identitas</h3>
                <div class="flex items-center gap-3 rounded-card border border-line bg-surface-2 p-3.5">
                    <span class="flex size-11 items-center justify-center rounded-lg border-2 border-ink bg-surface text-lg font-bold">S</span>
                    <div class="min-w-0 flex-1"><p class="truncate text-[13px] font-bold text-ink" x-text="p.logo"></p><p class="text-xs text-muted" x-text="p.logo_info"></p></div>
                    <label class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-control border border-line bg-surface px-3 text-[13px] font-semibold text-ink hover:bg-surface-2">
                        <x-ui.icon name="upload" :size="14" />Ganti
                        <input type="file" accept=".png" class="sr-only" x-on:change="pilihBerkas($event, 'logo')">
                    </label>
                </div>
                @foreach (['baris1' => 'Baris 1', 'baris2' => 'Baris 2', 'sekolah' => 'Nama sekolah', 'alamat' => 'Alamat', 'kontak' => 'Kontak'] as $kunci => $label)
                    <x-ui.field :label="$label" for="fr-{{ $kunci }}"><x-ui.input id="fr-{{ $kunci }}" x-model="p.{{ $kunci }}" /></x-ui.field>
                @endforeach
            </section>

            <section class="space-y-3.5 border-t border-line-soft pt-5">
                <h3 class="text-sm font-bold text-ink">Kepala Sekolah</h3>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Nama" for="fr-kepsek"><x-ui.input id="fr-kepsek" x-model="p.kepsek" /></x-ui.field>
                    <x-ui.field label="NIP" for="fr-nip"><x-ui.input id="fr-nip" x-model="p.nip_kepsek" inputmode="numeric" /></x-ui.field>
                </div>
                <label class="flex cursor-pointer items-center gap-2.5 rounded-card border border-dashed border-line px-3.5 py-3 text-[13px] text-muted hover:border-primary/50">
                    <x-ui.icon name="upload" :size="15" />
                    <span class="flex-1" x-text="ttdBerkas ?? 'Unggah tanda tangan (opsional) · PNG transparan'"></span>
                    <span class="font-semibold text-link">Pilih berkas</span>
                    <input type="file" accept=".png" class="sr-only" x-on:change="pilihBerkas($event, 'ttd')">
                </label>
            </section>

            <section class="space-y-3.5 border-t border-line-soft pt-5">
                <h3 class="text-sm font-bold text-ink">Tanggal Rapor</h3>
                <x-ui.field label="Tempat" for="fr-tempat"><x-ui.input id="fr-tempat" x-model="p.tempat" /></x-ui.field>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.field label="Tanggal Rapor STS" for="fr-tgl-sts"><x-ui.input id="fr-tgl-sts" type="date" x-model="p.tanggal_sts" /></x-ui.field>
                    <x-ui.field label="Tanggal Rapor Semester" for="fr-tgl-sem"><x-ui.input id="fr-tgl-sem" type="date" x-model="p.tanggal_semester" /></x-ui.field>
                </div>
            </section>

            <section class="border-t border-line-soft pt-5">
                <h3 class="text-sm font-bold text-ink">Komponen yang ditampilkan</h3>
                <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3">
                    <template x-for="(label, kunci) in komponenLabel" :key="kunci">
                        <label class="flex cursor-pointer items-center gap-3">
                            <button type="button" role="switch" :aria-checked="!!p.komponen[kunci]" :aria-label="label" x-on:click="p.komponen[kunci] = !p.komponen[kunci]"
                                    class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="p.komponen[kunci] ? 'bg-primary' : 'bg-line'">
                                <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow transition-transform" :class="p.komponen[kunci] && 'translate-x-4'"></span>
                            </button>
                            <span class="text-[13px] font-semibold text-ink" x-text="label"></span>
                        </label>
                    </template>
                </div>
            </section>
        </x-ui.card>

        {{-- Pratinjau --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center gap-3 border-b border-line-soft px-4 py-3">
                <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="jenis" label="Jenis rapor" />
                <x-ui.badge><span x-text="jenis === 'sts' ? '1 halaman' : '2 halaman'"></span></x-ui.badge>
                <p class="ml-auto text-[13px] text-muted">Pratinjau langsung · data contoh Citra Lestari</p>
            </div>
            <div class="max-h-[1100px] overflow-auto bg-neutral-soft px-4 py-6">
                <x-rapor.dokumen doc="doc" />
            </div>
        </x-ui.card>
    </div>
</div>
@endsection
