<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIAKAD A3') · SMA Negeri 1 Malang</title>
    <link rel="stylesheet" href="{{ asset('a3/a3.css') }}">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ url('/a3/siswa/dashboard') }}">
            <span class="brand-mark">S</span>
            <span><strong>SIAKAD</strong><small>SMA Negeri 1 Malang</small></span>
        </a>
        <div class="side-caption">MODUL A3 · BIMBINGAN & KONSELING</div>
        <nav class="side-nav">
            <span class="nav-label">MENU SISWA</span>
            <a href="{{ url('/a3/siswa/dashboard') }}" class="{{ request()->is('a3/siswa/dashboard') ? 'active' : '' }}">⌂ <span>Dashboard</span></a>
            <a href="{{ url('/a3/siswa/tes') }}" class="{{ request()->is('a3/siswa/tes') ? 'active' : '' }}">◎ <span>Tes RIASEC</span></a>
            <a href="{{ url('/a3/siswa/hasil') }}" class="{{ request()->is('a3/siswa/hasil') ? 'active' : '' }}">▤ <span>Hasil Tes</span></a>
            <a href="{{ url('/a3/siswa/rekomendasi-mapel') }}" class="{{ request()->is('a3/siswa/rekomendasi-mapel') ? 'active' : '' }}">▦ <span>Rekomendasi Mapel</span></a>
            <a href="{{ url('/a3/siswa/rekomendasi-prodi') }}" class="{{ request()->is('a3/siswa/rekomendasi-prodi') ? 'active' : '' }}">◇ <span>Rekomendasi Prodi</span></a>
            <a href="{{ url('/a3/siswa/riwayat') }}" class="{{ request()->is('a3/siswa/riwayat') ? 'active' : '' }}">◷ <span>Riwayat Tes</span></a>
            <a href="{{ url('/a3/siswa/pengajuan-bk') }}" class="{{ request()->is('a3/siswa/pengajuan-bk') ? 'active' : '' }}">✉ <span>Pengajuan BK</span></a>
            <span class="nav-label nav-gap">MENU GURU BK</span>
            <a href="{{ url('/a3/bk/dashboard') }}">▣ <span>Dashboard BK</span></a>
            <a href="{{ url('/a3/bk/rekap') }}">▤ <span>Rekap Tes RIASEC</span></a>
            <a href="{{ url('/a3/bk/pengajuan') }}">☷ <span>Antrean Pengajuan</span></a>
            <span class="nav-label nav-gap">MENU ADMIN</span>
            <a href="{{ url('/a3/admin/kelola') }}">⚙ <span>Kelola Data A3</span></a>
        </nav>
        <div class="sidebar-bottom"><span class="status-dot"></span> Prototype tampilan A3</div>
    </aside>

    <main class="main-area">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" aria-label="Buka menu">☰</button>
            <div class="search-box"><span>⌕</span><input type="search" placeholder="Cari menu atau informasi..." aria-label="Cari"></div>
            <div class="topbar-right">
                <span class="period">2026/2027 · Ganjil</span>
                <div class="user-chip"><span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}</span><span><b>{{ auth()->user()->name ?? 'Pengguna Demo' }}</b><small>Pratinjau Modul A3</small></span></div>
            </div>
        </header>
        <section class="page-wrap">
            @yield('content')
            <footer class="footer">© {{ date('Y') }} SMA Negeri 1 Malang · Modul A3 Bimbingan dan Konseling</footer>
        </section>
    </main>
</div>
<script src="{{ asset('a3/a3.js') }}"></script>
@stack('scripts')
</body>
</html>
