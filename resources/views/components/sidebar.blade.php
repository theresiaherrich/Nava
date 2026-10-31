@props([
    'menu'         => null,
    'academicYear' => '2026/2027 • Ganjil',
])

@php
    // Menu default (Wali Kelas). Backend bisa mengirim $menu sendiri lewat <x-sidebar :menu="$menu" />
    $defaultMenu = [
        [
            'code' => 'A1', 'label' => 'Presensi', 'icon' => 'calendar', 'href' => '#',
        ],
        [
            'code' => 'A2', 'label' => 'Akademik', 'icon' => 'file', 'sub_title' => 'Wali Kelas',
            'items' => [
                ['label' => 'Dashboard Kelas',    'icon' => 'grid',    'route' => null],
                ['label' => 'Verifikasi Nilai',   'icon' => 'check',   'route' => 'verifikasi-nilai.index', 'pattern' => 'verifikasi-nilai.*'],
                ['label' => 'Pelengkap Rapor',    'icon' => 'pencil',  'route' => null],
                ['label' => 'Rekap Nilai (DKN)',  'icon' => 'table',   'route' => 'rekap-nilai.index',      'pattern' => 'rekap-nilai.*'],
                ['label' => 'Cetak Rapor',        'icon' => 'printer', 'route' => null],
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
    ];

    $svg = fn (string $name, string $class = 'sb-icon') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? '').'</svg>';
@endphp

<aside class="sb" id="app-sidebar" aria-label="Navigasi utama">
    <a href="{{ url('/') }}" class="sb-brand">
        <img class="sb-brand__logo" src="{{ asset('images/logo-siakad.png') }}" alt="Logo SIAKAD">
        <span class="sb-brand__name">SIAKAD SMAN 1</span>
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
                            <span class="sb-item__code">{{ $m['code'] }}</span>
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
                            <span class="sb-item__code">{{ $m['code'] }}</span>
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