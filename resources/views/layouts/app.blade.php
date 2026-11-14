<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $title ?? 'SIAKAD SMAN 1')</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/sidebar.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased text-gray-800" style="display:flex; margin:0; min-height:100vh;">

    <x-sidebar />

    @php
        /*
         * Data header (topbar). Urutan prioritas:
         *   1. @section('topbar_name' | 'topbar_role' | 'topbar_initials' | 'topbar_year', '...') di halaman
         *   2. variabel $topbar = ['name' => ..., 'role' => ..., ...] dari controller / @php di halaman
         *   3. user login (auth) 4. dummy wali kelas
         * TODO (backend): ganti dummy dengan data user login, tahun ajaran aktif, dan notifikasi dari database.
         */
        $tbUser = auth()->user();
        $tbRole = $tbUser && method_exists($tbUser, 'getRoleNames')
            ? \Illuminate\Support\Str::headline((string) $tbUser->getRoleNames()->first())
            : null;

        $topbar = array_merge([
            'name'          => $tbUser?->name ?? 'Siti Rahmawati, S.Pd.',
            'role'          => $tbUser ? ($tbRole ?? '') : 'Wali Kelas · X-B',
            'initials'      => null,
            'academicYear'  => '2026/2027 · Ganjil',
            'academicYears' => ['2026/2027 · Ganjil', '2025/2026 · Genap', '2025/2026 · Ganjil'],
            'notifications' => [],
        ], is_array($topbar ?? null) ? $topbar : []);

        // @section('...', 'nilai') tersimpan ter-escape; kembalikan ke teks biasa agar tidak di-escape dua kali.
        $tbSection = fn (string $key, $fallback) => $__env->hasSection($key)
            ? html_entity_decode(trim($__env->yieldContent($key)), ENT_QUOTES)
            : $fallback;
    @endphp

    <main style="flex:1; min-width:0;">
        @unless ($hideTopbar ?? false)
            <x-topbar
                :name="$tbSection('topbar_name', $topbar['name'])"
                :role="$tbSection('topbar_role', $topbar['role'])"
                :initials="$tbSection('topbar_initials', $topbar['initials'])"
                :academic-year="$tbSection('topbar_year', $topbar['academicYear'])"
                :academic-years="$topbar['academicYears']"
                :notifications="$topbar['notifications']"
            />
        @endunless
        @yield('content')
    </main>

</body>
</html>