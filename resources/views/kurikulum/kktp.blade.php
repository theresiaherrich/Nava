@extends('layouts.app')

@section('title', 'KKTP & Deskripsi Capaian')

@push('styles')
    @vite(['resources/js/kurikulum-kktp.js'])
@endpush

@php
    $warnaInterval = [
        ['bg-danger', 'border-danger/50 text-danger bg-danger-soft/40', 'Perlu bimbingan'],
        ['bg-warning-strong', 'border-warning-strong/50 text-warning bg-warning-faint', 'Cukup'],
        ['bg-primary', 'border-primary/50 text-info bg-primary-faint', 'Baik'],
        ['bg-success-strong', 'border-success-strong/50 text-success bg-success-soft/40', 'Sangat baik'],
    ];
    $periodeBadge = ['sts' => ['Masuk STS', 'info'], 'semester' => ['Semester', 'neutral'], 'sas' => ['SAS', 'yellow']];
@endphp

@section('content')
<div class="px-7 pt-7 pb-10" x-data="kktpPage(JSON.parse(document.getElementById('data-kktp').textContent))">
    <x-ui.json-data id="data-kktp" :data="$data" />

    <x-ui.page-header title="KKTP & Deskripsi Capaian" role="Kurikulum" subtitle="Kriteria ketercapaian dapat berubah setiap tahun ajaran.">
        <x-slot:actions>
            <span x-show="!kotor" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-success"><x-ui.icon name="check-circle" :size="15" /><span x-text="`Tersimpan · ${tersimpan}`"></span></span>
            <span x-show="kotor" x-cloak class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-warning"><span class="size-2 rounded-full bg-warning-strong"></span>Belum disimpan</span>
            <x-ui.button size="lg" x-on:click="simpan()">Simpan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-select label="Tahun ajaran" model="tahun" :options="$data['tahun_ajaran']" />
        <x-ui.filter-select label="Mapel" model="mapelAktif" :options="$data['mapel']" />
        <x-ui.filter-select label="Rombel" model="rombelAktif" :options="$data['rombel']" />
        <span class="ml-1 text-[13px] text-muted">Guru: <span x-text="guru"></span></span>
    </x-ui.card>

    <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_379px]">
        <div class="flex min-w-0 flex-col gap-5">
            {{-- Interval capaian --}}
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-[15px] font-bold text-ink">Interval Capaian</h2>
                    <p class="text-xs text-muted">Klik angka batas untuk mengubah</p>
                </div>
                <div class="mt-4 flex h-2 overflow-hidden rounded-full">
                    @foreach ($warnaInterval as $i => [$bar])
                        <div class="{{ $bar }} flex-1"></div>
                    @endforeach
                </div>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($warnaInterval as $i => [, $chip, $label])
                        <div>
                            @if ($i === 0)
                                <button type="button" class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[13px] font-semibold {{ $chip }}" x-on:click="editInterval = editInterval === 0 ? null : 0" aria-label="Ubah batas bawah">
                                    <span x-text="labelInterval[0]"></span><x-ui.icon name="pencil" :size="11" />
                                </button>
                            @else
                                <button type="button" class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[13px] font-semibold {{ $chip }}" x-on:click="editInterval = editInterval === {{ $i - 1 }} ? null : {{ $i - 1 }}" aria-label="Ubah batas {{ $label }}">
                                    <span x-text="labelInterval[{{ $i }}]"></span><x-ui.icon name="pencil" :size="11" />
                                </button>
                            @endif
                            <p class="mt-1.5 text-[13px] font-semibold text-ink">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
                <div x-show="editInterval !== null" x-cloak class="mt-3 flex flex-wrap items-center gap-2 rounded-control border border-line bg-surface-2 px-3 py-2.5 text-[13px]">
                    <span class="text-ink-2" x-text="`Batas ${['Cukup', 'Baik', 'Sangat baik'][editInterval]} mulai dari`"></span>
                    <input type="number" min="1" max="100" class="h-8 w-20 rounded-control border px-2 text-center text-sm"
                           :class="intervalValid ? 'border-line' : 'border-danger'" x-model.number="interval[editInterval]" aria-label="Nilai batas">
                    <span x-show="!intervalValid" class="text-xs font-medium text-danger">Batas harus naik berurutan</span>
                    <button type="button" class="ml-auto text-[13px] font-semibold text-link" x-on:click="editInterval = null">Selesai</button>
                </div>
            </x-ui.card>

            {{-- KKTP per TP --}}
            <x-ui.card :padding="false" class="overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
                    <div>
                        <h2 class="text-[15px] font-bold text-ink">KKTP per Tujuan Pembelajaran</h2>
                        <p class="text-xs text-muted" x-text="`${tp.length} TP · KKTP default 75`"></p>
                    </div>
                    <x-ui.button variant="secondary" x-on:click="terapkanSemua()">Terapkan 75 ke semua</x-ui.button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-[13px]">
                        <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                            <tr><th class="px-5 py-3">Kode TP</th><th class="px-3 py-3">Deskripsi</th><th class="px-3 py-3 text-center">KKTP</th><th class="px-5 py-3 text-right">Periode</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            <template x-for="t in tp" :key="t.kode">
                                <tr>
                                    <td class="px-5 py-3 font-bold text-link" x-text="t.kode"></td>
                                    <td class="px-3 py-3 text-ink" x-text="t.deskripsi"></td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" min="0" max="100" inputmode="numeric" x-model="t.kktp" :aria-label="`KKTP ${t.kode}`"
                                               class="h-9 w-16 rounded-control border text-center text-sm font-semibold text-ink focus:ring-2 focus:outline-none"
                                               :class="+t.kktp >= 0 && +t.kktp <= 100 && String(t.kktp).trim() !== '' ? 'border-line focus:border-primary focus:ring-primary-soft' : 'border-danger bg-danger-soft/40 focus:ring-danger-soft'">
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @foreach ($periodeBadge as $kode => [$teks, $tone])
                                            <x-ui.badge :tone="$tone" x-show="t.periode === '{{ $kode }}'">{{ $teks }}</x-ui.badge>
                                        @endforeach
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            {{-- Templat kalimat --}}
            <x-ui.card>
                <h2 class="text-[15px] font-bold text-ink">Templat Kalimat Deskripsi</h2>
                @foreach (['terbaik' => 'TP terbaik', 'penguatan' => 'TP perlu penguatan'] as $kunci => $label)
                    <label for="tpl-{{ $kunci }}" class="mt-4 mb-1.5 block text-[13px] font-semibold text-ink">{{ $label }}</label>
                    <input id="tpl-{{ $kunci }}" x-ref="templat_{{ $kunci }}" x-model="templat.{{ $kunci }}" x-on:focus="templatFokus = '{{ $kunci }}'"
                           class="h-11 w-full rounded-control border border-line px-3.5 text-sm text-ink focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none">
                @endforeach
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-muted">Sisipkan:</span>
                    <template x-for="tk in tokenList" :key="tk">
                        <button type="button" x-on:mousedown.prevent x-on:click="sisipkan(tk)" x-text="tk"
                                class="rounded-md border border-primary-soft bg-primary-faint px-2 py-0.5 font-semibold text-link hover:bg-primary-soft"></button>
                    </template>
                </div>
            </x-ui.card>
        </div>

        {{-- Pratinjau deskripsi --}}
        <x-ui.card class="self-start">
            <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink"><x-ui.icon name="eye" :size="16" class="text-link" />Pratinjau Deskripsi</h2>
            <div class="mt-3 flex items-center gap-2.5">
                <span class="flex size-8 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white" x-text="contoh.nama.split(' ').map((k) => k[0]).join('').slice(0, 2)"></span>
                <div>
                    <p class="text-[13px] font-bold text-ink" x-text="contoh.nama"></p>
                    <p class="text-xs text-muted" x-text="`${contoh.kelas} · ${mapelAktif}`"></p>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-6 gap-1.5">
                <template x-for="(n, i) in contoh.nilai" :key="i">
                    <div class="rounded-control border px-1 py-1.5 text-center"
                         :class="i === tertinggi ? 'border-success/40 bg-success-soft' : (i === dibawah ? 'border-danger/40 bg-danger-soft' : 'border-line')">
                        <p class="text-[10px] text-muted" x-text="tp[i].kode"></p>
                        <p class="text-sm font-bold" :class="i === tertinggi ? 'text-success' : (i === dibawah ? 'text-danger' : 'text-ink')" x-text="n"></p>
                    </div>
                </template>
            </div>
            <p class="mt-2 flex flex-wrap gap-x-3 text-xs">
                <span class="flex items-center gap-1 text-success"><span class="size-2 rounded-sm bg-success-strong"></span><span x-text="`Tertinggi: ${tp[tertinggi].kode}`"></span></span>
                <span x-show="dibawah !== -1" class="flex items-center gap-1 text-danger"><span class="size-2 rounded-sm bg-danger"></span><span x-text="`Di bawah KKTP: ${tp[dibawah]?.kode}`"></span></span>
            </p>
            <p class="mt-3 rounded-card border border-line bg-surface-2 p-4 text-sm leading-relaxed text-ink-2" x-html="pratinjau" aria-live="polite"></p>
            <p class="mt-3 flex gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" class="mt-0.5" />TP terendah belum tentu di bawah KKTP. Jika semua TP mencapai KKTP, kalimat penguatan tidak ditampilkan.</p>
            <x-ui.button variant="secondary" icon="user" class="mt-3" x-on:click="gantiContoh()">Ganti siswa contoh</x-ui.button>
        </x-ui.card>
    </div>
</div>
@endsection
