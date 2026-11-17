@extends('layouts.app')

@section('title', 'Relasi Orang Tua – Anak')

@push('styles')
    @vite(['resources/js/operator-relasi.js'])
@endpush

@php use App\Support\Format; @endphp

@section('content')
<div class="px-7 pt-7 pb-10" x-data="relasiPage(JSON.parse(document.getElementById('data-relasi').textContent))">
    <x-ui.json-data id="data-relasi" :data="[
        'relasi' => $relasi->items(),
        'akunOrtu' => $akunOrtu,
        'calonAnak' => $calonAnak,
        'bukaModal' => request('modal') === 'hubungkan',
    ]" />

    <x-ui.page-header title="Relasi Orang Tua – Anak" role="Operator"
                      subtitle="Hubungkan akun orang tua dengan anaknya agar dapat melihat rapor yang sudah terbit.">
        <x-slot:actions>
            <x-ui.button icon="plus" size="lg" x-on:click="tambah()">Hubungkan</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($belumTerhubung)
        <x-ui.alert tone="warning" :title="$belumTerhubung.' siswa belum memiliki akun orang tua terhubung.'">
            Orang tua siswa tersebut belum dapat melihat rapor di portal (A4).
            <x-slot:action>
                <a href="{{ route('data-siswa.index', ['ortu' => 'belum']) }}" class="inline-flex items-center gap-1 text-[13px] font-semibold text-warning hover:underline">
                    Lihat daftar <x-ui.icon name="arrow-right" :size="13" />
                </a>
            </x-slot:action>
        </x-ui.alert>
    @endif

    <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.filter-link label="Status" param="status" :options="['' => 'Semua', 'terverifikasi' => 'Terverifikasi', 'menunggu' => 'Menunggu']" />
        <x-ui.filter-link label="Hubungan" param="hubungan" :options="['' => 'Semua', 'Ayah' => 'Ayah', 'Ibu' => 'Ibu', 'Wali' => 'Wali']" />
        <x-ui.search-form placeholder="Cari orang tua atau siswa…" class="ml-auto w-[260px]" />
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="px-5 pt-4 pb-3">
            <h2 class="text-[15px] font-bold text-ink">Daftar Relasi</h2>
            <p class="text-xs text-muted">{{ Format::angka($relasi->total()) }} orang tua terhubung</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="px-5 py-3">Nama Orang Tua/Wali</th>
                        <th class="px-3 py-3">Username</th>
                        <th class="px-3 py-3">Hubungan</th>
                        <th class="px-3 py-3">Anak Terhubung</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="r in relasi" :key="r.id_orang_tua">
                        <tr>
                            <td class="px-5 py-3 font-semibold text-ink" x-text="r.nama"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.username"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="r.hubungan"></td>
                            <td class="px-3 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="a in r.anak" :key="a.nama">
                                        <span class="rounded-full border border-line px-2 py-0.5 text-xs font-semibold text-ink" x-text="`${a.nama} · ${a.kelas}`"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-3 py-3"><span :class="$store.ref.badge(r.status === 'terverifikasi' ? 'success' : 'warning')" x-text="r.status === 'terverifikasi' ? 'Terverifikasi' : 'Menunggu'"></span></td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-1.5">
                                    <x-ui.icon-button icon="pencil" label="Ubah relasi" x-on:click="ubah(r)" />
                                    <x-ui.icon-button icon="trash" tone="danger" label="Putuskan relasi" x-on:click="hapus(r)" />
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!relasi.length"><td colspan="6" class="px-5 py-10 text-center text-sm text-muted">Tidak ada relasi yang cocok dengan filter.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft px-5 py-3">
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Satu akun orang tua dapat terhubung ke lebih dari satu anak.</p>
            <x-ui.pager :paginator="$relasi" satuan="" />
        </div>
    </x-ui.card>

    {{-- 2.10 Hubungkan orang tua ke siswa --}}
    <x-ui.modal name="hubungkan" width="max-w-[560px]">
        <h2 class="pr-10 text-lg font-bold text-ink" x-text="editId ? 'Ubah Relasi Orang Tua' : 'Hubungkan Orang Tua ke Siswa'"></h2>

        <form class="mt-4 space-y-4" x-on:submit.prevent="simpan()" novalidate>
            <div>
                <label for="hb-ortu" class="mb-1.5 block text-[13px] font-semibold text-ink">Akun orang tua<span class="text-danger"> *</span></label>
                <div class="relative" x-on:click.outside="combo.buka = false" x-on:keydown.escape.stop="combo.buka = false">
                    <button type="button" id="hb-ortu" x-on:click="!editId && (combo.buka = !combo.buka)" :aria-expanded="combo.buka" aria-haspopup="listbox"
                            class="flex h-10 w-full items-center gap-2 rounded-control border bg-surface px-3 text-left text-sm"
                            :class="error.ortu ? 'border-danger' : 'border-line'">
                        <x-ui.icon name="search" :size="15" class="text-subtle" />
                        <span class="flex-1 truncate" :class="form.ortu ? 'text-ink' : 'text-subtle'" x-text="form.ortu ? `${form.ortu.nama} · ${form.ortu.username}` : 'Cari akun orang tua…'"></span>
                        <x-ui.icon name="chevron-down" :size="14" class="text-ink-2" />
                    </button>
                    <div x-show="combo.buka" x-cloak class="absolute top-full left-0 z-20 mt-1 w-full rounded-control border border-line bg-surface p-2 shadow-pop">
                        <x-ui.search-input placeholder="Ketik nama atau username…" x-model="combo.cari" x-init="$watch('combo.buka', (b) => b && $nextTick(() => $el.querySelector('input')?.focus()))" />
                        <ul role="listbox" aria-label="Akun orang tua" class="mt-1 max-h-56 overflow-y-auto">
                            <template x-for="a in ortuTersaring" :key="a.id">
                                <li><button type="button" role="option" x-on:click="pilihOrtu(a)" class="flex w-full items-center justify-between rounded-control px-2 py-2 text-left text-[13px] hover:bg-primary-faint">
                                    <span class="font-semibold text-ink" x-text="a.nama"></span><span class="text-xs text-muted" x-text="a.username"></span>
                                </button></li>
                            </template>
                            <li x-show="!ortuTersaring.length" class="px-2 py-3 text-[13px] text-muted">Akun tidak ditemukan.</li>
                        </ul>
                    </div>
                </div>
                <p x-show="error.ortu" x-cloak class="mt-1.5 text-xs font-medium text-danger" role="alert" x-text="error.ortu"></p>
            </div>

            <fieldset>
                <legend class="mb-1.5 text-[13px] font-semibold text-ink">Pilih anak<span class="text-danger"> *</span></legend>
                <x-ui.search-input placeholder="Cari nama siswa atau NIS…" x-model="form.cariAnak" />
                <ul class="mt-2 space-y-2">
                    <template x-for="s in anakTampil" :key="s.nis">
                        <li>
                            <label class="flex cursor-pointer items-center gap-3 rounded-control border px-3 py-2.5"
                                   :class="form.anak.includes(s.nis) ? 'border-primary/50 bg-primary-faint' : 'border-line hover:bg-surface-2'">
                                <input type="checkbox" class="size-4 accent-primary" :checked="form.anak.includes(s.nis)" x-on:change="toggleAnak(s.nis)">
                                <span>
                                    <span class="block text-[13px] font-bold text-ink" x-text="`${s.nama} · ${s.kelas}`"></span>
                                    <span class="block text-xs text-muted" x-text="`NIS ${s.nis}`"></span>
                                </span>
                            </label>
                        </li>
                    </template>
                    <li x-show="!anakTampil.length" class="py-4 text-center text-[13px] text-muted">Siswa tidak ditemukan.</li>
                </ul>
                <p x-show="error.anak" x-cloak class="mt-1.5 text-xs font-medium text-danger" role="alert" x-text="error.anak"></p>
            </fieldset>

            <div class="flex items-center justify-between">
                <span class="text-[13px] font-semibold text-ink">Hubungan</span>
                <x-ui.segmented :options="['Ayah' => 'Ayah', 'Ibu' => 'Ibu', 'Wali' => 'Wali']" model="form.hubungan" label="Hubungan" size="sm" />
            </div>
            <p class="flex items-center gap-1.5 text-xs text-muted"><x-ui.icon name="info" :size="13" />Orang tua hanya dapat melihat rapor yang sudah diterbitkan.</p>

            <div class="flex justify-end gap-2.5 border-t border-line-soft pt-4">
                <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'hubungkan')">Batal</x-ui.button>
                <x-ui.button type="submit" size="lg" icon="link">Hubungkan</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
@endsection
