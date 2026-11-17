@extends('layouts.app')

@section('title', 'Rekap Nilai (DKN)')

@php
    use App\Support\Format;

    $s = $statistik;
    $mapelFilter = request('mapel');
    $kolomMapel = $mapelFilter ? array_values(array_filter($mapel, fn ($m) => $m[1] === $mapelFilter)) : $mapel;
    $url = fn (array $q) => request()->fullUrlWithQuery($q + ['page' => null]);
    $ekspor = [
        ['DKN kelas terpilih', $kelasDipilih ? "Kelas {$kelasDipilih} · 1 berkas" : "Semua kelas {$tingkat} · 1 berkas per kelas", $kelasDipilih ? "DKN {$kelasDipilih}" : "DKN Kelas {$tingkat}"],
        ["DKN seluruh Kelas {$tingkat}", Format::angka($s['siswa']).' siswa · 1 berkas', "DKN Kelas {$tingkat}"],
        ['DKN seluruh sekolah', '960 siswa · 30 sheet', 'DKN seluruh sekolah'],
    ];
@endphp

@section('content')
<div class="px-7 pt-7 pb-10">
    <x-ui.page-header title="Rekap Nilai (DKN)" role="Kurikulum" subtitle="Pantau kelengkapan dan ringkasan nilai seluruh kelas.">
        <x-slot:actions>
            {{-- 6.02 Ekspor DKN. TODO API: GET /api/kurikulum/dkn/ekspor?cakupan=kelas|tingkat|sekolah --}}
            <div class="relative" x-data="{ buka: @js(request('ekspor') === '1') }" x-on:click.outside="buka = false" x-on:keydown.escape="buka = false">
                <x-ui.button variant="secondary" icon="download" size="lg" x-on:click="buka = !buka" x-bind:aria-expanded="buka" aria-haspopup="menu">
                    Ekspor Excel <x-ui.icon name="chevron-down" :size="14" />
                </x-ui.button>
                <div x-show="buka" x-cloak x-transition.opacity role="menu" class="absolute top-full right-0 z-30 mt-1.5 w-[280px] rounded-card border border-line bg-surface p-2 shadow-pop">
                    @foreach ($ekspor as [$judul, $ket, $toast])
                        <button type="button" role="menuitem" class="flex w-full items-start gap-3 rounded-control px-2.5 py-2.5 text-left hover:bg-success-soft/40"
                                x-on:click="buka = false; $dispatch('toast', { message: @js($toast.' berhasil diekspor'), type: 'info' })">
                            <x-ui.icon name="sheet" :size="16" class="mt-0.5 text-success" />
                            <span><span class="block text-[13px] font-semibold text-success">{{ $judul }}</span><span class="block text-xs text-muted">{{ $ket }}</span></span>
                        </button>
                    @endforeach
                    <p class="mt-1 border-t border-line-soft px-2.5 pt-2.5 pb-1 text-xs text-muted">Format .xlsx</p>
                </div>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-ui.stat-card label="Jumlah siswa" :value="Format::angka($s['siswa'])" :hint="'Kelas '.$tingkat.' · '.$kelas->count().' kelas'" />
        <x-ui.stat-card label="Rata-rata angkatan" :value="$s['rata']" :hint="($periode === 'sts' ? 'STS' : 'Semester').' Ganjil 2026/2027'" />
        <x-ui.stat-card label="Kelengkapan nilai" :value="$s['kelengkapan'].'%'" :progress="$s['kelengkapan']" />
        <x-ui.stat-card label="Kelas terkunci" :value="$s['terkunci']" :suffix="'/ '.$kelas->count()" :hint="$s['kelas_terkunci'] ?: 'belum ada'" />
    </div>

    <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-link label="Tingkat" param="tingkat" :options="['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']" />
        <x-ui.filter-link label="Kelas" param="kelas" :options="['' => 'Semua kelas'] + $kelas->pluck('kelas', 'kelas')->all()" />
        <x-ui.filter-link label="Mapel" param="mapel" :options="['' => 'Semua'] + collect($mapel)->mapWithKeys(fn ($m) => [$m[1] => $m[0]])->all()" />
        <span class="ml-1 text-[13px] text-muted">Periode</span>
        <div class="inline-flex items-center rounded-control bg-neutral-soft p-0.5" role="group" aria-label="Periode">
            @foreach (['sts' => 'STS', 'semester' => 'Semester'] as $p => $teks)
                <a href="{{ $url(['periode' => $p === 'sts' ? null : $p]) }}" @if ($periode === $p) aria-current="true" @endif
                   @class(['rounded-[6px] px-3 py-1.5 text-[13px] font-medium', 'bg-surface text-link shadow-card' => $periode === $p, 'text-muted hover:text-ink' => $periode !== $p])>{{ $teks }}</a>
            @endforeach
        </div>
        <x-ui.search-form placeholder="Cari kelas atau wali…" class="ml-auto w-[200px]" />
    </x-ui.card>

    <nav aria-label="Tampilan rekap" class="mt-4 mb-4 flex gap-6 border-b border-line">
        @foreach (['siswa' => 'Per siswa', 'kelas' => 'Kelengkapan per kelas'] as $t => $teks)
            <a href="{{ $url(['tab' => $t]) }}" @if ($tab === $t) aria-current="page" @endif
               @class(['-mb-px border-b-2 px-0.5 pb-2.5 text-sm', 'border-primary font-semibold text-link' => $tab === $t, 'border-transparent text-muted hover:text-ink' => $tab !== $t])>{{ $teks }}</a>
        @endforeach
    </nav>

    @if ($tab === 'kelas')
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr>
                            <th class="px-5 py-3">Kelas</th><th class="px-3 py-3">Wali Kelas</th>
                            @foreach ($kolomMapel as $m)<th class="px-2 py-3 text-center" title="{{ $m[0] }}">{{ $m[1] }}</th>@endforeach
                            @unless ($mapelFilter)<th class="px-2 py-3 text-center">…</th>@endunless
                            <th class="px-3 py-3">Kelengkapan</th><th class="px-5 py-3 text-right">Status kunci</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @foreach ($kelas->filter(fn ($k) => \App\Support\Dummy\Paginasi::cocok(request('q'), $k['kelas'], (string) $k['wali'])) as $k)
                            <tr @class(['bg-primary-faint' => $kelasDipilih === $k['kelas']])>
                                <td class="px-5 py-3 font-bold text-ink"><a href="{{ $url(['kelas' => $k['kelas'], 'tab' => 'siswa']) }}" class="hover:text-link hover:underline">{{ $k['kelas'] }}</a></td>
                                <td class="px-3 py-3 text-ink-2">{{ $k['wali'] }}</td>
                                @foreach ($k['mapel'] as $sel)
                                    @continue($mapelFilter && $sel['singkat'] !== $mapelFilter)
                                    <td class="px-2 py-3 text-center">
                                        @switch($sel['status'])
                                            @case('d')<x-ui.icon name="check-circle" :size="15" class="inline text-success" aria-label="Disetujui" />@break
                                            @case('k')<span class="inline-block size-2 rounded-full bg-primary" aria-label="Dikirim" title="Dikirim"></span>@break
                                            @case('s')<span class="inline-block size-2 rounded-full bg-warning-strong" aria-label="Sedang diisi" title="Sedang diisi"></span>@break
                                            @default<span class="text-danger" aria-label="Belum ada TP" title="Belum ada TP">—</span>
                                        @endswitch
                                    </td>
                                @endforeach
                                @unless ($mapelFilter)<td class="px-2 py-3 text-center text-xs text-subtle">+{{ $jumlahMapel - 9 }}</td>@endunless
                                <td class="px-3 py-3"><x-ui.progress-mini :nilai="$k['kelengkapan']" :maks="100" :label="$k['kelengkapan'].'%'" class="[&>div]:w-16 [&>span]:font-bold [&>span]:text-ink" /></td>
                                <td class="px-5 py-3 text-right"><x-ui.status-kelas jenis="kunci" :nilai="$k['kunci']" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3 text-xs text-ink-2">
                <ul class="flex flex-wrap gap-4">
                    <li class="flex items-center gap-1.5"><x-ui.icon name="check-circle" :size="13" class="text-success" />Disetujui</li>
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-primary"></span>Dikirim</li>
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-full bg-warning-strong"></span>Sedang diisi</li>
                    <li class="flex items-center gap-1.5"><span class="text-danger">—</span>Belum ada TP</li>
                </ul>
                <p class="text-muted">{{ count($kolomMapel) }} dari {{ $jumlahMapel }} mapel ditampilkan @unless ($mapelFilter)· +{{ $jumlahMapel - 9 }} mapel lain @endunless</p>
            </div>
        </x-ui.card>
    @else
        <x-ui.card :padding="false" class="overflow-hidden">
            @if (! $kelasDipilih)
                <x-ui.empty-state icon="table" title="Pilih kelas untuk melihat nilai per siswa"
                                  description="Gunakan filter Kelas di atas, atau klik nama kelas pada tab Kelengkapan per kelas." />
            @else
                <div class="px-5 pt-4 pb-3">
                    <h2 class="text-[15px] font-bold text-ink">DKN Kelas {{ $kelasDipilih }}</h2>
                    <p class="text-xs text-muted">{{ count($nilaiSiswa) }} siswa · nilai rapor {{ $periode === 'sts' ? 'STS' : 'semester' }} · KKTP 75</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left text-[13px]">
                        <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                            <tr>
                                <th class="px-5 py-3">No</th><th class="px-3 py-3">NIS</th><th class="px-3 py-3">Nama</th>
                                @foreach ($kolomMapel as $m)<th class="px-2 py-3 text-center" title="{{ $m[0] }}">{{ $m[1] }}</th>@endforeach
                                <th class="px-5 py-3 text-center">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            @foreach ($nilaiSiswa as $i => $sw)
                                @continue(! \App\Support\Dummy\Paginasi::cocok(request('q'), $sw['nama'], $sw['nis']))
                                <tr>
                                    <td class="px-5 py-2.5 text-ink-2">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2.5 text-ink-2">{{ $sw['nis'] }}</td>
                                    <td class="px-3 py-2.5 font-semibold text-ink">{{ $sw['nama'] }}</td>
                                    @foreach ($sw['nilai'] as $j => $n)
                                        @continue($mapelFilter && $mapel[$j][1] !== $mapelFilter)
                                        <td @class(['px-2 py-2.5 text-center', 'font-semibold text-danger' => $n < 75, 'text-ink-2' => $n >= 75])>{{ $n }}</td>
                                    @endforeach
                                    <td class="px-5 py-2.5 text-center font-bold text-link">{{ Format::angka($sw['rata'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="flex items-center gap-1.5 border-t border-line-soft px-5 py-3 text-xs text-muted"><span class="font-semibold text-danger">Merah</span> = di bawah KKTP. Nilai dari paket yang sudah disetujui wali kelas.</p>
            @endif
        </x-ui.card>
    @endif
</div>
@endsection
