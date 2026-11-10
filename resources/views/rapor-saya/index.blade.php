@extends('layouts.app')

@section('title', 'Rapor Saya')

@section('content')
@vite(['resources/js/rapor-saya.js'])

@php
    $periodeAktif  = $periodeAktif  ?? 'sts';
    $raporSts      = $raporSts      ?? null;
    $raporSemester = $raporSemester ?? null;

    $isSemester  = $periodeAktif === 'semester';
    $raporAktif  = $isSemester ? $raporSemester : $raporSts;
    $namaPeriode = $isSemester ? 'Rapor Semester · Ganjil 2026/2027' : 'Rapor STS · Ganjil 2026/2027';
    $namaFile    = $raporAktif['nama_file'] ?? 'rapor.pdf';

    $urlSts      = route('rapor-saya.index', ['periode' => 'sts']);
    $urlSemester = route('rapor-saya.index', ['periode' => 'semester']);
@endphp

<div class="min-h-screen bg-slate-50 p-6 font-sans text-slate-800">

    {{-- HEADER HALAMAN --}}
    <header class="mb-5 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">SMA Negeri 1 Kota Malang · Siswa</p>
            <h1 class="mt-1 text-2xl font-bold">Rapor Saya</h1>
            <p class="mt-1 text-sm text-slate-500">Rapor resmi yang sudah diterbitkan oleh sekolah.</p>
        </div>

        <div class="inline-flex rounded-lg bg-white p-0.5 shadow-sm ring-1 ring-slate-200">
            <a href="{{ $urlSts }}"
               class="rounded-md px-3 py-1.5 text-xs {{ ! $isSemester ? 'bg-sky-500 font-semibold text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                Rapor STS
            </a>
            <a href="{{ $urlSemester }}"
               class="rounded-md px-3 py-1.5 text-xs {{ $isSemester ? 'bg-sky-500 font-semibold text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                Rapor Semester
            </a>
        </div>
    </header>

    <div class="grid gap-5 lg:grid-cols-[280px_1fr]">

        {{-- DAFTAR RAPOR --}}
        <aside class="h-fit rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold">Rapor tersedia</h2>

            <div class="flex flex-col gap-2.5">
                <a href="{{ $urlSts }}"
                   class="flex flex-col gap-2 rounded-lg border p-3 {{ ! $isSemester ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                    <span class="text-xs font-semibold">Rapor STS · Ganjil 2026/2027</span>
                    @if ($raporSts)
                        <span class="self-start rounded bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">
                            ✓ Terbit {{ $raporSts['tanggal'] }} · Versi {{ $raporSts['versi'] }}
                        </span>
                    @else
                        <span class="self-start rounded bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">Belum terbit</span>
                    @endif
                </a>

                <a href="{{ $urlSemester }}"
                   class="flex flex-col gap-2 rounded-lg border p-3 {{ $raporSemester ? '' : 'border-dashed opacity-60' }} {{ $isSemester ? 'border-sky-200 bg-sky-50' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                    <span class="text-xs font-semibold">Rapor Semester · Ganjil 2026/2027</span>
                    @if ($raporSemester)
                        <span class="self-start rounded bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700">
                            ✓ Terbit {{ $raporSemester['tanggal'] }} · Versi {{ $raporSemester['versi'] }}
                        </span>
                    @else
                        <span class="self-start rounded bg-slate-100 px-2 py-0.5 text-[10px] text-slate-500">Belum terbit</span>
                    @endif
                </a>
            </div>
        </aside>

        {{-- PRATINJAU --}}
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($raporAktif)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <span class="text-xs text-slate-500">Pratinjau rapor · {{ $namaPeriode }}</span>

                    <div class="flex gap-2">
                        <button type="button" data-cetak
                                class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium hover:bg-slate-50">
                            Cetak
                        </button>
                        <button type="button"
                                data-buka-unduh
                                data-url="{{ $raporAktif['url'] }}"
                                data-nama="{{ $namaFile }}"
                                class="rounded-lg bg-sky-500 px-3.5 py-2 text-xs font-semibold text-white hover:bg-sky-600">
                            Unduh PDF
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    <iframe data-pratinjau
                            src="{{ $raporAktif['url'] }}"
                            title="Pratinjau {{ $namaPeriode }}"
                            class="h-[720px] w-full rounded-lg border border-slate-200"></iframe>
                </div>
            @else
                <div class="flex min-h-[420px] flex-col items-center justify-center gap-2 p-8 text-center">
                    <div class="mb-2 grid size-16 place-items-center rounded-full bg-slate-100 text-2xl">
                        {{ $isSemester ? '🔒' : '📄' }}
                    </div>

                    @if ($isSemester)
                        <h3 class="text-sm font-semibold">Rapor Semester belum diterbitkan</h3>
                        <p class="max-w-xs text-xs text-slate-500">
                            Rapor Semester Ganjil 2026/2027 akan tersedia setelah diterbitkan oleh sekolah.
                        </p>
                        <a href="{{ $urlSts }}"
                           class="mt-3 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium hover:bg-slate-50">
                            Lihat Rapor STS
                        </a>
                    @else
                        <h3 class="text-sm font-semibold">Rapor STS belum tersedia</h3>
                        <p class="max-w-xs text-xs text-slate-500">
                            Rapor akan muncul di sini setelah diterbitkan oleh sekolah.
                        </p>
                    @endif
                </div>
            @endif
        </section>
    </div>

    <p class="mt-4 text-[11px] text-slate-500">ⓘ Jika ada data yang tidak sesuai, hubungi wali kelas.</p>

    {{-- MODAL UNDUH --}}
    @if ($raporAktif)
        <div data-modal class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
            <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true">
                <button type="button" data-tutup-modal
                        class="absolute right-3.5 top-3 text-xl text-slate-400 hover:text-slate-600"
                        aria-label="Tutup">×</button>

                <div class="mb-3 grid size-10 place-items-center rounded-full bg-sky-100 text-sky-600">⬇</div>
                <h3 class="mb-3.5 text-sm font-semibold">Unduh {{ $namaPeriode }}</h3>

                <div class="mb-4 flex items-center gap-2.5 rounded-lg border border-slate-200 p-2.5 text-xs font-semibold">
                    <span class="rounded bg-red-100 px-1.5 py-1 text-[10px] font-bold text-red-600">PDF</span>
                    <span data-modal-nama></span>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" data-tutup-modal
                            class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium hover:bg-slate-50">
                        Batal
                    </button>
                    <a href="#" data-modal-link download
                       class="rounded-lg bg-sky-500 px-3.5 py-2 text-xs font-semibold text-white hover:bg-sky-600">
                        Unduh PDF
                    </a>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection