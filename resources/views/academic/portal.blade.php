@extends('layouts.app')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@section('content')
<div class="space-y-5 p-6 sm:p-7" x-data="portalAkademik(@js($prefix), @js($mode), @js(request()->route('rombel')))">
    <x-ui.page-header :title="match ($mode) { 'beranda' => 'Beranda Akademik', 'nilai' => 'Nilai per Mata Pelajaran', 'detail' => 'Nilai per Tujuan Pembelajaran', default => 'Rapor Digital' }" />
    <p x-show="error" x-text="error" role="alert" class="rounded-control bg-danger-soft px-3 py-2 text-danger"></p>
    @if ($prefix === 'orang-tua')
        <label x-show="students.length" class="text-sm font-semibold">Anak
            <select x-model="student" @change="run(() => load())" :disabled="busy" class="ml-2 rounded-control border border-line bg-surface px-3 py-2">
                <template x-for="s in students" :key="s.id_siswa"><option :value="s.id_siswa" x-text="s.nama"></option></template>
            </select>
        </label>
    @endif
    <template x-if="data">
        <div class="space-y-3">
            @if ($mode === 'beranda')
                <x-ui.card>
                    <h2 class="text-xl font-bold" x-text="data.siswa.nama"></h2>
                    <p class="text-sm text-muted" x-text="(data.kelas?.nama ?? 'Belum terdaftar di kelas') + ' · Semester ' + data.semester.nama"></p>
                    <p class="mt-2 text-sm" x-text="'Rata-rata nilai mapel ' + (data.rata_rata ?? '–')"></p>
                    <div class="mt-3 flex gap-4 text-sm font-semibold text-link">
                        <a :href="'/' + prefix + '/nilai' + params">Lihat nilai</a>
                        <a :href="'/' + prefix + '/rapor' + params">Lihat rapor</a>
                    </div>
                </x-ui.card>
            @elseif ($mode === 'nilai')
                <p x-show="!data.length" class="text-muted">Belum ada mata pelajaran pada semester aktif.</p>
                <div class="grid gap-3 md:grid-cols-2">
                    <template x-for="r in data" :key="r.id_rombel">
                        <x-ui.card>
                            <h2 class="font-bold" x-text="r.mapel.nama"></h2>
                            <dl class="my-3 grid grid-cols-3 gap-2 text-sm">
                                <div><dt class="text-muted">STS</dt><dd x-text="r.nilai_sts ?? '–'"></dd></div>
                                <div><dt class="text-muted">SAS</dt><dd x-text="r.nilai_sas ?? '–'"></dd></div>
                                <div><dt class="text-muted">Rata-rata TP</dt><dd class="font-bold" x-text="r.nilai_akhir ?? '–'"></dd></div>
                            </dl>
                            <a class="font-semibold text-link" :href="'/' + prefix + '/nilai/' + r.id_rombel + params">Lihat detail TP</a>
                        </x-ui.card>
                    </template>
                </div>
            @elseif ($mode === 'detail')
                <x-ui.card>
                    <h2 class="mb-3 font-bold" x-text="data.mapel.nama"></h2>
                    <p class="mb-3 text-sm" x-text="'Rata-rata TP ' + (data.ringkasan.nilai_akhir ?? '–')"></p>
                    <ul class="divide-y divide-line-soft">
                        <template x-for="t in data.tp" :key="t.id_tp">
                            <li class="flex items-start justify-between gap-3 py-3 text-sm">
                                <span x-text="'TP' + t.urutan + ' · ' + t.deskripsi"></span>
                                <strong :class="t.perlu_penguatan ? 'text-warning-strong' : ''" x-text="t.nilai ?? '–'"></strong>
                            </li>
                        </template>
                    </ul>
                </x-ui.card>
            @else
                <p x-show="!data.length" class="text-muted">Belum ada rapor yang diterbitkan.</p>
                <template x-for="r in data" :key="r.id_rapor">
                    <x-ui.card class="flex flex-wrap items-center justify-between gap-3">
                        <div><h2 class="font-bold" x-text="r.jenis === 'STS' ? 'Rapor STS' : 'Rapor Semester'"></h2>
                            <p class="text-sm text-muted" x-text="r.tahun_ajaran + ' · ' + r.semester + ' · Versi ' + r.versi"></p>
                        </div>
                        <div class="flex gap-4 text-sm font-semibold text-link">
                            <a target="_blank" rel="noopener" :href="'/api/portal/rapor/' + r.id_rapor + '/unduh?pratinjau=1'">Pratinjau</a>
                            <a :href="'/api/portal/rapor/' + r.id_rapor + '/unduh'">Unduh PDF</a>
                        </div>
                    </x-ui.card>
                </template>
            @endif
        </div>
    </template>
</div>
@endsection
