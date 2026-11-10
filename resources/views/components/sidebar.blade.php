@props([
    'academicYear' => '2026/2027 · Ganjil',
    'role' => null,
])

@php
    $user = auth()->user();
    $currentRole = $role ?? ($user?->role ?? 'BK');
    $isGuruBk = in_array(strtoupper($currentRole), ['BK', 'GURU']);
@endphp

<style>
    /* Brand sidebar: sengaja ditaruh di sini agar tidak tergantung build/cache CSS */
    .sb .sb-brand { font-size: 16px; display: flex; align-items: center; gap: 12px; min-width: 0; padding: 0 4px; color: inherit; text-decoration: none; }
    .sb .sb-brand__logo { height: 40px; width: auto; flex: none; display: block; object-fit: contain; }
    .sb .sb-brand__text { min-width: 0; line-height: 1.1; }
    .sb .sb-brand__title { margin: 0; font-size: 16px; font-weight: 700; letter-spacing: .01em; white-space: nowrap; }
    .sb .sb-brand__sub { margin: 1px 0 0; font-size: 10.5px; font-weight: 400; color: rgba(255,255,255,.92); white-space: nowrap; }
</style>

<aside class="sb" id="app-sidebar">
    <!-- Brand Header -->
    <a href="{{ $isGuruBk ? route('bk.dashboard') : route('siswa.dashboard') }}" class="sb-brand">
        <img src="{{ asset('images/logo-sman1.png') }}" alt="Logo SMAN 1" class="sb-brand__logo">
        <div class="sb-brand__text">
            <p class="sb-brand__title">SIAKAD</p>
            <p class="sb-brand__sub">SMA Negeri 1 Malang</p>
        </div>
    </a>

    <!-- Area Tengah: Menu Scrollable -->
    <div class="sb-scroll">
        <p class="sb-label">Menu Utama</p>

        <nav class="sb-nav" aria-label="Menu Utama">
            <!-- Grup Modul A3 -->
            <div class="sb-group is-open">
                <button type="button" class="sb-item sb-toggle is-active" aria-expanded="true">
                    <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span class="sb-item__text">{{ $isGuruBk ? 'Bimbingan Konseling' : 'Peminatan & Konseling' }}</span>
                    <svg class="sb-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <div class="sb-submenu">
                    <nav class="sb-nav">
                        @if ($isGuruBk)
                            <!-- Menu Guru BK -->
                            <a href="{{ route('bk.dashboard') }}" class="sb-sub {{ request()->routeIs('bk.dashboard') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="3" width="7" height="7"></rect>
                                    <rect x="14" y="14" width="7" height="7"></rect>
                                    <rect x="3" y="14" width="7" height="7"></rect>
                                </svg>
                                <span class="sb-item__text">Dashboard BK</span>
                            </a>
                            <a href="{{ route('bk.rekap-tes') }}" class="sb-sub {{ request()->routeIs('bk.rekap-tes*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <span class="sb-item__text">Rekap Tes RIASEC</span>
                            </a>
                            <a href="{{ route('bk.pengajuan') }}" class="sb-sub {{ request()->routeIs('bk.pengajuan*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <span class="sb-item__text">Pengajuan BK</span>
                            </a>
                            <a href="{{ route('bk.riwayat') }}" class="sb-sub {{ request()->routeIs('bk.riwayat*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span class="sb-item__text">Riwayat Bimbingan</span>
                            </a>
                            <a href="{{ route('bk.profil') }}" class="sb-sub {{ request()->routeIs('bk.profil*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span class="sb-item__text">Profil Guru BK</span>
                            </a>
                        @else
                            <!-- Menu Siswa -->
                            <a href="{{ route('siswa.dashboard') }}" class="sb-sub {{ request()->routeIs('siswa.dashboard') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                <span class="sb-item__text">Dashboard</span>
                            </a>
                            <a href="{{ route('siswa.tes.petunjuk') }}" class="sb-sub {{ request()->routeIs('siswa.tes*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <span class="sb-item__text">Tes RIASEC</span>
                            </a>
                            <a href="{{ route('siswa.hasil') }}" class="sb-sub {{ request()->routeIs('siswa.hasil*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="20" x2="18" y2="10"></line>
                                    <line x1="12" y1="20" x2="12" y2="4"></line>
                                    <line x1="6" y1="20" x2="6" y2="14"></line>
                                </svg>
                                <span class="sb-item__text">Hasil Tes</span>
                            </a>
                            <a href="{{ route('siswa.mapel-pilihan') }}" class="sb-sub {{ request()->routeIs('siswa.mapel-pilihan*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                </svg>
                                <span class="sb-item__text">Rekomendasi Mapel</span>
                            </a>
                            <a href="{{ route('siswa.riwayat-tes') }}" class="sb-sub {{ request()->routeIs('siswa.riwayat-tes*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <span class="sb-item__text">Riwayat Tes</span>
                            </a>
                            <a href="{{ route('siswa.pengajuan-bk') }}" class="sb-sub {{ request()->routeIs('siswa.pengajuan-bk*') ? 'is-active' : '' }}">
                                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                <span class="sb-item__text">Pengajuan BK</span>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </nav>
    </div>

    <!-- Footer -->
    <div class="sb-footer">
        <div class="sb-year">
            <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <div>
                <p class="sb-year__label">Tahun ajaran</p>
                <p class="sb-year__value">{{ $academicYear }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sb-sub sb-logout">
                <svg class="sb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="sb-item__text">Keluar</span>
            </button>
        </form>
    </div>
</aside>

<div class="sb-overlay" id="sidebar-overlay" hidden></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sb = document.getElementById('app-sidebar');
        if (!sb) return;

        // Toggle submenu dropdown
        sb.querySelectorAll('.sb-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var group = btn.closest('.sb-group');
                if (!group) return;
                var isOpen = group.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });

        // Mobile drawer: overlay mengikuti status drawer
        var overlay = document.getElementById('sidebar-overlay');
        if (overlay) {
            var sync = function () { overlay.hidden = !sb.classList.contains('is-open'); };
            new MutationObserver(sync).observe(sb, { attributes: true, attributeFilter: ['class'] });
            overlay.addEventListener('click', function () { sb.classList.remove('is-open'); });
            sync();
        }
    });
</script>