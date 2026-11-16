@props([
    'academicYear' => '2025/2026 · Ganjil',
    'searchPlaceholder' => null,
])

@php
    $user = auth()->user();
    $role = strtoupper($user?->role ?? '');

    if ($role === 'ADMIN') {
        $namaUser = $user->name ?? $user->username ?? 'Administrator';
        $subtext = 'Administrator SIAKAD';
        $inisial = 'AD';
        $avatarBg = '#0284c7';
        $profileRoute = route('admin.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari data, menu, atau pertanyaan...';
    } elseif (in_array($role, ['GURU_BK', 'GURU', 'BK'])) {
        $guru = \App\Models\Guru::where('id_user', $user?->id_user)->first();
        $namaUser = $guru?->nama ?? $user?->name ?? 'Guru BK';
        $subtext = 'Guru Bimbingan dan Konseling';
        $initials = collect(explode(' ', trim($namaUser)))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
        $inisial = $initials ?: 'GB';
        $avatarBg = '#f0408f';
        $profileRoute = route('bk.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari siswa, NIS, atau pengajuan...';
    } else { // SISWA
        $siswa = \App\Models\Siswa::with('riwayatKelasAktif.kelas')->where('id_user', $user?->id_user)->first();
        $namaUser = $siswa?->nama ?? $user?->name ?? 'Siswa';
        $kelasNama = $siswa?->riwayatKelasAktif?->kelas?->nama ?? '';
        $subtext = 'Siswa' . ($kelasNama ? ' · Kelas ' . $kelasNama : '');
        $initials = collect(explode(' ', trim($namaUser)))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
        $inisial = $initials ?: 'NA';
        $avatarBg = '#e11d48';
        $profileRoute = route('siswa.profil');
        $searchPlaceholder = $searchPlaceholder ?? 'Cari menu atau informasi...';
    }
@endphp

<header class="h-20 px-8 bg-white border-b-[0.80px] border-slate-200 backdrop-blur-[10px] flex items-center justify-between sticky top-0 z-30">
    <!-- Tombol hamburger mobile -->
    <button class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 mr-2" onclick="document.getElementById('app-sidebar').classList.toggle('is-open')">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    <!-- Search Input dengan search icon & shortcut K (Sesuai Figma) -->
    <div class="relative flex-1 max-w-[538px] hidden sm:block">
        <div class="w-full h-12 px-3.5 bg-gray-50 rounded-xl outline outline-[0.80px] outline-offset-[-0.80px] outline-zinc-200 flex items-center gap-2.5 transition focus-within:outline-[#089BD5] focus-within:ring-2 focus-within:ring-[#089BD5]/20">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" class="flex-none">
                <path d="M14.9987 9.16671C14.9987 12.3884 12.387 15 9.16536 15C5.9437 15 3.33203 12.3884 3.33203 9.16671C3.33203 5.94505 5.9437 3.33337 9.16536 3.33337C12.387 3.33337 14.9987 5.94505 14.9987 9.16671Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="#8290A3"/>
                <path d="M16.6654 16.6667L13.332 13.3334" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="#8290A3"/>
            </svg>
            <input type="text"
                   placeholder="{{ $searchPlaceholder }}"
                   class="flex-1 bg-transparent border-0 outline-none text-slate-700 text-xs font-normal font-['Plus_Jakarta_Sans'] placeholder-gray-400">
            <span class="text-[10px] font-bold text-gray-400 font-['Plus_Jakarta_Sans'] flex-none">
                ⌘ K
            </span>
        </div>
    </div>

    <!-- Right Controls: Year selector, Profile -->
    <div class="flex items-center gap-3.5 ml-auto">
        <!-- Badge Semester Aktif (Sesuai Figma) -->
        <div class="hidden sm:flex items-center justify-between w-40 h-11 px-3 bg-white rounded-lg outline outline-[0.80px] outline-offset-[-0.80px] outline-zinc-200 shadow-2xs">
            <span class="text-gray-500 text-[10px] font-normal font-['Plus_Jakarta_Sans'] truncate">{{ $academicYear }}</span>
            <svg width="10" height="6" viewBox="0 0 10 6" fill="none" class="flex-none ml-2">
                <path d="M1 1L5 5L9 1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="#5F6D7E"/>
            </svg>
        </div>

        <!-- Profil Dropdown (Sesuai Figma) -->
        <div class="relative" id="profile-menu">
            <button type="button" id="profile-menu-btn" aria-haspopup="true" aria-expanded="false"
                    class="flex items-center gap-2.5 rounded-xl px-2 py-1.5 hover:bg-gray-50 transition focus:outline-none">
                <div class="w-11 h-11 bg-gradient-to-r from-pink-600 to-rose-400 rounded-3xl shadow-[0px_7px_15px_0px_rgba(231,47,119,0.18)] flex items-center justify-center text-white text-xs font-extrabold font-['Plus_Jakarta_Sans'] flex-none"
                     style="{{ $role === 'ADMIN' ? 'background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); box-shadow: 0px 7px 15px rgba(2, 132, 199, 0.2);' : '' }}">
                    {{ $inisial }}
                </div>
                <div class="hidden lg:block text-left">
                    <p class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">{{ $namaUser }}</p>
                    <p class="text-slate-500 text-[9px] font-normal font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">{{ $subtext }}</p>
                </div>
                <svg id="profile-menu-chevron" class="w-3.5 h-3.5 text-gray-400 hidden sm:block transition-transform ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div id="profile-menu-panel" hidden
                 class="absolute right-0 top-full mt-2 w-56 bg-white border border-gray-100 rounded-2xl shadow-xl py-2 z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-xs font-bold text-slate-800">{{ $namaUser }}</p>
                    <p class="text-[10px] text-gray-400">{{ $subtext }}</p>
                </div>
                <a href="{{ $profileRoute }}"
                   class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Saya
                </a>
                <a href="{{ $profileRoute }}#keamanan"
                   class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-sky-50 hover:text-sky-700 transition">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"></rect>
                        <path d="M7 11V7a5 5 0 0110 0v4" stroke-width="2"></path>
                    </svg>
                    Ubah Kata Sandi
                </a>
                <div class="my-1 border-t border-gray-100"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition text-left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
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
            if (chevron) chevron.classList.toggle('rotate-180', open);
        }

        btn.addEventListener('click', function (e) { e.stopPropagation(); setOpen(panel.hidden); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
    });
</script>
