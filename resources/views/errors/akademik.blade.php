@extends('layouts.app')
@section('title', 'Akses Akademik')
@section('content')
    <div class="p-7">
        <x-ui.page-header title="Halaman tidak dapat dibuka" :subtitle="$message" />
        <a href="/dashboard" class="font-semibold text-link">Kembali ke dashboard</a>
    </div>
@endsection
