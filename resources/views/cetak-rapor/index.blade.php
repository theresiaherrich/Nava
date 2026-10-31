@extends('layouts.app')

@section('title', 'Cetak & Ekspor Rapor')


 @vite(['resources/css/cetak-rapor.css', 'resources/js/cetak-rapor.js'])

@section('content')
@php
    use Illuminate\Pagination\LengthAwarePaginator;

    $kelas                = $kelas                ?? '-';
    $tab                  = $tab                  ?? 'pratinjau';
    $periodeAktif         = $periodeAktif         ?? 'sts';
    $dikunci              = $dikunci              ?? false;
    $dikunciAt            = $dikunciAt            ?? '-';
    $mapelBelumVerifikasi = $mapelBelumVerifikasi ?? 0;
    $siswaSemua           = $siswaSemua           ?? collect();
    $siswaPage            = $siswaPage            ?? new LengthAwarePaginator(collect(), 0, 10);
    $totalSiswa           = $totalSiswa           ?? 0;
    $siapCount            = $siapCount            ?? 0;
    $siswaAktif           = $siswaAktif           ?? null;
    $rapor                = $rapor                ?? null;
    $halamanRapor         = $halamanRapor         ?? [];
    $halamanAktif         = $halamanAktif         ?? 1;
    $halamanTotal         = max(1, count($halamanRapor));
    $riwayat              = $riwayat              ?? collect();
    $jobCetak             = $jobCetak             ?? null;

    $semuaSiap = $totalSiswa > 0 && $siapCount === $totalSiswa;
    $jobProses = $jobCetak && $jobCetak['status'] === 'proses';
    $jobPersen = ($jobCetak && ($jobCetak['total'] ?? 0) > 0)
        ? round((($jobCetak['selesai'] ?? 0) / $jobCetak['total']) * 100)
        : 0;

    $halamanIni = $halamanRapor[$halamanAktif - 1] ?? ($rapor['mapel'] ?? []);
@endphp

<div class="min-h-screen bg-slate-50 p-6 font-sans text-slate-800">

    {{-- HEADER --}}
    <header class="mb-5">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">SMA Negeri 1 Kota Malang · Wali Kelas</p>
        <h1 class="mt-1 text-2xl font-bold">Cetak &amp; Ekspor Rapor</h1>
        <p class="mt-1 text-sm text-slate-500">Pratinjau, cetak, dan unduh rapor siswa tanpa mengolah ulang dokumen.</p>
    </header>

    {{-- TOP BAR --}}
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            Kelas {{ $kelas }}
        </span>

        {{-- Pencarian --}}
        <div class="relative w-64" id="cr-search-wrap">
            <input type="search" id="cr-search" placeholder="Cari nama atau NISN..." autocomplete="off"
                   class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100">
            <div id="cr-search-results" class="absolute left-0 right-0 top-full z-30 mt-1 hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg"></div>
        </div>

        <div class="ml-auto flex flex-wrap items-center gap-3">
            @if($dikunci)
                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 ring-1 ring-green-200">Nilai dikunci {{ $dikunciAt }}</span>
            @else
                <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700 ring-1 ring-orange-200">Belum dikunci</span>
            @endif

            @if($jobProses)
                <button type="button" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white opacity-90">
                    <span class="size-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                    Membuat PDF...
                </button>
            @else
                <form method="POST" action="{{ route('cetak-rapor.kelas') }}">
                    @csrf
                    <button type="submit" @disabled(! $dikunci)
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                        PDF satu kelas
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- BANNER BELUM DIKUNCI --}}
    @unless($dikunci)
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <span class="grid size-7 shrink-0 place-items-center rounded-full bg-amber-100 text-sm font-bold text-amber-700">!</span>
            <div class="flex-1 text-sm">
                <strong class="block text-amber-900">Rapor belum dapat dicetak</strong>
                <span class="text-amber-800">Nilai {{ $kelas }} belum dikunci Kurikulum. {{ $mapelBelumVerifikasi }} mapel masih menunggu verifikasi.</span>
            </div>
            <a href="{{ route('verifikasi-nilai.index') }}" class="shrink-0 text-sm font-semibold text-amber-800 hover:underline">Lihat kelengkapan →</a>
        </div>
    @endunless

    {{-- TAB --}}
    <div class="mb-4 flex gap-6 border-b border-slate-200 text-sm">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'pratinjau']) }}"
           class="-mb-px border-b-2 px-1 pb-2 {{ $tab === 'pratinjau' ? 'border-sky-500 font-semibold text-sky-700' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Pratinjau rapor</a>
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'riwayat']) }}"
           class="-mb-px border-b-2 px-1 pb-2 {{ $tab === 'riwayat' ? 'border-sky-500 font-semibold text-sky-700' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Riwayat cetak</a>
    </div>

    @if($tab === 'riwayat')
        {{-- RIWAYAT CETAK --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs text-slate-500">
                        <th class="px-4 py-2.5 font-semibold">Tanggal</th>
                        <th class="px-4 py-2.5 font-semibold">Jenis rapor</th>
                        <th class="px-4 py-2.5 font-semibold">Versi</th>
                        <th class="px-4 py-2.5 font-semibold">Dicetak oleh</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($riwayat as $r)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $r->tanggal }}</td>
                            <td class="px-4 py-3 font-semibold">{{ $r->jenis }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $r->versi === 'v1' ? 'bg-slate-100 text-slate-600' : 'bg-orange-50 text-orange-700 ring-1 ring-orange-200' }}">
                                    {{ $r->versi }}{{ $r->catatan_versi ? ' · ' . $r->catatan_versi : '' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $r->dicetak_oleh }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('cetak-rapor.ulang', $r->id) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50">
                                    ↓ Unduh ulang
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">Belum ada riwayat cetak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <p class="border-t border-slate-100 px-4 py-2.5 text-[11px] text-slate-500">
                Cetak ulang versi lama memakai data final lama. Versi v2 dibuat setelah koreksi nilai disetujui Kurikulum.
            </p>
        </div>
    @else
        {{-- DAFTAR SISWA DARI HASIL PENCARIAN (tersembunyi, sumber untuk dropdown) --}}
        <ul id="cr-search-source" class="hidden">
            @foreach($siswaSemua as $s)
                <li data-nama="{{ $s->nama }}"
                    data-nisn="{{ $s->nisn }}"
                    data-kelas="{{ $kelas }}"
                    data-url="{{ request()->fullUrlWithQuery(['siswa' => $s->id, 'halaman' => 1]) }}"></li>
            @endforeach
        </ul>

        <div class="grid items-start gap-4 lg:grid-cols-[230px_1fr]">

            {{-- SIDEBAR SISWA --}}
            <aside class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                <div class="mb-2 flex items-center justify-between px-1">
                    <span class="text-sm font-semibold">{{ $totalSiswa }} siswa</span>
                    @if($semuaSiap)
                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700">✓ Semua siap</span>
                    @else
                        <span class="rounded-full bg-orange-50 px-2 py-0.5 text-[11px] font-semibold text-orange-700">{{ $siapCount }} siap</span>
                    @endif
                </div>

                <ul class="flex flex-col gap-1">
                    @forelse($siswaPage as $s)
                        @php
                            $aktif = $siswaAktif && $siswaAktif->id === $s->id;
                            $inisial = collect(explode(' ', $s->nama))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                        @endphp
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['siswa' => $s->id, 'halaman' => 1]) }}"
                               class="flex items-center gap-2.5 rounded-lg px-2 py-2 {{ $aktif ? 'bg-sky-50 ring-1 ring-sky-200' : 'hover:bg-slate-50' }}">
                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">{{ $inisial }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] font-semibold {{ $aktif ? 'text-sky-700' : '' }}">{{ $s->nama }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ $s->nisn }} · {{ $kelas }}</span>
                                </span>
                                @if($s->status === 'siap')
                                    <svg class="size-4 shrink-0 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                @else
                                    <span class="shrink-0 rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700">Belum siap</span>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="p-4 text-center text-xs text-slate-500">Belum ada siswa.</li>
                    @endforelse
                </ul>

                <div class="mt-3 flex items-center justify-between px-1 text-xs text-slate-500">
                    <span>{{ $siswaPage->firstItem() ?? 0 }}–{{ $siswaPage->lastItem() ?? 0 }} dari {{ $siswaPage->total() }}</span>
                    <nav class="flex items-center gap-1">
                        @if($siswaPage->onFirstPage())
                            <span class="rounded-md px-2 py-1 text-slate-300">‹</span>
                        @else
                            <a href="{{ $siswaPage->previousPageUrl() }}" class="rounded-md px-2 py-1 hover:bg-slate-100">‹</a>
                        @endif

                        @foreach($siswaPage->getUrlRange(1, $siswaPage->lastPage()) as $p => $url)
                            <a href="{{ $url }}"
                               class="rounded-md px-2 py-1 {{ $p === $siswaPage->currentPage() ? 'bg-sky-500 font-semibold text-white' : 'hover:bg-slate-100' }}">{{ $p }}</a>
                        @endforeach

                        @if($siswaPage->hasMorePages())
                            <a href="{{ $siswaPage->nextPageUrl() }}" class="rounded-md px-2 py-1 hover:bg-slate-100">›</a>
                        @else
                            <span class="rounded-md px-2 py-1 text-slate-300">›</span>
                        @endif
                    </nav>
                </div>
            </aside>

            {{-- PRATINJAU --}}
            <section class="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div>
                        <p class="text-xs text-slate-500">
                            Pratinjau rapor · <strong class="text-slate-700">Halaman {{ $halamanAktif }} dari {{ $halamanTotal }}</strong>
                            @if($siswaAktif) · {{ $siswaAktif->nama }} @endif
                        </p>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                                <a href="{{ request()->fullUrlWithQuery(['periode' => 'sts', 'halaman' => 1]) }}"
                                   class="rounded-md px-3 py-1 text-xs {{ $periodeAktif === 'sts' ? 'bg-white font-semibold shadow-sm' : 'text-slate-500' }}">Rapor STS</a>
                                <a href="{{ request()->fullUrlWithQuery(['periode' => 'semester', 'halaman' => 1]) }}"
                                   class="rounded-md px-3 py-1 text-xs {{ $periodeAktif === 'semester' ? 'bg-white font-semibold shadow-sm' : 'text-slate-500' }}">Rapor Semester</a>
                            </div>

                            @if($periodeAktif === 'semester' && $halamanTotal > 1)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ $halamanTotal }} halaman</span>
                                <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                                    @for($h = 1; $h <= $halamanTotal; $h++)
                                        <a href="{{ request()->fullUrlWithQuery(['halaman' => $h]) }}"
                                           class="rounded-md px-3 py-1 text-xs {{ $halamanAktif === $h ? 'bg-white font-semibold shadow-sm' : 'text-slate-500' }}">Halaman {{ $h }}</a>
                                    @endfor
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" data-print @disabled(! $dikunci) title="{{ $dikunci ? '' : 'Tersedia setelah nilai dikunci' }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-400">
                            🖨 Cetak
                        </button>

                        @if($siswaAktif)
                            <form method="POST" action="{{ route('cetak-rapor.siswa', $siswaAktif->id) }}">
                                @csrf
                                <button type="submit" @disabled(! $dikunci)
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-sky-500 px-3 py-2 text-sm font-semibold text-white hover:bg-sky-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                                    ↓ Unduh PDF
                                </button>
                            </form>
                        @else
                            <button type="button" disabled
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-200 px-3 py-2 text-sm font-semibold text-slate-400">
                                ↓ Unduh PDF
                            </button>
                        @endif
                    </div>
                </div>

                <div class="relative max-h-[78vh] overflow-auto bg-slate-100 p-6">
                    @if(! $rapor)
                        <div class="flex flex-col items-center justify-center py-24 text-center text-slate-500">
                            <p class="text-sm">Pilih siswa di sebelah kiri untuk melihat pratinjau rapor.</p>
                        </div>
                    @else
                        <article id="cr-paper" class="relative mx-auto max-w-[560px] overflow-hidden bg-white p-8 shadow-md">
                            {{-- Kop --}}
                            <div class="flex items-center gap-4">
                                <div class="grid size-12 shrink-0 place-items-center rounded-lg border-2 border-slate-800 text-lg font-bold">S</div>
                                <div class="flex-1 text-center">
                                    <p class="text-[8px] uppercase tracking-wide text-slate-600">Pemerintah Provinsi Jawa Timur · Dinas Pendidikan</p>
                                    <p class="text-sm font-bold">{{ $rapor['sekolah'] }}</p>
                                    <p class="text-[8px] text-slate-600">{{ $rapor['alamat'] }}</p>
                                </div>
                                <div class="size-12 shrink-0"></div>
                            </div>
                            <hr class="my-3 border-slate-800">

                            {{-- Judul --}}
                            <div class="text-center">
                                <p class="text-[11px] font-bold tracking-wide">LAPORAN HASIL BELAJAR — {{ $rapor['judul'] }}</p>
                                <p class="text-[9px] text-slate-600">Tahun Ajaran {{ $rapor['tahun_ajaran'] }}</p>
                            </div>

                            {{-- Identitas --}}
                            <div class="mt-4 grid grid-cols-2 gap-x-6 text-[9px]">
                                <dl class="grid grid-cols-[72px_1fr] gap-y-0.5">
                                    <dt>Nama Peserta Didik</dt><dd>: <strong>{{ $rapor['nama'] }}</strong></dd>
                                    <dt>NISN</dt><dd>: {{ $rapor['nisn'] }}</dd>
                                </dl>
                                <dl class="grid grid-cols-[72px_1fr] gap-y-0.5">
                                    <dt>Kelas</dt><dd>: {{ $rapor['kelas'] }}</dd>
                                    <dt>{{ $rapor['jenis'] === 'sts' ? 'Fase' : 'Semester' }}</dt><dd>: {{ $rapor['fase'] }}</dd>
                                </dl>
                            </div>

                            {{-- Tabel nilai --}}
                            <div class="mt-4 overflow-hidden">
                                @if($rapor['jenis'] === 'sts')
                                    <table class="w-full border-collapse text-[9px]">
                                        <thead>
                                            <tr class="bg-slate-50">
                                                <th class="border border-slate-400 px-1 py-1 text-center">No</th>
                                                <th class="border border-slate-400 px-1 py-1 text-left">Mata pelajaran</th>
                                                @foreach($rapor['kolom_tp'] as $kolom)
                                                    <th class="border border-slate-400 px-1 py-1 text-center">{{ $kolom }}</th>
                                                @endforeach
                                                <th class="border border-slate-400 px-1 py-1 text-center">Nilai akhir</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($halamanIni as $i => $m)
                                                <tr>
                                                    <td class="border border-slate-400 px-1 py-1 text-center">{{ $i + 1 }}</td>
                                                    <td class="border border-slate-400 px-1 py-1">{{ $m['nama'] }}</td>
                                                    @foreach($m['tp'] as $n)
                                                        <td class="border border-slate-400 px-1 py-1 text-center">{{ $n }}</td>
                                                    @endforeach
                                                    <td class="border border-slate-400 px-1 py-1 text-center font-bold">{{ $m['nilai_akhir'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <table class="w-full border-collapse text-[9px]">
                                        <thead>
                                            <tr class="bg-slate-50">
                                                <th class="border border-slate-400 px-1 py-1 text-center">No</th>
                                                <th class="border border-slate-400 px-1 py-1 text-left">Mata Pelajaran</th>
                                                <th class="border border-slate-400 px-1 py-1 text-center">Nilai Akhir</th>
                                                <th class="border border-slate-400 px-1 py-1 text-left">Capaian Kompetensi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($halamanIni as $i => $m)
                                                <tr class="align-top">
                                                    <td class="border border-slate-400 px-1 py-1 text-center">{{ $i + 1 }}</td>
                                                    <td class="border border-slate-400 px-1 py-1">{{ $m['nama'] }}</td>
                                                    <td class="border border-slate-400 px-1 py-1 text-center font-bold">{{ $m['nilai_akhir'] }}</td>
                                                    <td class="border border-slate-400 px-1 py-1 leading-snug">{{ $m['capaian'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            {{-- Tanda tangan --}}
                            <div class="mt-8 grid grid-cols-2 text-[9px]">
                                <div class="text-center">
                                    <p>Mengetahui,</p>
                                    <p>Orang Tua/Wali</p>
                                    <div class="mx-auto mt-12 w-32 border-b border-slate-700"></div>
                                </div>
                                <div class="text-center">
                                    <p>Malang, {{ $rapor['tanggal_ttd'] }}</p>
                                    <p>Wali Kelas {{ $rapor['kelas'] }}</p>
                                    <div class="mt-12"></div>
                                    <p class="font-bold">{{ $rapor['wali_nama'] }}</p>
                                    <p>NIP {{ $rapor['wali_nip'] }}</p>
                                </div>
                            </div>

                            <p class="mt-6 text-right text-[8px] text-slate-500">Halaman {{ $halamanAktif }} dari {{ $halamanTotal }}</p>

                            @unless($dikunci)
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <span class="select-none text-6xl font-bold text-slate-300/60" style="transform: rotate(-30deg)">DRAFT</span>
                                </div>
                            @endunless
                        </article>
                    @endif
                </div>
            </section>
        </div>
    @endif

    {{-- NOTIFIKASI PROGRES / HASIL CETAK KELAS --}}
    @if($jobProses)
        <div data-toast class="fixed bottom-5 left-5 z-40 w-80 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
            <div class="flex items-center gap-2">
                <span class="size-4 animate-spin rounded-full border-2 border-sky-500 border-t-transparent"></span>
                <strong class="text-sm">Membuat PDF {{ $jobCetak['label'] }}</strong>
            </div>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-sky-500" style="width: {{ $jobPersen }}%"></div>
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>{{ $jobCetak['selesai'] }} / {{ $jobCetak['total'] }} siswa</span>
                <span>{{ $jobPersen }}%</span>
            </div>
            <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500">
                <span>Estimasi {{ $jobCetak['estimasi'] ?? '-' }}. Anda boleh tetap di halaman ini.</span>
                <button type="button" data-toast-close class="font-semibold text-sky-600">Batalkan</button>
            </div>
        </div>
    @elseif($jobCetak && $jobCetak['status'] === 'selesai')
        <div data-toast class="fixed bottom-5 right-5 z-40 w-80 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
            <div class="flex items-start gap-2.5">
                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-green-100 text-xs font-bold text-green-700">✓</span>
                <div class="flex-1">
                    <strong class="text-sm">{{ $jobCetak['total'] }} rapor siap diunduh</strong>
                    <p class="mt-0.5 text-xs text-slate-500">{{ $jobCetak['nama_file'] }} · {{ $jobCetak['ukuran'] }}</p>
                </div>
                <button type="button" data-toast-close class="text-slate-400 hover:text-slate-600">×</button>
            </div>
            <div class="mt-3 flex gap-2">
                <a href="{{ $jobCetak['zip_url'] }}" class="flex-1 rounded-lg bg-sky-500 px-3 py-2 text-center text-xs font-semibold text-white hover:bg-sky-600">Unduh ZIP</a>
                <a href="{{ $jobCetak['gabung_url'] }}" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-center text-xs font-semibold hover:bg-slate-50">Unduh satu PDF gabungan</a>
            </div>
        </div>
    @endif
</div>
@endsection