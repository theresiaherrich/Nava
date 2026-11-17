@props([
    'academicYear' => '2025/2026 · Ganjil',
    'searchPlaceholder' => null,
    'context' => null,
])

@php
    $user = auth()->user();
    $role = strtoupper($context['role'] ?? ($user?->role ?? ''));

    if ($role === 'ADMIN') {
        $namaUser = $user->name ?? $user->username ?? 'Administrator';
        $subtext = 'Administrator SIAKAD';
        $inisial = 'AD';
        $avatarBg = '#0284c7';
        $profileRoute = route('admin.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari data, menu, atau pertanyaan...';
    } elseif (in_array($role, ['GURU_BK', 'GURU', 'BK'], true)) {
        $guru = \App\Models\Guru::where('id_user', $user?->id_user)->first();
        $namaUser = $guru?->nama ?? $user?->name ?? 'Guru BK';
        $subtext = 'Guru Bimbingan dan Konseling';
        $initials = collect(explode(' ', trim((string) $namaUser)))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
        $inisial = $initials ?: 'GB';
        $avatarBg = '#f0408f';
        $profileRoute = route('bk.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari siswa, NIS, atau pengajuan...';
    } else { // SISWA
        $siswa = \App\Models\Siswa::with('riwayatKelasAktif.kelas')->where('id_user', $user?->id_user)->first();
        $namaUser = $siswa?->nama ?? $user?->name ?? 'Siswa';
        $kelasNama = $siswa?->riwayatKelasAktif?->kelas?->nama ?? '';
        $subtext = 'Siswa' . ($kelasNama ? ' · Kelas ' . $kelasNama : '');
        $initials = collect(explode(' ', trim((string) $namaUser)))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
        $inisial = $initials ?: 'NA';
        $avatarBg = '#e11d48';
        $profileRoute = route('siswa.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari menu atau informasi...';
    }

    $academicYear = $academicYear ?? ($context['academicYear'] ?? '2025/2026 · Ganjil');
@endphp

<header class="tb" data-topbar aria-label="Topbar navigasi">
    {{-- Tombol Hamburger (Mobile) --}}
    <button type="button" class="tb-icon-btn tb-menu" data-sidebar-toggle aria-label="Buka menu navigasi">
        <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    {{-- Pencarian --}}
    <div class="tb-search" data-tb-search>
        <button type="button" class="tb-search__toggle" data-tb-search-toggle aria-label="Buka pencarian">
            <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </button>
        <label class="tb-search__field">
            <svg class="tb-icon tb-search__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="search" class="tb-search__input" data-tb-search-input placeholder="{{ $searchPlaceholder }}" aria-label="Pencarian">
            <kbd class="tb-kbd" data-tb-kbd>⌘ K</kbd>
        </label>
    </div>

    {{-- Area Kanan: Tahun Ajaran, Notifikasi, Profil --}}
    <div class="tb-actions">
        {{-- Dropdown Semester / Tahun Ajaran --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-year" data-tb-dd-toggle aria-expanded="false" aria-haspopup="true">
                <span data-tb-year-label>{{ $academicYear }}</span>
                <svg class="tb-icon tb-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <ul class="tb-menu-list tb-menu-list--year" data-tb-dd-menu hidden>
                <li>
                    <button type="button" class="tb-menu-item is-selected" data-tb-year="{{ $academicYear }}" aria-selected="true">
                        <span>{{ $academicYear }}</span>
                        <svg class="tb-icon tb-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                </li>
            </ul>
        </div>

        {{-- Notifikasi --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-icon-btn" data-tb-dd-toggle aria-expanded="false" aria-label="Notifikasi">
                <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="tb-dot" data-tb-dot></span>
            </button>
            <div class="tb-menu-list tb-notif" data-tb-dd-menu hidden>
                <div class="tb-notif__head">
                    <span class="tb-notif__title">Notifikasi</span>
                    <button type="button" class="tb-link" data-tb-read-all>Tandai dibaca</button>
                </div>
                <div>
                    <div class="tb-notif__item is-new">
                        <span class="tb-notif__mark"></span>
                        <div class="tb-notif__body">
                            <span class="tb-notif__judul">Layanan Peminatan &amp; BK Aktif</span>
                            <span class="tb-notif__ket">Semester aktif sedang berjalan</span>
                            <span class="tb-notif__waktu">Baru saja</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dropdown Profil Pengguna --}}
        <div class="tb-dd" data-tb-dd>
            <button type="button" class="tb-profile" data-tb-dd-toggle aria-expanded="false" aria-haspopup="true">
                <span class="tb-avatar" style="{{ $avatarBg ? 'background-color: '.$avatarBg : '' }}">{{ $inisial }}</span>
                <span class="tb-profile__text">
                    <span class="tb-profile__name">{{ $namaUser }}</span>
                    <span class="tb-profile__role">{{ $subtext }}</span>
                </span>
                <svg class="tb-icon tb-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="tb-menu-list" data-tb-dd-menu hidden>
                <div class="tb-profile__mobile">
                    <div class="tb-profile__name">{{ $namaUser }}</div>
                    <div class="tb-profile__role">{{ $subtext }}</div>
                </div>
                <a href="{{ $profileRoute }}" class="tb-menu-item">
                    <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M4 21a8 8 0 0 1 16 0"></path>
                    </svg>
                    <span>Profil Saya</span>
                </a>
                <a href="{{ $profileRoute }}#keamanan" class="tb-menu-item">
                    <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Ubah Password</span>
                </a>
                <hr class="tb-sep">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="tb-menu-item is-danger">
                        <svg class="tb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    // Topbar: dropdown, pencarian, dan pintasan Ctrl/⌘ + K.
    (function () {
        function initTopbar() {
            var bar = document.querySelector('[data-topbar]');
            if (!bar || bar.__tbInitialized) return;
            bar.__tbInitialized = true;

            /* ---------- Dropdown (tahun ajaran, notifikasi, profil) ---------- */
            var dropdowns = Array.prototype.slice.call(bar.querySelectorAll('[data-tb-dd]'));

            var setOpen = function (dd, open) {
                var btn = dd.querySelector('[data-tb-dd-toggle]');
                var menu = dd.querySelector('[data-tb-dd-menu]');
                if (!menu || !btn) return;
                menu.hidden = !open;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            };
            var closeAll = function (except) {
                dropdowns.forEach(function (dd) {
                    if (dd !== except) setOpen(dd, false);
                });
            };

            dropdowns.forEach(function (dd) {
                var toggleBtn = dd.querySelector('[data-tb-dd-toggle]');
                if (!toggleBtn) return;
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var menu = dd.querySelector('[data-tb-dd-menu]');
                    var open = menu ? menu.hidden : false;
                    closeAll(dd);
                    setOpen(dd, open);
                });
            });

            document.addEventListener('click', function (e) {
                if (!e.target.closest('[data-tb-dd]')) closeAll();
            });

            /* ---------- Pilih tahun ajaran (dummy: hanya ganti label) ---------- */
            var yearLabel = bar.querySelector('[data-tb-year-label]');
            bar.querySelectorAll('[data-tb-year]').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    bar.querySelectorAll('[data-tb-year]').forEach(function (o) {
                        o.classList.toggle('is-selected', o === opt);
                        o.setAttribute('aria-selected', o === opt ? 'true' : 'false');
                    });
                    if (yearLabel) yearLabel.textContent = opt.dataset.tbYear;
                    closeAll();
                });
            });

            /* ---------- Tandai semua notifikasi dibaca ---------- */
            var readAll = bar.querySelector('[data-tb-read-all]');
            if (readAll) {
                readAll.addEventListener('click', function () {
                    bar.querySelectorAll('.tb-notif__item.is-new').forEach(function (n) {
                        n.classList.remove('is-new');
                    });
                    var dot = bar.querySelector('[data-tb-dot]');
                    if (dot) dot.remove();
                    readAll.remove();
                });
            }

            /* ---------- Pencarian ---------- */
            var searchForm = bar.querySelector('[data-tb-search]');
            var searchInput = bar.querySelector('[data-tb-search-input]');
            var searchToggle = bar.querySelector('[data-tb-search-toggle]');
            var kbd = bar.querySelector('[data-tb-kbd]');

            var isMac = /Mac|iPhone|iPad/i.test(navigator.platform || navigator.userAgent);
            if (kbd && !isMac) kbd.textContent = 'Ctrl K';

            var openSearch = function () {
                if (searchForm) searchForm.classList.add('is-open');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            };

            if (searchToggle) searchToggle.addEventListener('click', openSearch);
            if (searchInput) {
                searchInput.addEventListener('blur', function () {
                    if (!searchInput.value && searchForm) searchForm.classList.remove('is-open');
                });
            }

            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    closeAll();
                    openSearch();
                }
                if (e.key === 'Escape') {
                    closeAll();
                    if (searchInput && document.activeElement === searchInput) {
                        searchInput.blur();
                    }
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTopbar);
        } else {
            initTopbar();
        }
    })();
</script>
