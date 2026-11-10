@props([
    'academicYear' => '2026/2027 - Ganjil',
    'searchPlaceholder' => null,
])

@php
    $user = auth()->user();
    $role = $user?->role ?? 'BK';
    $isGuruBk = in_array(strtoupper($role), ['BK', 'GURU']);

    // Ambil nama dan inisial dari relasi model jika ada
    if ($isGuruBk) {
        $guru = \App\Models\Guru::where('id_user', $user?->id_user)->first();
        $namaUser = $guru?->nama ?? $user?->name ?? 'Guru BK';
        $subtext = 'Guru Bimbingan dan Konseling';
        $initials = collect(explode(' ', $namaUser))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $inisial = $initials ?: 'BK';
        $searchPlaceholder = $searchPlaceholder ?? 'Cari siswa atau NIS...';
    } else {
        $siswa = \App\Models\Siswa::where('id_user', $user?->id_user)->first();
        $namaUser = $siswa?->nama ?? $user?->name ?? 'Siswa';
        $subtext = 'Siswa' . ($siswa ? ' - Kelas ' . ($siswa->riwayatKelasAktif?->kelas?->nama ?? '') : '');
        $initials = collect(explode(' ', $namaUser))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
        $inisial = $initials ?: 'SW';
        $searchPlaceholder = $searchPlaceholder ?? 'Cari menu atau informasi...';
    }
@endphp

<header class="h-16 px-6 bg-white border-b border-gray-100 flex items-center justify-between sticky top-0 z-30">
    <!-- Tombol hamburger mobile -->
    <button class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 mr-2" onclick="document.getElementById('app-sidebar').classList.toggle('is-open')">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Search Input (hanya ikon search) -->
    <div class="relative flex-1 max-w-md hidden sm:block">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" stroke-width="2"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"></line>
            </svg>
        </span>
        <input type="text"
               placeholder="{{ $searchPlaceholder }}"
               class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
    </div>

    <!-- Right Controls: Year selector, Profile -->
    <div class="flex items-center gap-4 ml-auto">
        <!-- Dropdown Tahun Ajaran -->
        <div class="relative hidden sm:block">
            <select class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-xs rounded-xl py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <option selected>{{ $academicYear }}</option>
                <option>2025/2026 - Genap</option>
                <option>2025/2026 - Ganjil</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </div>
        </div>

        <!-- Profil: klik untuk membuka menu -->
        <div class="relative pl-4 border-l border-gray-100" id="profile-menu">
            <button type="button" id="profile-menu-btn" aria-haspopup="true" aria-expanded="false"
                    class="flex items-center gap-2.5 rounded-xl px-2 py-1 hover:bg-gray-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30">
                <div class="w-8 h-8 rounded-full bg-[#f0408f] text-white flex items-center justify-center text-xs font-bold shadow-sm">
                    {{ $inisial }}
                </div>
                <div class="hidden lg:block text-left">
                    <p class="text-xs font-semibold text-gray-800 leading-tight">{{ $namaUser }}</p>
                    <p class="text-[10px] text-gray-500">{{ $subtext }}</p>
                </div>
                <svg id="profile-menu-chevron" class="w-3.5 h-3.5 text-gray-400 hidden sm:block transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div id="profile-menu-panel" hidden
                 class="absolute right-0 top-full mt-2 w-52 bg-white border border-gray-100 rounded-xl shadow-lg py-1.5 z-50">
                @if ($isGuruBk)
                    <a href="{{ route('bk.profil') }}"
                       class="flex items-center gap-2.5 px-3.5 py-2 text-xs text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4" stroke-width="2"></circle>
                        </svg>
                        Profil Saya
                    </a>
                    <div class="my-1 border-t border-gray-100"></div>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs text-red-600 hover:bg-red-50 transition text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7" stroke-width="2"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12" stroke-width="2"></line>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var wrap = document.getElementById('profile-menu');
        if (!wrap) return;
        var btn = document.getElementById('profile-menu-btn');
        var panel = document.getElementById('profile-menu-panel');
        var chevron = document.getElementById('profile-menu-chevron');

        function setOpen(open) {
            panel.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            chevron.classList.toggle('rotate-180', open);
        }

        btn.addEventListener('click', function (e) { e.stopPropagation(); setOpen(panel.hidden); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    });
</script>