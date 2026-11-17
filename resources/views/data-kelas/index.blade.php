@extends('layouts.app')

@section('title', 'Data Kelas & Anggota Kelas')

@push('styles')
    @vite(['resources/js/operator-kelas.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10" x-data="kelasPage(JSON.parse(document.getElementById('data-kelas').textContent))">
    <x-ui.json-data id="data-kelas" :data="[
        'tingkat' => $tingkat,
        'kelas' => $kelas,
        'tanpaKelas' => $tanpaKelas,
        'bukaPindah' => request('modal') === 'pindah',
    ]" />

    <x-ui.page-header title="Data Kelas & Anggota Kelas" role="Operator" subtitle="Kelas asal siswa per semester.">
        <x-slot:actions>
            <x-ui.button icon="plus" size="lg" x-on:click="bukaKelasBaru()">Tambah Kelas</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card class="flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-select label="Semester" model="semester" :options="['Ganjil 2026/2027', 'Genap 2025/2026']" />
        <x-ui.filter-link label="Tingkat" param="tingkat" :options="['X' => 'Kelas X', 'XI' => 'Kelas XI', 'XII' => 'Kelas XII']" />
        <span class="ml-1 text-[13px] text-muted" x-text="`${kelas.length} kelas · ${totalSiswa} siswa`"></span>
    </x-ui.card>

    <div class="mt-4 grid gap-4 lg:grid-cols-[331px_minmax(0,1fr)]">
        {{-- Daftar kelas --}}
        <x-ui.card :padding="false" class="self-start overflow-hidden py-2">
            <div class="flex items-center justify-between px-4 pt-2 pb-2">
                <h2 class="text-[15px] font-bold text-ink">Kelas {{ $tingkat }}</h2>
                <x-ui.badge tone="info"><span x-text="`${kelas.length} kelas`"></span></x-ui.badge>
            </div>
            <ul role="listbox" aria-label="Pilih kelas" class="px-2 pb-1">
                <template x-for="(k, i) in kelas" :key="k.id_kelas">
                    <li>
                        <button type="button" role="option" :aria-selected="aktif === i" x-on:click="aktif = i"
                                class="relative flex w-full items-center gap-3 rounded-control px-3 py-2.5 text-left"
                                :class="aktif === i ? 'bg-primary-faint before:absolute before:top-0 before:bottom-0 before:left-0 before:w-[3px] before:rounded-l-control before:bg-primary' : 'hover:bg-surface-2'">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-control text-[13px] font-bold"
                                  :class="aktif === i ? 'bg-primary-soft text-link' : 'bg-neutral-soft text-ink-2'" x-text="k.kelas"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13px] font-bold" :class="aktif === i ? 'text-link' : 'text-ink'" x-text="`Kelas ${k.kelas}`"></span>
                                <span class="block truncate text-xs text-muted" x-text="`Wali: ${k.wali ?? 'belum ditetapkan'}`"></span>
                            </span>
                            <span class="shrink-0 text-xs text-muted" x-text="`${k.anggota.length} siswa`"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </x-ui.card>

        {{-- Anggota kelas --}}
        <x-ui.card :padding="false" class="self-start overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-4 pb-3">
                <div>
                    <h2 class="text-[15px] font-bold text-ink" x-text="`Anggota Kelas ${kelasAktif.kelas}`"></h2>
                    <p class="text-xs text-muted" x-text="`${kelasAktif.anggota.length} siswa · Wali kelas: ${kelasAktif.wali ?? 'belum ditetapkan'}`"></p>
                </div>
                <div class="flex items-center gap-2.5">
                    <x-ui.search-input placeholder="Cari siswa…" x-model="cari" class="w-[180px]" />
                    <x-ui.button icon="plus" x-on:click="bukaTambah()">Tambah Siswa</x-ui.button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr>
                            <th class="w-[56px] px-5 py-3">No</th>
                            <th class="px-3 py-3">NIS</th>
                            <th class="px-3 py-3">Nama</th>
                            <th class="px-3 py-3">L/P</th>
                            <th class="px-3 py-3">Agama</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="(s, i) in barisHalaman" :key="s.nis">
                            <tr>
                                <td class="px-5 py-2.5 text-ink-2" x-text="(page - 1) * perPage + i + 1"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="s.nis"></td>
                                <td class="px-3 py-2.5 font-semibold text-ink" x-text="s.nama"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="s.jenis_kelamin"></td>
                                <td class="px-3 py-2.5 text-ink-2" x-text="s.agama"></td>
                                <td class="px-5 py-2 text-right"><x-ui.button variant="secondary" size="sm" x-on:click="bukaPindahkan(s)">Pindahkan</x-ui.button></td>
                            </tr>
                        </template>
                        <tr x-show="!barisHalaman.length"><td colspan="6" class="px-5 py-10 text-center text-sm text-muted">Belum ada siswa di kelas ini.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
                <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Satu siswa hanya memiliki satu kelas asal per semester.</p>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-muted" x-text="rentang"></span>
                    <x-ui.pagination page="page" pages="halaman" />
                </div>
            </div>
        </x-ui.card>
    </div>

    {{-- 2.08 Pindahkan siswa --}}
    <x-ui.modal name="pindah" title="Pindahkan Siswa" subtitle="Kelas asal baru berlaku untuk semester Ganjil 2026/2027." width="max-w-[480px]">
        <template x-if="pindah.siswa">
            <form class="space-y-4" x-on:submit.prevent="simpanPindah()" novalidate>
                <div class="flex items-center gap-3 rounded-card border border-line bg-surface-2 px-4 py-3">
                    <span class="flex size-9 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white" x-text="inisial(pindah.siswa.nama)"></span>
                    <div>
                        <p class="text-sm font-bold text-ink" x-text="pindah.siswa.nama"></p>
                        <p class="text-xs text-muted">NIS <span x-text="pindah.siswa.nis"></span> · saat ini di <strong class="text-ink" x-text="kelasAktif.kelas"></strong></p>
                    </div>
                </div>
                <x-ui.field label="Pindahkan ke kelas" for="pd-kelas" required error="pindah.error">
                    <x-ui.select id="pd-kelas" x-model="pindah.ke" placeholder="Pilih kelas tujuan" x-on:change="pindah.error = ''">
                        <template x-for="k in tujuanPindah" :key="k.kelas">
                            <option :value="k.kelas" :selected="pindah.ke === k.kelas" x-text="`${k.kelas} · Wali: ${k.wali ?? '—'} · ${k.anggota.length} siswa`"></option>
                        </template>
                    </x-ui.select>
                </x-ui.field>
                <x-ui.field label="Alasan (opsional)" for="pd-alasan">
                    <textarea id="pd-alasan" x-model="pindah.alasan" rows="3" placeholder="Contoh: penyesuaian jumlah siswa per kelas"
                              class="w-full rounded-control border border-line px-3 py-2.5 text-sm text-ink placeholder:text-subtle focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none"></textarea>
                </x-ui.field>
                <p class="flex items-center gap-2 rounded-control border border-primary-soft bg-primary-faint px-3 py-2.5 text-[13px] text-info">
                    <x-ui.icon name="info" :size="14" /> Nilai yang sudah dikunci tetap tersimpan di rapor kelas lama.
                </p>
                <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                    <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'pindah')">Batal</x-ui.button>
                    <x-ui.button type="submit" size="lg">Pindahkan</x-ui.button>
                </div>
            </form>
        </template>
    </x-ui.modal>

    {{-- Tambah siswa ke kelas --}}
    <x-ui.modal name="tambah-anggota" width="max-w-[520px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="`Tambah Siswa ke ${kelasAktif.kelas}`"></h2>
        <p class="mt-1 text-[13px] text-muted">Siswa yang belum memiliki kelas asal pada semester ini.</p>
        <ul class="mt-4 space-y-2">
            <template x-for="s in tanpaKelas" :key="s.nis">
                <li>
                    <label class="flex cursor-pointer items-center gap-3 rounded-control border px-3 py-2.5"
                           :class="tambah.dipilih.includes(s.nis) ? 'border-primary/50 bg-primary-faint' : 'border-line hover:bg-surface-2'">
                        <input type="checkbox" class="size-4 accent-primary" :checked="tambah.dipilih.includes(s.nis)" x-on:change="toggleTambah(s.nis)">
                        <span>
                            <span class="block text-[13px] font-semibold text-ink" x-text="s.nama"></span>
                            <span class="block text-xs text-muted" x-text="`NIS ${s.nis} · ${s.jenis_kelamin} · ${s.agama}`"></span>
                        </span>
                    </label>
                </li>
            </template>
            <li x-show="!tanpaKelas.length" class="py-6 text-center text-sm text-muted">Semua siswa sudah memiliki kelas asal.</li>
        </ul>
        <div class="mt-5 flex items-center justify-between gap-3 border-t border-line-soft pt-4">
            <span class="text-[13px] font-semibold text-link" x-text="`${tambah.dipilih.length} siswa dipilih`"></span>
            <div class="flex gap-2.5">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'tambah-anggota')">Batal</x-ui.button>
                <x-ui.button size="lg" x-bind:disabled="!tambah.dipilih.length" x-on:click="simpanTambah()">Tambahkan</x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    {{-- Tambah kelas --}}
    <x-ui.modal name="kelas-baru" title="Tambah Kelas" subtitle="Kelas baru berlaku untuk semester Ganjil 2026/2027." width="max-w-[420px]">
        <form class="space-y-4" x-on:submit.prevent="simpanKelasBaru()" novalidate>
            <x-ui.field label="Nama kelas" for="kb-nama" required error="kelasBaru.error">
                <x-ui.input id="kb-nama" x-model="kelasBaru.nama" error="kelasBaru.error" x-on:input="kelasBaru.error = ''" />
            </x-ui.field>
            <x-ui.field label="Wali kelas (opsional)" for="kb-wali" hint="Wali kelas dapat ditetapkan Kurikulum di menu Penetapan Wali Kelas.">
                <x-ui.input id="kb-wali" x-model="kelasBaru.wali" />
            </x-ui.field>
            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'kelas-baru')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg">Simpan Kelas</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
