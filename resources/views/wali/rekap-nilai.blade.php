@extends('layouts.app')

@section('title', 'Rekap Nilai (DKN)')

@php
    use App\Support\Format;

    $statusMapel = [
        'menunggu' => ['Menunggu verifikasi', 'info'], 'dikirim_ulang' => ['Dikirim ulang', 'violet'], 'revisi' => ['Dikembalikan', 'violet'],
        'disetujui' => ['Disetujui', 'success'], 'terkunci' => ['Terkunci', 'success'], 'sedang_diisi' => ['Sedang diisi', 'warning'], 'belum_tp' => ['Belum ada TP', 'danger'],
    ];
    $s = $statistik;
    $url = fn (array $q) => request()->fullUrlWithQuery($q + ['page' => null]);
    $angka = fn ($n) => $n === null ? '—' : Format::angka($n, 1);
@endphp

@section('content')
<div class="px-7 pt-7 pb-10">
    <x-ui.page-header title="Rekap Nilai (DKN)" role="Wali Kelas" :subtitle="'Pantau kelengkapan dan ringkasan nilai seluruh mata pelajaran kelas '.$kelas.'.'" class="mb-3">
        <x-slot:actions>
            {{-- 4.03 Ekspor Excel. TODO API: GET /api/wali/rekap/ekspor?jenis=dkn|kelengkapan&periode= --}}
            <div class="relative" x-data="{ buka: @js(request('ekspor') === '1') }" x-on:click.outside="buka = false" x-on:keydown.escape="buka = false">
                <x-ui.button variant="secondary" icon="download" size="lg" x-on:click="buka = !buka" x-bind:aria-expanded="buka" aria-haspopup="menu">Ekspor Excel <x-ui.icon name="chevron-down" :size="14" /></x-ui.button>
                <div x-show="buka" x-cloak x-transition.opacity role="menu" class="absolute top-full right-0 z-30 mt-1.5 w-[280px] rounded-card border border-line bg-surface p-2 shadow-pop">
                    @foreach ([["DKN kelas {$kelas} (semua mapel)", "{$s['siswa']} siswa · ".count($mapel).' mapel', "DKN kelas {$kelas}"], ['Kelengkapan per mapel', count($mapel).' baris', 'Kelengkapan per mapel']] as [$judul, $ket, $toast])
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

    <nav aria-label="Tampilan rekap" class="mb-5 flex gap-6 border-b border-line">
        @foreach (['siswa' => 'Per siswa', 'mapel' => 'Kelengkapan per mapel'] as $t => $teks)
            <a href="{{ $url(['tab' => $t === 'siswa' ? null : $t]) }}" @if ($tab === $t) aria-current="page" @endif
               @class(['-mb-px border-b-2 px-0.5 pb-2.5 text-sm', 'border-primary font-semibold text-link' => $tab === $t, 'border-transparent text-muted hover:text-ink' => $tab !== $t])>{{ $teks }}</a>
        @endforeach
    </nav>


    @if ($tab === 'siswa')
        {{-- 4.01 Per siswa --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <x-ui.stat-card label="Jumlah siswa" :value="$s['siswa']" hint="16 laki-laki · 16 perempuan" />
            <x-ui.stat-card label="Rata-rata kelas" :value="$periode === 'sts' ? $angka($s['rata']) : '—'" :hint="$periode === 'sts' ? '+2,1 dari semester lalu' : 'Nilai semester belum tersedia'" />
            <x-ui.stat-card label="Kelengkapan nilai" :value="($periode === 'sts' ? $s['kelengkapan'] : 0).'%'" :progress="$periode === 'sts' ? $s['kelengkapan'] : 0" />
        </div>

        <x-ui.card class="mt-4 flex flex-wrap items-center gap-3 !py-3.5">
            <span class="inline-flex h-9 items-center gap-1.5 rounded-control border border-line bg-surface-2 px-3 text-[13px] font-semibold text-ink" title="Anda hanya dapat melihat kelas perwalian"><x-ui.icon name="lock" :size="13" />Kelas {{ $kelas }}</span>
            <x-ui.filter-link label="Mapel" param="mapel" :options="['' => 'Semua mata pelajaran', 'semua' => 'Semua (16 kolom)'] + collect($mapel)->mapWithKeys(fn ($m) => [$m['id'] => $m['mapel']])->all()" />
            <span class="text-[13px] text-muted">Periode:</span>
            <x-ui.segmented-link :options="['sts' => 'STS', 'semester' => 'Semester']" param="periode" default="sts" label="Periode" />
            <x-ui.search-form placeholder="Cari nama atau NISN…" class="w-full sm:ml-auto sm:w-[220px]" />
        </x-ui.card>

        <x-ui.card :padding="false" class="mt-4 overflow-hidden">
            @if ($periode === 'semester')
                <x-ui.empty-state icon="table" title="Nilai semester belum tersedia" description="Rekap semester muncul setelah guru mengirim nilai SAS Ganjil 2026/2027 (dibuka 16 Nov 2026)." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]" style="min-width: {{ 260 + count($kolom) * 84 }}px">
                        <thead class="text-xs font-semibold text-ink-2">
                            <tr class="bg-surface-2">
                                <th class="sticky left-0 z-10 bg-surface-2 px-5 py-3">Nama siswa</th>
                                @foreach ($kolom as $k)
                                    <th class="px-2 py-3 text-center" title="{{ $k['mapel'] }}">{{ $k['singkat'] }}</th>
                                @endforeach
                                @unless ($semuaKolom || $pilihMapel)
                                    <th class="px-2 py-3 text-center"><a href="{{ $url(['mapel' => 'semua']) }}" class="text-link hover:underline" aria-label="Tampilkan semua {{ count($mapel) }} mapel">+{{ count($mapel) - count($kolom) }}</a></th>
                                @endunless
                                <th class="bg-rapor px-4 py-3 text-center text-link">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            @forelse ($baris as $b)
                                <tr>
                                    <td class="sticky left-0 z-10 bg-surface px-5 py-2 leading-tight"><p class="font-semibold text-ink">{{ $b['nama'] }}</p><p class="text-xs text-muted">{{ $b['nisn'] }}</p></td>
                                    @foreach ($kolom as $k)
                                        <td class="px-2 text-center {{ $b['nilai'][$k['id']] === null ? 'text-subtle' : 'text-ink-2' }}">{{ $b['nilai'][$k['id']] ?? '—' }}</td>
                                    @endforeach
                                    @unless ($semuaKolom || $pilihMapel)<td class="px-2 text-center text-subtle">…</td>@endunless
                                    <td class="bg-rapor px-4 text-center font-bold text-link">{{ $angka($b['rata']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($kolom) + 3 }}" class="px-5 py-10 text-center text-sm text-muted">Siswa tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
                    <p class="text-xs text-muted">— = belum diisi / belum dikirim · Rata-rata dihitung dari mapel yang sudah ada nilainya · {{ count($kolom) }} dari {{ count($mapel) }} mapel ditampilkan</p>
                    <x-ui.pager :paginator="$baris" satuan="" />
                </div>
            @endif
        </x-ui.card>
    @else
        {{-- 4.02 Kelengkapan per mapel --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
                <div><h2 class="text-[15px] font-bold text-ink">Kelengkapan per mata pelajaran</h2><p class="text-xs text-muted">{{ count($mapel) }} mapel · Kelas {{ $kelas }} · Rapor {{ $periode === 'sts' ? 'STS' : 'Semester' }}</p></div>
                <div class="flex items-center gap-2.5"><span class="inline-flex h-9 items-center gap-1.5 rounded-control border border-line bg-surface-2 px-3 text-[13px] font-semibold text-ink" title="Anda hanya dapat melihat kelas perwalian"><x-ui.icon name="lock" :size="13" />Kelas {{ $kelas }}</span><x-ui.segmented-link :options="['sts' => 'STS', 'semester' => 'Semester']" param="periode" default="sts" label="Periode" /></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[920px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr><th class="px-5 py-3">Mata Pelajaran</th><th class="px-3 py-3">Guru</th><th class="px-3 py-3">Nilai terisi</th><th class="px-3 py-3">Status</th><th class="px-3 py-3">Rata-rata</th><th class="px-5 py-3">Diperbarui</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @foreach ($mapel as $m)
                            @php
                                [$label, $tone] = $statusMapel[$m['status']];
                                $terisi = $periode === 'sts' ? $m['terisi'] : 0;
                            @endphp
                            <tr>
                                <td class="px-5 py-2 leading-tight"><p class="font-semibold text-ink">{{ $m['mapel'] }}</p><p class="text-xs text-muted">{{ $m['keterangan'] }}</p></td>
                                <td class="px-3 text-ink-2">{{ $m['guru'] }}</td>
                                <td class="px-3"><x-ui.progress-mini :nilai="$terisi" :maks="$m['peserta']" class="[&>div]:w-16" /></td>
                                <td class="px-3">
                                    @if ($periode === 'sts')<x-ui.badge :tone="$tone">{{ $label }}</x-ui.badge>@else<x-ui.badge>Belum dibuka</x-ui.badge>@endif
                                </td>
                                <td class="px-3 font-bold text-ink">{{ $periode === 'sts' ? $angka($m['rata']) : '—' }}</td>
                                <td class="px-5 text-ink-2">{{ $periode === 'sts' ? ($m['diperbarui'] ?? '—') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif
</div>
@endsection
