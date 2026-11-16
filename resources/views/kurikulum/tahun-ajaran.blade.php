@extends('layouts.app')

@section('title', 'Tahun Ajaran & Semester')

@push('styles')
    @vite(['resources/js/kurikulum-tahun-ajaran.js'])
@endpush

@section('content')
<div class="px-7 pt-7 pb-10"
     x-data="tahunAjaran({ semester: @js($semester), bukaModal: @js(request('modal') === 'aktifkan') })">
    <x-ui.page-header title="Tahun Ajaran & Semester" role="Kurikulum"
                      subtitle="Atur semester aktif untuk seluruh modul SIAKAD.">
        <x-slot:actions>
            <x-ui.button icon="plus" size="lg" x-on:click="segeraHadir('Form tambah tahun ajaran')">Tambah Tahun Ajaran</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Semester aktif --}}
    <section class="flex items-center gap-4 rounded-card border border-primary-soft bg-primary-faint px-5 py-5" aria-label="Semester aktif">
        <span class="flex size-12 shrink-0 items-center justify-center rounded-control border border-primary-soft bg-surface text-link">
            <x-ui.icon name="calendar" :size="22" />
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-bold uppercase text-link">Semester aktif</p>
            <p class="text-[22px] font-bold text-info" x-text="`${aktif.tahun_ajaran} · ${aktif.semester}`"></p>
            <p class="text-[13px] text-ink-2" x-text="`${aktif.rentang} · dipakai oleh Presensi, Akademik, BK, dan Portal Orang Tua`"></p>
        </div>
        <x-ui.badge tone="info" icon="check" class="bg-primary-soft">Aktif</x-ui.badge>
    </section>

    <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1fr)_357px]">
        <x-ui.card :padding="false" class="overflow-hidden">
            <div class="px-5 pt-5 pb-3">
                <h2 class="text-[15px] font-bold text-ink">Daftar Tahun Ajaran</h2>
                <p class="text-xs text-muted" x-text="`${jumlahTahun} tahun ajaran · ${semester.length} semester`"></p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-[13px]">
                    <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                        <tr>
                            <th class="px-5 py-2.5">Tahun Ajaran</th>
                            <th class="px-3 py-2.5">Semester</th>
                            <th class="px-3 py-2.5">Mulai</th>
                            <th class="px-3 py-2.5">Selesai</th>
                            <th class="px-3 py-2.5">Status</th>
                            <th class="px-5 py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="s in semester" :key="s.id_semester">
                            <tr>
                                <td class="px-5 py-3.5 font-bold text-ink" x-text="s.tahun_ajaran"></td>
                                <td class="px-3 py-3.5 text-ink-2" x-text="s.semester"></td>
                                <td class="px-3 py-3.5 text-ink-2" x-text="s.mulai"></td>
                                <td class="px-3 py-3.5 text-ink-2" x-text="s.selesai"></td>
                                <td class="px-3 py-3.5">
                                    <span x-show="s.status === 'aktif'"><x-ui.badge tone="info">Aktif</x-ui.badge></span>
                                    <span x-show="s.status === 'akan_datang'"><x-ui.badge tone="outline">Akan datang</x-ui.badge></span>
                                    <span x-show="s.status === 'selesai'"><x-ui.badge>Selesai</x-ui.badge></span>
                                </td>
                                <td class="px-5 py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-ui.button variant="secondary" size="sm" x-show="s.status === 'akan_datang'"
                                                     x-on:click="konfirmasi(s)">Aktifkan</x-ui.button>
                                        <x-ui.icon-button icon="pencil" label="Ubah semester"
                                                          x-on:click="segeraHadir('Form ubah semester')" />
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        <x-ui.card class="self-start">
            <h2 class="text-[15px] font-bold text-ink">Ketentuan</h2>
            <ul class="mt-3 space-y-3 text-[13px] text-ink-2">
                <li class="flex gap-2.5"><x-ui.icon name="info" :size="15" class="mt-0.5 text-primary-strong" />Hanya satu semester yang dapat aktif.</li>
                <li class="flex gap-2.5"><x-ui.icon name="archive" :size="15" class="mt-0.5 text-success" />Data semester sebelumnya tetap tersimpan dan dapat dilihat di Arsip Rapor.</li>
                <li class="flex gap-2.5"><x-ui.icon name="alert" :size="15" class="mt-0.5 text-warning-strong" />Pastikan rapor semester berjalan sudah diterbitkan sebelum mengaktifkan semester berikutnya.</li>
            </ul>
        </x-ui.card>
    </div>

    {{-- 4.02 Konfirmasi aktifkan semester --}}
    <x-ui.modal name="aktifkan" width="max-w-[440px]">
        <x-slot:icon>
            <span class="flex size-11 items-center justify-center rounded-full bg-warning-soft text-warning-strong">
                <x-ui.icon name="calendar" :size="20" />
            </span>
        </x-slot:icon>

        <h2 class="text-lg font-bold text-ink" x-text="target ? `Aktifkan ${target.tahun_ajaran} ${target.semester}?` : ''"></h2>
        <p class="mt-3 text-sm text-ink-2" x-text="target ? `Seluruh menu akan memakai semester ${target.semester}. Pastikan rapor ${aktif.semester} sudah diterbitkan.` : ''"></p>

        <p class="mt-4 flex items-center gap-2 rounded-control border border-orange-200 bg-warning-faint px-3 py-2.5 text-[13px] text-warning">
            <x-ui.icon name="alert" :size="14" />
            <span>Rapor STS <span x-text="aktif.semester"></span>: <strong>{{ $ringkasan['rapor_sts_terbit'] }} dari {{ $ringkasan['jumlah_kelas'] }} kelas</strong> sudah terbit.</span>
        </p>

        <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-control border border-primary-soft bg-primary-faint px-3 py-2.5">
            <input type="checkbox" x-model="salinRombel" class="mt-0.5 size-4 accent-primary">
            <span>
                <span class="block text-[13px] font-semibold text-ink">Salin rombel dan wali kelas dari semester sebelumnya</span>
                <span class="block text-xs text-muted">{{ $ringkasan['jumlah_rombel'] }} rombel dan {{ $ringkasan['jumlah_wali'] }} wali kelas disalin, dapat diubah setelahnya</span>
            </span>
        </label>

        <x-slot:footer>
            <x-ui.button variant="secondary" size="lg" x-on:click="$dispatch('close-modal', 'aktifkan')">Batal</x-ui.button>
            <x-ui.button size="lg" x-on:click="aktifkan()">Aktifkan</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
@endsection
