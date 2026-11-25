<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? 'SIAKAD SMAN 1')</title>
    @fonts
    {{-- Poppins dipakai CSS halaman modul A1 (Presensi). --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/js/app.js', 'resources/js/siakad-api.js'])
    @stack('styles')
</head>
@php
    $account = auth()->user();
    $roles = config('siakad.roles');
    $activeRole = \App\Support\AcademicAccess::peranAktif();
    $semester = \App\Models\Semester::aktif()?->loadMissing('tahunAjaran');
    $academicYear = $semester
        ? $semester->tahunAjaran->nama.' · '.ucfirst(strtolower($semester->nama))
        : '2026/2027 · Ganjil';
@endphp
<body class="bg-surface-2 font-sans text-ink" style="display:flex; margin:0; min-height:100vh;">

    <x-sidebar :academic-year="$academicYear" />

    <main style="flex:1; min-width:0; margin-left:220px;">
        <x-topbar
            :role="$roles[$activeRole] ?? ($account?->role ?? '')"
            :academic-year="$academicYear"
            :profile-url="route('password.change')" />
        @yield('content')
        <x-ui.toast-stack />
        <x-ui.confirm-dialog />
    </main>

    {{-- Memanggil JavaScript dari setiap halaman --}}
    @stack('scripts')

</body>
</html>
