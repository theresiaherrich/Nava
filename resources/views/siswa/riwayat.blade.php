@extends('layouts.app')

@section('title', 'Riwayat Rapor')

@push('styles')
    @vite(['resources/js/siswa.js'])
@endpush

@section('content')
<div class="px-4 pt-5 pb-28 md:px-7 md:pt-7 md:pb-10" x-data="riwayatSiswa">
    <x-ui.page-header title="Riwayat Rapor" role="Siswa" subtitle="Semua rapor resmi yang pernah diterbitkan untukmu." />

    <x-ui.card class="!py-3.5">
        <x-ui.filter-select label="Tahun ajaran" :options="['Semua', '2026/2027']" model="tahun" />
    </x-ui.card>

    <x-ui.card :padding="false" class="mt-4 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left text-[13px]">
                <thead class="bg-surface-2 text-xs font-semibold text-ink-2">
                    <tr><th class="px-5 py-3">Tahun Ajaran</th><th class="px-3 py-3">Semester</th><th class="px-3 py-3">Jenis Rapor</th><th class="px-3 py-3">Kelas</th><th class="px-3 py-3">Tanggal Terbit</th><th class="px-3 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-line-soft">
                    @foreach ($rapor as $x)
                        @php $label = "{$x['label']} Ganjil 2026/2027"; @endphp
                        <tr @class(['bg-violet-soft/30' => $diperbarui && $x['jenis'] === 'sts']) x-show="tahun === 'Semua' || tahun === '2026/2027'">
                            <td class="px-5 py-3 text-ink-2">2026/2027</td>
                            <td class="px-3 text-ink-2">Ganjil</td>
                            <td class="px-3">
                                <p @class(['font-bold text-ink' => $x['terbit'], 'font-semibold text-muted' => ! $x['terbit']])>{{ $x['label'] }}</p>
                                @if ($diperbarui && $x['jenis'] === 'sts')<x-ui.badge tone="violet" class="mt-1">Versi {{ $x['versi'] }} · diperbarui {{ $x['terbit'] }}</x-ui.badge>@endif
                            </td>
                            <td class="px-3 text-ink-2">{{ $kelas }}</td>
                            <td class="px-3 text-ink-2">{{ $x['terbit'] ?? '—' }}</td>
                            <td class="px-3">@if ($x['terbit'])<x-ui.badge tone="success" icon="check">Terbit</x-ui.badge>@else<x-ui.badge>Belum terbit</x-ui.badge>@endif</td>
                            <td class="px-5 py-2.5">
                                <div class="flex justify-end gap-2">
                                    @if ($x['terbit'])
                                        <x-ui.button variant="secondary" size="sm" icon="eye" :href="route('rapor-saya.index', ['jenis' => $x['jenis']] + request()->only('state'))" :aria-label="'Lihat '.$label">Lihat</x-ui.button>
                                        <x-ui.button size="sm" icon="download" x-on:click="unduh({{ \Illuminate\Support\Js::from($label) }})" :aria-label="'Unduh '.$label">Unduh</x-ui.button>
                                    @else
                                        <x-ui.button variant="soft" size="sm" icon="eye" disabled :aria-label="'Lihat '.$label.' (belum terbit)'">Lihat</x-ui.button>
                                        <x-ui.button variant="soft" size="sm" icon="download" disabled :aria-label="'Unduh '.$label.' (belum terbit)'">Unduh</x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($diperbarui)
                        <tr><td colspan="7" class="bg-violet-soft/50 px-5 py-2.5 text-[13px] text-violet"><span class="flex items-center gap-1.5"><x-ui.icon name="info" :size="14" />Sekolah melakukan koreksi nilai. Versi terbaru ditampilkan.</span></td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <p class="flex items-center gap-1.5 border-t border-line-soft px-5 py-3 text-xs text-muted"><x-ui.icon name="lock" :size="12" />Rapor yang sudah diterbitkan tersimpan permanen dan dapat diunduh kembali kapan saja.</p>
    </x-ui.card>
</div>
<x-siswa.bottom-nav />
@endsection
