@extends('layouts.app')

@section('title', 'Dashboard Guru Mapel')

@php
    $status = [
        'terkirim' => ['Nilai terkirim', 'info', 'bg-primary'],
        'sedang_diisi' => ['Sedang diisi', 'warning', 'bg-warning-strong'],
        'belum_ada_tp' => ['TP belum ditulis', 'danger', 'bg-line'],
        'perlu_revisi' => ['Perlu revisi', 'violet', 'bg-violet'],
        'belum_mulai' => ['Belum mulai', 'neutral', 'bg-line'],
        'disetujui' => ['Disetujui', 'success', 'bg-success-strong'],
    ];
    $aktivitasGaya = [
        'revisi' => ['alert', 'bg-warning-soft text-warning-strong'],
        'terkirim' => ['send', 'bg-primary-soft text-primary-strong'],
        'disetujui' => ['check', 'bg-success-soft text-success'],
    ];
    $s = $data['statistik'];
@endphp

@section('content')
<div class="px-7 pt-7 pb-10" x-data="{ semester: @js($data['semester'][0]) }">
    <x-ui.page-header :title="'Selamat pagi, '.$data['guru']['sapaan']"
                      subtitle="Pantau progres pengisian nilai untuk kelas yang Anda ampu.">
        <x-slot:actions>
            <span class="inline-flex h-9 items-center gap-2 rounded-control border border-line bg-surface px-3 text-[13px] text-muted">
                <x-ui.icon name="calendar" :size="15" class="text-link" />
                Hari ini · <strong class="font-bold text-ink">{{ $data['hari_ini'] }}</strong>
            </span>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.alert tone="info" icon="calendar" :title="$data['periode']['judul']">
        {{ $data['periode']['keterangan'] }}
        <x-slot:action>
            <a href="#" class="text-[13px] font-semibold text-link hover:underline"
               x-on:click.prevent="$dispatch('toast', { message: 'Halaman periode untuk guru belum tersedia.', type: 'info' })">Lihat periode ›</a>
        </x-slot:action>
    </x-ui.alert>

    <div class="mt-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-ui.stat-card label="Rombel diampu" :value="$s['rombel']" :hint="$s['rombel_ket']" />
        <x-ui.stat-card label="TP dibuat" :value="$s['tp']" :suffix="$s['tp_maks'] ? '/ '.$s['tp_maks'] : null" :hint="$s['tp_ket']" />
        <x-ui.stat-card label="Nilai terisi" :value="$s['terisi'].'%'" :progress="$s['terisi']" />
        <x-ui.stat-card label="Dikembalikan untuk revisi" :value="$s['revisi']" :hint="$s['revisi_ket']"
                        :tone="$s['revisi'] > 0 ? 'warning' : 'ink'" />
    </div>

    <div class="mt-7 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-ink">Kelas yang Anda ampu</h2>
            <p class="text-[13px] text-muted">{{ count($data['kelas']) }} kelas · {{ $data['guru']['mapel'] }}</p>
        </div>
        <label class="relative">
            <span class="sr-only">Semester</span>
            <select x-model="semester"
                    class="h-9 appearance-none rounded-control border border-line bg-surface pr-9 pl-3 text-[13px] font-medium text-ink focus:border-primary focus:outline-none">
                @foreach ($data['semester'] as $opsi)
                    <option>{{ $opsi }}</option>
                @endforeach
            </select>
            <x-ui.icon name="chevron-down" :size="14" class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-ink-2" />
        </label>
    </div>

    @if (empty($data['kelas']))
        <x-ui.card class="mt-4" :padding="false">
            <x-ui.empty-state icon="clipboard" title="Belum ada rombel yang Anda ampu"
                              description="Hubungi Waka Kurikulum untuk pembagian mengajar semester ini." class="py-16" />
        </x-ui.card>
    @else
        <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_317px]">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($data['kelas'] as $k)
                    @php [$label, $tone, $bar] = $status[$k['status']]; $bisaInput = $k['jumlah_tp'] > 0; @endphp
                    <x-ui.card class="flex flex-col">
                        <div class="flex items-start justify-between">
                            <span class="flex size-9 items-center justify-center rounded-control bg-primary-soft text-sm font-bold text-primary-strong">
                                {{ mb_substr($k['mapel'], 0, 1) }}
                            </span>
                            <x-ui.badge :tone="$tone">{{ $label }}</x-ui.badge>
                        </div>
                        <p class="mt-4 text-[11px] font-bold tracking-wide text-muted uppercase">{{ $k['mapel'] }} · {{ $k['jumlah_tp'] }} TP</p>
                        <p class="mt-0.5 text-[22px] font-bold text-ink">{{ $k['kelas'] }}</p>
                        <p class="text-[13px] text-muted">{{ $k['jumlah_siswa'] }} siswa</p>

                        <div class="mt-4 flex items-center justify-between text-xs">
                            <span class="text-muted">Nilai TP terisi</span>
                            <span class="font-bold text-ink">{{ $k['terisi'] }}%</span>
                        </div>
                        <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $k['terisi'] }}"
                             aria-valuemin="0" aria-valuemax="100" aria-label="Nilai TP terisi {{ $k['kelas'] }}">
                            <div class="h-full rounded-full {{ $bar }}" style="width: {{ $k['terisi'] }}%"></div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <x-ui.button variant="secondary" :href="route('tp.index', ['rombel' => $k['id_rombel']])">Kelola TP</x-ui.button>
                            @if ($bisaInput)
                                <x-ui.button :href="route('input-nilai.index', ['rombel' => $k['id_rombel']])">Input nilai</x-ui.button>
                            @else
                                <x-ui.button variant="soft" disabled title="Tulis TP terlebih dahulu">Input nilai</x-ui.button>
                            @endif
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <x-ui.card class="self-start">
                <h3 class="text-[15px] font-bold text-ink">Aktivitas terbaru</h3>
                <ul class="mt-3 divide-y divide-line-soft">
                    @foreach ($data['aktivitas'] as $a)
                        @php [$ikon, $gaya] = $aktivitasGaya[$a['jenis']]; @endphp
                        <li class="flex items-start gap-3 py-3">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-control {{ $gaya }}">
                                <x-ui.icon :name="$ikon" :size="14" />
                            </span>
                            <div>
                                <p class="text-[13px] font-semibold text-ink">{{ $a['judul'] }}</p>
                                <p class="text-xs text-muted">{{ $a['waktu'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <a href="#" class="mt-1 inline-block text-[13px] font-semibold text-link hover:underline"
                   x-on:click.prevent="$dispatch('toast', { message: 'Halaman notifikasi belum tersedia.', type: 'info' })">Lihat semua notifikasi ›</a>
            </x-ui.card>
        </div>
    @endif
</div>
@endsection
