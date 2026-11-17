@extends('layouts.app')

@section('title', 'Terbitkan Rapor')

@push('styles')
    @vite(['resources/js/kurikulum-rapor.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-28" x-data="terbitkanRapor(JSON.parse(document.getElementById('data-terbit').textContent))">
    <x-ui.json-data id="data-terbit" :data="['kelas' => $kelas, 'bukaModal' => request('modal') === 'terbitkan']" />

    <x-ui.page-header title="Terbitkan Rapor" role="Kurikulum" subtitle="Rapor yang diterbitkan dapat dilihat siswa dan orang tua di portal." />

    <x-ui.alert tone="info" icon="megaphone" title="Kunci nilai tidak otomatis menerbitkan rapor.">
        Terbitkan setelah PDF diperiksa. Rapor yang terbit langsung muncul di Portal Orang Tua (A4).
    </x-ui.alert>

    <x-ui.card class="mt-4 flex flex-wrap items-center gap-2.5 !py-3.5">
        <x-ui.segmented :options="['sts' => 'Rapor STS', 'semester' => 'Rapor Semester']" model="jenis" label="Jenis rapor" />
        <x-ui.filter-select label="Tingkat" model="tingkat" :options="['Kelas X', 'Kelas XI', 'Kelas XII']" />
        <x-ui.search-input placeholder="Cari kelas…" x-model="cari" class="w-[160px]" />
        <div class="ml-auto flex items-center gap-2 text-ink-2">
            <x-ui.badge tone="success" icon="lock">Terkunci</x-ui.badge><x-ui.icon name="arrow-right" :size="13" />
            <x-ui.badge tone="info">PDF dibuat</x-ui.badge><x-ui.icon name="arrow-right" :size="13" />
            <x-ui.badge tone="success" icon="check">Terbit</x-ui.badge>
        </div>
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr>
                        <th class="w-12 px-5 py-3"><span class="sr-only">Pilih</span></th>
                        <th class="px-3 py-3">Kelas</th><th class="px-3 py-3">Wali Kelas</th><th class="px-3 py-3">Kunci</th>
                        <th class="px-3 py-3">PDF</th><th class="px-3 py-3">Status Terbit</th><th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="k in tampil" :key="k.kelas">
                        <tr :class="dipilih.includes(k.kelas) && 'bg-primary-faint'">
                            <td class="px-5 py-3">
                                <input type="checkbox" class="size-4 accent-primary disabled:opacity-40" :checked="dipilih.includes(k.kelas)" :disabled="!bisa(k)"
                                       x-on:change="toggle(k)" :aria-label="`Pilih kelas ${k.kelas}`">
                            </td>
                            <td class="px-3 py-3 font-bold text-ink" x-text="k.kelas"></td>
                            <td class="px-3 py-3 text-ink-2" x-text="k.wali"></td>
                            <td class="px-3 py-3">
                                <span x-show="k.kunci === 'terkunci'" class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2 py-0.5 text-xs font-semibold text-success"><x-ui.icon name="lock" :size="12" :stroke="2.2" />Terkunci</span>
                                <span x-show="k.kunci === 'koreksi'" class="inline-flex items-center gap-1 rounded-md bg-violet-soft px-2 py-0.5 text-xs font-semibold text-violet"><x-ui.icon name="unlock" :size="12" :stroke="2.2" />Koreksi</span>
                                <span x-show="k.kunci === 'terbuka'" :class="$store.ref.badge('neutral')">Terbuka</span>
                            </td>
                            <td class="px-3 py-3"><span :class="$store.ref.badge(k.pdf ? 'success' : 'warning')" x-text="k.pdf ? `${k.jumlah}/${k.jumlah} dibuat` : 'Belum dibuat'"></span></td>
                            <td class="px-3 py-3">
                                <span x-show="k.terbit" class="inline-flex items-center gap-1 rounded-md bg-success-soft px-2 py-0.5 text-xs font-semibold text-success"><x-ui.icon name="check" :size="12" :stroke="2.4" /><span x-text="`Terbit · ${k.terbit}`"></span></span>
                                <span x-show="!k.terbit" :class="$store.ref.badge('neutral')">Belum terbit</span>
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                <x-ui.button variant="secondary" size="sm" icon="undo" x-show="k.terbit" x-on:click="tarik(k)">Tarik terbitan</x-ui.button>
                                <x-ui.button size="sm" icon="megaphone" x-show="bisa(k)" x-on:click="minta([k.kelas])">Terbitkan</x-ui.button>
                                <span x-show="!k.terbit && !bisa(k)" class="group relative inline-block">
                                    <x-ui.button variant="soft" size="sm" icon="megaphone" disabled>Terbitkan</x-ui.button>
                                    <span role="tooltip" class="pointer-events-none absolute right-0 bottom-full z-10 mb-1.5 hidden rounded-md bg-toast px-2.5 py-1.5 text-xs whitespace-nowrap text-white group-hover:block">Kunci nilai dan buat PDF terlebih dahulu</span>
                                </span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!tampil.length"><td colspan="7" class="px-5 py-10 text-center text-sm text-muted">Tidak ada kelas yang cocok.</td></tr>
                </tbody>
            </table>
        </div>
        <p class="border-t border-line-soft px-5 py-3 text-xs text-muted" x-text="`${tampil.length} kelas · ${kelas.filter((k) => k.terbit).length} sudah terbit`"></p>
    </x-ui.card>

    {{-- Bilah pilihan massal --}}
    <div x-show="dipilih.length" x-cloak x-transition.opacity
         class="fixed bottom-6 left-1/2 z-30 flex -translate-x-1/2 items-center gap-5 rounded-card bg-toast py-2.5 pr-2.5 pl-5 text-sm text-white shadow-pop lg:left-[calc(50%+120px)]"
         role="region" aria-label="Kelas dipilih">
        <span><strong x-text="`${dipilih.length} kelas dipilih`"></strong> <span class="ml-2 text-[13px] text-white/70" x-text="`${dipilih.join(', ')} · ${dipilih.length * 32} rapor`"></span></span>
        <button type="button" class="text-[13px] font-semibold text-white/90 hover:text-white" x-on:click="dipilih = []">Batal pilih</button>
        <x-ui.button icon="megaphone" x-on:click="minta([...dipilih])">Terbitkan yang dipilih</x-ui.button>
    </div>

    {{-- 7.04 Konfirmasi terbitkan --}}
    <x-ui.modal name="terbitkan" width="max-w-[440px]">
        <x-slot:icon><span class="flex size-11 items-center justify-center rounded-full bg-primary-soft text-primary-strong"><x-ui.icon name="megaphone" :size="19" /></span></x-slot:icon>
        <h2 class="text-lg font-bold text-ink" x-text="target.length === 1 ? `Terbitkan ${labelJenis} kelas ${target[0]}?` : `Terbitkan ${labelJenis} ${target.length} kelas?`"></h2>
        <p class="mt-3 text-sm text-ink-2">Rapor akan langsung dapat dilihat di portal siswa dan orang tua.</p>
        <div class="mt-4 rounded-control border border-success/30 bg-success-soft/50 px-3.5 py-2.5 text-[13px] text-success">
            <p class="flex items-center gap-1.5 font-bold"><x-ui.icon name="check-circle" :size="14" /><span x-text="`${jumlahSiswa} siswa · ${jumlahSiswa} PDF siap`"></span></p>
            <p class="mt-1 flex items-center gap-1.5"><x-ui.icon name="user" :size="14" /><span x-text="`Akan terlihat oleh ${jumlahSiswa} siswa dan ${jumlahSiswa - tanpaOrtu.length} akun orang tua terhubung`"></span></p>
        </div>
        <p x-show="tanpaOrtu.length" class="mt-3 flex items-center gap-2 rounded-control border border-orange-200 bg-warning-faint px-3.5 py-2.5 text-[13px] text-warning">
            <x-ui.icon name="alert" :size="14" /><span x-text="`${tanpaOrtu.length} siswa belum memiliki akun orang tua (${tanpaOrtu.join(', ')}).`"></span>
        </p>
        <label class="mt-3 flex cursor-pointer items-center gap-3 rounded-control border border-primary-soft bg-primary-faint px-3.5 py-2.5">
            <input type="checkbox" class="size-4 accent-primary" x-model="notifikasi">
            <span class="text-[13px] font-semibold text-ink">Kirim notifikasi ke portal siswa dan orang tua (A4)</span>
        </label>
        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'terbitkan')">Batal</x-ui.button>
            <x-ui.button size="lg" icon="megaphone" x-on:click="terbitkan()">Terbitkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
