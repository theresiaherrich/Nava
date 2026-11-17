@extends('layouts.app')

@section('title', 'Verifikasi Nilai')

@push('styles')
    @vite(['resources/js/wali-verifikasi.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="verifikasiPage(JSON.parse(document.getElementById('data-verifikasi').textContent))">
    <x-ui.json-data id="data-verifikasi" :data="['kelas' => $kelas, 'paket' => $paket, 'aktif' => $aktif, 'state' => $state, 'modal' => request('modal'), 'bukaPeriode' => request('pilih-periode') === '1']" />

    <x-ui.page-header :title="'Verifikasi Nilai '.$kelas" role="Wali Kelas" subtitle="Periksa kelengkapan dan kewajaran nilai sebelum disetujui untuk rapor.">
        <x-slot:actions>
            {{-- 2.03 Pilih periode --}}
            <div class="relative" x-on:click.outside="pilihPeriode = false" x-on:keydown.escape="pilihPeriode = false">
                <button type="button" x-on:click="pilihPeriode = !pilihPeriode" :aria-expanded="pilihPeriode" aria-haspopup="listbox"
                        class="flex h-9 items-center gap-2 rounded-control border border-line bg-surface px-3 text-[13px] text-muted">
                    Periode: <span class="font-medium text-ink" x-text="periode"></span><x-ui.icon name="chevron-down" :size="14" class="ml-1 text-ink-2" />
                </button>
                <ul x-show="pilihPeriode" x-cloak role="listbox" aria-label="Periode" class="absolute top-full right-0 z-30 mt-1 w-[280px] rounded-card border border-line bg-surface p-1.5 shadow-pop">
                    @foreach ([['STS Ganjil 2026/2027', 'Dibuka · ditutup 23 Okt 2026', true], ['SAS Ganjil 2026/2027', 'Dibuka 16 Nov 2026', false], ['SAS Genap 2025/2026', 'Selesai · terkunci', true]] as [$nama, $ket, $bisa])
                        <li>
                            <button type="button" role="option" @disabled(! $bisa) :aria-selected="periode === @js($nama)"
                                    x-on:click="periode = @js($nama); pilihPeriode = false"
                                    class="flex w-full items-start gap-2 rounded-control px-2.5 py-2 text-left hover:bg-surface-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <span class="mt-0.5 w-4"><x-ui.icon name="check" :size="14" class="text-link" x-show="periode === {{ \Illuminate\Support\Js::from($nama) }}" /></span>
                                <span><span class="block text-[13px] font-semibold text-ink">{{ $nama }}</span><span class="block text-xs text-muted">{{ $ket }}</span></span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    <div x-show="state === 'terkunci'" x-cloak class="mb-4 flex items-start gap-3.5 rounded-card border border-success/30 bg-success-soft/50 px-5 py-4" role="status">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-success shadow-card"><x-ui.icon name="lock" :size="16" /></span>
        <div><p class="text-sm font-bold text-success" x-text="`Nilai ${kelas} sudah dikunci Kurikulum`"></p><p class="mt-0.5 text-[13px] text-ink-2">26 Okt 2026, 14.05. Nilai hanya dapat dilihat. Perubahan melalui koreksi beralasan dari Admin Kurikulum.</p></div>
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-[331px_minmax(0,1fr)]">
        {{-- Daftar mapel --}}
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="px-4 pt-4">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15px] font-bold text-ink">Mata pelajaran</h2>
                    <x-ui.segmented :options="['menunggu' => 'Menunggu', 'semua' => 'Semua']" model="filter" label="Filter mapel" size="sm" />
                </div>
                <p class="mt-2 text-xs text-muted" x-text="`${jumlahDisetujui} dari ${paket.length} disetujui`"></p>
                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-line"><div class="h-full rounded-full bg-success-strong" :style="`width: ${jumlahDisetujui / paket.length * 100}%`"></div></div>
            </div>
            <ul class="mt-2 max-h-[400px] overflow-y-auto px-2 pb-2" role="listbox" aria-label="Mata pelajaran">
                <template x-for="x in daftar" :key="x.id">
                    <li>
                        <button type="button" role="option" :aria-selected="x.id === aktifId" :disabled="!terkirim(x)" x-on:click="aktifId = x.id"
                                class="relative flex w-full items-center gap-3 rounded-control px-2.5 py-2.5 text-left disabled:cursor-default"
                                :class="x.id === aktifId ? 'bg-primary-faint before:absolute before:inset-y-0 before:left-0 before:w-[3px] before:rounded-l-control before:bg-primary' : (terkirim(x) ? 'hover:bg-surface-2' : 'opacity-60')">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-control text-[13px] font-bold" :class="x.id === aktifId ? 'bg-primary-soft text-link' : 'bg-neutral-soft text-ink-2'" x-text="x.mapel[0]"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] leading-tight font-bold" :class="x.id === aktifId ? 'text-link' : 'text-ink'" x-text="x.mapel"></span>
                                <span class="block truncate text-xs text-muted" x-text="`${x.guru}${x.keterangan && x.keterangan !== 'rombel campuran' ? ' · ' + x.keterangan : ''}${terkirim(x) ? '' : ' · belum dikirim'}`"></span>
                            </span>
                            <span :class="$store.ref.badge(toneStatus(x.status))" x-text="labelStatus(x.status)"></span>
                        </button>
                    </li>
                </template>
                <li x-show="!daftar.length" class="px-3 py-6 text-center text-[13px] text-muted">Tidak ada nilai yang menunggu verifikasi.</li>
            </ul>
        </x-ui.card>

        {{-- Detail paket --}}
        <div class="flex min-w-0 flex-col gap-5">
            <x-ui.card :padding="false" x-show="!adaKiriman" x-cloak>
                <x-ui.empty-state icon="clipboard" title="Belum ada nilai yang dikirim guru" description="Nilai muncul di sini setelah guru mapel mengirimkannya. Anda akan menerima notifikasi." />
            </x-ui.card>

            <x-ui.card :padding="false" class="overflow-hidden" x-show="adaKiriman && terkirim(p)">
                <div class="px-5 pt-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-ink" x-text="p.keterangan && p.keterangan !== 'rombel campuran' ? `${p.mapel} · ${p.keterangan}` : p.mapel"></h2>
                            <p class="text-[13px] text-muted" x-text="`${p.guru} · ${p.tp} TP · ${p.status === 'dikirim_ulang' || p.riwayat.some((r) => r.jenis === 'dikirim_ulang') ? 'Dikirim ulang' : 'Dikirim'} ${p.dikirim}`"></p>
                        </div>
                        <span :class="$store.ref.badge(toneStatus(p.status))" x-text="labelStatus(p.status)"></span>
                    </div>
                    <p x-show="p.campuran" class="mt-2 inline-flex items-center gap-1.5 rounded-md bg-violet-soft px-2 py-0.5 text-xs font-semibold text-violet">
                        <x-ui.icon name="info" :size="12" /><span x-text="`Rombel campuran X-A + ${kelas} · Anda memeriksa ${p.nilai.length} siswa ${kelas}`"></span>
                    </p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-card border border-line p-3.5">
                            <p class="text-xs text-muted">Kelengkapan</p><p class="mt-1 text-[22px] leading-tight font-bold text-ink">100%</p>
                            <div class="mt-2 h-1 overflow-hidden rounded-full bg-line"><div class="h-full w-full rounded-full bg-success-strong"></div></div>
                            <p class="mt-1.5 text-xs text-muted" x-text="`${p.nilai.length}/${p.nilai.length} siswa${p.campuran ? ' ' + kelas : ''}`"></p>
                        </div>
                        <div class="rounded-card border border-line p-3.5">
                            <p class="text-xs text-muted" x-text="p.campuran ? `Rata-rata siswa ${kelas}` : 'Rata-rata kelas'"></p><p class="mt-1 text-[22px] leading-tight font-bold text-ink" x-text="rataKelas"></p>
                            <p class="mt-1.5 text-xs text-muted" x-text="`Rentang ${rentang}`"></p>
                        </div>
                        <div class="rounded-card border border-line p-3.5">
                            <p class="text-xs text-muted">Perlu diperiksa</p>
                            <p class="mt-1 text-[22px] leading-tight font-bold" :class="daftarAneh.length ? 'text-danger' : 'text-success'" x-text="`${daftarAneh.length} nilai`"></p>
                            <p class="mt-1.5 text-xs text-muted" x-text="daftarAneh.length ? 'Nilai di luar rentang wajar' : 'Semua dalam rentang wajar'"></p>
                        </div>
                    </div>
                    <p class="mt-4 mb-2 flex items-center gap-1.5 text-xs text-muted">
                        <x-ui.icon name="info" :size="13" x-show="p.campuran" />
                        <span x-text="`${p.campuran ? 'Siswa X-A diperiksa oleh wali kelas X-A. ' : ''}Menampilkan ${barisTampil.length} dari ${p.nilai.length} siswa${p.campuran ? ' ' + kelas : ''}.`"></span>
                        <button type="button" x-show="p.nilai.length > 8" x-on:click="semuaBaris = !semuaBaris" class="font-semibold text-link hover:underline" x-text="semuaBaris ? 'Ringkas' : 'Tampilkan semua'"></button>
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-left text-[13px]">
                        <thead class="text-xs font-semibold text-ink-2">
                            <tr class="bg-surface-2">
                                <th class="px-5 py-2.5">Nama siswa</th>
                                <th x-show="p.campuran" class="px-3 py-2.5">Kelas Asal</th>
                                <template x-for="j in kolom" :key="j"><th class="px-2 py-2.5 text-center" :class="j === p.tp - 1 && 'bg-sas text-amber-800'" x-text="j === p.tp - 1 ? `TP${j + 1} · SAS` : `TP${j + 1}`"></th></template>
                                <th class="bg-rapor px-3 py-2.5 text-center text-link">Nilai rapor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line-soft">
                            <template x-for="b in barisTampil" :key="b.nisn">
                                <tr>
                                    <td class="px-5 py-1.5 leading-tight"><p class="font-semibold text-ink" x-text="b.nama"></p><p class="text-xs text-muted" x-text="b.nisn"></p></td>
                                    <td x-show="p.campuran" class="px-3"><span class="rounded-md bg-info-soft px-2 py-0.5 text-xs font-semibold text-info" x-text="b.kelas"></span></td>
                                    <template x-for="j in kolom" :key="j">
                                        <td class="px-2 text-center" :class="j === p.tp - 1 && 'bg-sas'">
                                            <span x-show="!aneh(b, j)" class="text-ink-2" x-text="b.nilai[j]"></span>
                                            <span x-show="aneh(b, j)" class="group relative inline-block">
                                                <span class="inline-block rounded-control border border-danger bg-danger-soft/50 px-3 py-0.5 font-semibold text-danger" x-text="b.nilai[j]" tabindex="0" :aria-label="`${b.nilai[j]}, jauh di bawah rata-rata kelas`"></span>
                                                <span role="tooltip" class="absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 rounded-md bg-toast px-2 py-1 text-[11px] whitespace-nowrap text-white">Jauh di bawah rata-rata kelas</span>
                                            </span>
                                        </td>
                                    </template>
                                    <td class="bg-rapor px-3 text-center font-bold text-link" x-text="rapor(b)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="bisaDiperiksa" class="px-5 pt-3 pb-4">
                    <label for="vr-catatan" class="mb-1.5 block text-[13px] font-semibold text-ink">Catatan untuk guru (opsional)</label>
                    <textarea id="vr-catatan" x-model="catatan" rows="2" placeholder="Tulis catatan bila perlu…" class="w-full rounded-control border border-line px-3 py-2.5 text-sm text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none"></textarea>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3.5">
                    <p x-show="!bisaDiperiksa" class="flex items-center gap-1.5 text-[13px] text-muted"><x-ui.icon name="lock" :size="13" />
                        <span x-text="p.status === 'revisi' ? 'Menunggu guru memperbaiki nilai' : (p.status === 'terkunci' ? 'Nilai sudah dikunci Kurikulum' : 'Menunggu penguncian oleh Kurikulum')"></span></p>
                    <span x-show="bisaDiperiksa"></span>
                    <div class="flex gap-2.5">
                        <x-ui.button variant="warning" size="lg" icon="undo" x-bind:disabled="!bisaDiperiksa" x-bind:class="!bisaDiperiksa && '!border-line !bg-line !text-subtle'" x-on:click="mintaRevisi()">Minta revisi</x-ui.button>
                        <x-ui.button size="lg" icon="check" x-bind:disabled="!bisaDiperiksa" x-on:click="$dispatch('open-modal', 'setujui')">Setujui nilai</x-ui.button>
                    </div>
                </div>
            </x-ui.card>

            {{-- Riwayat verifikasi (2.07) --}}
            <x-ui.card x-show="adaKiriman && (p.riwayat.length > 2 || p.riwayat.some((r) => r.jenis === 'revisi'))">
                <h2 class="text-[15px] font-bold text-ink">Riwayat Verifikasi</h2>
                <ol class="relative mt-4 space-y-4 before:absolute before:top-2 before:bottom-2 before:left-[13px] before:w-px before:bg-line">
                    <template x-for="(r, i) in p.riwayat" :key="i">
                        <li class="relative flex gap-3">
                            <span class="relative z-10 flex size-7 shrink-0 items-center justify-center rounded-full ring-4 ring-surface"
                                  :class="{ disetujui: 'bg-success-soft text-success', terkunci: 'bg-success-soft text-success', revisi: 'bg-warning-soft text-warning-strong' }[r.jenis] ?? 'bg-primary-soft text-primary-strong'">
                                <x-ui.icon name="check" :size="13" x-show="['disetujui', 'terkunci'].includes(r.jenis)" />
                                <x-ui.icon name="undo" :size="13" x-show="r.jenis === 'revisi'" />
                                <x-ui.icon name="send" :size="13" x-show="['dikirim', 'dikirim_ulang'].includes(r.jenis)" />
                            </span>
                            <div>
                                <p class="text-[13px] font-bold text-ink" x-text="r.judul"></p>
                                <p class="text-xs text-muted" x-text="`${r.oleh} · ${r.waktu}`"></p>
                                <p x-show="r.catatan" class="mt-1.5 rounded-control bg-surface-2 px-3 py-1.5 text-[13px] text-ink-2" x-text="`“${r.catatan}”`"></p>
                            </div>
                        </li>
                    </template>
                </ol>
                <p class="mt-4 flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="lock" :size="12" />Riwayat tidak dapat dihapus atau diubah.</p>
            </x-ui.card>
        </div>
    </div>

    {{-- 2.04/2.05 Minta revisi --}}
    <x-ui.modal name="revisi" width="max-w-[480px]" labelledby="judul-revisi">
        <div class="flex items-center gap-3 pr-10">
            <span class="flex size-10 items-center justify-center rounded-full bg-warning-soft text-warning-strong"><x-ui.icon name="undo" :size="18" /></span>
            <h2 id="judul-revisi" class="text-lg font-bold text-ink">Kembalikan nilai untuk revisi</h2>
        </div>
        <form class="mt-4 space-y-4" x-on:submit.prevent="kirimRevisi()" novalidate>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-1 rounded-control border border-line bg-surface-2 px-3.5 py-2.5 text-[13px]">
                <div><dt class="inline text-muted">Mata pelajaran:</dt> <dd class="inline font-bold text-ink" x-text="p.mapel"></dd></div>
                <div><dt class="inline text-muted">Kelas:</dt> <dd class="inline font-bold text-ink" x-text="kelas"></dd></div>
                <div><dt class="inline text-muted">Guru:</dt> <dd class="inline font-bold text-ink" x-text="p.guru"></dd></div>
                <div><dt class="inline text-muted">Rapor:</dt> <dd class="inline font-bold text-ink">STS</dd></div>
            </dl>
            <div>
                <label for="vr-alasan" class="mb-1.5 block text-[13px] font-semibold text-ink">Alasan revisi<span class="text-danger"> *</span></label>
                <textarea id="vr-alasan" x-model="revisi.alasan" rows="3" :maxlength="maksAlasan" placeholder="Jelaskan nilai yang perlu dicek guru…" x-on:input="revisi.error = ''"
                          class="w-full rounded-control border px-3 py-2.5 text-sm text-ink placeholder:text-subtle focus:ring-2 focus:outline-none"
                          :class="revisi.error ? 'border-danger focus:ring-danger-soft' : 'border-line focus:border-primary focus:ring-primary-soft'"></textarea>
                <div class="mt-1 flex justify-between text-xs">
                    <span x-show="revisi.error" class="flex items-center gap-1 font-medium text-danger" role="alert"><x-ui.icon name="alert-circle" :size="13" /><span x-text="revisi.error"></span></span>
                    <span class="ml-auto text-muted" x-text="`${revisi.alasan.length}/${maksAlasan}`"></span>
                </div>
            </div>
            <fieldset>
                <legend class="mb-1.5 text-[13px] font-semibold text-ink">Tandai siswa (opsional)</legend>
                <x-ui.search-input placeholder="Cari siswa…" x-model="revisi.cari" />
                <ul class="mt-2 space-y-2">
                    <template x-for="b in siswaRevisi" :key="b.nisn">
                        <li>
                            <label class="flex cursor-pointer items-center gap-3 rounded-control border px-3 py-2.5" :class="revisi.siswa.includes(b.nama) ? 'border-warning-strong/50 bg-warning-faint' : 'border-line hover:bg-surface-2'">
                                <input type="checkbox" class="size-4 accent-primary" :checked="revisi.siswa.includes(b.nama)" x-on:change="toggleSiswa(b.nama)">
                                <span><span class="block text-[13px] font-semibold text-ink" x-text="b.nama"></span><span x-show="ketSiswa(b)" class="block text-xs text-muted" x-text="ketSiswa(b)"></span></span>
                            </label>
                        </li>
                    </template>
                </ul>
            </fieldset>
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Guru akan menerima catatan ini dan dapat mengedit nilai kembali.</p>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'revisi')">Batal</x-ui.button>
                <button type="submit" :disabled="!revisi.alasan.trim()" class="inline-flex h-10 items-center gap-2 rounded-control bg-warning-strong px-4 text-[15px] font-semibold text-white hover:bg-warning disabled:bg-orange-200">
                    <x-ui.icon name="undo" :size="16" />Kembalikan ke Guru
                </button>
            </div>
        </form>
    </x-ui.modal>

    {{-- 2.06 Setujui nilai --}}
    <x-ui.modal name="setujui" width="max-w-[440px]" labelledby="judul-setujui">
        <x-slot:icon><span class="flex size-11 items-center justify-center rounded-full bg-success-soft text-success"><x-ui.icon name="check" :size="20" /></span></x-slot:icon>
        <h2 id="judul-setujui" class="text-lg font-bold text-ink" x-text="`Setujui nilai ${p.mapel} ${kelas}?`"></h2>
        <p class="mt-2 text-sm text-ink-2">Nilai yang disetujui diteruskan ke Kurikulum untuk dikunci. Guru tidak dapat mengubahnya lagi kecuali Anda atau Kurikulum membuka koreksi.</p>
        <p class="mt-3 flex items-center gap-2 rounded-control border border-success/30 bg-success-soft/50 px-3.5 py-2.5 text-[13px] text-success">
            <x-ui.icon name="check-circle" :size="14" /><span><strong x-text="p.mapel"></strong> <span x-text="`· ${p.nilai.length} siswa · ${p.tp} TP · rata-rata ${rataKelas}`"></span></span>
        </p>
        <p x-show="daftarAneh.length" class="mt-3 flex items-start gap-2 rounded-control border border-orange-200 bg-warning-faint px-3.5 py-2.5 text-[13px] text-warning">
            <x-ui.icon name="alert" :size="14" class="mt-0.5" /><span x-text="`Masih ada ${daftarAneh.length} nilai di luar rentang wajar (${daftarAneh.map((a) => `${a.nama} · ${a.tp} = ${a.nilai}`).join(', ')}). Pertimbangkan meminta revisi.`"></span>
        </p>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'setujui')">Batal</x-ui.button>
            <x-ui.button size="lg" icon="check" x-on:click="setujui()">Setujui nilai</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
