
{{-- Layout Admin menggunakan sidebar dan CSS yang sama dengan Portal Orang Tua. --}}

@php
    $adminMenu = [
        [
            'code' => 'A4',
            'label' => 'Portal Orang Tua',
            'icon' => 'shield',
            'href' => '#',
            'pattern' => 'admin.*',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'grid',
                    'route' => 'admin.dashboard',
                    'pattern' => 'admin.dashboard'
                ],
                [
                    'label' => 'Manajemen Pengguna',
                    'icon' => 'id',
                    'route' => 'admin.users.index',
                    'pattern' => 'admin.users.*'
                ],
                [
                    'label' => 'Relasi Orang Tua dan Siswa',
                    'icon' => 'check',
                    'route' => 'admin.relations.index',
                    'pattern' => 'admin.relations.*'
                ],
                [
                    'label' => 'Notifikasi WhatsApp',
                    'icon' => 'file',
                    'route' => 'admin.notifications.index',
                    'pattern' => 'admin.notifications.*'
                ],
                [
                    'label' => 'Pengaturan',
                    'icon' => 'compass',
                    'route' => 'admin.settings.index',
                    'pattern' => 'admin.settings.*'
                ],
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Admin Portal Orang Tua') - SIAKAD SMAN 1</title>

    {{-- Font untuk sidebar --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    {{-- CSS utama aplikasi --}}
    @vite([
        'resources/css/app.css',
        'resources/css/portal-orang-tua.css',
        'resources/css/admin.css',
        'resources/css/sidebar.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>

<body style="display: flex; margin: 0; min-height: 100vh;">

    {{-- SIDEBAR ADMIN: menggunakan komponen yang sama dengan Portal Orang Tua --}}
    <x-sidebar
        :menu="$adminMenu"
        logout-route="admin.logout"
    />

    <main style="flex: 1; min-width: 0;">

        <div class="pt pt--admin">

            
{{-- TOPBAR ADMIN --}}
<header class="pt-topbar pt-admin-topbar">

    {{-- Hamburger --}}
    <button
        type="button"
        class="pt-burger"
        data-sidebar-toggle
        aria-label="Buka menu"
    >
        <x-portal.icon name="list" :size="18" />
    </button>

    {{-- Search --}}
    <form
        class="pt-search"
        action="{{ route('admin.users.index') }}"
        method="GET"
        role="search"
    >
        <x-portal.icon name="search" :size="15" />

        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari data siswa atau orang tua..."
            autocomplete="off"
            aria-label="Cari data pengguna"
        >
    </form>

    {{-- Notifikasi --}}
    <a
        href="{{ route('admin.notifications.index') }}"
        class="pt-btn pt-btn--ghost pt-btn--sm"
        aria-label="Notifikasi WhatsApp"
    >
        <x-portal.icon name="message" :size="18" />
        <span>Notifikasi</span>
    </a>

    <span class="pt-topbar__spacer"></span>

    {{-- Identitas Admin --}}
    <div class="pt-user">

        <span class="pt-avatar pt-avatar--sm">AD</span>

        <div class="pt-user__txt">
            <strong>
                {{ session('admin_username', 'Administrator') }}
            </strong>

            <span>Administrator</span>
        </div>

    </div>

</header>


            {{-- KONTEN HALAMAN ADMIN --}}
            <div class="pt-page pt-admin-page">

                @if (session('success'))
                    <div
                        class="pt-admin-alert pt-admin-alert--success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="pt-admin-alert pt-admin-alert--error"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')

            </div>

        </div>

    </main>

</body>
</html>
