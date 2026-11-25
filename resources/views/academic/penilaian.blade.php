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
            <form x-show="period" class="mt-4 flex flex-wrap items-start gap-3" novalidate x-on:submit.prevent="simpanJadwal()">
                @foreach (['openAt' => ['periode-mulai', 'Mulai (WIB)'], 'closeAt' => ['periode-selesai', 'Selesai (WIB)']] as $model => [$id, $label])
                    <div>
                        <label for="{{ $id }}" class="text-sm">{{ $label }}</label>
                        <input id="{{ $id }}" type="datetime-local" required min="2000-01-01T00:00" max="2099-12-31T23:59"
                               x-model="{{ $model }}" x-on:input="tandai('{{ $model }}')" x-on:blur="tandai('{{ $model }}')" :disabled="busy || periodeSelesai"
                               :aria-invalid="errors.{{ $model }} ? 'true' : 'false'" aria-describedby="{{ $id }}-error"
                               class="block rounded-control border p-2" :class="errors.{{ $model }} ? 'border-danger' : 'border-line'">
                        <p id="{{ $id }}-error" x-show="errors.{{ $model }}" x-cloak class="mt-1.5 flex items-center gap-1 text-xs font-medium text-danger" role="alert">
                            <x-ui.icon name="alert-circle" :size="13" />
                            <span x-text="errors.{{ $model }}"></span>
                        </p>
                    </div>
                @endforeach
                <fieldset class="text-sm" :disabled="busy || periodeSelesai"><legend>Tingkat</legend>
                    @foreach (['X', 'XI', 'XII'] as $tingkat)
                        <label class="mr-2"><input type="checkbox" value="{{ $tingkat }}" x-model="levels"> {{ $tingkat }}</label>
                    @endforeach
                </fieldset>
                <div class="flex flex-col gap-1.5 pt-5" x-show="tampilSimpan || tampilTutup">
                    {{-- Belum dijadwalkan / jadwal diubah: simpan. Sudah tersimpan: aksi utama tutup periode. --}}
                    <x-ui.button type="submit" x-show="tampilSimpan" x-bind:disabled="busy || !jadwalValid">Simpan jadwal</x-ui.button>
                    <x-ui.button x-show="tampilTutup" x-bind:disabled="busy || period?.status_efektif !== 'dibuka'" x-on:click="tutupPeriode()">Tutup periode</x-ui.button>
                    <p x-show="tampilTutup && period?.status_efektif === 'belum_dibuka'" class="text-xs text-muted">Periode bisa ditutup setelah waktu mulai.</p>
                </div>
            </form>
            <x-ui.alert x-show="period?.status_efektif === 'ditutup'" x-cloak tone="info" icon="clock" title="Periode ditutup" class="mt-4">
                Input nilai sudah ditutup. Kunci nilai per kelas di bawah setelah semua paket siap.
            </x-ui.alert>
            <x-ui.alert x-show="period?.status_efektif === 'dikunci'" x-cloak tone="amber" title="Periode dikunci" class="mt-4">
                Nilai periode ini sudah dikunci dan tidak dapat diubah.
            </x-ui.alert>
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
