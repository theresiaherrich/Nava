@props([
    'name' => 'Pengguna',
    'role' => '',
    'initials' => null,
    'academicYear' => '2026/2027 · Ganjil',
    'academicYears' => null,            // daftar pilihan tahun ajaran; null = hanya tahun aktif
    'notifications' => [],              // [['judul','keterangan','waktu','baru' => bool, 'url' => opsional]]
    'searchPlaceholder' => 'Cari siswa (nama / NISN)...',
    'searchAction' => '#',              // TODO (backend): arahkan ke route pencarian siswa
    'profileUrl' => '#',                // TODO (backend): route profil
    'switchRoleUrl' => '#',             // TODO (backend): route ganti peran
])

@php
    // Inisial otomatis dari nama bila tidak dikirim, gelar setelah koma diabaikan.
    if (! $initials) {
        $words    = preg_split('/\s+/', trim(strtok($name, ',')));
        $initials = mb_strtoupper(collect($words)->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    }

    $academicYears = $academicYears ?: [$academicYear];
    $unread        = collect($notifications)->where('baru', true)->count();

    $icons = [
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'bell'   => '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'chev'   => '<path d="m6 9 6 6 6-6"/>',
        'menu'   => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'user'   => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'swap'   => '<path d="M7 4 3 8l4 4"/><path d="M3 8h14"/><path d="m17 20 4-4-4-4"/><path d="M21 16H7"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/>',
        'check'  => '<path d="M20 6 9 17l-5-5"/>',
    ];
    $svg = fn (string $n, string $c = 'tb-icon') => '<svg class="'.$c.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$n] ?? '').'</svg>';
@endphp

@vite(['resources/css/topbar.css', 'resources/js/topbar.js'])

<header class="tb" data-topbar>
    <button type="button" class="tb-icon-btn tb-menu" data-sidebar-toggle aria-label="Buka menu navigasi">
        {!! $svg('menu') !!}
    </button>

    {{-- Pencarian --}}
    <form class="tb-search" action="{{ $searchAction }}" method="GET" role="search" data-tb-search>
        <button type="button" class="tb-search__toggle" data-tb-search-toggle aria-label="Buka pencarian">
            {!! $svg('search') !!}
        </button>
        <label class="tb-search__field">
            {!! $svg('search', 'tb-icon tb-search__icon') !!}
            <span class="tb-sr">Cari siswa</span>
            <input type="search" name="q" class="tb-search__input" placeholder="{{ $searchPlaceholder }}"
                   autocomplete="off" data-tb-search-input>
            <kbd class="tb-kbd" data-tb-kbd title="Pintasan keyboard">⌘K</kbd>
        </label>
    </form>

    <div class="tb-actions">
        {{-- Tahun ajaran --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-year" data-tb-dd-toggle aria-haspopup="listbox" aria-expanded="false">
                <span data-tb-year-label>{{ $academicYear }}</span>
                {!! $svg('chev', 'tb-icon tb-chev') !!}
            </button>
            <ul class="tb-menu-list tb-menu-list--year" role="listbox" hidden data-tb-dd-menu>
                @foreach ($academicYears as $year)
                    <li>
                        {{-- TODO (backend): ganti tahun ajaran aktif di sesi / query string --}}
                        <button type="button" role="option" class="tb-menu-item {{ $year === $academicYear ? 'is-selected' : '' }}"
                                aria-selected="{{ $year === $academicYear ? 'true' : 'false' }}"
                                data-tb-year="{{ $year }}">
                            <span>{{ $year }}</span>
                            {!! $svg('check', 'tb-icon tb-tick') !!}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Notifikasi --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-icon-btn tb-bell" data-tb-dd-toggle aria-haspopup="true" aria-expanded="false"
                    aria-label="Notifikasi{{ $unread ? ' ('.$unread.' belum dibaca)' : '' }}">
                {!! $svg('bell') !!}
                @if ($unread)
                    <span class="tb-dot" data-tb-dot></span>
                @endif
            </button>
            <div class="tb-menu-list tb-notif" hidden data-tb-dd-menu>
                <div class="tb-notif__head">
                    <p class="tb-notif__title">Notifikasi</p>
                    @if ($unread)
                        <button type="button" class="tb-link" data-tb-read-all>Tandai dibaca</button>
                    @endif
                </div>
                @forelse ($notifications as $n)
                    <a href="{{ $n['url'] ?? '#' }}" class="tb-notif__item {{ ! empty($n['baru']) ? 'is-new' : '' }}">
                        <span class="tb-notif__mark" aria-hidden="true"></span>
                        <span class="tb-notif__body">
                            <span class="tb-notif__judul">{{ $n['judul'] }}</span>
                            <span class="tb-notif__ket">{{ $n['keterangan'] ?? '' }}</span>
                        </span>
                        <span class="tb-notif__waktu">{{ $n['waktu'] ?? '' }}</span>
                    </a>
                @empty
                    <p class="tb-notif__empty">Belum ada notifikasi.</p>
                @endforelse
            </div>
        </div>

        {{-- Profil --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-profile" data-tb-dd-toggle aria-haspopup="menu" aria-expanded="false">
                <span class="tb-avatar">{{ $initials }}</span>
                <span class="tb-profile__text">
                    <span class="tb-profile__name">{{ $name }}</span>
                    <span class="tb-profile__role">{{ $role }}</span>
                </span>
                {!! $svg('chev', 'tb-icon tb-chev') !!}
            </button>
            <div class="tb-menu-list tb-menu-list--profile" role="menu" hidden data-tb-dd-menu>
                <div class="tb-profile__mobile">
                    <p class="tb-profile__name">{{ $name }}</p>
                    <p class="tb-profile__role">{{ $role }}</p>
                </div>
                <a href="{{ $profileUrl }}" class="tb-menu-item" role="menuitem">{!! $svg('user') !!} Profil</a>
                <a href="{{ $switchRoleUrl }}" class="tb-menu-item" role="menuitem">{!! $svg('swap') !!} Ganti peran</a>
                <hr class="tb-sep">
                @if (\Illuminate\Support\Facades\Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="tb-menu-item is-danger" role="menuitem">{!! $svg('logout') !!} Keluar</button>
                    </form>
                @else
                    <a href="#" class="tb-menu-item is-danger" role="menuitem">{!! $svg('logout') !!} Keluar</a>
                @endif
            </div>
        </div>
    </div>
</header>
