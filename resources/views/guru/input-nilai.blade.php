@extends('layouts.app')

@section('title', 'Input Nilai')

@push('styles')
    @vite(['resources/js/guru-input-nilai.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="inputNilai(JSON.parse(document.getElementById('data-nilai').textContent))">
    <x-ui.json-data id="data-nilai" :data="[
        'rombel' => $rombel, 'aktif' => $aktif, 'grid' => $grid, 'periode' => $periode, 'paket' => $paketData,
    ]" />

    {{-- Kepala halaman + status --}}
    <header class="mb-5 flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-bold tracking-[0.02em] text-link uppercase">SMA Negeri 1 Kota Malang</p>
            <h1 class="mt-1.5 text-[30px] leading-tight font-bold tracking-[-0.01em] text-ink" x-text="`Input Nilai — ${r.judul}`"></h1>
            <p class="mt-1 text-sm text-muted">Satu nilai per TP. Cara Anda mengambil nilai tetap bebas — masukkan hasil akhirnya saja.</p>
            <p x-show="r.campuran" x-cloak class="mt-2 flex gap-2"><x-ui.badge tone="violet">Rombel campuran</x-ui.badge><x-ui.badge x-text="`${peserta.length} siswa dari ${r.kelas_asal?.length} kelas asal`"></x-ui.badge></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <template x-if="!r.campuran && ['draf', 'revisi', 'ditutup', 'terkunci'].includes(status) && status !== 'revisi'">
                <span :class="$store.ref.badge(jumlahLengkap === peserta.length ? 'success' : 'warning')" x-text="`${jumlahLengkap}/${peserta.length} lengkap`"></span>
            </template>
            <span x-show="status === 'revisi'" :class="$store.ref.badge('violet')">Perlu revisi</span>
            <span x-show="status === 'terkirim'" class="inline-flex items-center gap-1.5 rounded-md bg-info-soft px-2 py-1 text-[13px] font-semibold text-info"><x-ui.icon name="send" :size="13" />Terkirim · Menunggu verifikasi</span>
            <template x-if="!bacaSaja">
                <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold" role="status"
                      :class="{ 'text-success': simpan === 'tersimpan', 'text-muted': simpan === 'menyimpan', 'text-danger': simpan === 'gagal' }">
                    <x-ui.icon name="check-circle" :size="14" x-show="simpan === 'tersimpan'" />
                    <x-ui.icon name="refresh" :size="14" x-show="simpan === 'menyimpan'" class="animate-spin" />
                    <x-ui.icon name="alert" :size="14" x-show="simpan === 'gagal'" />
                    <span x-text="{ tersimpan: 'Tersimpan', menyimpan: 'Menyimpan…', gagal: 'Gagal menyimpan ·' }[simpan]"></span>
                    <button type="button" x-show="simpan === 'gagal'" x-on:click="cobaLagi()" class="hover:underline">Coba lagi</button>
                </span>
            </template>
            <span x-show="!r.campuran && r.kelas_asal.some(k => dapatKirim(k))" class="group relative inline-block">
                <x-ui.button size="lg" icon="send" x-bind:disabled="simpan === 'gagal'" x-on:click="mintaKirim()">
                    <span x-text="status === 'revisi' ? 'Kirim Ulang' : 'Kirim nilai'"></span>
                </x-ui.button>
                <span x-show="simpan === 'gagal'" role="tooltip" class="absolute right-0 bottom-full z-10 mb-2 rounded-md bg-toast px-2.5 py-1.5 text-xs whitespace-nowrap text-white">Simpan perubahan terlebih dahulu</span>
            </span>
        </div>
    </header>

    {{-- Rombel campuran: pengiriman per kelas asal (21) --}}
    <div x-show="r.campuran" x-cloak class="mb-4 grid gap-4 md:grid-cols-2">
        <template x-for="k in (r.kelas_asal ?? [])" :key="k.kelas">
            <x-ui.card class="flex items-center gap-3 !py-4">
                <div class="min-w-0 flex-1">
                    <p class="text-[15px] font-bold text-ink" x-text="`${k.kelas} · ${k.jumlah} siswa`"></p>
                    <p class="text-xs text-muted" x-text="`Diperiksa oleh wali kelas ${k.kelas}`"></p><p x-show="k.revisi?.catatan && k.status === 'perlu_revisi'" x-text="k.revisi?.catatan" class="text-xs text-warning"></p>
                </div>
                <span x-show="pengirimanKelas[k.kelas] === 'draf'" :class="$store.ref.badge('info')">Belum dikirim</span>
                <span x-show="pengirimanKelas[k.kelas] === 'terkirim'" :class="$store.ref.badge('info')">Terkirim</span>
                <span x-show="pengirimanKelas[k.kelas] === 'disetujui'" class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2 py-0.5 text-xs font-semibold text-success"><x-ui.icon name="check" :size="12" :stroke="2.4" />Disetujui</span>
                <x-ui.button size="sm" icon="send" x-show="dapatKirim(k)" x-on:click="mintaKirim(k.kelas)"><span x-text="`Kirim ${k.kelas}`"></span></x-ui.button>
            </x-ui.card>
        </template>
    </div>

    {{-- Filter --}}
    <x-ui.card class="flex flex-wrap items-center gap-3 !py-3.5">
        <div class="relative" x-on:click.outside="pilihRombel = false" x-on:keydown.escape="pilihRombel = false">
            <button type="button" x-on:click="pilihRombel = !pilihRombel" :aria-expanded="pilihRombel" aria-haspopup="listbox"
                    class="flex h-9 items-center gap-2 rounded-control border border-line px-3 text-[13px] text-muted">
                Rombel: <span class="font-medium text-ink" x-text="r.kelas"></span><x-ui.icon name="chevron-down" :size="14" class="ml-1 text-ink-2" />
            </button>
            <div x-show="pilihRombel" x-cloak class="absolute top-full left-0 z-30 mt-1 w-[300px] rounded-card border border-line bg-surface p-2 shadow-pop">
                <x-ui.search-input placeholder="Cari rombel…" x-model="cariRombel" />
                <ul role="listbox" aria-label="Rombel" class="mt-1.5">
                    <template x-for="x in rombelTersaring" :key="x.id">
                        <li>
                            <a :href="`?rombel=${x.id}&periode=${periode}`" role="option" :aria-selected="x.id === r.id"
                               class="flex items-center gap-2 rounded-control px-2 py-2 text-[13px]" :class="x.id === r.id ? 'bg-primary-faint font-semibold' : 'hover:bg-surface-2'">
                                <span class="w-4"><x-ui.icon name="check" :size="14" class="text-link" x-show="x.id === r.id" /></span>
                                <span class="flex-1 text-ink" x-text="x.kelas"></span>
                                <span class="text-xs" :class="x.jumlah_tp || x.campuran ? 'text-muted' : 'text-danger'" x-text="labelStatusRombel(x)"></span>
                            </a>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
        <span class="text-[13px] text-muted">Periode:</span>
        <div class="inline-flex items-center rounded-control bg-neutral-soft p-0.5" role="group" aria-label="Periode">
            @foreach (['sts' => 'STS', 'semester' => 'Semester'] as $p => $teks)
                <a href="{{ request()->fullUrlWithQuery(['periode' => $p]) }}" @if ($periode === $p) aria-current="true" @endif
                   @class(['rounded-[6px] px-3 py-1.5 text-[13px] font-medium', 'bg-surface text-link shadow-card' => $periode === $p, 'text-muted hover:text-ink' => $periode !== $p])>{{ $teks }}</a>
            @endforeach
        </div>
        <template x-if="r.campuran">
            <div class="flex items-center gap-2"><span class="text-[13px] text-muted">Kelas asal:</span>
                <select aria-label="Kelas asal" x-model="filterKelas"><option value="semua">Semua</option><template x-for="k in r.kelas_asal" :key="k.id"><option :value="k.kelas" x-text="k.kelas"></option></template></select></div>
        </template>
        <p x-show="!bacaSaja" class="ml-auto text-xs text-muted">Tip: tempel langsung dari Excel <kbd class="ml-1 rounded border border-line bg-surface px-1.5 py-0.5 font-sans text-[11px] font-semibold text-ink-2">Ctrl + V</kbd></p>
        <p x-show="bacaSaja" x-cloak class="ml-auto flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="lock" :size="13" />Hanya baca</p>
    </x-ui.card>

    {{-- Pemberitahuan sesuai status --}}
    <div class="mt-4">
        <template x-if="status === 'draf' && !r.campuran && periode === 'semester'">
            <x-ui.alert tone="warning" icon="info" title="Periode SAS">
                <span x-text="`Isi nilai SAS pada kolom TP${idxSas + 1} (TP terakhir). Nilai rapor = rata-rata TP1–TP${jumlahTp}.`"></span>
                <x-slot:action><span class="text-xs text-warning">SAS tidak dihitung dua kali</span></x-slot:action>
            </x-ui.alert>
        </template>
        <template x-if="status === 'draf' && !r.campuran && periode === 'sts'">
            <x-ui.alert tone="info" icon="calendar" title="Periode STS dibuka">
                <span>Rapor STS memakai rata-rata nilai yang diisi dalam periode STS.</span>
            </x-ui.alert>
        </template>
        <template x-if="status === 'terkirim'">
            <x-ui.alert tone="info" icon="send" title="Nilai sudah dikirim"><span x-text="`· Menunggu verifikasi wali kelas ${r.kelas}.`"></span></x-ui.alert>
        </template>
        <template x-if="status === 'revisi'">
            <div class="rounded-card border border-orange-200 bg-warning-faint px-5 py-4" role="alert">
                <p class="flex items-center gap-3 text-sm"><span class="flex size-8 items-center justify-center rounded-control bg-surface text-warning-strong shadow-card"><x-ui.icon name="alert" :size="16" /></span>
                    <span><strong class="text-warning">Dikembalikan untuk revisi</strong> <span class="text-[13px] text-warning" x-text="`— oleh ${revisi.oleh} · ${revisi.waktu}`"></span></span></p>
                <p class="mt-3 ml-11 rounded-control border-l-[3px] border-warning-strong bg-surface px-4 py-2.5 text-[13px] text-ink-2"><strong class="text-ink">Catatan wali kelas:</strong> <span x-text="revisi.catatan"></span></p>
            </div>
        </template>
        <template x-if="status === 'ditutup'">
            <div class="flex items-start gap-3.5 rounded-card border border-line bg-neutral-soft px-5 py-4" role="status">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-ink-2 shadow-card"><x-ui.icon name="lock" :size="16" /></span>
                <div><p class="text-sm font-bold text-ink">Periode belum dibuka atau sudah ditutup</p><p class="mt-0.5 text-[13px] text-ink-2">Nilai tidak dapat diubah. Hubungi Waka Kurikulum jika perlu perpanjangan.</p></div>
            </div>
        </template>
        <template x-if="status === 'terkunci'">
            <div class="flex items-start gap-3.5 rounded-card border border-success/30 bg-success-soft/50 px-5 py-4" role="status">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-surface text-success shadow-card"><x-ui.icon name="lock" :size="16" /></span>
                <div><p class="text-sm font-bold text-success">Nilai sudah dikunci</p><p class="mt-0.5 text-[13px] text-ink-2">Perubahan hanya melalui koreksi beralasan dari Admin Kurikulum.</p></div>
            </div>
        </template>
    </div>

    {{-- Rombel tanpa TP --}}
    <x-ui.card :padding="false" class="mt-4" x-show="!jumlahTp" x-cloak>
        <x-ui.empty-state icon="target" title="Belum ada TP di rombel ini" description="Tulis tujuan pembelajaran terlebih dahulu sebelum mengisi nilai.">
            <x-ui.button class="mt-5" icon="plus" x-bind:href="`{{ route('tp.index') }}?rombel=${r.id}`" href="{{ route('tp.index') }}">Kelola TP</x-ui.button>
        </x-ui.empty-state>
    </x-ui.card>

    {{-- Tabel nilai --}}
    <x-ui.card :padding="false" class="mt-4 overflow-hidden" x-show="jumlahTp">
        <div class="px-5 pt-4 pb-3">
            <h2 class="text-[15px] font-bold text-ink">Daftar Peserta Didik</h2>
            <p class="text-xs text-muted" x-text="r.campuran ? `${peserta.length} siswa · dari ${r.kelas_asal.length} kelas asal` : `${peserta.length} siswa · Urut berdasarkan nama`"></p>
            <p x-show="r.campuran" x-cloak class="mt-2 flex items-center gap-1.5 text-xs text-ink-2"><x-ui.icon name="info" :size="13" />Nilai dikirim per kelas asal dan diperiksa wali kelas masing-masing.</p>
        </div>
        <div class="overflow-x-auto" x-on:paste="tanganiTempel($event)">
            <table class="w-full min-w-[980px] text-left text-[13px]">
                <thead class="text-xs font-semibold text-ink-2">
                    <tr class="bg-surface-2">
                        <th class="w-12 px-5 py-3">No</th><th class="px-3 py-3">NIS</th><th class="px-3 py-3">Nama siswa</th>
                        <th x-show="r.campuran" class="px-3 py-3">Kelas Asal</th>
                        <template x-for="j in kolom" :key="j">
                            <th class="px-2 py-2 text-center" :class="j === idxSas && 'bg-sas text-amber-800'">
                                <span class="inline-flex items-center gap-1"><x-ui.icon name="lock" :size="11" x-show="bacaSaja" /><span x-text="j === idxSas ? `TP${j + 1} · SAS` : `TP${j + 1}`"></span></span>
                                <span class="block text-[11px] font-normal" :class="j === idxSas ? 'text-amber-700' : 'text-muted'" x-text="singkat[j]"></span>
                            </th>
                        </template>
                        <th class="bg-rapor px-3 py-2 text-center text-link">Nilai rapor<span class="block text-[11px] font-normal" x-text="periode === 'sts' ? 'rata-rata TP STS' : 'rata-rata TP'"></span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(p, i) in tampil" :key="p.nisn">
                        <tr>
                            <td class="px-5 text-ink-2" :class="bacaSaja ? 'py-2' : 'py-2.5'" x-text="i + 1"></td>
                            <td class="px-3 text-ink-2" x-text="p.nisn"></td>
                            <td class="px-3 font-semibold text-ink"><span class="inline-flex items-center gap-1"><span x-text="p.nama"></span><x-ui.icon name="lock" :size="11" class="text-muted" x-show="r.campuran && !bisaUbah(p)" /></span></td>
                            <td x-show="r.campuran" class="px-3"><span class="rounded-md px-2 py-0.5 text-xs font-semibold" :class="p.kelas === 'X-A' ? 'bg-info-soft text-info' : 'bg-neutral-soft text-neutral'" x-text="p.kelas"></span></td>
                            <template x-for="j in kolom" :key="j">
                                <td class="px-2 py-1 text-center" :class="j === idxSas && 'bg-sas'">
                                    <template x-if="bisaUbah(p, j)">
                                        <div class="group relative mx-auto w-14">
                                            <input type="text" inputmode="decimal" maxlength="6" :value="p.nilai[j]" :data-sel="`${peserta.indexOf(p)}-${j}`"
                                                   :aria-label="`Nilai TP${j + 1} ${p.nama}`" :aria-invalid="!valid(p.nilai[j])"
                                                   x-on:focus="fokus = { i, j: kolom.indexOf(j) }; $el.select()" x-on:input="ubah(p, j, $event.target.value)"
                                                   x-on:keydown.enter.prevent="document.querySelector(`[data-sel='${peserta.indexOf(p) + 1}-${j}']`)?.focus()"
                                                   class="h-8 w-14 rounded-control border bg-transparent text-center text-[13px] text-ink placeholder:text-subtle focus:bg-surface focus:ring-2 focus:outline-none"
                                                   :class="!valid(p.nilai[j]) ? 'border-danger bg-danger-soft/40 font-semibold text-danger focus:ring-danger-soft'
                                                        : (belumTersimpan(p, j) && simpan === 'gagal' ? 'border-danger focus:ring-danger-soft'
                                                        : (diminta(p, j) ? 'border-warning-strong font-semibold text-warning focus:ring-warning-soft' : 'border-transparent hover:border-line focus:border-primary focus:ring-primary-soft'))"
                                                   placeholder="—">
                                            <span x-show="!valid(p.nilai[j])" role="tooltip" class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 hidden -translate-x-1/2 rounded-md bg-toast px-2 py-1 text-[11px] whitespace-nowrap text-white group-focus-within:block group-hover:block">Nilai harus 0–100</span>
                                            <span x-show="valid(p.nilai[j]) && belumTersimpan(p, j) && simpan === 'gagal'" role="tooltip" class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 hidden -translate-x-1/2 rounded-md bg-toast px-2 py-1 text-[11px] whitespace-nowrap text-white group-hover:block">Belum tersimpan</span>
                                            <span x-show="diminta(p, j)" role="tooltip" class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1.5 -translate-x-1/2 rounded-md bg-toast px-2 py-1 text-[11px] whitespace-nowrap text-white">Diminta dicek wali kelas</span>
                                        </div>
                                    </template>
                                    <template x-if="!bisaUbah(p, j)"><span class="text-ink-2" x-text="p.nilai[j] === '' ? '—' : p.nilai[j]"></span></template>
                                </td>
                            </template>
                            <td class="bg-rapor px-3 text-center font-bold text-link" x-text="rapor(p) ?? '—'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3 text-xs">
            <ul class="flex flex-wrap gap-4 text-ink-2">
                <li class="flex items-center gap-1.5"><span class="size-2.5 rounded-sm bg-sas ring-1 ring-amber-200"></span>Kolom SAS</li>
                <li class="flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-danger"></span>Perlu diperbaiki</li>
                <li class="flex items-center gap-1.5"><span class="text-subtle">—</span>Belum diisi</li>
            </ul>
            <p x-show="bacaSaja" class="flex items-center gap-1.5 text-muted"><x-ui.icon name="lock" :size="13" />Nilai tidak dapat diubah</p>
            <p x-show="waktuSimpan && simpan === 'tersimpan'" class="flex items-center gap-1.5 font-semibold text-success"><x-ui.icon name="check-circle" :size="13" /><span x-text="`Perubahan tersimpan otomatis pukul ${waktuSimpan}`"></span></p>
            <p x-show="!bacaSaja && simpan === 'gagal'" class="flex items-center gap-1.5 font-semibold text-danger"><x-ui.icon name="alert" :size="13" /><span x-text="`${kotor.length} perubahan belum tersimpan`"></span></p>
        </div>
    </x-ui.card>

    {{-- 22 Pratinjau tempel dari Excel --}}
    <x-ui.modal name="tempel" width="max-w-[760px]">
        <h2 class="pr-10 text-lg font-bold text-ink">Pratinjau Tempel Nilai</h2>
        <p class="mt-1 text-[13px] text-muted" x-text="`${r.kelas} · ${tempel.baris.length} baris × ${tempel.baris[0]?.sel.length ?? 0} kolom dari clipboard. Periksa sebelum diterapkan.`"></p>
        <div class="mt-3 flex flex-wrap gap-2">
            <x-ui.badge tone="success" icon="check"><span x-text="`${tempel.ringkas.valid} nilai valid`"></span></x-ui.badge>
            <x-ui.badge tone="danger" icon="alert-circle"><span x-text="`${tempel.ringkas.salah} tidak valid`"></span></x-ui.badge>
            <x-ui.badge><span x-text="`${tempel.ringkas.kosong} sel kosong`"></span></x-ui.badge>
        </div>
        <div class="mt-3 overflow-x-auto rounded-card border border-line">
            <table class="w-full text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr><th class="px-3 py-2">No</th><th class="px-3 py-2">Nama</th>
                        <template x-for="s in (tempel.baris[0]?.sel ?? [])" :key="s.j"><th class="px-2 py-2 text-center" x-text="s.j === idxSas ? `TP${s.j + 1} · SAS` : `TP${s.j + 1}`"></th></template></tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="(b, i) in tempel.baris.slice(0, 8)" :key="i">
                        <tr>
                            <td class="px-3 py-1.5 text-muted" x-text="i + 1"></td>
                            <td class="px-3 py-1.5 font-semibold text-ink" x-text="b.nama"></td>
                            <template x-for="s in b.sel" :key="s.j">
                                <td class="px-2 py-1 text-center" :class="s.jenis === 'kosong' && 'bg-surface-2 text-subtle'">
                                    <span :class="s.jenis === 'salah' && 'inline-block rounded-control border border-danger px-3 py-0.5 font-semibold text-danger'" x-text="s.jenis === 'kosong' ? '—' : s.nilai"></span>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-muted" x-text="`Menampilkan ${Math.min(8, tempel.baris.length)} dari ${tempel.baris.length} baris.`"></p>
        <p class="mt-3 flex items-center gap-2 rounded-control bg-surface-2 px-3 py-2.5 text-[13px] text-ink-2"><x-ui.icon name="info" :size="14" />Sel kosong tetap <strong class="text-ink">belum diisi</strong>, bukan nilai 0. Nilai tidak valid tidak diterapkan.</p>
        <div class="mt-5 flex items-center justify-between border-t border-line-soft pt-4">
            <button type="button" class="text-[13px] font-semibold text-link hover:underline" x-on:click="tempelUlang()">Tempel ulang</button>
            <div class="flex gap-2.5">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'tempel')">Batal</x-ui.button>
                <x-ui.button size="lg" x-bind:disabled="!tempel.ringkas.valid" x-on:click="terapkanTempel()"><span x-text="`Terapkan ${tempel.ringkas.valid} Nilai`"></span></x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- 24/25 Konfirmasi kirim --}}
    <x-ui.modal name="kirim" width="max-w-[440px]">
        <x-slot:icon>
            <span class="flex size-11 items-center justify-center rounded-full" :class="masalah.length ? 'bg-warning-soft text-warning-strong' : 'bg-primary-soft text-primary-strong'">
                <x-ui.icon name="alert" :size="19" x-show="masalah.length" /><x-ui.icon name="send" :size="19" x-show="!masalah.length" />
            </span>
        </x-slot:icon>
        <template x-if="masalah.length">
            <div>
                <h2 class="text-lg font-bold text-ink">Nilai belum lengkap</h2>
                <p class="mt-2 text-sm text-ink-2" x-text="`Masih ada ${masalah.length} nilai kosong atau tidak valid. Lengkapi dulu sebelum mengirim ke wali kelas.`"></p>
                <ul class="mt-3 max-h-40 space-y-1 overflow-y-auto rounded-control border border-orange-200 bg-warning-faint px-4 py-2.5 text-[13px] text-warning">
                    <template x-for="(m, k) in masalah" :key="k">
                        <li class="flex gap-2"><span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-warning-strong"></span><span><strong x-text="m.tp"></strong> — <span x-text="m.nama"></span> <span class="ml-1" :class="m.ket.startsWith('tidak') && 'text-danger'" x-text="`(${m.ket})`"></span></span></li>
                    </template>
                </ul>
            </div>
        </template>
        <template x-if="!masalah.length">
            <div>
                <h2 class="text-lg font-bold text-ink" x-text="kirimKelas ? `Kirim nilai ${kirimKelas} ke wali kelas?` : 'Kirim nilai ke wali kelas?'"></h2>
                <p class="mt-2 text-sm text-ink-2">Setelah dikirim, nilai tidak dapat diubah sampai wali kelas mengembalikannya untuk revisi.</p>
                <p class="mt-3 flex items-center gap-2 rounded-control border border-success/30 bg-success-soft/50 px-3.5 py-2.5 text-[13px] text-success">
                    <x-ui.icon name="check-circle" :size="14" /><span><strong x-text="`${r.kelas}`"></strong> <span x-text="`· ${barisKirim.length} siswa · ${barisKirim.length}/${barisKirim.length} lengkap · ${kolom.length} TP`"></span></span>
                </p>
            </div>
        </template>
        <x-slot:footer>
            <template x-if="masalah.length">
                <div class="flex gap-2.5">
                    <x-ui.button variant="soft" size="lg" disabled>Kirim Nilai</x-ui.button>
                    <x-ui.button size="lg" x-on:click="kembaliMengisi()">Kembali Mengisi</x-ui.button>
                </div>
            </template>
            <template x-if="!masalah.length">
                <div class="flex gap-2.5">
                    <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'kirim')">Batal</x-ui.button>
                    <x-ui.button size="lg" x-on:click="kirim()" x-bind:disabled="sedangKirim">Kirim Nilai</x-ui.button>
                </div>
            </template>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
