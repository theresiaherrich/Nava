@extends('layouts.app')

@section('title', 'Akses ditolak')

@section('content')
<div class="flex justify-center px-4 pt-16 pb-28 md:pt-36 md:pb-10">
    <x-ui.card class="w-full max-w-[460px] !px-8 !py-11 text-center" role="alert">
        <span class="mx-auto flex size-[72px] items-center justify-center rounded-full bg-neutral-soft text-ink-2"><x-ui.icon name="lock" :size="30" /></span>
        <h1 class="mt-5 text-xl font-bold text-ink">Halaman tidak dapat diakses</h1>
        <p class="mt-2 text-sm text-ink-2">Anda hanya dapat melihat rapor milik sendiri yang sudah diterbitkan.</p>
        <x-ui.button icon="home" class="mt-5" :href="route('beranda-akademik.index')">Kembali ke Beranda</x-ui.button>
    </x-ui.card>
</div>
<x-siswa.bottom-nav />
@endsection
