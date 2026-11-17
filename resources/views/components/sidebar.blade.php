@props([
    'menu' => null,
    'academicYear' => '2026/2027 • Ganjil',
    'role' => null,
])

@php
    $role = strtolower(str_replace([' ', '-'], '_', $role ?? request('role', 'wali_kelas')));

    $presensiMenus = [
        'siswa' => [
            ['label' => 'Presensi QR Code', 'icon' => 'qr', 'route' => 'presensi.qr-code'],
            ['label' => 'Presensi Manual', 'icon' => 'map-pin', 'route' => 'presensi.manual'],
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat'],
        ],
        'wali_kelas' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat'],
            ['label' => 'Edit Presensi', 'icon' => 'pencil', 'route' => 'presensi.edit'],
        ],
        'budaya_positif' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat'],
            ['label' => 'Scan Presensi', 'icon' => 'scan', 'route' => 'presensi.scan'],
        ],
        'admin' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat'],
            ['label' => 'Edit Presensi', 'icon' => 'pencil', 'route' => 'presensi.edit'],
            ['label' => 'Scan Presensi', 'icon' => 'scan', 'route' => 'presensi.scan'],
        ],
    ];

    $presensiItems = $presensiMenus[$role] ?? $presensiMenus['wali_kelas'];

    // Menu default (Wali Kelas). Backend bisa mengirim $menu sendiri lewat <x-sidebar :menu="$menu" />
    $defaultMenu = [
        [
            'code' => 'A1', 'label' => 'Presensi', 'icon' => 'calendar', 'sub_title' => 'Presensi', 'items' => $presensiItems,
        ],
        [
            'code' => 'A2', 'label' => 'Akademik', 'icon' => 'file', 'sub_title' => 'Wali Kelas',
            'items' => [
                ['label' => 'Dashboard Kelas', 'icon' => 'grid', 'route' => 'dashboard-kelas.index', 'pattern' => 'dashboard-kelas.*'],
                ['label' => 'Verifikasi Nilai',  'icon' => 'check',   'route' => 'verifikasi-nilai.index', 'pattern' => 'verifikasi-nilai.*'],
                ['label' => 'Pelengkap Rapor',   'icon' => 'pencil',  'route' => null],
                ['label' => 'Rekap Nilai (DKN)', 'icon' => 'table',   'route' => 'rekap-nilai.index',      'pattern' => 'rekap-nilai.*'],
                ['label' => 'Cetak Rapor',       'icon' => 'printer', 'route' => null],
            ],
        ],
        [
            'code' => 'A3', 'label' => 'BK', 'icon' => 'shield', 'href' => '#',
        ],
        [
            'code' => 'A4', 'label' => 'Portal Orang Tua', 'icon' => 'home', 'href' => '#',
        ],
    ];

    $menu = $menu ?? $defaultMenu;

    $resolve  = fn (?string $route) => ($route && \Illuminate\Support\Facades\Route::has($route)) ? route($route) : '#';
    $isActive = fn (?string $pattern) => $pattern && request()->routeIs($pattern);

    $icons = [
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'home'     => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'pencil'   => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/>',
        'table'    => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/>',
        'printer'  => '<path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'chev'     => '<path d="m6 9 6 6 6-6"/>',
        'qr'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v2M17 20h4M20 18v3"/>',
        'map-pin'  => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>',
        'history'  => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        'scan'     => '<path d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4M4 12h16"/>',
        // Ikon menu sesuai desain sidebar per peran
        'key'       => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.8-9.8M17 6l3 3M14.5 8.5l2 2"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users'     => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/>',
        'building'  => '<path d="M3 21h18M5 21V10l7-5 7 5v11"/><path d="M9 21v-5h6v5M9 12h.01M15 12h.01"/>',
        'link'      => '<path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/>',
        'book'      => '<path d="M5 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5z"/><path d="M5 3v18M9 7h6"/>',
        'layers'    => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
        'user-star' => '<circle cx="10" cy="8" r="4"/><path d="M3 21a7 7 0 0 1 10.5-6"/><path d="m18 14 1.2 2.4 2.6.4-1.9 1.8.5 2.6-2.4-1.3-2.4 1.3.5-2.6-1.9-1.8 2.6-.4z"/>',
        'lock'      => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'sliders'   => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'megaphone' => '<path d="M3 11v2a1 1 0 0 0 1 1h3l6 4V6L7 10H4a1 1 0 0 0-1 1z"/><path d="M17 9a4 4 0 0 1 0 6M19.5 6.5a8 8 0 0 1 0 11"/>',
        'archive'   => '<rect x="2" y="3" width="20" height="5" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M10 12h4"/>',
        'target'    => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h8M8 9h2"/>',
    ];

    $svg = fn (string $name, string $class = 'sb-icon') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? '').'</svg>';
@endphp

<aside class="sb" id="app-sidebar" aria-label="Navigasi utama">
    <a href="{{ url('/') }}" class="sb-brand">
        <img class="sb-brand__logo" src="{{ asset('images/logo-siakad.png') }}" alt="">
        <span class="sb-brand__text">
            <span class="sb-brand__name">SIAKAD</span>
            <span class="sb-brand__school">SMA Negeri 1 Malang</span>
        </span>
    </a>

    <div class="sb-scroll">
        <p class="sb-label">Modul utama</p>

        <nav class="sb-nav" aria-label="Modul utama">
            @foreach ($menu as $m)
                @php
                    $items       = $m['items'] ?? [];
                    $groupActive = collect($items)->contains(fn ($i) => $isActive($i['pattern'] ?? $i['route'] ?? null));
                @endphp

                <div class="sb-group {{ $groupActive ? 'is-open' : '' }}">
                    @if (count($items))
                        <button type="button"
                                class="sb-item sb-toggle {{ $groupActive ? 'is-active' : '' }}"
                                aria-expanded="{{ $groupActive ? 'true' : 'false' }}">
                            {!! $svg($m['icon']) !!}
                            <span class="sb-item__text">{{ $m['label'] }}</span>
                            {!! $svg('chev', 'sb-chevron') !!}
                        </button>

                        <div class="sb-submenu">
                            @if (! empty($m['sub_title']))
                                <p class="sb-label">{{ $m['sub_title'] }}</p>
                            @endif

                            <nav class="sb-nav" aria-label="{{ $m['label'] }}">
                                @foreach ($items as $item)
                                    @php $active = $isActive($item['pattern'] ?? $item['route'] ?? null); @endphp
                                    <a href="{{ $resolve($item['route'] ?? null) }}"
                                       class="sb-sub {{ $active ? 'is-active' : '' }}"
                                       @if ($active) aria-current="page" @endif>
                                        {!! $svg($item['icon']) !!}
                                        <span class="sb-item__text">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @else
                        <a href="{{ $m['href'] ?? '#' }}" class="sb-item">
                            {!! $svg($m['icon']) !!}
                            <span class="sb-item__text">{{ $m['label'] }}</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </nav>
    </div>

    <div class="sb-footer">
        <div class="sb-year">
            {!! $svg('calendar') !!}
            <div>
                <p class="sb-year__label">Tahun ajaran</p>
                <p class="sb-year__value">{{ $academicYear }}</p>
            </div>
        </div>

        @if (\Illuminate\Support\Facades\Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sb-sub sb-logout">
                    {!! $svg('logout') !!}
                    <span class="sb-item__text">Keluar</span>
                </button>
            </form>
        @else
            <a href="#" class="sb-sub sb-logout">
                {!! $svg('logout') !!}
                <span class="sb-item__text">Keluar</span>
            </a>
        @endif
    </div>
</aside>

<div class="sb-overlay" data-sidebar-toggle hidden></div>

<script>
    (function () {
        var sb = document.getElementById('app-sidebar');
        if (!sb) return;

        // Buka/tutup submenu
        sb.addEventListener('click', function (e) {
            var btn = e.target.closest('.sb-toggle');
            if (!btn) return;
            var open = btn.parentNode.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // Drawer di layar kecil. Tombol pembuka cukup diberi atribut data-sidebar-toggle.
        var overlay = document.querySelector('.sb-overlay');
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-sidebar-toggle]')) return;
            var open = sb.classList.toggle('is-open');
            if (overlay) overlay.hidden = !open;
        });
    })();
</script>