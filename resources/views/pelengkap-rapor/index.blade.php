@extends('layouts.app')

@section('title', 'Pelengkap Rapor')

@vite(['resources/js/pelengkap-rapor.js'])

@section('content')
@php
    $kelas                = $kelas                ?? '-';
    $periodeAktif         = $periodeAktif         ?? 'sts';
    $statusFilter         = $statusFilter         ?? 'semua';
    $search               = $search               ?? '';
    $siswa                = $siswa                ?? collect();
    $totalSiswa           = $totalSiswa           ?? 0;
    $catatanTerisi        = $catatanTerisi        ?? 0;
    $ekskulTerisi         = $ekskulTerisi         ?? 0;
    $prestasiBelumDinilai = $prestasiBelumDinilai ?? 0;
    $lengkap              = $lengkap              ?? 0;
    $kehadiranTerisi      = $kehadiranTerisi      ?? false;
    $kehadiranSumber      = $kehadiranSumber      ?? '-';
    $kehadiranWaktu       = $kehadiranWaktu       ?? null;
    $templateCepat        = $templateCepat        ?? [];
    $terkunci             = $terkunci             ?? false;
    $infoBanner           = $infoBanner           ?? null;
    $terakhirTersimpan    = $terakhirTersimpan    ?? '-';

    $statusOpsi = [
        'semua'   => 'Semua siswa',
        'belum'   => 'Belum lengkap',
        'lengkap' => 'Lengkap',
    ];

    $bannerStyle = [
        'info'    => 'border-sky-200 bg-sky-50 text-sky-800',
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'warning' => 'border-orange-200 bg-orange-50 text-orange-800',
    ];

    $headerSelesai = $totalSiswa > 0 && $lengkap === $totalSiswa;
    $persenCatatan = $totalSiswa ? round(($catatanTerisi / $totalSiswa) * 100) : 0;
    $persenEkskul  = $totalSiswa ? round(($ekskulTerisi / $totalSiswa) * 100) : 0;

    $urlPratinjau = \Illuminate\Support\Facades\Route::has('pelengkap-rapor.pratinjau')
        ? route('pelengkap-rapor.pratinjau')
        : '#';
@endphp

<div class="min-h-screen bg-slate-50 p-6 font-sans text-slate-800"
     data-total="{{ $totalSiswa }}"
     data-terkunci="{{ $terkunci ? '1' : '0' }}">

    {{-- HEADER --}}
    <header class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">SMA Negeri 1 Kota Malang · Wali Kelas</p>
            <h1 class="mt-1 text-2xl font-bold">Pelengkap Rapor — {{ $kelas }}</h1>
            <p class="mt-1 text-sm text-slate-500">Lengkapi catatan wali kelas, data kehadiran, dan prestasi ekstrakurikuler sebelum rapor diterbitkan.</p>
        </div>

        <div class="flex items-center gap-3">
            <span id="pl-lengkap-badge"
                  class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $headerSelesai ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                <span id="pl-lengkap-count">{{ $lengkap }}/{{ $totalSiswa }}</span>
                {{ $terkunci ? '· Terkunci' : 'lengkap' }}
            </span>

            @unless($terkunci)
                <span class="text-xs text-slate-500">
                    Tersimpan otomatis <span id="pl-waktu-simpan" class="font-semibold text-slate-700">{{ $terakhirTersimpan }}</span>
                </span>
                <button type="submit" form="form-pelengkap"
                        class="rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600">
                    Simpan
                </button>
            @endunless
        </div>
    </header>

    {{-- FILTER BAR --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        {{-- Periode --}}
        <div class="inline-flex items-center gap-2 text-xs text-slate-500">
            <span>Periode</span>
            <div class="inline-flex rounded-lg bg-white p-0.5 shadow-sm ring-1 ring-slate-200">
                <a href="{{ request()->fullUrlWithQuery(['periode' => 'sts']) }}"
                   class="rounded-md px-3 py-1.5 text-xs {{ $periodeAktif === 'sts' ? 'bg-sky-500 font-semibold text-white' : 'text-slate-500 hover:bg-slate-50' }}">Rapor STS</a>
                <a href="{{ request()->fullUrlWithQuery(['periode' => 'semester']) }}"
                   class="rounded-md px-3 py-1.5 text-xs {{ $periodeAktif === 'semester' ? 'bg-sky-500 font-semibold text-white' : 'text-slate-500 hover:bg-slate-50' }}">Rapor Semester</a>
            </div>
        </div>

        {{-- Dropdown status --}}
        <div class="relative" data-dropdown>
            <button type="button" data-dropdown-toggle
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm hover:bg-slate-50">
                <span>Status: {{ $statusOpsi[$statusFilter] ?? 'Semua siswa' }}</span>
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div data-dropdown-menu class="absolute left-0 top-full z-20 mt-1.5 hidden w-56 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                @foreach($statusOpsi as $kode => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $kode]) }}"
                       class="flex items-center justify-between rounded-lg px-2.5 py-2 text-sm {{ $statusFilter === $kode ? 'bg-sky-100 font-semibold' : 'hover:bg-slate-50' }}">
                        <span>{{ $label }}</span>
                        @if($statusFilter === $kode)
                            <svg class="size-3.5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('pelengkap-rapor.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="periode" value="{{ $periodeAktif }}">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau NISN..."
                   class="w-56 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100">
        </form>

        <a href="{{ $urlPratinjau }}" class="ml-auto text-sm font-semibold text-sky-600 hover:underline">Lihat pratinjau rapor →</a>
    </div>

    {{-- BANNER --}}
    @if($infoBanner)
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm {{ $bannerStyle[$infoBanner['tipe']] ?? $bannerStyle['info'] }}">
            <strong class="block">{{ $infoBanner['judul'] }}</strong>
            <span>{{ $infoBanner['teks'] }}</span>
        </div>
    @endif

    {{-- RINGKASAN --}}
    <div class="mb-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Catatan wali kelas</p>
            <p class="mt-1 text-2xl font-bold">{{ $catatanTerisi }} <span class="text-sm font-normal text-slate-400">/ {{ $totalSiswa }}</span></p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-green-500" style="width: {{ $persenCatatan }}%"></div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Kehadiran (Presensi A1)</p>
            <span class="mt-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $kehadiranTerisi ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                {{ $kehadiranTerisi ? 'Terisi' : 'Belum terisi' }}
            </span>
            @if($kehadiranTerisi)
                <p class="mt-2 text-xs text-slate-500">Sumber: {{ $kehadiranSumber }}</p>
                @if($kehadiranWaktu)
                    <p class="text-xs text-slate-400">Diperbarui {{ $kehadiranWaktu }}</p>
                @endif
            @else
                <p class="mt-2 text-xs text-slate-500">Menunggu data kehadiran dari modul presensi.</p>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Prestasi ekstrakurikuler</p>
            <p class="mt-1 text-2xl font-bold">{{ $ekskulTerisi }} <span class="text-sm font-normal text-slate-400">/ {{ $totalSiswa }}</span></p>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-sky-500" style="width: {{ $persenEkskul }}%"></div>
            </div>
            <p class="mt-2 text-xs {{ $prestasiBelumDinilai > 0 ? 'text-orange-600' : 'text-slate-500' }}">
                @if($prestasiBelumDinilai > 0)
                    {{ $prestasiBelumDinilai }} siswa belum dinilai
                @else
                    Semua siswa sudah dinilai
                @endif
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-600">Template catatan cepat</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @forelse($templateCepat as $t)
                    <button type="button" data-template="{{ $t }}" @disabled($terkunci)
                            class="rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] text-sky-700 hover:bg-sky-100 disabled:opacity-50">
                        + {{ $t }}
                    </button>
                @empty
                    <span class="text-xs text-slate-400">Belum ada template.</span>
                @endforelse
            </div>
            <p class="mt-2 text-[10px] text-slate-400">Klik untuk menyisipkan ke catatan yang sedang diisi.</p>
        </div>
    </div>

    {{-- TABEL --}}
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="mb-3">
            <h2 class="text-sm font-semibold">Daftar peserta didik</h2>
            <p class="text-xs text-slate-500">{{ $totalSiswa }} siswa · {{ $kelas }}</p>
        </div>

        <form id="form-pelengkap" method="POST" action="{{ route('pelengkap-rapor.simpan') }}">
            @csrf
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full min-w-[960px] border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] text-slate-500">
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold">No</th>
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold">NIS</th>
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold">Nama siswa</th>
                            <th colspan="3" class="border-l border-slate-200 px-3 py-2 text-center font-semibold">Kehadiran · Presensi A1</th>
                            <th rowspan="2" class="border-l border-slate-200 px-3 py-2 text-left font-semibold">Ekstrakurikuler</th>
                            <th rowspan="2" class="border-l border-slate-200 px-3 py-2 text-left font-semibold">Catatan wali kelas</th>
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold">Status</th>
                        </tr>
                        <tr class="bg-slate-50 text-[11px] text-slate-500">
                            <th class="border-l border-slate-200 px-2 py-1.5 text-center font-medium">Sakit</th>
                            <th class="px-2 py-1.5 text-center font-medium">Izin</th>
                            <th class="px-2 py-1.5 text-center font-medium">Alpa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($siswa as $i => $s)
                            @php $sudahIsi = filled($s->catatan); @endphp
                            <tr class="align-top" data-row>
                                <td class="px-3 py-3 text-slate-500">{{ $i + 1 }}</td>
                                <td class="px-3 py-3 text-slate-500">{{ $s->nisn }}</td>
                                <td class="px-3 py-3 font-semibold">{{ $s->nama }}</td>

                                <td class="border-l border-slate-100 px-2 py-3 text-center">{{ $s->sakit ?? '—' }}</td>
                                <td class="px-2 py-3 text-center">{{ $s->izin ?? '—' }}</td>
                                <td class="px-2 py-3 text-center">
                                    @if(is_null($s->alpa))
                                        <span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600">Belum diisi</span>
                                    @else
                                        {{ $s->alpa }}
                                    @endif
                                </td>

                                <td class="border-l border-slate-100 px-3 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($s->ekskul ?? [] as $e)
                                            <span class="rounded-md bg-sky-50 px-1.5 py-0.5 text-[11px] text-sky-800">
                                                {{ $e['nama'] }} <b>{{ $e['nilai'] }}</b>
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="border-l border-slate-100 px-3 py-3" style="min-width: 260px">
                                    <textarea name="catatan[{{ $s->id }}]" rows="2" maxlength="300"
                                              data-catatan-url="{{ route('pelengkap-rapor.catatan', $s->id) }}"
                                              placeholder="Tulis catatan untuk siswa..."
                                              @disabled($terkunci)
                                              class="w-full resize-y rounded-lg border px-2.5 py-1.5 text-xs focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100 disabled:bg-slate-50 {{ $sudahIsi ? 'border-slate-300' : 'border-orange-300' }}">{{ $s->catatan }}</textarea>
                                    <div class="mt-1 text-right text-[10px] text-slate-400">
                                        <span data-counter>{{ mb_strlen($s->catatan ?? '') }}</span>/300
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    <span data-status
                                          class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $sudahIsi ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $sudahIsi ? 'Lengkap' : 'Belum lengkap' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-8 text-center text-slate-500">Belum ada data peserta didik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>
@endsection