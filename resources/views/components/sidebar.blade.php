@props([
    'menu' => null,
    'academicYear' => '2026/2027 • Ganjil',
    'role' => null,
])

@php
    $role = strtolower(str_replace(
        [' ', '-'],
        '_',
        $role ?? auth()->user()?->role ?? request('role', 'wali_kelas')
    ));
    $presensiMenus = [
    'siswa' => [
        [
            'label' => 'Presensi',
            'icon' => 'presensi',
            'href' => route('presensi.siswa'),
            'pattern' => 'presensi.siswa',
        ],
    ],

    'admin' => [
        [
            'label' => 'Presensi',
            'icon' => 'presensi',
            'href' => route('presensi.admin.riwayat'),
            'pattern' => 'presensi.admin.riwayat',
        ],
        [
            'label' => 'Admin Presensi',
            'icon' => 'scan',
            'href' => route('presensi.admin'),
            'pattern' => 'presensi.admin',
        ],
    ],

    'budaya_positif' => [
        [
            'label' => 'Presensi',
            'icon' => 'presensi',
            'href' => route('presensi.budaya-positif'),
            'pattern' => 'presensi.budaya-positif',
        ],
        [
            'label' => 'Admin Presensi',
            'icon' => 'scan',
            'href' => route('presensi.budaya-positif.admin-presensi'),
            'pattern' => 'presensi.budaya-positif.admin-presensi',
        ],
    ],

    'guru' => [
        [
            'label' => 'Presensi',
            'icon' => 'presensi',
            'href' => route('presensi.guru'),
            'pattern' => 'presensi.guru',
        ],
    ],

    'kurikulum' => [
        [
            'label' => 'Presensi',
            'icon' => 'presensi',
            'href' => route('presensi.kurikulum'),
            'pattern' => 'presensi.kurikulum',
        ],
    ],
];

    $presensiItems = $presensiMenus[$role] ?? [];

    // Menu Akademik (A2) mengikuti peran aktif pengguna (App\Support\WebNavigation).
    $peranAktif = auth()->check() ? \App\Support\AcademicAccess::peranAktif() : null;
    $ikonAkademik = fn (string $label) => match (true) {
        str_contains($label, 'Dashboard'), str_contains($label, 'Beranda') => 'grid',
        str_contains($label, 'Verifikasi') => 'check',
        str_contains($label, 'Input'), str_contains($label, 'TP') => 'pencil',
        str_contains($label, 'Rekap'), str_contains($label, 'Nilai') => 'table',
        str_contains($label, 'Cetak'), str_contains($label, 'Rapor') => 'printer',
        default => 'file',
    };
    $akademikItems = (auth()->check() && ! auth()->user()->must_change_password)
        ? collect(\App\Support\WebNavigation::links())
            ->map(fn ($label, $url) => ['label' => $label, 'icon' => $ikonAkademik($label), 'href' => url($url), 'path' => ltrim($url, '/')])
            ->values()->all()
        : [];

    // Menu default (Wali Kelas). Backend bisa mengirim $menu sendiri lewat <x-sidebar :menu="$menu" />
    $defaultMenu = [

        [
            'label' => 'Presensi', 'icon' => 'calendar', 'sub_title' => 'Presensi', 'items' => $presensiItems,
        ],
        [
            'label' => 'Akademik', 'icon' => 'file',
            'sub_title' => $akademikItems ? (config('siakad.roles')[$peranAktif] ?? 'Akademik') : 'Wali Kelas',
            'items' => $akademikItems ?: [
                ['label' => 'Dashboard Kelas', 'icon' => 'grid', 'route' => 'dashboard-kelas.index', 'pattern' => 'dashboard-kelas.*'],
                ['label' => 'Verifikasi Nilai',  'icon' => 'check',   'route' => 'verifikasi-nilai.index', 'pattern' => 'verifikasi-nilai.*'],
                ['label' => 'Pelengkap Rapor',   'icon' => 'pencil',  'route' => null],
                ['label' => 'Rekap Nilai (DKN)', 'icon' => 'table',   'route' => 'rekap-nilai.index',      'pattern' => 'rekap-nilai.*'],
                ['label' => 'Cetak Rapor',       'icon' => 'printer', 'route' => null],
            ],
        ],
        [
            'label' => 'BK', 'icon' => 'shield', 'href' => '#',
        ],
        [
            'label' => 'Portal Orang Tua', 'icon' => 'home', 'href' => '#',
        ],
    ];

    $menu = $menu ?? array_merge(
        $presensiItems,
        array_slice($defaultMenu, 1)
    );

    $resolve  = fn (?string $route) => ($route && \Illuminate\Support\Facades\Route::has($route)) ? route($route) : '#';
    $isActive = fn (?string $pattern) => $pattern && request()->routeIs($pattern);
    // Item boleh memakai 'route' (nama route) atau 'href' + 'path' (URL, mis. menu A2).
    $itemHref   = fn (array $i) => $i['href'] ?? $resolve($i['route'] ?? null);
    $itemActive = fn (array $i) => $isActive($i['pattern'] ?? $i['route'] ?? null)
        || (! empty($i['path']) && request()->is($i['path'], $i['path'].'/*'));

    $icons = [
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'presensi' => '<g transform="translate(2 2) scale(1.4286)" fill="none" stroke="currentColor" stroke-width="0.85" stroke-linecap="round" stroke-linejoin="round"><path d="M5.39 2.41c-.87.04-1.722.134-2.553.23a2.094 2.094 0 0 0-1.835 1.84C.882 5.558.766 6.673.766 7.813s.116 2.256.236 3.335c.108.96.875 1.727 1.835 1.838c1.084.125 2.204.25 3.35.25c1.147 0 2.267-.125 3.351-.25a2.094 2.094 0 0 0 1.834-1.838a39 39 0 0 0 .212-2.38"/><path d="m10.108 1.303l-3.685 4.18l-.504 2.375c-.082.382.317.744.69.626l2.362-.742l3.806-4.005c.633-.665.522-1.778-.243-2.455c-.747-.66-1.833-.652-2.426.02Z"/></g>',
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
                    $groupActive = collect($items)->contains(fn ($i) => $itemActive($i));
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
                                    @php $active = $itemActive($item); @endphp
                                    <a href="{{ $itemHref($item) }}"
                                       class="sb-sub {{ $active ? 'is-active' : '' }}"
                                       @if ($active) aria-current="page" @endif>
                                        {!! $svg($item['icon']) !!}
                                        <span class="sb-item__text">{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    @else
                        @php
                            $active = $isActive($m['pattern'] ?? null);
                        @endphp

                        <a href="{{ $m['href'] ?? '#' }}"
                        class="sb-item {{ $active ? 'is-active' : '' }}"
                        @if ($active) aria-current="page" @endif>
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