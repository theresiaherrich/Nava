@extends('layouts.app')

@section('title', 'Pelengkap Rapor')

@push('styles')
    @vite(['resources/js/wali-pelengkap.js'])
@endpush

@section('content')
<div class="px-7 pt-7" x-data="pelengkapPage(JSON.parse(document.getElementById('data-pelengkap').textContent))">
    <x-ui.json-data id="data-pelengkap" :data="['siswa' => $siswa, 'state' => $state]" />

    <x-ui.page-header :title="'Pelengkap Rapor — '.$kelas" role="Wali Kelas" subtitle="Lengkapi catatan wali kelas dan periksa kehadiran serta ekstrakurikuler sebelum rapor dicetak.">
        <x-slot:actions>
            @if ($state === 'terkunci')
                <x-ui.badge tone="success" icon="lock" class="!text-[13px]">{{ count($siswa) }}/{{ count($siswa) }} · Terkunci</x-ui.badge>
            @else
                <span class="rounded-md px-2 py-0.5 text-xs font-semibold" :class="jumlahBelum ? 'bg-amber-100 text-amber-700' : 'bg-success-soft text-success'"
                      x-text="`${jumlahLengkap}/${siswa.length} lengkap`"></span>
                <span class="flex items-center gap-1.5 text-[13px] font-medium" :class="menyimpan ? 'text-muted' : 'text-success'" aria-live="polite">
                    <x-ui.icon name="check-circle" :size="14" x-show="!menyimpan" />
                    <span x-text="menyimpan ? 'Menyimpan…' : `Tersimpan otomatis · ${tersimpan}`"></span>
                </span>
                <x-ui.button size="lg" x-on:click="simpan()">Simpan</x-ui.button>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Filter --}}
    <x-ui.card class="mb-4 flex flex-wrap items-center gap-3 !py-3">
        <span class="text-[13px] text-muted">Periode</span>
        <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="periode" label="Periode rapor" />
        <x-ui.filter-select label="Status" :options="['Semua siswa', 'Belum lengkap', 'Lengkap']" model="status" />
        <x-ui.search-input placeholder="Cari nama atau NIS…" x-model="cari" class="w-full sm:w-[220px]" />
        <a href="{{ route('cetak-rapor.index', ['pratinjau' => 1]) }}" class="ml-auto inline-flex items-center gap-1 text-[13px] font-semibold text-link hover:underline">
            Lihat pratinjau rapor <x-ui.icon name="arrow-right" :size="14" />
        </a>
    </x-ui.card>

    @if ($state === 'terkunci')
        <x-ui.alert tone="success" icon="lock" title="Pelengkap rapor STS terkunci" class="mb-4">Terkunci bersama nilai pada 26 Okt 2026. Catatan wali kelas tidak dapat diubah lagi.</x-ui.alert>
    @elseif ($state === 'belum-a1')
        <x-ui.alert tone="warning" title="Data kehadiran belum tersedia" class="mb-4">Rekap kehadiran resmi dari Presensi (A1) belum tersedia untuk periode ini. Catatan wali kelas tetap dapat diisi.</x-ui.alert>
    @else
        <x-ui.alert tone="amber" title="Data dari modul lain" class="mb-4">Kehadiran diambil otomatis dari Presensi (A1) dan predikat ekstrakurikuler dari Kesiswaan. Kolom abu-abu tidak dapat diubah di halaman ini.</x-ui.alert>
    @endif

    {{-- Ringkasan --}}
    <div class="mb-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card>
            <p class="text-[13px] text-muted">Catatan wali kelas</p>
            <p class="mt-2 text-[28px] leading-none font-bold text-ink"><span x-text="jumlahLengkap"></span><span class="text-base font-semibold text-muted" x-text="` / ${siswa.length}`"></span></p>
            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Kelengkapan catatan" :aria-valuenow="jumlahLengkap" aria-valuemin="0" :aria-valuemax="siswa.length">
                <div class="h-full rounded-full transition-all" :class="jumlahBelum ? 'bg-primary' : 'bg-success-strong'" :style="`width: ${jumlahLengkap / siswa.length * 100}%`"></div>
            </div>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[13px] text-muted">Kehadiran (Presensi A1)</p>
            @if ($state === 'belum-a1')
                <x-ui.badge tone="warning" class="mt-2 !text-[13px]">Belum tersedia</x-ui.badge>
                <p class="mt-2 text-xs text-muted">Menunggu rekap resmi dari Presensi (A1)</p>
            @else
                <x-ui.badge tone="success" class="mt-2 !text-[13px]">Tersedia</x-ui.badge>
                <p class="mt-2 text-xs text-muted">Diperbarui {{ $diperbarui }}</p>
            @endif
        </x-ui.card>
        <x-ui.card>
            <p class="text-[13px] text-muted">Predikat ekstrakurikuler</p>
            <p class="mt-2 text-[28px] leading-none font-bold text-ink"><span x-text="ekskulDinilai"></span><span class="text-base font-semibold text-muted" x-text="` / ${siswa.length}`"></span></p>
            <p class="mt-2 text-xs text-muted" x-text="siswa.length - ekskulDinilai ? `${siswa.length - ekskulDinilai} siswa belum dinilai Kesiswaan` : 'Semua siswa sudah dinilai'"></p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-[13px] font-bold text-ink">Templat catatan cepat</p>
            <div class="mt-2 flex flex-col items-start gap-1.5">
                @foreach ($templat as $t)
                    <button type="button" x-on:click="sisipkan(@js($t))" :disabled="terkunci"
                            class="rounded-md border border-primary-soft bg-primary-faint px-2 py-0.5 text-xs font-semibold text-link hover:bg-primary-soft disabled:opacity-50 disabled:hover:bg-primary-faint">+ {{ $t }}</button>
                @endforeach
            </div>
            <p class="mt-2 text-[11px] text-muted">Klik untuk menyisipkan, lalu sesuaikan.</p>
        </x-ui.card>
    </div>

    {{-- Daftar peserta didik --}}
    <x-ui.card :padding="false" class="overflow-hidden" id="daftar-peserta">
        <div class="flex flex-wrap items-end justify-between gap-3 px-5 pt-4 pb-3">
            <div>
                <h2 class="text-[15px] font-bold text-ink">Daftar Peserta Didik</h2>
                <p class="text-xs text-muted" x-text="`${siswa.length} siswa · Kelas {{ $kelas }} · menampilkan ${baris.length}`"></p>
            </div>
            <x-ui.pagination page="halaman" pages="jumlahHalaman" x-show="jumlahHalaman > 1" />
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px] text-left text-[13px]">
                <thead class="text-xs font-semibold text-ink-2">
                    <tr>
                        <th rowspan="2" class="w-10 px-5 pt-3 pb-2 align-bottom">No</th>
                        <th rowspan="2" class="w-[86px] px-2 pt-3 pb-2 align-bottom">NIS</th>
                        <th rowspan="2" class="px-2 pt-3 pb-2 align-bottom">Nama Siswa</th>
                        <th colspan="3" class="bg-surface-2 px-2 pt-2.5 text-center text-[11px] tracking-wide text-muted uppercase"><span class="inline-flex items-center gap-1"><x-ui.icon name="lock" :size="11" />Kehadiran · <span class="normal-case">dari A1</span></span></th>
                        <th rowspan="2" class="w-[170px] bg-surface-2 px-3 pt-3 pb-2 align-bottom"><span class="inline-flex items-center gap-1"><x-ui.icon name="lock" :size="11" />Ekstrakurikuler</span></th>
                        <th rowspan="2" class="w-[400px] px-2.5 pt-3 pb-2 align-bottom">Catatan Wali Kelas</th>
                        <th rowspan="2" class="w-[130px] px-3 pt-3 pb-2 align-bottom">Status</th>
                    </tr>
                    <tr class="bg-surface-2">
                        <th class="w-14 px-2 pb-2 text-center">Sakit</th><th class="w-14 px-2 pb-2 text-center">Izin</th><th class="w-14 px-2 pb-2 text-center">Alpa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="s in baris" :key="s.nis">
                        <tr>
                            <td class="px-5 text-muted" x-text="nomor(s)"></td>
                            <td class="px-2 text-muted" x-text="s.nis"></td>
                            <td class="px-2 font-bold text-ink" x-text="s.nama"></td>
                            <template x-for="k in ['sakit', 'izin', 'alpa']" :key="k">
                                <td class="bg-surface-2 px-2 text-center text-ink-2" x-text="s.kehadiran ? s.kehadiran[k] : '—'"></td>
                            </template>
                            <td class="bg-surface-2 px-3">
                                <div class="flex flex-col items-start gap-1">
                                    <template x-for="e in s.ekskul" :key="e[0]">
                                        <span class="rounded-md bg-surface px-2 py-0.5 text-xs text-ink-2 ring-1 ring-line ring-inset"><span x-text="e[0]"></span> · <b class="text-ink" x-text="e[1]"></b></span>
                                    </template>
                                    <span x-show="!s.ekskul.length" class="inline-flex items-center gap-1 text-xs font-semibold text-warning"><x-ui.icon name="alert-circle" :size="12" />Belum dinilai</span>
                                </div>
                            </td>
                            <td class="group px-2.5 pt-2 pb-1">
                                <textarea :id="`catatan-${s.nis}`" rows="2" :maxlength="maks" :readonly="terkunci" placeholder="Tulis catatan untuk siswa…"
                                          :aria-label="`Catatan wali kelas untuk ${s.nama}`" :value="isi[s.nis]"
                                          x-on:input="ubah(s.nis, $event.target.value)" x-on:focus="terakhirFokus = s.nis"
                                          class="block w-full resize-none rounded-control border px-2.5 py-1.5 text-[13px] leading-snug text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none"
                                          :class="terkunci ? 'border-line bg-surface-2 text-ink-2' : (lengkap(s) ? 'border-line' : 'border-orange-300')"></textarea>
                                <p class="mt-0.5 text-right text-[11px] text-subtle group-focus-within:text-link" x-text="`${isi[s.nis].length}/${maks}`"></p>
                            </td>
                            <td class="px-3">
                                <span :class="$store.ref.badge(lengkap(s) ? 'success' : 'warning')" x-text="lengkap(s) ? 'Lengkap' : 'Belum lengkap'"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!baris.length">
                        <td colspan="9"><x-ui.empty-state icon="search" title="Tidak ada siswa yang cocok" description="Ubah kata kunci atau filter status." class="!py-10" /></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @if ($state !== 'terkunci')
        <div class="sticky bottom-0 z-20 -mx-7 mt-6 flex flex-wrap items-center gap-3 border-t border-line bg-surface px-7 py-3">
            <p class="flex items-center gap-1.5 text-[13px] text-ink-2" x-show="jumlahBelum">
                <x-ui.icon name="alert-circle" :size="14" class="text-warning-strong" />
                <span><strong class="text-warning" x-text="`${jumlahBelum} siswa belum lengkap.`"></strong> Rapor tetap bisa dicetak setelah nilai dikunci, namun kolom catatan akan kosong.</span>
            </p>
            <p class="flex items-center gap-1.5 text-[13px] font-medium text-success" x-show="!jumlahBelum"><x-ui.icon name="check-circle" :size="14" />Semua catatan wali kelas sudah lengkap.</p>
            <div class="ml-auto flex gap-2.5">
                <x-ui.button variant="secondary" x-show="jumlahBelum" x-on:click="tampilkanBelum()">Tampilkan yang belum lengkap</x-ui.button>
                <x-ui.button x-on:click="simpan()">Simpan</x-ui.button>
            </div>
        </div>
    @else
        <div class="h-10"></div>
    @endif
</div>
@endsection
