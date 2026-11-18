@extends('layouts.app')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@section('content')
<div class="space-y-5 p-7" x-data="penilaianAkademik(@js($mode))">
    <x-ui.page-header :title="$title" />
    <p x-show="error" x-text="error" role="alert" class="rounded-control bg-danger-soft px-3 py-2 text-danger"></p>
    <x-ui.card>
        <label class="text-sm font-semibold">Periode
            <select aria-label="Periode" x-model="periodId" x-on:change="run(() => load())" :disabled="busy" class="ml-2 rounded-control border border-line px-3 py-2">
                <template x-for="p in periods" :key="p.id_periode"><option :value="p.id_periode" x-text="p.jenis"></option></template>
            </select>
        </label>
        <span class="ml-3 text-sm text-muted" x-text="period?.status_efektif.replaceAll('_', ' ')"></span>
        @if ($mode === 'periode')
            <form x-show="period" class="mt-4 flex flex-wrap items-end gap-3" x-on:submit.prevent="mutate(path, 'PUT', { tgl_buka: openAt + '+07:00', tgl_tutup: closeAt + '+07:00', tingkat: levels })">
                <label class="text-sm">Mulai (WIB)<input type="datetime-local" required x-model="openAt" :disabled="busy" class="block rounded-control border border-line p-2"></label>
                <label class="text-sm">Selesai (WIB)<input type="datetime-local" required x-model="closeAt" :disabled="busy" class="block rounded-control border border-line p-2"></label>
                <fieldset class="text-sm" :disabled="busy"><legend>Tingkat</legend>
                    @foreach (['X', 'XI', 'XII'] as $tingkat)
                        <label class="mr-2"><input type="checkbox" value="{{ $tingkat }}" x-model="levels"> {{ $tingkat }}</label>
                    @endforeach
                </fieldset>
                <x-ui.button type="submit" x-bind:disabled="busy || period?.status_efektif === 'dikunci'">Simpan jadwal</x-ui.button>
                <x-ui.button variant="secondary" x-show="period?.status_efektif === 'dibuka'" x-bind:disabled="busy" x-on:click="mutate(path + '/tutup')">Tutup periode</x-ui.button>
            </form>
        @endif
    </x-ui.card>
    <p x-show="!busy && !rows.length && !error" class="text-muted">Belum ada paket nilai untuk periode ini.</p>
    @if ($mode === 'periode')
        <template x-for="row in rows" :key="row.id_kelas">
            <x-ui.card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><strong x-text="row.kelas"></strong><p class="text-sm text-muted" x-text="(row.wali_kelas ?? 'Wali belum ditetapkan') + ' · ' + row.status.replaceAll('_', ' ')"></p></div>
                    <x-ui.button x-bind:disabled="busy || row.status !== 'siap_dikunci'" x-on:click="mutate(path + '/kelas/' + row.id_kelas + '/kunci')">Kunci nilai kelas</x-ui.button>
                </div>
                <div class="mt-3 divide-y divide-line-soft">
                    <template x-for="paket in row.paket" :key="paket.id_paket_nilai">
                        <div class="flex justify-between gap-3 py-2 text-sm"><span x-text="paket.mapel"></span><span x-text="paket.status.replaceAll('_', ' ') + (paket.lengkap ? ' · lengkap' : ' · belum lengkap')"></span></div>
                    </template>
                </div>
            </x-ui.card>
        </template>
    @else
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <template x-for="row in rows" :key="row.id_paket_nilai">
                <x-ui.card>
                    <strong x-text="row.mapel"></strong><p class="text-sm text-muted" x-text="row.kelas.nama + ' · ' + row.guru.join(', ')"></p>
                    <p class="my-2 text-sm" x-text="row.status.replaceAll('_', ' ')"></p>
                    <x-ui.button size="sm" variant="secondary" x-bind:disabled="busy" x-on:click="openDetail(row.id_paket_nilai)">Lihat nilai</x-ui.button>
                </x-ui.card>
            </template>
        </div>
        <template x-if="detail">
            <x-ui.card>
                <h2 class="text-lg font-bold" x-text="detail.mapel + ' · ' + detail.kelas"></h2>
                <p class="text-sm text-muted" x-text="detail.kelengkapan.sel_terisi + '/' + detail.kelengkapan.sel_wajib + ' nilai wajib terisi'"></p>
                <div class="my-3 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-surface-2"><tr><th class="p-2">Siswa</th><template x-for="t in detail.tp" :key="t.id_tp"><th class="p-2" :title="t.deskripsi" x-text="'TP' + t.urutan"></th></template><th class="p-2">Rata-rata</th></tr></thead>
                        <tbody class="divide-y divide-line-soft">
                            <template x-for="row in detail.siswa" :key="row.siswa.id_siswa">
                                <tr><td class="p-2" x-text="row.siswa.nama"></td><template x-for="t in detail.tp" :key="t.id_tp"><td class="p-2" x-text="row.nilai[t.id_tp] ?? '–'"></td></template><td class="p-2" x-text="(detail.periode.jenis === 'STS' ? row.nilai_sts : row.nilai_akhir) ?? '–'"></td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <ul class="my-3 text-sm text-muted">
                    <template x-for="r in detail.riwayat" :key="r.id_verifikasi_nilai"><li x-text="r.aksi + ' · ' + (r.user?.nama ?? '—') + (r.catatan ? ' · ' + r.catatan : '')"></li></template>
                </ul>
                <div x-show="['terkirim', 'disetujui'].includes(detail.paket.status)" class="space-y-2">
                    <label class="block text-sm">Catatan verifikasi<textarea x-model="note" maxlength="500" class="mt-1 block w-full rounded-control border border-line p-2"></textarea></label>
                    <x-ui.button x-show="detail.paket.status === 'terkirim'" x-bind:disabled="busy" x-on:click="mutate('/wali/paket/' + detail.paket.id_paket_nilai + '/setujui', 'POST', { catatan: note || null })">Setujui</x-ui.button>
                    <x-ui.button variant="secondary" x-bind:disabled="busy || note.trim().length < 5" x-on:click="mutate('/wali/paket/' + detail.paket.id_paket_nilai + '/revisi', 'POST', { catatan: note })">Minta revisi</x-ui.button>
                </div>
            </x-ui.card>
        </template>
    @endif
</div>
@endsection
