@extends('layouts.app')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@section('content')
<div class="space-y-5 p-7" x-data="dashboardAkademik(@js($endpoint ?? null))">
    <x-ui.page-header :title="$title" :subtitle="'Selamat datang, '.auth()->user()->nama" />
    <p x-show="error" x-text="error" role="alert" class="text-danger"></p>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <template x-for="[label, value] in counts" :key="label">
            <x-ui.card><p class="text-sm capitalize text-muted" x-text="label.replaceAll('_', ' ')"></p><strong class="text-2xl" x-text="value"></strong></x-ui.card>
        </template>
    </div>
    <template x-for="rombel in data.rombel ?? []" :key="rombel.id_rombel">
        <x-ui.card class="flex items-center justify-between gap-3">
            <div><strong x-text="rombel.nama"></strong><p class="text-sm text-muted" x-text="rombel.jumlah_siswa + ' peserta · ' + rombel.jumlah_tp + ' TP'"></p></div>
            <a class="font-semibold text-link" :href="'/guru/input-nilai?rombel=' + rombel.id_rombel">Input nilai</a>
        </x-ui.card>
    </template>
    <template x-for="kelas in Array.isArray(data.kelas) ? data.kelas : []" :key="kelas.id_kelas">
        <x-ui.card><strong x-text="kelas.nama"></strong><p class="text-sm text-muted" x-text="kelas.jumlah_siswa + ' siswa'"></p></x-ui.card>
    </template>
</div>
@endsection
