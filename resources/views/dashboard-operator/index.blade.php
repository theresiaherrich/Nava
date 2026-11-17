@extends('layouts.app')

@section('title', 'Dashboard Operator')

@php
    use App\Support\Format;

    $s = $data['statistik'];
    $persenOrtu = $s['siswa'] ? round($s['ortu_terhubung'] / $s['siswa'] * 100) : 0;
    $tone = [
        'success' => 'bg-success-soft text-success',
        'info' => 'bg-info-soft text-info',
        'warning' => 'bg-warning-soft text-warning-strong',
        'danger' => 'bg-danger-soft text-danger',
        'violet' => 'bg-violet-soft text-violet',
        'neutral' => 'bg-neutral-soft text-neutral',
    ];
    $aksiCepat = [
        ['upload', 'Impor siswa', 'Dari berkas Excel', route('data-siswa.index', ['modal' => 'impor'])],
        ['upload', 'Impor guru', 'ASN & GTT', route('data-guru.index', ['modal' => 'impor'])],
        ['plus', 'Tambah akun', 'Satu per satu', route('akun-peran.index', ['modal' => 'akun'])],
        ['link', 'Hubungkan orang tua', 'Relasi orang tua–anak', route('relasi-orang-tua.index', ['modal' => 'hubungkan'])],
    ];
@endphp

@section('content')
<div class="px-7 pt-7 pb-10">
    <x-ui.page-header :title="'Selamat pagi, '.$data['sapaan']" role="Operator"
                      subtitle="Kelola data induk yang dipakai seluruh modul SIAKAD.">
        <x-slot:actions><x-ui.today :tanggal="$data['hari_ini']" /></x-slot:actions>
    </x-ui.page-header>

    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
        <x-ui.stat-card label="Akun aktif" :value="Format::angka($s['akun'])" hint="guru, siswa, orang tua, staf" />
        <x-ui.stat-card label="Siswa" :value="Format::angka($s['siswa'])" :hint="$s['kelas'].' kelas · 2026/2027'" />
        <x-ui.stat-card label="Guru" :value="$s['guru']" :hint="$s['asn'].' ASN · '.$s['gtt'].' GTT'" />
        <x-ui.card class="px-5 py-5" :padding="false">
            <p class="text-[13px] text-muted">Orang tua terhubung</p>
            <p class="mt-1.5 flex items-baseline gap-1.5">
                <span class="text-[30px] leading-none font-bold text-ink">{{ Format::angka($s['ortu_terhubung']) }}</span>
                <span class="text-base font-semibold text-muted">/ {{ Format::angka($s['siswa']) }}</span>
            </p>
            <div class="mt-4 h-1 overflow-hidden rounded-full bg-line" role="progressbar" aria-valuenow="{{ $persenOrtu }}" aria-valuemin="0" aria-valuemax="100" aria-label="Orang tua terhubung">
                <div class="h-full rounded-full bg-warning-strong" style="width: {{ $persenOrtu }}%"></div>
            </div>
            <p class="mt-2 text-xs text-muted"><strong class="font-bold text-warning">{{ $s['ortu_belum'] }} belum</strong> terhubung</p>
        </x-ui.card>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_439px]">
        <div class="flex flex-col gap-5">
            <x-ui.card>
                <h2 class="flex items-center gap-2 text-[15px] font-bold text-ink">
                    Perlu ditindaklanjuti
                    <span class="rounded-full bg-warning-soft px-2 text-xs font-bold text-warning">{{ count($data['tindak_lanjut']) }}</span>
                </h2>
                <ul class="mt-3 divide-y divide-line-soft">
                    @foreach ($data['tindak_lanjut'] as $t)
                        <li class="flex items-center gap-3 py-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-control {{ $tone[$t['tone']] }}">
                                <x-ui.icon :name="$t['ikon']" :size="15" />
                            </span>
                            <span class="flex-1 text-sm font-semibold text-ink">{{ $t['teks'] }}</span>
                            <a href="{{ route(...$t['route']) }}" class="inline-flex items-center gap-1 text-[13px] font-semibold text-link hover:underline">
                                {{ $t['aksi'] }} <x-ui.icon name="arrow-right" :size="13" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-[15px] font-bold text-ink">Aksi cepat</h2>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($aksiCepat as [$ikon, $judul, $ket, $url])
                        <a href="{{ $url }}" class="flex items-center gap-3 rounded-card border border-line px-4 py-3.5 transition-colors hover:border-primary/50 hover:bg-primary-faint">
                            <span class="flex size-8 items-center justify-center rounded-control bg-primary-soft text-primary-strong">
                                <x-ui.icon :name="$ikon" :size="15" />
                            </span>
                            <span>
                                <span class="block text-sm font-bold text-ink">{{ $judul }}</span>
                                <span class="block text-xs text-muted">{{ $ket }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <x-ui.card class="self-start">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-ink">Aktivitas terbaru</h2>
                <a href="#" class="text-[13px] font-semibold text-link hover:underline"
                   x-data x-on:click.prevent="$dispatch('toast', { message: 'Halaman log aktivitas belum tersedia di desain.', type: 'info' })">Lihat semua</a>
            </div>
            <ol class="relative mt-4 space-y-4 before:absolute before:top-2 before:bottom-2 before:left-[13px] before:w-px before:bg-line">
                @foreach ($data['aktivitas'] as $a)
                    <li class="relative flex gap-3">
                        <span class="relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full ring-4 ring-surface {{ $tone[$a['tone']] }}">
                            <x-ui.icon :name="$a['ikon']" :size="13" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-[13px] font-bold text-ink">{{ $a['judul'] }}</p>
                                <p class="shrink-0 text-xs text-muted">{{ $a['waktu'] }}</p>
                            </div>
                            <p class="text-xs text-muted">{{ $a['ket'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    </div>
</div>
@endsection
