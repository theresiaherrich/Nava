@props([
    'academicYear' => '2026/2027 - Ganjil',
    'searchPlaceholder' => null,
])

@php
    $user = auth()->user();
    $role = $user?->role ?? 'BK';
    $isGuruBk = in_array(strtoupper($role), ['BK', 'GURU']);

    // Default nama & inisial jika belum ada profil spesifik di auth
    if ($isGuruBk) {
        $namaUser = 'Rina Wulandari, S.Pd.';
        $subtext = 'Guru Bimbingan dan Konseling';
        $inisial = 'RW';
        $searchPlaceholder = $searchPlaceholder ?? 'Cari siswa atau NIS...';
    } else {
        $namaUser = 'Nadia Azzahra Putri';
        $subtext = 'Siswa - Kelas X-3';
        $inisial = 'NA';
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

    <!-- Search Input -->
    <div class="relative flex-1 max-w-md hidden sm:block">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" stroke-width="2"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"></line>
            </svg>
        </span>
        <input type="text"
               placeholder="{{ $searchPlaceholder }}"
               class="w-full pl-9 pr-12 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
        @if ($isGuruBk)
            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-[10px] text-gray-400 font-mono">
                ⌘ K
            </span>
        @endif
    </div>

    <!-- Right Controls: Year selector, Notification, Profile -->
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

        <!-- Notification Bell -->
        <button class="relative p-2 text-gray-500 hover:bg-gray-100 rounded-xl transition" title="Notifikasi">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">2</span>
        </button>

        <!-- Profile Avatar Pill -->
        <div class="flex items-center gap-2.5 pl-2 border-l border-gray-100 cursor-pointer">
            <div class="w-8 h-8 rounded-full bg-[#f0408f] text-white flex items-center justify-center text-xs font-bold shadow-sm">
                {{ $inisial }}
            </div>
            <div class="hidden lg:block text-left">
                <p class="text-xs font-semibold text-gray-800 leading-tight">{{ $namaUser }}</p>
                <p class="text-[10px] text-gray-500">{{ $subtext }}</p>
            </div>
            <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </div>
</header>

