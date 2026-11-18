@extends('layouts.app')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@section('content')
<div class="space-y-5 p-7" x-data="raporAkademik(@js($prefix), @js($dkn ?? false))">
    <x-ui.page-header :title="$title" />
    <p x-show="error" x-text="error" role="alert" class="rounded-control bg-danger-soft px-3 py-2 text-danger"></p>
    <x-ui.card class="flex flex-wrap items-center gap-4">
        <label class="text-sm font-semibold">Kelas
            <select aria-label="Kelas" x-model="classId" x-on:change="run(() => load())" :disabled="busy" class="ml-2 rounded-control border border-line px-3 py-2">
                <template x-for="k in classes" :key="k.id_kelas"><option :value="k.id_kelas" x-text="k.nama"></option></template>
            </select>
        </label>
        <label class="text-sm font-semibold">Periode
            <select aria-label="Periode" x-model="periodId" x-on:change="run(() => load())" :disabled="busy" class="ml-2 rounded-control border border-line px-3 py-2">
                <template x-for="p in periods" :key="p.id_periode"><option :value="p.id_periode" x-text="p.jenis"></option></template>
            </select>
        </label>
        @if ($dkn ?? false)
            <a x-show="data" class="font-semibold text-link" :href="'/api' + base + '/ekspor' + params + '&format=xlsx'">Ekspor Excel</a>
            <a x-show="data" class="font-semibold text-link" :href="'/api' + base + '/ekspor' + params + '&format=pdf'">Ekspor PDF</a>
        @else
            <x-ui.button variant="secondary" x-bind:disabled="busy || !data?.status_nilai.dikunci" x-on:click="renderClass('pdf')">PDF satu kelas</x-ui.button>
            <x-ui.button variant="secondary" x-bind:disabled="busy || !data?.status_nilai.dikunci" x-on:click="renderClass('zip')">ZIP satu kelas</x-ui.button>
            @if ($prefix === 'kurikulum')
                <x-ui.button x-bind:disabled="busy || !data?.status_nilai.dikunci || !data?.siswa.some(s => s.versi[0]?.status === 'draf')" x-on:click="publish()">Terbitkan rapor kelas</x-ui.button>
            @endif
        @endif
    </x-ui.card>
    <p x-show="!busy && !error && !classId" class="text-muted">Belum ada kelas pada semester aktif.</p>
    <template x-if="data">
        <x-ui.card class="overflow-x-auto">
            @if ($dkn ?? false)
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-2"><tr><th class="p-2">NIS</th><th class="p-2">Siswa</th><template x-for="m in data.mapel" :key="m.id_rombel"><th class="p-2" x-text="m.mapel"></th></template><th class="p-2">Rata-rata</th><th class="p-2">Peringkat</th></tr></thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="row in data.siswa" :key="row.id_riwayat_kelas">
                            <tr><td class="p-2" x-text="row.siswa.nis"></td><td class="p-2" x-text="row.siswa.nama"></td><template x-for="m in data.mapel" :key="m.id_rombel"><td class="p-2" x-text="row.nilai[m.id_rombel] ?? '–'"></td></template><td class="p-2" x-text="row.rata_rata ?? '–'"></td><td class="p-2" x-text="row.peringkat ?? '–'"></td></tr>
                        </template>
                    </tbody>
                </table>
            @else
                <p class="mb-3 text-sm" x-text="data.status_nilai.dikunci ? 'Semua nilai kelas dikunci.' : 'Menunggu penguncian nilai untuk mencetak rapor.'"></p>
                <table class="w-full min-w-[480px] text-left text-sm">
                    <thead class="bg-surface-2"><tr><th class="p-2">Siswa</th><th class="p-2">Versi rapor</th><th class="p-2">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-line-soft">
                        <template x-for="row in data.siswa" :key="row.id_riwayat_kelas">
                            <tr>
                                <td class="p-2"><strong x-text="row.siswa.nama"></strong><p class="text-xs text-muted" x-text="row.siswa.nis"></p></td>
                                <td class="p-2">
                                    <span x-show="!row.versi.length" class="text-muted">Belum dicetak</span>
                                    <template x-for="v in row.versi" :key="v.id_rapor">
                                        <div class="flex flex-wrap gap-2 py-1"><span x-text="'Versi ' + v.versi + ' · ' + v.status"></span>
                                            <a target="_blank" rel="noopener" class="font-semibold text-link" :href="'/api' + base + '/' + v.id_rapor + '/unduh?pratinjau=1'">Pratinjau</a>
                                            <a class="font-semibold text-link" :href="'/api' + base + '/' + v.id_rapor + '/unduh'">Unduh PDF</a>
                                        </div>
                                    </template>
                                </td>
                                <td class="p-2"><x-ui.button size="sm" variant="secondary" x-bind:disabled="busy || !data.status_nilai.dikunci" x-on:click="renderStudent(row.id_riwayat_kelas)">Bentuk rapor</x-ui.button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            @endif
        </x-ui.card>
    </template>
    @unless ($dkn ?? false)
        <x-ui.card x-show="history.length">
            <h2 class="mb-2 font-bold">Riwayat kelas</h2>
            <ul class="space-y-1 text-sm">
                <template x-for="log in history" :key="log.id_log">
                    <li x-text="log.aksi.replaceAll('_', ' ') + ' · ' + (log.user?.nama ?? '–') + ' · ' + new Date(log.created_at).toLocaleDateString('id-ID', { timeZone: 'Asia/Jakarta' })"></li>
                </template>
            </ul>
        </x-ui.card>
    @endunless
</div>
@endsection
