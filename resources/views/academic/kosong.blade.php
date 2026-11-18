@extends('layouts.app')
@section('content')
    <div class="p-7">
        <x-ui.page-header :title="$title" />
        <x-ui.empty-state icon="users" title="Belum ada rombel" description="Hubungi Admin Kurikulum untuk memeriksa semester aktif dan pembagian mengajar." />
    </div>
@endsection
