{{--
    resources/views/portal-orang-tua/layout.blade.php
    Kerangka bersama 5 halaman Portal Orang Tua (A4). Memakai layouts.app (sidebar sudah ada di sana).

    Child view mengisi:  @section('title', '...')   dan   @section('portal') ... @endsection
    Variabel opsional:   $ortu (nama, peran, foto, inisial)
    Session:             toast => ['type' => success|error|info|warning, 'message' => '...']
--}}

@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/portal-orang-tua.css', 'resources/js/portal-orang-tua.js'])
@endpush

@section('content')
@php
    $ortu = $ortu ?? (object) ['nama' => 'Orang Tua', 'peran' => 'Wali Siswa', 'foto' => null, 'inisial' => 'OT'];
@endphp

<div class="pt">
    <header class="pt-topbar">
        <button type="button" class="pt-burger" data-sidebar-toggle aria-label="Buka menu">
            <x-portal.icon name="list" :size="18" />
        </button>

        <form class="pt-search" action="{{ route('portal.akademik') }}" method="GET" role="search" data-pt-search-form>
            <x-portal.icon name="search" :size="15" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari mata pelajaran...."
                   autocomplete="off" aria-label="Cari mata pelajaran" data-pt-search>
        </form>

        <a href="{{ route('portal.rapor.unduh') }}" class="pt-btn pt-btn--ghost pt-btn--sm">
            <x-portal.icon name="download" :size="14" /> Unduh Rapor
        </a>

        <span class="pt-topbar__spacer"></span>

        <div class="pt-user">
            <x-portal.avatar :src="$ortu->foto" :initials="$ortu->inisial" class="pt-avatar--sm" />
            <div class="pt-user__txt">
                <strong>{{ $ortu->nama }}</strong>
                <span>{{ $ortu->peran }}</span>
            </div>
        </div>
    </header>

    <div class="pt-page">
        @yield('portal')
    </div>

    @if (session('toast'))
        <div class="pt-toast pt-toast--{{ session('toast.type', 'success') }}" role="status" data-pt-toast>
            {{ session('toast.message') }}
        </div>
    @endif
</div>
@endsection
