@extends('layouts.app')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@php
    $tabs = [
        ['label' => 'Tahun Ajaran', 'path' => '/kurikulum/tahun-ajaran', 'columns' => ['Tahun Ajaran' => 'nama', 'Semester' => 'semester', 'Status' => 'aktif']],
        ['label' => 'Siswa', 'path' => '/master/siswa', 'columns' => ['NIS' => 'nis', 'Nama' => 'nama', 'Status' => 'aktif']],
        ['label' => 'Guru', 'path' => '/master/guru', 'columns' => ['Nama' => 'nama', 'NIP' => 'nip', 'Kepegawaian' => 'status_kepegawaian']],
        ['label' => 'Kelas', 'path' => '/master/kelas', 'columns' => ['Kelas' => 'nama', 'Tingkat' => 'tingkat', 'Fase' => 'fase']],
    ];
    if ($kurikulum ?? false) {
        $tabs[] = ['label' => 'Rombel dan Pengampu', 'path' => '/kurikulum/rombel', 'columns' => ['Rombel' => 'nama', 'Mapel' => 'mapel.nama', 'Guru' => 'guru', 'Peserta' => 'peserta_count']];
        $tabs[] = ['label' => 'Wali Kelas', 'path' => '/kurikulum/wali-kelas', 'columns' => ['Kelas' => 'nama', 'Wali' => 'guru.nama']];
    }
@endphp
@section('content')
<div class="space-y-4 p-7" x-data="masterAkademik(@js($tabs))">
    <x-ui.page-header :title="$title" />
    <p x-show="error" x-text="error" role="alert" class="text-danger"></p>
    <x-ui.card class="flex flex-wrap gap-2">
        <template x-for="(item, i) in tabs" :key="item.path">
            <button class="rounded-control border border-line px-3 py-2 text-sm font-semibold" :class="index === i && 'bg-primary-faint text-link'" :disabled="busy" x-on:click="choose(i)" x-text="item.label"></button>
        </template>
    </x-ui.card>
    <x-ui.card class="overflow-x-auto">
        <form x-show="meta" class="mb-4 flex gap-2" x-on:submit.prevent="indexPage = 1; run(() => load())">
            <input x-model="search" placeholder="Cari data" aria-label="Cari data" class="rounded-control border border-line px-3 py-1.5">
            <x-ui.button type="submit" x-bind:disabled="busy">Cari</x-ui.button>
        </form>
        <table class="w-full text-left text-sm">
            <thead class="bg-surface-2"><tr>
                <template x-for="label in Object.keys(tab.columns)" :key="label"><th class="p-2" x-text="label"></th></template>
            </tr></thead>
            <tbody class="divide-y divide-line-soft">
                <template x-for="(row, i) in data" :key="i"><tr>
                    <template x-for="keys in Object.values(tab.columns)" :key="keys"><td class="p-2" x-text="field(row, keys)"></td></template>
                </tr></template>
            </tbody>
        </table>
        <p x-show="!busy && !data.length && !error" class="py-3 text-sm text-muted">Belum ada data.</p>
        <div x-show="meta?.last_page > 1" class="mt-4 flex items-center gap-3">
            <x-ui.button size="sm" variant="secondary" x-bind:disabled="busy || indexPage <= 1" x-on:click="indexPage--; run(() => load())">Sebelumnya</x-ui.button>
            <span class="text-sm" x-text="indexPage + ' / ' + meta?.last_page"></span>
            <x-ui.button size="sm" variant="secondary" x-bind:disabled="busy || indexPage >= meta?.last_page" x-on:click="indexPage++; run(() => load())">Berikutnya</x-ui.button>
        </div>
    </x-ui.card>
</div>
@endsection
