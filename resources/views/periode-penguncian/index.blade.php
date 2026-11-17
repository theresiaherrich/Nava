@extends('layouts.app')

@section('title', 'Periode & Penguncian')

@push('styles')
    @vite(['resources/js/kurikulum-periode.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10"
     x-data="periodePage(JSON.parse(document.getElementById('data-periode').textContent))">
    <x-ui.json-data id="data-periode" :data="['periode' => $periode, 'kelas' => $kelas, 'mapelKelas' => $mapelKelas, 'modal' => request('modal')]" />

    <x-ui.page-header title="Periode & Penguncian" role="Kurikulum" subtitle="Atur jadwal pengisian dan kunci nilai sebelum rapor diterbitkan." class="mb-3">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="plus" size="lg" x-on:click="tambahPeriode()">Tambah periode</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div role="tablist" aria-label="Semester" class="mb-5 flex gap-6 border-b border-line">
        @foreach (['ganjil' => 'Semester Ganjil 2026/2027', 'genap' => 'Semester Genap 2026/2027'] as $kode => $teks)
            <button type="button" role="tab" :aria-selected="semester === '{{ $kode }}'" x-on:click="semester = '{{ $kode }}'"
                    class="-mb-px border-b-2 px-0.5 pt-1 pb-2.5 text-sm"
                    :class="semester === '{{ $kode }}' ? 'border-primary font-semibold text-link' : 'border-transparent text-muted hover:text-ink'">{{ $teks }}</button>
        @endforeach
    </div>

    <div x-show="semester === 'genap'" x-cloak>
        <x-ui.card :padding="false">
            <x-ui.empty-state icon="calendar" title="Periode semester Genap belum dibuat"
                              description="Periode STS dan SAS dibuat otomatis saat semester Genap diaktifkan di menu Tahun Ajaran." />
        </x-ui.card>
    </div>

    <div x-show="semester === 'ganjil'" class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_401px]">
        {{-- STS --}}
        <x-ui.card :padding="false" class="self-start overflow-hidden">
            <div class="px-5 pt-5">
                <div class="flex flex-wrap items-start gap-3">
                    <span class="flex size-9 items-center justify-center rounded-control border border-success-soft bg-success-soft/50 text-success"><x-ui.icon name="calendar" :size="17" /></span>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-bold text-ink" x-text="periode.sts.judul"></h2>
                        <p class="text-xs text-muted" x-text="`${periode.sts.rentang} · ditutup dalam ${periode.sts.sisa_hari} hari`"></p>
                    </div>
                    <x-ui.badge tone="success">Dibuka</x-ui.badge>
                    <button type="button" x-on:click="ubahJadwal('sts')" class="text-[13px] font-semibold text-link hover:underline">Ubah jadwal</button>
                </div>
                <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-line" role="progressbar" :aria-valuenow="periode.sts.progres" aria-valuemin="0" aria-valuemax="100" aria-label="Waktu periode STS berjalan">
                    <div class="h-full rounded-full bg-success-strong" :style="`width: ${periode.sts.progres}%`"></div>
                </div>

                <div role="tablist" aria-label="Tingkat" class="mt-4 flex gap-6 border-b border-line">
                    @foreach (['X', 'XI', 'XII'] as $t)
                        <button type="button" role="tab" :aria-selected="tingkat === '{{ $t }}'" x-on:click="tingkat = '{{ $t }}'"
                                class="-mb-px border-b-2 px-0.5 pb-2.5 text-sm"
                                :class="tingkat === '{{ $t }}' ? 'border-primary font-semibold text-link' : 'border-transparent text-muted hover:text-ink'">Kelas {{ $t }}</button>
                    @endforeach
                </div>
                <div class="flex items-center justify-between pt-4 pb-3">
                    <h3 class="text-sm font-bold text-ink">Status per Kelas</h3>
                    <p class="text-xs text-muted">
                        <span x-text="`Menampilkan ${kelasTampil.length} dari ${kelasTingkat.length} kelas`"></span>
                        <button type="button" x-show="kelasTingkat.length > 6" x-on:click="semua = !semua" class="ml-1 font-semibold text-link hover:underline" x-text="semua ? 'Ringkas' : 'Tampilkan semua'"></button>
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr><th class="px-5 py-2.5">Kelas</th><th class="px-3 py-2.5">Wali Kelas</th><th class="px-3 py-2.5">Mapel disetujui</th><th class="px-3 py-2.5">Status</th><th class="px-5 py-2.5 text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="k in kelasTampil" :key="k.kelas">
                            <tr>
                                <td class="px-5 py-3 font-bold text-ink" x-text="k.kelas"></td>
                                <td class="px-3 py-3 text-ink-2" x-text="k.wali ?? '—'"></td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-1 w-14 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full" :class="k.disetujui === 16 ? 'bg-success-strong' : (k.disetujui >= 12 ? 'bg-primary' : 'bg-warning-strong')" :style="`width: ${k.disetujui / 16 * 100}%`"></div></div>
                                        <span class="text-xs text-ink-2" x-text="`${k.disetujui}/16`"></span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span x-show="k.status === 'siap'" :class="$store.ref.badge('success')">Siap dikunci</span>
                                    <span x-show="k.status === 'terkunci'" class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2 py-0.5 text-xs font-semibold text-success"><x-ui.icon name="lock" :size="12" :stroke="2.2" />Terkunci</span>
                                    <span x-show="k.status === 'menunggu'" :class="$store.ref.badge('info')">Menunggu verifikasi</span>
                                    <span x-show="k.status === 'terbuka'" :class="$store.ref.badge('neutral')">Terbuka</span>
                                    <span x-show="k.status === 'koreksi'">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-violet-soft px-2 py-0.5 text-xs font-semibold text-violet"><x-ui.icon name="unlock" :size="12" :stroke="2.2" />Dibuka untuk koreksi</span>
                                        <span class="mt-0.5 block text-xs text-muted" x-text="k.koreksi"></span>
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 text-right">
                                    <x-ui.button size="sm" icon="lock" x-show="k.status === 'siap'" x-on:click="mintaKunci([k.kelas])">Kunci</x-ui.button>
                                    <x-ui.button variant="secondary" size="sm" icon="unlock" x-show="k.status === 'terkunci'" x-on:click="bukaKoreksi(k)">Buka Koreksi</x-ui.button>
                                    <span x-show="k.status === 'menunggu' || k.status === 'terbuka'" class="group relative inline-block">
                                        <x-ui.button variant="soft" size="sm" icon="lock" disabled aria-describedby="tip-kunci">Kunci</x-ui.button>
                                        <span role="tooltip" class="pointer-events-none absolute right-0 bottom-full z-10 mb-1.5 hidden rounded-md bg-toast px-2.5 py-1.5 text-xs whitespace-nowrap text-white group-hover:block">Masih ada mapel belum disetujui</span>
                                    </span>
                                    <span x-show="k.status === 'koreksi'" class="text-xs text-muted">menunggu guru</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3.5">
                <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="lock" :size="13" />Penguncian tidak otomatis menerbitkan rapor.</p>
                <x-ui.button icon="lock" x-bind:disabled="!siap.length" x-on:click="mintaKunci(siap.map((k) => k.kelas))">
                    <span x-text="`Kunci semua yang siap (${siap.length} kelas)`"></span>
                </x-ui.button>
            </div>
        </x-ui.card>

        {{-- SAS --}}
        <x-ui.card class="self-start">
            <div class="flex items-start gap-3">
                <span class="flex size-9 items-center justify-center rounded-control border border-line bg-surface-2 text-ink-2"><x-ui.icon name="calendar" :size="17" /></span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[15px] font-bold text-ink" x-text="periode.sas.judul"></h2>
                    <p class="text-xs text-muted" x-text="periode.sas.rentang"></p>
                </div>
                <x-ui.badge>Terjadwal</x-ui.badge>
            </div>
            <div class="mt-4 flex items-center gap-4 rounded-card border border-line bg-surface-2 p-4">
                <span class="flex size-14 flex-col items-center justify-center rounded-control border border-line bg-surface">
                    <span class="text-[10px] font-bold text-danger" x-text="periode.sas.bulan"></span>
                    <span class="text-xl leading-none font-bold text-ink" x-text="periode.sas.tanggal"></span>
                </span>
                <div>
                    <p class="text-sm font-bold text-ink" x-text="`Dibuka dalam ${periode.sas.dibuka_dalam} hari`"></p>
                    <p class="text-xs text-muted">Guru dapat mengisi nilai SAS mulai tanggal ini</p>
                </div>
            </div>
            <x-ui.button variant="secondary" icon="calendar" size="lg" class="mt-4" x-on:click="ubahJadwal('sas')">Ubah jadwal</x-ui.button>
        </x-ui.card>
    </div>

    {{-- 5.02 Ubah jadwal --}}
    <x-ui.modal name="jadwal" width="max-w-[480px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="`Ubah jadwal ${periode[jadwal.jenis].singkat}`"></h2>
        <p class="mt-1 text-[13px] text-muted">Perubahan jadwal langsung berlaku untuk seluruh guru.</p>
        <form class="mt-4 space-y-4" x-on:submit.prevent="simpanJadwal()" novalidate>
            <div class="grid grid-cols-2 gap-3.5">
                <x-ui.field label="Tanggal buka" for="jd-buka" required error="jadwal.error.buka"><x-ui.input id="jd-buka" type="date" x-model="jadwal.buka" error="jadwal.error.buka" /></x-ui.field>
                <x-ui.field label="Tanggal tutup" for="jd-tutup" required error="jadwal.error.tutup"><x-ui.input id="jd-tutup" type="date" x-model="jadwal.tutup" error="jadwal.error.tutup" /></x-ui.field>
            </div>
            <label class="flex cursor-pointer items-center gap-3 rounded-control border border-line px-3 py-2.5">
                <button type="button" role="switch" :aria-checked="jadwal.perpanjangan" x-on:click="jadwal.perpanjangan = !jadwal.perpanjangan"
                        class="relative h-5 w-9 shrink-0 rounded-full transition-colors" :class="jadwal.perpanjangan ? 'bg-primary' : 'bg-line'">
                    <span class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white shadow transition-transform" :class="jadwal.perpanjangan && 'translate-x-4'"></span>
                    <span class="sr-only">Izinkan pengisian setelah tutup</span>
                </button>
                <span>
                    <span class="block text-[13px] font-semibold text-ink">Izinkan pengisian setelah tutup (perpanjangan)</span>
                    <span class="block text-xs text-muted">Hanya untuk guru yang belum mengirim nilai</span>
                </span>
            </label>
            <x-ui.field label="Alasan perubahan" for="jd-alasan" required error="jadwal.error.alasan">
                <textarea id="jd-alasan" x-model="jadwal.alasan" rows="3" class="w-full rounded-control border px-3 py-2.5 text-sm text-ink focus:ring-2 focus:outline-none"
                          :class="jadwal.error.alasan ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'"></textarea>
            </x-ui.field>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'jadwal')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan Jadwal</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- 6.03 Konfirmasi kunci nilai --}}
    <x-ui.modal name="kunci" width="max-w-[460px]">
        <x-slot:icon><span class="flex size-11 items-center justify-center rounded-full bg-primary-soft text-primary-strong"><x-ui.icon name="lock" :size="19" /></span></x-slot:icon>
        <h2 class="text-lg font-bold text-ink" x-text="`Kunci nilai ${kunci.daftar.length} kelas?`"></h2>
        <p class="mt-3 text-sm text-ink-2">Pastikan seluruh nilai telah diverifikasi. Setelah dikunci, perubahan hanya dapat dilakukan oleh Admin Kurikulum melalui koreksi beralasan.</p>
        <div class="mt-4 grid grid-cols-2 divide-x divide-line overflow-hidden rounded-control border border-line bg-surface-2 text-[13px]">
            <div class="px-3.5 py-2.5"><p class="text-xs text-muted">Kelas</p><p class="font-bold text-ink" x-text="`${kunci.daftar.join(', ')} · ${periode.sts.singkat}`"></p></div>
            <div class="px-3.5 py-2.5"><p class="text-xs text-muted">Kelengkapan</p><p class="font-bold text-success">100% nilai disetujui</p></div>
        </div>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'kunci')">Batal</x-ui.button>
            <x-ui.button size="lg" icon="lock" x-on:click="kunciNilai()">Ya, kunci nilai</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    {{-- 6.04 Buka koreksi nilai --}}
    <x-ui.modal name="koreksi" width="max-w-[520px]">
        <x-slot:icon><span class="flex size-11 items-center justify-center rounded-full bg-violet-soft text-violet"><x-ui.icon name="unlock" :size="19" /></span></x-slot:icon>
        <h2 class="text-lg font-bold text-ink">Buka koreksi nilai</h2>
        <template x-if="koreksi.kelas">
            <form class="mt-4 space-y-4" x-on:submit.prevent="simpanKoreksi()" novalidate>
                <p class="flex items-center gap-2 rounded-control border border-line bg-surface-2 px-3 py-2.5 text-[13px] text-ink-2">
                    <x-ui.icon name="lock" :size="13" />
                    <span><strong class="text-ink" x-text="`Kelas ${koreksi.kelas.kelas}`"></strong> · <span x-text="`${periode.sts.singkat} 2026/2027 · Terkunci sejak ${koreksi.kelas.terkunci_sejak ?? '—'}`"></span></span>
                </p>
                <div>
                    <label for="kr-mapel" class="mb-1.5 block text-[13px] font-semibold text-ink">Mata pelajaran yang dikoreksi<span class="text-danger"> *</span></label>
                    <div class="relative" x-data="{ fokus: false }" x-on:click.outside="fokus = false">
                        <div class="flex min-h-10 flex-wrap items-center gap-1.5 rounded-control border border-line px-2 py-1.5 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary-soft">
                            <template x-for="m in koreksi.mapel" :key="m">
                                <span class="inline-flex items-center gap-1 rounded-md bg-violet-soft px-2 py-0.5 text-xs font-semibold text-violet">
                                    <span x-text="`${m} — ${guruMapel(m)}`"></span>
                                    <button type="button" x-on:click="koreksi.mapel = koreksi.mapel.filter((x) => x !== m)" :aria-label="`Hapus ${m}`"><x-ui.icon name="x" :size="12" :stroke="2.4" /></button>
                                </span>
                            </template>
                            <input id="kr-mapel" x-model="koreksi.cari" x-on:focus="fokus = true" placeholder="Tambah mapel…" autocomplete="off"
                                   x-on:keydown.enter.prevent="tambahMapel(saranMapel[0])" class="min-w-24 flex-1 border-0 bg-transparent px-1 text-sm focus:outline-none">
                        </div>
                        <ul x-show="fokus && saranMapel.length" x-cloak class="absolute top-full left-0 z-10 mt-1 w-full rounded-control border border-line bg-surface py-1 shadow-pop">
                            <template x-for="m in saranMapel" :key="m.mapel">
                                <li><button type="button" x-on:click="tambahMapel(m)" class="flex w-full justify-between px-3 py-2 text-left text-[13px] hover:bg-surface-2"><span class="font-semibold text-ink" x-text="m.mapel"></span><span class="text-xs text-muted" x-text="m.guru"></span></button></li>
                            </template>
                        </ul>
                    </div>
                </div>
                <x-ui.field label="Alasan koreksi" for="kr-alasan" required>
                    <textarea id="kr-alasan" x-model="koreksi.alasan" rows="3" placeholder="Jelaskan kesalahan yang dikoreksi…" x-on:input="koreksi.error = ''"
                              x-on:blur="!koreksi.alasan.trim() && (koreksi.error = 'Alasan koreksi wajib diisi.')"
                              class="w-full rounded-control border px-3 py-2.5 text-sm text-ink placeholder:text-subtle focus:ring-2 focus:outline-none"
                              :class="koreksi.error ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'"></textarea>
                    <p x-show="koreksi.error" x-cloak class="mt-1.5 flex items-center gap-1 text-xs font-medium text-danger" role="alert"><x-ui.icon name="alert-circle" :size="13" /><span x-text="koreksi.error"></span></p>
                </x-ui.field>
                <x-ui.field label="Batas waktu koreksi" for="kr-batas"><x-ui.input id="kr-batas" type="date" x-model="koreksi.batas" /></x-ui.field>
                <p class="flex items-start gap-2 rounded-control border border-orange-200 bg-warning-faint px-3 py-2.5 text-[13px] text-warning">
                    <x-ui.icon name="alert" :size="14" class="mt-0.5" /> Koreksi akan membuat versi rapor baru. Rapor versi lama tetap tersimpan di arsip.
                </p>
                <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                    <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'koreksi')">Batal</x-ui.button>
                    <x-ui.button type="submit" size="lg" icon="unlock" x-bind:disabled="!koreksi.alasan.trim()">Buka Koreksi</x-ui.button>
                </div>
            </form>
        </template>
    </x-ui.modal>
</div>
@endsection
