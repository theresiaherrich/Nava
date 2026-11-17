@props([
    'menu' => null,
    'academicYear' => '2025/2026 · Ganjil',
    'role' => null,
    'context' => null,
])

@php
    // Normalisasi role dari context, prop, auth, atau request
    $rawRole = $role ?? ($context['role'] ?? (auth()->user()?->role ?? request('role', 'wali_kelas')));
    $role = strtolower(str_replace([' ', '-'], '_', (string) $rawRole));

    // Tingkat kelas siswa (default 'X')
    $tingkat = strtoupper($context['grade'] ?? (auth()->user()?->siswa?->kelas?->tingkat ?? 'X'));

    // Tahun ajaran
    $academicYear = $academicYear ?? ($context['academicYear'] ?? '2025/2026 · Ganjil');

    // ==============================================================
    // A1 - PRESENSI MENU ITEMS
    // ==============================================================
    $presensiMenus = [
        'siswa' => [
            ['label' => 'Presensi QR Code', 'icon' => 'qr', 'route' => 'presensi.qr-code', 'pattern' => 'presensi.qr-code*'],
            ['label' => 'Presensi Manual', 'icon' => 'map-pin', 'route' => 'presensi.manual', 'pattern' => 'presensi.manual*'],
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat', 'pattern' => 'presensi.riwayat*'],
        ],
        'wali_kelas' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat', 'pattern' => 'presensi.riwayat*'],
            ['label' => 'Edit Presensi', 'icon' => 'pencil', 'route' => 'presensi.edit', 'pattern' => 'presensi.edit*'],
        ],
        'budaya_positif' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat', 'pattern' => 'presensi.riwayat*'],
            ['label' => 'Scan Presensi', 'icon' => 'scan', 'route' => 'presensi.scan', 'pattern' => 'presensi.scan*'],
        ],
        'admin' => [
            ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat', 'pattern' => 'presensi.riwayat*'],
            ['label' => 'Edit Presensi', 'icon' => 'pencil', 'route' => 'presensi.edit', 'pattern' => 'presensi.edit*'],
            ['label' => 'Scan Presensi', 'icon' => 'scan', 'route' => 'presensi.scan', 'pattern' => 'presensi.scan*'],
        ],
    ];

    $presensiItems = $presensiMenus[$role] ?? ($presensiMenus['wali_kelas'] ?? []);

    // ==============================================================
    // A2 - AKADEMIK MENU ITEMS
    // ==============================================================
    $akademikItems = [
        ['label' => 'Dashboard Kelas', 'icon' => 'grid', 'route' => 'dashboard-kelas.index', 'pattern' => 'dashboard-kelas.*'],
        ['label' => 'Verifikasi Nilai',  'icon' => 'check',   'route' => 'verifikasi-nilai.index', 'pattern' => 'verifikasi-nilai.*'],
        ['label' => 'Pelengkap Rapor',   'icon' => 'pencil',  'route' => null],
        ['label' => 'Rekap Nilai (DKN)', 'icon' => 'table',   'route' => 'rekap-nilai.index',      'pattern' => 'rekap-nilai.*'],
        ['label' => 'Cetak Rapor',       'icon' => 'printer', 'route' => null],
    ];

    // ==============================================================
    // A3 - PEMINATAN & BIMBINGAN KONSELING MENU ITEMS
    // ==============================================================
    $a3Label = 'BK';
    $a3SubTitle = 'Peminatan & Konseling';
    $a3Items = [];

    if ($role === 'admin') {
        $a3Label = 'Pemetaan Minat';
        $a3SubTitle = 'Admin Modul A3';
        $a3Items = [
            ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard*'],
            ['label' => 'Dimensi RIASEC', 'icon' => 'sliders', 'route' => 'admin.dimensi', 'pattern' => 'admin.dimensi*'],
            ['label' => 'Pertanyaan Tes', 'icon' => 'target', 'route' => 'admin.soal', 'pattern' => 'admin.soal*'],
            ['label' => 'Mapel Pilihan', 'icon' => 'book', 'route' => 'admin.mapel', 'pattern' => 'admin.mapel*'],
            ['label' => 'Program Studi', 'icon' => 'building', 'route' => 'admin.prodi', 'pattern' => 'admin.prodi*'],
            ['label' => 'Profesi', 'icon' => 'user-star', 'route' => 'admin.profesi', 'pattern' => 'admin.profesi*'],
        ];
    } elseif (in_array($role, ['guru_bk', 'bk', 'guru'], true)) {
        $a3Label = 'Bimbingan Konseling';
        $a3SubTitle = 'Guru Bimbingan Konseling';
        $a3Items = [
            ['label' => 'Dashboard BK', 'icon' => 'home', 'route' => 'bk.dashboard', 'pattern' => 'bk.dashboard*'],
            ['label' => 'Rekap Tes RIASEC', 'icon' => 'table', 'route' => 'bk.rekap-tes', 'pattern' => 'bk.rekap-tes*'],
            ['label' => 'Pengajuan BK', 'icon' => 'message', 'route' => 'bk.pengajuan', 'pattern' => 'bk.pengajuan*'],
            ['label' => 'Validasi Mapel', 'icon' => 'book', 'route' => 'bk.validasi', 'pattern' => 'bk.validasi*'],
            ['label' => 'Riwayat Bimbingan', 'icon' => 'history', 'route' => 'bk.riwayat', 'pattern' => 'bk.riwayat*'],
        ];
    } elseif ($role === 'siswa') {
        if ($tingkat === 'XI') {
            $a3Label = 'Bimbingan Konseling';
            $a3SubTitle = 'Siswa Kelas XI';
            $a3Items = [
                ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'siswa.dashboard', 'pattern' => 'siswa.dashboard*'],
                ['label' => 'Hasil Tes', 'icon' => 'chart', 'route' => 'siswa.hasil', 'pattern' => 'siswa.hasil*'],
                ['label' => 'Riwayat Tes', 'icon' => 'history', 'route' => 'siswa.riwayat-tes', 'pattern' => 'siswa.riwayat-tes*'],
                ['label' => 'Pengajuan BK', 'icon' => 'message', 'route' => 'siswa.pengajuan-bk', 'pattern' => 'siswa.pengajuan-bk*'],
            ];
        } elseif ($tingkat === 'XII') {
            $a3Label = 'Peminatan & Konseling';
            $a3SubTitle = 'Siswa Kelas XII';
            $a3Items = [
                ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'siswa.dashboard', 'pattern' => 'siswa.dashboard*'],
                ['label' => 'Hasil Tes', 'icon' => 'chart', 'route' => 'siswa.hasil', 'pattern' => 'siswa.hasil*'],
                ['label' => 'Rekomendasi Prodi', 'icon' => 'building', 'route' => 'siswa.rekomendasi-prodi', 'pattern' => 'siswa.rekomendasi-prodi*'],
                ['label' => 'Riwayat Tes', 'icon' => 'history', 'route' => 'siswa.riwayat-tes', 'pattern' => 'siswa.riwayat-tes*'],
                ['label' => 'Pengajuan BK', 'icon' => 'message', 'route' => 'siswa.pengajuan-bk', 'pattern' => 'siswa.pengajuan-bk*'],
            ];
        } else {
            // Default Kelas X
            $a3Label = 'Peminatan & Konseling';
            $a3SubTitle = 'Siswa Kelas X';
            $a3Items = [
                ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'siswa.dashboard', 'pattern' => 'siswa.dashboard*'],
                ['label' => 'Tes RIASEC', 'icon' => 'clipboard', 'route' => 'siswa.tes.petunjuk', 'pattern' => 'siswa.tes*'],
                ['label' => 'Hasil Tes', 'icon' => 'chart', 'route' => 'siswa.hasil', 'pattern' => 'siswa.hasil*'],
                ['label' => 'Rekomendasi Mapel', 'icon' => 'book', 'route' => 'siswa.mapel-pilihan', 'pattern' => 'siswa.mapel-pilihan*'],
                ['label' => 'Riwayat Tes', 'icon' => 'history', 'route' => 'siswa.riwayat-tes', 'pattern' => 'siswa.riwayat-tes*'],
                ['label' => 'Pengajuan BK', 'icon' => 'message', 'route' => 'siswa.pengajuan-bk', 'pattern' => 'siswa.pengajuan-bk*'],
            ];
        }
    } else {
        // Fallback jika role lain
        $a3Label = 'Bimbingan Konseling';
        $a3SubTitle = 'Peminatan & BK';
        $a3Items = [
            ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'siswa.dashboard', 'pattern' => 'siswa.dashboard*'],
            ['label' => 'Dashboard Guru BK', 'icon' => 'user-star', 'route' => 'bk.dashboard', 'pattern' => 'bk.dashboard*'],
        ];
    }

    // Menu default gabungan A1, A2, A3, A4
    $defaultMenu = [
        [
            'code' => 'A1', 'label' => 'Presensi', 'icon' => 'calendar', 'sub_title' => 'Presensi', 'items' => $presensiItems,
        ],
        [
            'code' => 'A2', 'label' => 'Akademik', 'icon' => 'file', 'sub_title' => 'Wali Kelas', 'items' => $akademikItems,
        ],
        [
            'code' => 'A3', 'label' => $a3Label, 'icon' => 'shield', 'sub_title' => $a3SubTitle, 'items' => $a3Items,
        ],
        [
            'code' => 'A4', 'label' => 'Portal Orang Tua', 'icon' => 'home', 'href' => '#',
        ],
    ];

    $menu = $menu ?? $defaultMenu;

    $resolve = function (?string $route) {
        if (! $route) {
            return '#';
        }
        return \Illuminate\Support\Facades\Route::has($route) ? route($route) : '#';
    };

    $isActive = fn (?string $pattern) => $pattern && request()->routeIs($pattern);

    $icons = [
        'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'file'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'home'      => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'grid'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'pencil'    => '<path d="M17 3a2.8 2.8 0 0 1 4 4L7.5 20.5 2 22l1.5-5.5z"/><path d="m15 5 4 4"/>',
        'table'     => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 4v16"/>',
        'printer'   => '<path d="M6 9V2h12v7"/><rect x="6" y="14" width="12" height="8"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'chev'      => '<path d="m6 9 6 6 6-6"/>',
        'qr'        => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v2M17 20h4M20 18v3"/>',
        'map-pin'   => '<path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>',
        'history'   => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        'scan'      => '<path d="M4 8V4h4M16 4h4v4M20 16v4h-4M8 20H4v-4M4 12h16"/>',
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
        // Ikon tambahan khas A3
        'clipboard' => '<rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>',
        'chart'     => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'message'   => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    ];

    $svg = fn (string $name, string $class = 'sb-icon') => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name] ?? '').'</svg>';
@endphp

<aside class="sb" id="app-sidebar" aria-label="Navigasi utama">
    <a href="{{ url('/') }}" class="sb-brand">
        <img class="sb-brand__logo" src="{{ asset('images/logo-sman1.png') }}" alt="Logo SIAKAD SMA Negeri 1 Malang">
        <span class="sb-brand__text">
            <span class="sb-brand__name sb-brand__title font-extrabold text-lg">SIAKAD</span>
            <span class="sb-brand__school sb-brand__sub text-[11.5px]">SMA Negeri 1 Malang</span>
        </span>
    </a>

    <div class="sb-scroll">
        <p class="sb-label">Modul utama</p>

        <nav class="sb-nav" aria-label="Modul utama">
            @foreach ($menu as $m)
                @php
                    $items = $m['items'] ?? [];
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

        // Drawer di layar kecil
        var overlay = document.querySelector('.sb-overlay');
        document.addEventListener('click', function (e) {
            if (!e.target.closest('[data-sidebar-toggle]')) return;
            var open = sb.classList.toggle('is-open');
            if (overlay) overlay.hidden = !open;
        });
    })();
</script>
