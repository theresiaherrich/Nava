@extends('layouts.app')
@section('title', 'Predikat Ekstrakurikuler')
@push('styles')
    @vite('resources/js/academic.js')
@endpush
@section('content')
<div class="space-y-4 p-7" x-data="ekskulAkademik">
    <x-ui.page-header title="Predikat Ekstrakurikuler" />
    <p x-show="error" x-text="error" role="alert" class="text-danger"></p>
    <x-ui.card>
        <label class="text-sm font-semibold">Ekstrakurikuler
            <select aria-label="Ekstrakurikuler" x-model="id" x-on:change="run(() => load())" :disabled="busy" class="ml-2 rounded-control border border-line px-3 py-2">
                <template x-for="e in options" :key="e.id_ekskul"><option :value="e.id_ekskul" x-text="e.nama"></option></template>
            </select>
        </label>
        <form x-on:submit.prevent="save()" class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead class="bg-surface-2"><tr><th class="p-2">Siswa</th><th class="p-2">Kelas</th><th class="p-2">Predikat</th><th class="p-2">Catatan</th></tr></thead>
                <tbody class="divide-y divide-line-soft">
                    <template x-for="p in participants" :key="p.id_peserta_ekskul">
                        <tr>
                            <td class="p-2" x-text="p.siswa.nama"></td><td class="p-2" x-text="p.kelas.nama"></td>
                            <td class="p-2"><select x-model="p.predikat" :aria-label="'Predikat ' + p.siswa.nama" class="rounded-control border border-line p-1"><option value="">–</option><option>SB</option><option>B</option><option>K</option></select></td>
                            <td class="p-2"><input x-model="p.catatan" :aria-label="'Catatan ' + p.siswa.nama" maxlength="255" class="w-full rounded-control border border-line px-2 py-1"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <p x-show="!busy && !participants.length" class="my-3 text-sm text-muted">Belum ada peserta.</p>
            <x-ui.button type="submit" class="mt-4" x-bind:disabled="busy || !participants.length">Simpan predikat</x-ui.button>
        </form>
    </x-ui.card>
</div>
@endsection
