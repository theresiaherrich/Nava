@extends('layouts.app')

@section('title', 'Rekap Nilai (DKN)')

@php
    use App\Support\Format;

    $statusRombel = [
        'terkirim' => ['Terkirim', 'info'], 'sedang_diisi' => ['Sedang diisi', 'warning'], 'belum_ada_tp' => ['Belum ada TP', 'danger'],
        'perlu_revisi' => ['Perlu revisi', 'violet'], 'belum_mulai' => ['Belum mulai', 'neutral'], 'disetujui' => ['Disetujui', 'success'],
    ];
    $s = $statistik;
    $nTp = count($aktif['tp']);
    $sasIdx = collect($aktif['tp'])->search(fn ($t) => $t['sas']);
    $url = fn (array $q) => request()->fullUrlWithQuery($q + ['page' => null]);
    $angka = fn ($n) => $n === null ? '—' : Format::angka($n, 1);
@endphp

@section('content')
<div class="px-7 pt-7 pb-10">
    <x-ui.page-header title="Rekap Nilai (DKN)" subtitle="Pantau kelengkapan dan ringkasan nilai rombel yang Anda ampu." class="mb-3">
        <x-slot:actions>
            {{-- 33 Ekspor Excel. TODO API: GET /api/guru/rekap/ekspor?rombel=|semua --}}
            <div class="relative" x-data="{ buka: @js(request('ekspor') === '1') }" x-on:click.outside="buka = false" x-on:keydown.escape="buka = false">
                <x-ui.button variant="secondary" icon="download" size="lg" x-on:click="buka = !buka" x-bind:aria-expanded="buka" aria-haspopup="menu">Ekspor Excel <x-ui.icon name="chevron-down" :size="14" /></x-ui.button>
                <div x-show="buka" x-cloak x-transition.opacity role="menu" class="absolute top-full right-0 z-30 mt-1.5 w-[270px] rounded-card border border-line bg-surface p-2 shadow-pop">
                    @foreach ([["DKN {$aktif['kelas']} · Matematika", "{$s['siswa']} siswa · {$nTp} TP", "DKN {$aktif['kelas']}"], ['Semua rombel yang diampu', count($ringkasan).' rombel · 1 sheet per rombel', 'DKN semua rombel']] as [$judul, $ket, $toast])
                        <button type="button" role="menuitem" class="flex w-full items-start gap-3 rounded-control px-2.5 py-2.5 text-left hover:bg-success-soft/40"
                                x-on:click="buka = false; $dispatch('toast', { message: @js($toast.' berhasil diekspor'), type: 'info' })">
                            <x-ui.icon name="sheet" :size="16" class="mt-0.5 text-success" />
                            <span><span class="block text-[13px] font-semibold text-success">{{ $judul }}</span><span class="block text-xs text-muted">{{ $ket }}</span></span>
                        </button>
                    @endforeach
                    <p class="mt-1 border-t border-line-soft px-2.5 pt-2.5 pb-1 text-xs text-muted">Format .xlsx · nilai TP, STS, dan semester</p>
                </div>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    <nav aria-label="Tampilan rekap" class="mb-5 flex gap-6 border-b border-line">
        @foreach (['ringkasan' => 'Ringkasan rombel', 'siswa' => 'Per siswa'] as $t => $teks)
            <a href="{{ $url(['tab' => $t]) }}" @if ($tab === $t) aria-current="page" @endif
               @class(['-mb-px border-b-2 px-0.5 pb-2.5 text-sm', 'border-primary font-semibold text-link' => $tab === $t, 'border-transparent text-muted hover:text-ink' => $tab !== $t])>{{ $teks }}</a>
        @endforeach
    </nav>

    @if ($tab === 'ringkasan')
        {{-- 32 Ringkasan rombel --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
                <div><h2 class="text-[15px] font-bold text-ink">Ringkasan rombel yang Anda ampu</h2><p class="text-xs text-muted">{{ count($ringkasan) }} rombel · Matematika · Semester Ganjil 2026/2027</p></div>
                <div class="flex items-center gap-2.5"><span class="text-[13px] text-muted">Periode:</span>
                    <div class="inline-flex items-center rounded-control bg-neutral-soft p-0.5" role="group" aria-label="Periode">
                        @foreach (['sts' => 'STS', 'semester' => 'Semester'] as $p => $teks)
                            <a href="{{ $url(['periode' => $p === 'semester' ? null : $p]) }}" @class(['rounded-[6px] px-3 py-1.5 text-[13px] font-medium', 'bg-surface text-link shadow-card' => $periode === $p, 'text-muted hover:text-ink' => $periode !== $p])>{{ $teks }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            @if ($kosong || collect($ringkasan)->sum('tp') === 0)
                <x-ui.empty-state icon="table" title="Belum ada nilai untuk direkap" description="Rekap muncul setelah Anda menulis TP dan mengisi nilai di rombel yang diampu.">
                    <x-ui.button class="mt-5" icon="target" :href="route('tp.index')">Kelola TP</x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-[13px]">
                        <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                            <tr><th class="px-5 py-3">Rombel</th><th class="px-3 py-3">Siswa</th><th class="px-3 py-3">TP</th><th class="px-3 py-3">Kelengkapan</th><th class="px-3 py-3">Rata-rata</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            @foreach ($ringkasan as $r)
                                @php [$label, $tone] = $statusRombel[$r['status']]; @endphp
                                <tr>
                                    <td class="px-5 py-3"><strong class="text-ink">{{ $r['kelas'] }}</strong> <span class="text-muted">· Matematika</span></td>
                                    <td class="px-3 py-3 text-ink-2">{{ $r['siswa'] }}</td>
                                    <td class="px-3 py-3 text-ink-2">{{ $r['tp'] }} TP</td>
                                    <td class="px-3 py-3"><x-ui.progress-mini :nilai="$r['kelengkapan']" :maks="100" :label="$r['kelengkapan'].'%'" class="[&>div]:w-24" /></td>
                                    <td class="px-3 py-3 font-bold text-ink">{{ $angka($r['rata']) }}</td>
                                    <td class="px-3 py-3"><x-ui.badge :tone="$tone">{{ $label }}</x-ui.badge></td>
                                    <td class="px-5 py-2.5 text-right">
                                        @if ($r['tp'])
                                            <x-ui.button variant="secondary" size="sm" :href="$url(['tab' => 'siswa', 'rombel' => $r['id']])">Lihat</x-ui.button>
                                        @else
                                            <x-ui.button variant="soft" size="sm" disabled title="Belum ada TP">Lihat</x-ui.button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    @else
        {{-- 31 Per siswa --}}
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
            <x-ui.stat-card label="Jumlah siswa" :value="$s['siswa']" :hint="$aktif['kelas'].' · Matematika'" />
            <x-ui.stat-card label="Rata-rata rombel" :value="$angka($s['rata'])" :hint="$periode === 'sts' ? 'nilai STS' : '+2,1 dari semester lalu'" />
            <x-ui.stat-card label="Kelengkapan nilai" :value="$s['kelengkapan'].'%'" :progress="$s['kelengkapan']" />
            <x-ui.stat-card label="Di bawah KKTP" :value="$s['dibawah'].' siswa'" :hint="'KKTP '.$kktp" :tone="$s['dibawah'] ? 'warning' : 'ink'" />
        </div>

        <x-ui.card class="mt-4 flex flex-wrap items-center gap-3 !py-3.5">
            <x-ui.filter-link label="Rombel" param="rombel" :options="collect($rombel)->mapWithKeys(fn ($r) => [$r['id'] => $r['kelas'].' · Matematika'])->all()" />
            <span class="text-[13px] text-muted">Periode:</span>
            <div class="inline-flex items-center rounded-control bg-neutral-soft p-0.5" role="group" aria-label="Periode">
                @foreach (['sts' => 'STS', 'semester' => 'Semester'] as $p => $teks)
                    <a href="{{ $url(['periode' => $p === 'semester' ? null : $p]) }}" @class(['rounded-[6px] px-3 py-1.5 text-[13px] font-medium', 'bg-surface text-link shadow-card' => $periode === $p, 'text-muted hover:text-ink' => $periode !== $p])>{{ $teks }}</a>
                @endforeach
            </div>
            <x-ui.search-form placeholder="Cari nama atau NISN…" class="ml-auto w-[240px]" />
        </x-ui.card>

        <x-ui.card :padding="false" class="mt-4 overflow-hidden">
            @if (! $nTp)
                <x-ui.empty-state icon="table" :title="'Belum ada nilai untuk '.$aktif['kelas']" description="Tulis TP dan isi nilai terlebih dahulu.">
                    <x-ui.button class="mt-5" icon="target" :href="route('tp.index', ['rombel' => $aktif['id']])">Kelola TP</x-ui.button>
                </x-ui.empty-state>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-left text-[13px]">
                        <thead class="text-xs font-semibold text-ink-2">
                            <tr class="bg-surface-2">
                                <th class="px-5 py-3">Nama siswa</th>
                                @for ($j = 0; $j < $nTp; $j++)
                                    @continue($periode === 'sts' && $j >= $jumlahSts)
                                    <th @class(['px-2 py-3 text-center', 'bg-sas text-amber-800' => $j === $sasIdx && $periode === 'semester'])>TP{{ $j + 1 }}{{ $j === $sasIdx && $periode === 'semester' ? ' · SAS' : '' }}</th>
                                @endfor
                                <th @class(['px-3 py-3 text-center', 'bg-rapor text-link' => $periode === 'sts'])>Nilai STS</th>
                                @if ($periode === 'semester')<th class="bg-rapor px-3 py-3 text-center text-link">Nilai Semester</th>@endif
                                <th class="px-5 py-3">Capaian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            @forelse ($baris as $b)
                                @php $akhir = $periode === 'sts' ? $b['sts'] : $b['semester']; @endphp
                                <tr>
                                    <td class="px-5 py-2.5"><p class="font-semibold text-ink">{{ $b['nama'] }}</p><p class="text-xs text-muted">{{ $b['nisn'] }}</p></td>
                                    @foreach ($b['nilai'] as $j => $n)
                                        @continue($periode === 'sts' && $j >= $jumlahSts)
                                        <td @class(['px-2 py-2.5 text-center', 'bg-sas' => $j === $sasIdx && $periode === 'semester'])>
                                            @if ($n === null)<span class="text-subtle">—</span>
                                            @elseif ($n > 100)<span class="rounded-md bg-danger-soft px-1.5 py-0.5 font-semibold text-danger" title="Tidak valid">{{ $n }}</span>
                                            @elseif ($n < $kktp)<span class="rounded-md bg-warning-soft px-1.5 py-0.5 font-semibold text-warning">{{ $n }}</span>
                                            @else<span class="text-ink-2">{{ $n }}</span>@endif
                                        </td>
                                    @endforeach
                                    @foreach ($periode === 'semester' ? ['sts', 'semester'] : ['sts'] as $k)
                                        <td @class(['px-3 py-2.5 text-center', 'bg-rapor font-bold' => $k === ($periode === 'sts' ? 'sts' : 'semester'), 'text-ink-2' => $k === 'sts' && $periode === 'semester'])>
                                            @if ($b[$k] === null)<span class="text-subtle">—</span>
                                            @elseif ($b[$k] < $kktp)<span class="rounded-md bg-warning-soft px-1.5 py-0.5 font-semibold text-warning">{{ $angka($b[$k]) }}</span>
                                            @else<span class="text-ink">{{ $angka($b[$k]) }}</span>@endif
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-2.5">
                                        @if ($akhir === null)<x-ui.badge>Belum lengkap</x-ui.badge>
                                        @elseif ($akhir < $kktp)<x-ui.badge tone="warning">Perlu penguatan</x-ui.badge>
                                        @else<x-ui.badge tone="success">Tercapai</x-ui.badge>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $nTp + 4 }}" class="px-5 py-10 text-center text-sm text-muted">Siswa tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
                    <p class="flex items-center gap-1.5 text-xs text-muted"><span class="size-2.5 rounded-sm bg-warning-soft ring-1 ring-orange-200"></span>Oranye = di bawah KKTP ({{ $kktp }}) · Nilai STS = rata-rata TP1–TP{{ $jumlahSts }}</p>
                    <x-ui.pager :paginator="$baris" satuan="" />
                </div>
            @endif
        </x-ui.card>
    @endif
</div>
@endsection
