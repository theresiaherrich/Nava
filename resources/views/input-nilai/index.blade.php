@extends('layouts.app')

@section('title', 'Verifikasi Nilai')

@vite(['resources/js/verifikasi-nilai.js'])

@section('content')
@php
    $daftarMapel   = $daftarMapel   ?? collect();
    $mapelAktif    = $mapelAktif    ?? null;
    $kolomTp       = $kolomTp       ?? [];
    $siswa         = $siswa         ?? collect();
    $statistik     = $statistik     ?? ['kelengkapan' => 0, 'rata_rata' => '-', 'rentang' => '-', 'perlu_diperiksa' => 0, 'jumlah_terisi' => 0, 'jumlah_siswa' => 0];
    $riwayat       = $riwayat       ?? collect();
    $periodeAktif  = $periodeAktif  ?? '-';
    $periodeList   = $periodeList   ?? [];
    $kelas         = $kelas         ?? '-';
    $kelasTerkunci = $kelasTerkunci ?? false;
    $kunciAt       = $kunciAt       ?? '-';

    $statusMap = [
        'belum_dikirim' => ['Belum dikirim',       'bg-slate-100 text-slate-500'],
        'menunggu'      => ['Menunggu verifikasi', 'bg-sky-100 text-sky-700'],
        'dikirim_ulang' => ['Dikirim ulang',       'bg-purple-100 text-purple-700'],
        'disetujui'     => ['Disetujui',           'bg-green-100 text-green-700'],
        'terkunci'      => ['🔒 Terkunci',          'bg-green-100 text-green-700'],
        'diisi'         => ['Sedang diisi',        'bg-orange-100 text-orange-700'],
    ];

    $bisaAksi      = $mapelAktif && in_array($mapelAktif->status, ['menunggu', 'dikirim_ulang']);
    $jumlahSelesai = collect($daftarMapel)->whereIn('status', ['disetujui', 'terkunci'])->count();
    $totalMapel    = collect($daftarMapel)->count();
    $persenSelesai = $totalMapel ? round(($jumlahSelesai / $totalMapel) * 100) : 0;
@endphp

<div class="min-h-screen bg-slate-50 p-6 font-sans text-slate-800">

    {{-- HEADER --}}
    <header class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700">SMA Negeri 1 Kota Malang · Wali Kelas</p>
            <h1 class="mt-1 text-2xl font-bold">Verifikasi Nilai {{ $kelas }}</h1>
            <p class="mt-1 text-sm text-slate-500">Periksa kelengkapan dan kewajaran nilai sebelum disetujui untuk rapor.</p>
        </div>

        <div class="relative">
            <button type="button" data-period-toggle
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm hover:bg-slate-50">
                <span>Periode: {{ $periodeAktif }}</span>
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div data-period-menu class="absolute right-0 top-full z-20 mt-1.5 hidden w-64 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                @forelse($periodeList as $periode)
                    <div class="flex flex-col rounded-lg px-2.5 py-2 {{ $periode['aktif'] ? 'bg-sky-100' : '' }} {{ $periode['terkunci'] ? 'text-slate-400' : '' }}">
                        <span class="text-sm">{{ $periode['nama'] }}</span>
                        @if($periode['terkunci'])
                            <small class="text-xs text-slate-400">Belum dibuka</small>
                        @elseif(!empty($periode['sampai']))
                            <small class="text-xs text-slate-500">Dibuka sampai {{ $periode['sampai'] }}</small>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg bg-sky-100 px-2.5 py-2 text-sm">{{ $periodeAktif }}</div>
                @endforelse
            </div>
        </div>
    </header>

    {{-- BANNER KELAS TERKUNCI --}}
    @if($kelasTerkunci)
        <div class="mb-5 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3">
            <span class="mt-0.5">🔒</span>
            <div class="text-sm">
                <strong class="block text-green-800">Nilai {{ $kelas }} telah dikunci Kurikulum pada {{ $kunciAt }}</strong>
                <span class="text-green-700">Nilai tidak dapat disetujui atau dikembalikan lagi. Rapor STS siap dicetak.</span>
            </div>
        </div>
    @endif

    <div class="grid items-start gap-5 lg:grid-cols-[300px_1fr]">

        {{-- SIDEBAR --}}
        <aside class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold">Mata pelajaran</h2>
                <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                    <button type="button" data-filter="menunggu"
                            class="rounded-md px-2.5 py-1 text-xs text-slate-500">Menunggu</button>
                    <button type="button" data-filter="semua"
                            class="rounded-md px-2.5 py-1 text-xs">Semua</button>
                </div>
            </div>

            <p class="mb-1.5 text-xs text-slate-500">{{ $jumlahSelesai }} dari {{ $totalMapel }} disetujui</p>
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-green-500 transition-all" style="width: {{ $persenSelesai }}%"></div>
            </div>

            <ul class="mt-4 flex flex-col gap-1.5">
                @forelse($daftarMapel as $mapel)
                    @php
                        [$label, $badge] = $statusMap[$mapel->status] ?? ['-', 'bg-slate-100 text-slate-500'];
                        $aktif = $mapelAktif && $mapelAktif->id === $mapel->id;
                        $perlu = in_array($mapel->status, ['menunggu', 'dikirim_ulang']);
                        $qsMapel = ['mapel' => $mapel->id];
                    @endphp
                    <li data-mapel-item data-perlu="{{ $perlu ? '1' : '0' }}">
                        <a href="{{ route('verifikasi-nilai.show', $qsMapel) }}"
                           class="flex items-center gap-2.5 rounded-lg border p-2.5 no-underline text-inherit
                                  {{ $aktif ? 'border-sky-200 bg-sky-50' : 'border-transparent hover:bg-slate-50' }}
                                  {{ $mapel->status === 'diisi' ? 'opacity-50' : '' }}">
                            <span class="grid size-7 shrink-0 place-items-center rounded-md bg-sky-100 text-sm font-bold text-sky-700">
                                {{ strtoupper(substr($mapel->nama, 0, 1)) }}
                            </span>
                            <span class="flex min-w-0 flex-1 flex-col">
                                <strong class="text-[13px]">{{ $mapel->nama }}</strong>
                                <small class="truncate text-[11px] text-slate-500">{{ $mapel->guru }}</small>
                            </span>
                            <span class="whitespace-nowrap rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $badge }}">{{ $label }}</span>
                        </a>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-slate-500">Belum ada mata pelajaran.</li>
                @endforelse
            </ul>
        </aside>

        {{-- MAIN --}}
        <main class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">

            @if(!$mapelAktif)
                <div class="px-6 py-20 text-center text-slate-500">
                    <p>Pilih mata pelajaran di sebelah kiri untuk mulai verifikasi.</p>
                </div>

            @elseif($mapelAktif->status === 'belum_dikirim')
                <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                    <span class="mb-4 grid size-20 place-items-center rounded-full bg-sky-50 text-sky-500">
                        <svg class="size-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/>
                        </svg>
                    </span>
                    <h3 class="font-semibold">Belum ada nilai yang dikirim</h3>
                    <p class="mt-1 text-sm text-slate-500">Nilai akan muncul di sini setelah guru mengirimkannya.</p>
                </div>

            @else
                <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $mapelAktif->nama }}</h2>
                        <p class="text-sm text-slate-500">{{ $mapelAktif->guru }} · {{ $mapelAktif->jumlah_tp ?? count($kolomTp) }} TP · Dikirim {{ $mapelAktif->dikirim_at ?? '-' }}</p>
                    </div>
                    @php [$lbl, $bdg] = $statusMap[$mapelAktif->status] ?? ['-', 'bg-slate-100 text-slate-500']; @endphp
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $bdg }}">{{ $lbl }}</span>
                </div>

                {{-- STATISTIK --}}
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5 rounded-lg border border-slate-200 p-3">
                        <span class="text-xs text-slate-500">Kelengkapan</span>
                        <span class="text-xl font-bold">{{ $statistik['kelengkapan'] }}%</span>
                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-green-500" style="width: {{ $statistik['kelengkapan'] }}%"></div>
                        </div>
                        <small class="text-xs text-slate-500">{{ $statistik['jumlah_terisi'] }}/{{ $statistik['jumlah_siswa'] }} siswa</small>
                    </div>
                    <div class="flex flex-col gap-1.5 rounded-lg border border-slate-200 p-3">
                        <span class="text-xs text-slate-500">Rata-rata kelas</span>
                        <span class="text-xl font-bold">{{ $statistik['rata_rata'] }}</span>
                        <small class="text-xs text-slate-500">Rentang {{ $statistik['rentang'] }}</small>
                    </div>
                    <div class="flex flex-col gap-1.5 rounded-lg border border-slate-200 p-3">
                        <span class="text-xs text-slate-500">Perlu diperiksa</span>
                        <span class="text-xl font-bold {{ $statistik['perlu_diperiksa'] > 0 ? 'text-red-600' : 'text-slate-800' }}">{{ $statistik['perlu_diperiksa'] }} nilai</span>
                        <small class="text-xs text-slate-500">{{ $statistik['perlu_diperiksa'] > 0 ? 'Nilai di luar rentang wajar' : 'Sudah dikoreksi guru' }}</small>
                    </div>
                </div>

                <p class="my-2.5 text-sm text-slate-500">Menampilkan {{ count($siswa) }} dari {{ $statistik['jumlah_siswa'] }} siswa</p>

                {{-- TABEL --}}
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full min-w-[640px] border-collapse text-center text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold text-slate-500">
                                <th class="px-3 py-2.5 text-left">Nama siswa</th>
                                @foreach($kolomTp as $tp)
                                    <th class="px-3 py-2.5">{{ $tp }}</th>
                                @endforeach
                                <th class="bg-amber-100 px-3 py-2.5">TP6 · SAS</th>
                                <th class="bg-sky-100 px-3 py-2.5">Nilai rapor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($siswa as $s)
                                <tr>
                                    <td class="px-3 py-2.5 text-left">
                                        <div class="font-semibold">{{ $s->nama }}</div>
                                        <small class="text-xs text-slate-500">{{ $s->nisn }}</small>
                                    </td>
                                    @foreach($s->nilai_tp as $nilai)
                                        <td class="px-3 py-2.5">
                                            @if(!empty($nilai['warn']))
                                                <span class="group relative inline-block min-w-7 cursor-help rounded-md border border-red-300 bg-red-100 px-1.5 py-0.5 font-bold text-red-600">
                                                    {{ $nilai['nilai'] }}
                                                    <span class="pointer-events-none absolute bottom-full left-1/2 mb-1.5 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-[11px] font-normal text-white group-hover:block">{{ $nilai['tooltip'] ?? 'Di luar rentang wajar' }}</span>
                                                </span>
                                            @else
                                                {{ $nilai['nilai'] ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="bg-amber-50 px-3 py-2.5">{{ $s->sas ?? '-' }}</td>
                                    <td class="bg-sky-50 px-3 py-2.5 font-bold">{{ $s->nilai_rapor ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($kolomTp) + 3 }}" class="p-6 text-slate-500">Belum ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($mapelAktif->status === 'terkunci')
                    {{-- FOOTER NILAI FINAL --}}
                    <div class="mt-4 flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2.5 text-sm text-slate-600">
                        <span>🔒</span>
                        <span>Nilai final · dikunci Waka Kurikulum {{ $kunciAt }}</span>
                    </div>
                @else
                    {{-- CATATAN --}}
                    <div class="mt-4 flex flex-col gap-1.5">
                        <label for="catatan-guru" class="text-xs text-slate-500">Catatan untuk guru (opsional)</label>
                        <textarea id="catatan-guru" rows="2" placeholder="Tulis catatan..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100"></textarea>
                    </div>

                    {{-- AKSI --}}
                    <div class="mt-4 flex justify-end gap-2.5">
                        <button type="button" data-modal-open="modal-revisi" @disabled(! $bisaAksi)
                                class="inline-flex items-center gap-1.5 rounded-lg border border-orange-300 bg-white px-4 py-2 text-sm font-semibold text-orange-600 hover:bg-orange-50 disabled:cursor-not-allowed disabled:opacity-50">
                            ↺ Minta revisi
                        </button>
                        <button type="button" data-modal-open="modal-setujui" @disabled(! $bisaAksi)
                                class="inline-flex items-center gap-1.5 rounded-lg bg-sky-500 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-600 disabled:cursor-not-allowed disabled:opacity-50">
                            ✓ Setujui nilai
                        </button>
                    </div>
                @endif

                {{-- RIWAYAT --}}
                @if(count($riwayat))
                    <section class="mt-6 border-t border-slate-200 pt-4">
                        <h3 class="mb-3 text-sm font-semibold">Riwayat verifikasi</h3>
                        <ol class="ml-1 border-l-2 border-slate-200">
                            @foreach($riwayat as $r)
                                @php
                                    $dot = match($r['jenis']) {
                                        'disetujui' => 'bg-green-500',
                                        'revisi'    => 'bg-orange-500',
                                        default     => 'bg-sky-500',
                                    };
                                @endphp
                                <li class="relative flex flex-col gap-0.5 pb-4 pl-4">
                                    <span class="absolute -left-[6px] top-1 size-2.5 rounded-full {{ $dot }}"></span>
                                    <strong class="text-sm">{{ $r['judul'] }}</strong>
                                    <small class="text-xs text-slate-500">{{ $r['oleh'] }} · {{ $r['waktu'] }}</small>
                                    @if(!empty($r['catatan']))
                                        <blockquote class="mt-1 rounded-md bg-slate-50 px-2.5 py-1.5 text-xs">"{{ $r['catatan'] }}"</blockquote>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                        <p class="text-xs text-slate-500">Riwayat tidak dapat dihapus atau diubah.</p>
                    </section>
                @endif
            @endif
        </main>
    </div>
</div>

{{-- MODAL: MINTA REVISI --}}
@if($bisaAksi)
<div id="modal-revisi" data-modal aria-hidden="true"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
        <header class="flex items-center gap-2.5">
            <span class="grid size-7 place-items-center rounded-full bg-orange-100 font-bold text-orange-600">↺</span>
            <h3 class="flex-1 text-base font-semibold">Kembalikan nilai untuk revisi</h3>
            <button type="button" data-modal-close aria-label="Tutup" class="text-xl text-slate-400 hover:text-slate-600">×</button>
        </header>

        <div class="mt-3 grid grid-cols-2 gap-2 rounded-lg bg-slate-50 p-3 text-xs">
            <div class="flex flex-col"><span class="text-slate-500">Mata pelajaran</span><strong>{{ $mapelAktif->nama }}</strong></div>
            <div class="flex flex-col"><span class="text-slate-500">Kelas</span><strong>{{ $kelas }}</strong></div>
            <div class="flex flex-col"><span class="text-slate-500">Guru</span><strong>{{ $mapelAktif->guru }}</strong></div>
            <div class="flex flex-col"><span class="text-slate-500">Rapor</span><strong>{{ $periodeAktif }}</strong></div>
        </div>

        <form id="form-revisi" method="POST" action="{{ route('verifikasi-nilai.revisi', $mapelAktif->id) }}" novalidate class="mt-3 flex flex-col gap-2">
            @csrf

            <label for="alasan-revisi" class="text-xs font-semibold">Alasan revisi <span class="text-red-600">*</span></label>
            <textarea id="alasan-revisi" name="alasan_revisi" rows="3" maxlength="500" required data-revisi-alasan
                      placeholder="Jelaskan nilai yang perlu dicek guru..."
                      class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100"></textarea>
            <div class="flex justify-between text-xs">
                <span data-revisi-error class="hidden text-red-600">Alasan revisi wajib diisi.</span>
                <span data-revisi-count class="ml-auto text-slate-500">0/500</span>
            </div>

            <label class="text-xs font-semibold">Tandai siswa (opsional)</label>
            <input type="search" placeholder="Cari siswa..." data-siswa-search
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100">

            <div class="flex max-h-48 flex-col gap-1.5 overflow-y-auto">
                @foreach($siswa as $s)
                    <label data-siswa-option data-nama="{{ $s->nama }}"
                           class="flex cursor-pointer items-center gap-2.5 rounded-lg border border-slate-200 px-2.5 py-2 has-[:checked]:border-sky-200 has-[:checked]:bg-sky-50">
                        <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="size-4">
                        <span class="flex flex-col">
                            <strong class="text-sm">{{ $s->nama }}</strong>
                        </span>
                    </label>
                @endforeach
            </div>

            <p class="text-xs text-slate-500">ⓘ Guru akan menerima catatan ini dan dapat mengedit nilai kembali.</p>

            <div class="mt-1 flex justify-end gap-2">
                <button type="button" data-modal-close class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" data-revisi-submit disabled
                        class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50">
                    ↺ Kembalikan ke guru
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL: SETUJUI --}}
<div id="modal-setujui" data-modal aria-hidden="true"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/45 p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl">
        <header class="flex items-center gap-2.5">
            <span class="grid size-7 place-items-center rounded-full bg-green-100 font-bold text-green-600">✓</span>
            <h3 class="flex-1 text-base font-semibold">Setujui nilai {{ $mapelAktif->nama ?? '' }} {{ $kelas }}?</h3>
            <button type="button" data-modal-close aria-label="Tutup" class="text-xl text-slate-400 hover:text-slate-600">×</button>
        </header>

        <div class="mt-3 flex flex-col gap-0.5 rounded-lg border border-green-200 bg-green-50 px-3 py-2.5 text-sm">
            <strong>✓ Kelengkapan {{ $statistik['jumlah_terisi'] }}/{{ $statistik['jumlah_siswa'] }} siswa · {{ $mapelAktif->jumlah_tp ?? count($kolomTp) }} TP</strong>
            <small class="text-green-800">Rata-rata kelas {{ $statistik['rata_rata'] }}</small>
            <small class="text-green-800">Dikirim {{ $mapelAktif->dikirim_at ?? '-' }}</small>
        </div>

        <p class="mt-3 text-xs text-slate-500">Nilai diteruskan ke Waka Kurikulum untuk dikunci. Guru tidak dapat mengubah nilai setelah disetujui.</p>

        <form method="POST" action="{{ route('verifikasi-nilai.setujui', $mapelAktif->id) }}" class="mt-3 flex flex-col gap-2">
            @csrf
            <label for="catatan-setujui" class="text-xs font-semibold">Catatan (opsional)</label>
            <input id="catatan-setujui" type="text" name="catatan" placeholder="Contoh: Sudah dicek bersama guru."
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none focus:ring-3 focus:ring-sky-100">

            <div class="mt-1 flex justify-end gap-2">
                <button type="button" data-modal-close class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">✓ Setujui nilai</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection