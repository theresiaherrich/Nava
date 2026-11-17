@extends('layouts.app')

@section('title', 'Dashboard Kurikulum')

@php
    use App\Support\Format;

    $s = $statistik;
    $tone = [
        'warning' => 'bg-warning-soft text-warning-strong',
        'violet' => 'bg-violet-soft text-violet',
        'neutral' => 'bg-neutral-soft text-neutral',
    ];
    $aksiCepat = [
        ['clock', 'Atur periode', route('periode-penguncian.index')],
        ['layers', 'Rombel & pengampu', route('rombel.index')],
        ['printer', 'Cetak rapor', route('kurikulum.cetak-rapor.index')],
        ['megaphone', 'Terbitkan rapor', route('terbitkan-rapor.index')],
    ];
    $r = $rapor;
    $pct = fn ($n) => $r['total'] ? round($n / $r['total'] * 100, 2) : 0;
@endphp

@section('content')
<div class="px-7 pt-7 pb-10">
    <x-ui.page-header :title="'Selamat pagi, '.$sapaan" role="Kurikulum" subtitle="Pantau progres penilaian dan rapor seluruh kelas.">
        <x-slot:actions><x-ui.today :tanggal="$hariIni" /></x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="info" icon="calendar" title="Periode STS Ganjil dibuka">
        Ditutup Jumat, 23 Oktober 2026.
        <x-slot:action><a href="{{ route('periode-penguncian.index') }}" class="text-[13px] font-semibold text-link hover:underline">Atur periode ›</a></x-slot:action>
    </x-ui.alert>

    <div class="mt-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-ui.stat-card label="Siswa aktif" :value="Format::angka($s['siswa'])" :hint="$s['kelas'].' kelas'" />
        <x-ui.stat-card label="Guru" :value="$s['guru']" :hint="$s['asn'].' ASN · '.$s['gtt'].' GTT'" />
        <x-ui.stat-card label="Rombel tanpa pengampu" :value="$s['tanpa_pengampu']" hint="perlu pengampu" :tone="$s['tanpa_pengampu'] ? 'warning' : 'ink'" />
        <x-ui.stat-card label="Kelas tanpa wali" :value="$s['tanpa_wali']" :hint="$s['tanpa_wali'] ? 'Kelas '.$s['kelas_tanpa_wali'] : 'semua kelas sudah ada wali'" :tone="$s['tanpa_wali'] ? 'warning' : 'ink'" />
    </div>

    {{-- Progres penilaian STS --}}
    <x-ui.card class="mt-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-[15px] font-bold text-ink">Progres Penilaian STS</h2>
            <p class="text-xs text-muted">STS Ganjil 2026/2027 · 21 Sep – 23 Okt 2026</p>
        </div>
        <div class="mt-3 divide-y divide-line-soft">
            @foreach ($progres as $p)
                <div class="grid items-center gap-x-8 gap-y-2 py-3 md:grid-cols-[110px_1fr_1fr_1fr]">
                    <p class="text-sm font-bold text-ink">{{ $p['tingkat'] }}</p>
                    @foreach (['terisi' => ['Terisi', 'bg-primary'], 'terverifikasi' => ['Terverifikasi', 'bg-violet'], 'terkunci' => ['Terkunci', 'bg-green-800']] as $k => [$label, $warna])
                        <div>
                            <div class="flex justify-between text-xs"><span class="text-muted">{{ $label }}</span><span class="font-bold text-ink">{{ $p[$k] }}%</span></div>
                            <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $p[$k] }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $label }} {{ $p['tingkat'] }}">
                                <div class="h-full rounded-full {{ $warna }}" style="width: {{ $p[$k] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_365px]">
        {{-- Status per kelas --}}
        <x-ui.card :padding="false" class="self-start overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
                <div>
                    <h2 class="text-[15px] font-bold text-ink">Status per Kelas</h2>
                    <p class="text-xs text-muted">STS Ganjil 2026/2027</p>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-ui.filter-link label="Tingkat" param="tingkat" :options="['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']" />
                    <x-ui.search-form placeholder="Cari kelas…" class="w-[150px]" />
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr>
                            <th class="px-5 py-3">Kelas</th><th class="px-3 py-3">Wali Kelas</th><th class="px-3 py-3">Mapel disetujui</th>
                            <th class="px-3 py-3">Verifikasi</th><th class="px-3 py-3">Kunci</th><th class="px-3 py-3">Rapor</th><th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @forelse ($kelas as $k)
                            <tr>
                                <td class="px-5 py-3 font-bold text-ink">{{ $k['kelas'] }}</td>
                                <td class="px-3 py-3 text-ink-2">{{ $k['wali'] ?? '—' }}</td>
                                <td class="px-3 py-3"><x-ui.progress-mini :nilai="$k['disetujui']" /></td>
                                <td class="px-3 py-3"><x-ui.status-kelas jenis="verifikasi" :nilai="$k['verifikasi']" /></td>
                                <td class="px-3 py-3"><x-ui.status-kelas jenis="kunci" :nilai="$k['kunci']" /></td>
                                <td class="px-3 py-3"><x-ui.status-kelas jenis="rapor" :nilai="$k['rapor']" /></td>
                                <td class="px-5 py-2.5 text-right">
                                    <x-ui.button variant="secondary" size="sm" :href="route('kurikulum.rekap-nilai.index', ['tingkat' => $k['tingkat'], 'kelas' => $k['kelas']])">Detail</x-ui.button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Tidak ada kelas yang cocok.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end border-t border-line-soft px-5 py-3"><x-ui.pager :paginator="$kelas" satuan="kelas" /></div>
        </x-ui.card>

        <div class="flex flex-col gap-5">
            <x-ui.card>
                <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink">
                    Perlu ditindaklanjuti
                    <span class="rounded-full bg-warning-soft px-2 text-xs font-bold text-warning">7</span>
                </h2>
                <ul class="mt-2 divide-y divide-line-soft">
                    @foreach ($tindakLanjut as $t)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-control {{ $tone[$t['tone']] }}"><x-ui.icon :name="$t['ikon']" :size="15" /></span>
                            <span class="flex-1 text-[13px] font-semibold {{ $t['aksi'] ? 'text-ink' : 'text-ink-2' }}">{{ $t['teks'] }}</span>
                            @if ($t['aksi'])
                                <a href="{{ $t['url'] }}" class="text-[13px] font-semibold text-link hover:underline">{{ $t['aksi'] }}</a>
                            @else
                                <span class="text-xs text-muted">{{ $t['ket'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-[15px] font-bold text-ink">Aksi cepat</h2>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    @foreach ($aksiCepat as [$ikon, $judul, $url])
                        <a href="{{ $url }}" class="flex flex-col gap-3 rounded-card border border-line p-3.5 transition-colors hover:border-primary/50 hover:bg-primary-faint">
                            <span class="flex size-8 items-center justify-center rounded-control bg-primary-soft text-primary-strong"><x-ui.icon :name="$ikon" :size="15" /></span>
                            <span class="text-[13px] font-bold text-ink">{{ $judul }}</span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-[15px] font-bold text-ink">Status Rapor STS</h2>
                <p class="mt-1 text-[13px] text-ink-2"><strong class="text-ink">{{ $r['siap'] }} kelas</strong> siap dikunci · <strong class="text-ink">{{ $r['terkunci'] }}</strong> terkunci · <strong class="text-ink">{{ $r['terbit'] }}</strong> terbit</p>
                <div class="mt-3 flex h-2.5 overflow-hidden rounded-full bg-line" role="img" aria-label="{{ $r['siap'] }} siap dikunci, {{ $r['terkunci'] }} terkunci, {{ $r['terbit'] }} terbit, {{ $r['belum'] }} belum siap">
                    <div class="bg-primary" style="width: {{ $pct($r['siap']) }}%"></div>
                    <div class="bg-green-800" style="width: {{ $pct($r['terkunci']) }}%"></div>
                    <div class="bg-success-strong" style="width: {{ $pct($r['terbit']) }}%"></div>
                </div>
                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-2">
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-primary"></span>Siap dikunci {{ $r['siap'] }}</li>
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-green-800"></span>Terkunci {{ $r['terkunci'] }}</li>
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-success-strong"></span>Terbit {{ $r['terbit'] }}</li>
                    <li class="flex items-center gap-1.5"><span class="size-2 rounded-sm bg-line"></span>Belum siap {{ $r['belum'] }}</li>
                </ul>
                <p class="mt-2 text-xs text-muted">Dari {{ $r['total'] }} kelas seluruh tingkat</p>
            </x-ui.card>
        </div>
    </div>
</div>
@endsection
