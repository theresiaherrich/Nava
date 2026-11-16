@props(['context'])

@php
    $role = $context['role'];
    $dashboardRoute = $context['homeRoute'];
    $profileRoute = $context['profileRoute'];
    $isAdmin = $role === 'ADMIN';
    $isGuruBk = in_array($role, ['GURU', 'GURU_BK'], true);
    $tingkat = $context['grade'];

    if ($isAdmin) {
        $groupTitle1 = 'A3 · Pemetaan';
        $groupTitle2 = 'Minat Siswa';
    } elseif ($isGuruBk) {
        $groupTitle1 = 'A3 · Bimbingan';
        $groupTitle2 = 'Konseling';
    } elseif ($tingkat === 'XI') {
        $groupTitle1 = 'A3 · Bimbingan';
        $groupTitle2 = 'Konseling';
    } else {
        $groupTitle1 = 'A3 · Peminatan';
        $groupTitle2 = 'dan Konseling';
    }

    $academicYear = $context['academicYear'] ?? '2025/2026 · Ganjil';
@endphp

<aside class="w-60 min-h-screen bg-[#089BD5] flex flex-col justify-between px-3.5 pt-5 pb-5 text-white font-['Plus_Jakarta_Sans'] select-none flex-none sticky top-0 h-screen overflow-y-auto"
       id="app-sidebar"
       style="background: linear-gradient(180deg, #10A3DE 0%, #089BD5 45%, #0B7EBE 100%);">
    
    <div>
        {{-- Brand Header (Logo & SMAN 1 Malang) --}}
        <a href="{{ route($dashboardRoute) }}" class="flex items-center gap-2.5 px-1.5 pb-4 border-b border-white/15 hover:opacity-95 transition">
            <img src="{{ asset('images/logo-sman1.png') }}" alt="Logo SMA Negeri 1 Malang" class="h-8 w-auto flex-none object-contain drop-shadow">
            <div class="leading-tight min-w-0">
                <div class="text-white text-sm font-bold font-['Poppins'] tracking-tight">SIAKAD</div>
                <div class="text-white/90 text-[11px] font-normal truncate">SMA Negeri 1 Malang</div>
            </div>
        </a>

        {{-- Navigation Section --}}
        <div class="pt-5">
            <div class="text-white/60 text-[9.5px] font-extrabold tracking-widest uppercase px-1.5 mb-2.5">
                MENU UTAMA
            </div>

            {{-- Module Header Card (Pink pill) --}}
            <div class="w-full px-3 py-2.5 bg-gradient-to-r from-pink-600 to-rose-400 rounded-xl shadow-[0px_6px_16px_0px_rgba(201,37,94,0.22)] flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-2 min-w-0">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" class="flex-none">
                        <path d="M13.3346 6.66671C13.3346 8.50766 11.8423 10 10.0013 10C8.16035 10 6.66797 8.50766 6.66797 6.66671C6.66797 4.82576 8.16035 3.33337 10.0013 3.33337C11.8423 3.33337 13.3346 4.82576 13.3346 6.66671Z" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="white"/>
                        <path d="M3.33203 17.5C3.33203 15.7319 4.03441 14.0362 5.28465 12.786C6.5349 11.5358 8.23059 10.8334 9.9987 10.8334C11.7668 10.8334 13.4625 11.5358 14.7127 12.786C15.963 14.0362 16.6654 15.7319 16.6654 17.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="white"/>
                    </svg>
                    <div class="text-white text-[11px] font-bold leading-tight truncate">
                        {{ $groupTitle1 }}<br>{{ $groupTitle2 }}
                    </div>
                </div>
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" class="flex-none opacity-90">
                    <path d="M4 6L8 10L12 6" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="white"/>
                </svg>
            </div>

            {{-- Submenu Items with Tree Connector Line --}}
            <div class="relative pl-4 pt-1 space-y-0.5">
                {{-- Vertical Tree Line --}}
                <div class="absolute left-[16px] top-2 bottom-2 w-px bg-white/25 pointer-events-none"></div>

                @if ($isAdmin)
                    {{-- ADMIN MENU --}}
                    @php $act = request()->routeIs('admin.dashboard'); @endphp
                    <a href="{{ route('admin.dashboard') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.125 7.65L8.5 2.125L14.875 7.65" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M3.54297 6.94165V14.875H6.3763M6.3763 14.875H10.6263M6.3763 14.875V9.91665H10.6263V14.875M10.6263 14.875H13.4596V6.94165" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Dashboard</span>
                    </a>

                    @php $act = request()->routeIs('admin.dimensi*'); @endphp
                    <a href="{{ route('admin.dimensi') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                            <path d="M10 2.5L16.6667 6.25V13.75L10 17.5L3.33333 13.75V6.25L10 2.5Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Dimensi RIASEC</span>
                    </a>

                    @php $act = request()->routeIs('admin.soal*'); @endphp
                    <a href="{{ route('admin.soal') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                            <path d="M7.5 7.5C7.5 6.11929 8.61929 5 10 5C11.3807 5 12.5 6.11929 12.5 7.5C12.5 8.5 11.5 9 10 10V11M10 14H10.01" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Pertanyaan Tes</span>
                    </a>

                    @php $act = request()->routeIs('admin.mapel*'); @endphp
                    <a href="{{ route('admin.mapel') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.83203 2.83337H7.79036C8.35395 2.83337 8.89445 3.05726 9.29297 3.45577C9.69148 3.85429 9.91536 4.39479 9.91536 4.95837V14.875C9.91536 14.3115 9.69148 13.771 9.29297 13.3724C8.89445 12.9739 8.35395 12.75 7.79036 12.75H2.83203V2.83337Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M14.168 2.83337H12.043C11.4794 2.83337 10.9389 3.05726 10.5404 3.45577C10.1419 3.85429 9.91797 4.39479 9.91797 4.95837V14.875C9.91797 14.3115 10.1419 13.771 10.5404 13.3724C10.9389 12.9739 11.4794 12.75 12.043 12.75H14.168V2.83337Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Mapel Pilihan</span>
                    </a>

                    @php $act = request()->routeIs('admin.prodi*'); @endphp
                    <a href="{{ route('admin.prodi') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                            <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M4.16667 8.75V13.3333C4.16667 13.3333 6.66667 15.8333 10 15.8333C13.3333 15.8333 15.8333 13.3333 15.8333 13.3333V8.75" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Program Studi</span>
                    </a>

                    @php $act = request()->routeIs('admin.profesi*'); @endphp
                    <a href="{{ route('admin.profesi') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                            <path d="M16.668 17.5V15.8333C16.668 14.9493 16.3168 14.1014 15.6917 13.4763C15.0666 12.8512 14.2187 12.5 13.3346 12.5H6.66797C5.78391 12.5 4.93607 12.8512 4.31095 13.4763C3.68582 14.1014 3.33464 14.9493 3.33464 15.8333V17.5" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M10.0013 9.16667C11.8423 9.16667 13.3346 7.67428 13.3346 5.83333C13.3346 3.99238 11.8423 2.5 10.0013 2.5C8.16035 2.5 6.66797 3.99238 6.66797 5.83333C6.66797 7.67428 8.16035 9.16667 10.0013 9.16667Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Profesi</span>
                    </a>

                @elseif ($isGuruBk)
                    {{-- GURU BK MENU --}}
                    @php $act = request()->routeIs('bk.dashboard'); @endphp
                    <a href="{{ route('bk.dashboard') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.125 7.65L8.5 2.125L14.875 7.65" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M3.54297 6.94165V14.875H6.3763M6.3763 14.875H10.6263M6.3763 14.875V9.91665H10.6263V14.875M10.6263 14.875H13.4596V6.94165" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Dashboard BK</span>
                    </a>

                    @php $act = request()->routeIs('bk.rekap-tes*'); @endphp
                    <a href="{{ route('bk.rekap-tes') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.83464 13.4583V6.37496M2.83464 13.4583H7.08464M2.83464 13.4583H1.41797M7.08464 13.4583V3.54163M7.08464 13.4583H11.3346M11.3346 13.4583V8.49996M11.3346 13.4583H15.5846" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Rekap Tes RIASEC</span>
                    </a>

                    @php $act = request()->routeIs('bk.pengajuan*'); @endphp
                    <a href="{{ route('bk.pengajuan') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M14.875 10.625C14.875 11.3764 14.5765 12.0971 14.0451 12.6285C13.5138 13.1598 12.7931 13.4583 12.0417 13.4583H5.66667L2.125 15.5833V4.95833C2.125 4.20689 2.42351 3.48622 2.95486 2.95486C3.48622 2.42351 4.20689 2.125 4.95833 2.125H12.0417C12.7931 2.125 13.5138 2.42351 14.0451 2.95486C14.5765 3.48622 14.875 4.20689 14.875 4.95833V10.625Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M5.66797 6.375H11.3346M5.66797 9.20833H9.20964" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Pengajuan BK</span>
                    </a>

                    @php $act = request()->routeIs('bk.validasi*'); @endphp
                    <a href="{{ route('bk.validasi') }}#mapel" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.83203 2.83337H7.79036C8.35395 2.83337 8.89445 3.05726 9.29297 3.45577C9.69148 3.85429 9.91536 4.39479 9.91536 4.95837V14.875C9.91536 14.3115 9.69148 13.771 9.29297 13.3724C8.89445 12.9739 8.35395 12.75 7.79036 12.75H2.83203V2.83337Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Validasi Mapel</span>
                    </a>

                    <a href="{{ route('bk.validasi') }}#prodi" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition text-white/85 hover:bg-white/10 hover:text-white">
                        <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                            <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Validasi Program Studi</span>
                    </a>

                    @php $act = request()->routeIs('bk.riwayat*'); @endphp
                    <a href="{{ route('bk.riwayat') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.125 8.50005C2.12367 9.95996 2.62346 11.3761 3.54084 12.5118C4.45821 13.6475 5.73759 14.4339 7.16514 14.7395C8.59269 15.0452 10.0819 14.8517 11.3839 14.1913C12.6859 13.5308 13.7217 12.4435 14.3184 11.1111C14.915 9.77866 15.0362 8.28182 14.6617 6.87075C14.2873 5.45967 13.4399 4.21987 12.2611 3.35856C11.0823 2.49725 9.64366 2.06661 8.18553 2.13862C6.7274 2.21064 5.33816 2.78094 4.25 3.75422L2.125 5.66672" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M2.125 2.125V5.66667H5.66667M8.5 4.95833V8.5L10.625 9.91667" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Riwayat Bimbingan</span>
                    </a>

                @else
                    {{-- SISWA MENU (KELAS X, XI, XII) --}}
                    @php $act = request()->routeIs('siswa.dashboard'); @endphp
                    <a href="{{ route('siswa.dashboard') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.125 7.65L8.5 2.125L14.875 7.65" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M3.54297 6.94165V14.875H6.3763M6.3763 14.875H10.6263M6.3763 14.875V9.91665H10.6263V14.875M10.6263 14.875H13.4596V6.94165" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Dashboard</span>
                    </a>

                    @if (in_array($tingkat, ['X', 'XII'], true))
                        @php $act = request()->routeIs('siswa.tes*'); @endphp
                        <a href="{{ route('siswa.tes.petunjuk') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M6.3737 3.54163H4.2487C3.87297 3.54163 3.51264 3.69088 3.24696 3.95656C2.98129 4.22223 2.83203 4.58257 2.83203 4.95829V14.875H14.1654V4.95829C14.1654 4.58257 14.0161 4.22223 13.7504 3.95656C13.4848 3.69088 13.1244 3.54163 12.7487 3.54163H10.6237" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M6.3763 2.125H10.6263C11.0175 2.125 11.3346 2.44213 11.3346 2.83333V4.25C11.3346 4.6412 11.0175 4.95833 10.6263 4.95833H6.3763C5.9851 4.95833 5.66797 4.6412 5.66797 4.25V2.83333C5.66797 2.44213 5.9851 2.125 6.3763 2.125Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M5.66797 9.20837L7.08464 10.625L10.6263 7.08337M5.66797 13.4584H11.3346" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Tes RIASEC</span>
                        </a>
                    @endif

                    @php $act = request()->routeIs('siswa.hasil*'); @endphp
                    <a href="{{ route('siswa.hasil') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M2.83464 13.4583V6.37496M2.83464 13.4583H7.08464M2.83464 13.4583H1.41797M7.08464 13.4583V3.54163M7.08464 13.4583H11.3346M11.3346 13.4583V8.49996M11.3346 13.4583H15.5846" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">{{ $tingkat === 'XI' ? 'Hasil RIASEC' : 'Hasil Tes' }}</span>
                    </a>

                    @if ($tingkat === 'XI')
                        @php $act = request()->routeIs('siswa.riwayat-tes*'); @endphp
                        <a href="{{ route('siswa.riwayat-tes') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M2.125 8.50005C2.12367 9.95996 2.62346 11.3761 3.54084 12.5118C4.45821 13.6475 5.73759 14.4339 7.16514 14.7395C8.59269 15.0452 10.0819 14.8517 11.3839 14.1913C12.6859 13.5308 13.7217 12.4435 14.3184 11.1111C14.915 9.77866 15.0362 8.28182 14.6617 6.87075C14.2873 5.45967 13.4399 4.21987 12.2611 3.35856C11.0823 2.49725 9.64366 2.06661 8.18553 2.13862C6.7274 2.21064 5.33816 2.78094 4.25 3.75422L2.125 5.66672" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M2.125 2.125V5.66667H5.66667M8.5 4.95833V8.5L10.625 9.91667" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Riwayat Tes</span>
                        </a>

                        @php $act = request()->routeIs('siswa.mapel-pilihan*'); @endphp
                        <a href="{{ route('siswa.mapel-pilihan') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M2.83203 2.83337H7.79036C8.35395 2.83337 8.89445 3.05726 9.29297 3.45577C9.69148 3.85429 9.91536 4.39479 9.91536 4.95837V14.875C9.91536 14.3115 9.69148 13.771 9.29297 13.3724C8.89445 12.9739 8.35395 12.75 7.79036 12.75H2.83203V2.83337Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M14.168 2.83337H12.043C11.4794 2.83337 10.9389 3.05726 10.5404 3.45577C10.1419 3.85429 9.91797 4.39479 9.91797 4.95837V14.875C9.91797 14.3115 10.1419 13.771 10.5404 13.3724C10.9389 12.9739 11.4794 12.75 12.043 12.75H14.168V2.83337Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Mata Pelajaran Pilihan</span>
                        </a>
                    @elseif ($tingkat === 'X')
                        @php $act = request()->routeIs('siswa.mapel-pilihan*'); @endphp
                        <a href="{{ route('siswa.mapel-pilihan') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M2.83203 2.83337H7.79036C8.35395 2.83337 8.89445 3.05726 9.29297 3.45577C9.69148 3.85429 9.91536 4.39479 9.91536 4.95837V14.875C9.91536 14.3115 9.69148 13.771 9.29297 13.3724C8.89445 12.9739 8.35395 12.75 7.79036 12.75H2.83203V2.83337Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M14.168 2.83337H12.043C11.4794 2.83337 10.9389 3.05726 10.5404 3.45577C10.1419 3.85429 9.91797 4.39479 9.91797 4.95837V14.875C9.91797 14.3115 10.1419 13.771 10.5404 13.3724C10.9389 12.9739 11.4794 12.75 12.043 12.75H14.168V2.83337Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Rekomendasi Mapel</span>
                        </a>

                        @php $act = request()->routeIs('siswa.riwayat-tes*'); @endphp
                        <a href="{{ route('siswa.riwayat-tes') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M2.125 8.50005C2.12367 9.95996 2.62346 11.3761 3.54084 12.5118C4.45821 13.6475 5.73759 14.4339 7.16514 14.7395C8.59269 15.0452 10.0819 14.8517 11.3839 14.1913C12.6859 13.5308 13.7217 12.4435 14.3184 11.1111C14.915 9.77866 15.0362 8.28182 14.6617 6.87075C14.2873 5.45967 13.4399 4.21987 12.2611 3.35856C11.0823 2.49725 9.64366 2.06661 8.18553 2.13862C6.7274 2.21064 5.33816 2.78094 4.25 3.75422L2.125 5.66672" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M2.125 2.125V5.66667H5.66667M8.5 4.95833V8.5L10.625 9.91667" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Riwayat Tes</span>
                        </a>
                    @elseif ($tingkat === 'XII')
                        @php $act = request()->routeIs('siswa.mapel-pilihan*') || request()->is('*rekomendasi*'); @endphp
                        <a href="{{ route('siswa.hasil') }}#rekomendasi" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                                <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Rekomendasi Prodi</span>
                        </a>

                        @php $act = request()->routeIs('siswa.riwayat-tes*'); @endphp
                        <a href="{{ route('siswa.riwayat-tes') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                            @if($act)
                                <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                            @endif
                            <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                                <path d="M2.125 8.50005C2.12367 9.95996 2.62346 11.3761 3.54084 12.5118C4.45821 13.6475 5.73759 14.4339 7.16514 14.7395C8.59269 15.0452 10.0819 14.8517 11.3839 14.1913C12.6859 13.5308 13.7217 12.4435 14.3184 11.1111C14.915 9.77866 15.0362 8.28182 14.6617 6.87075C14.2873 5.45967 13.4399 4.21987 12.2611 3.35856C11.0823 2.49725 9.64366 2.06661 8.18553 2.13862C6.7274 2.21064 5.33816 2.78094 4.25 3.75422L2.125 5.66672" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                                <path d="M2.125 2.125V5.66667H5.66667M8.5 4.95833V8.5L10.625 9.91667" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            </svg>
                            <span class="text-[11.5px] leading-none">Riwayat Tes</span>
                        </a>
                    @endif

                    @php $act = request()->routeIs('siswa.pengajuan-bk*'); @endphp
                    <a href="{{ route('siswa.pengajuan-bk') }}" class="relative group flex items-center gap-2.5 px-2.5 py-2 rounded-lg transition {{ $act ? 'bg-white/20 text-white font-semibold shadow-2xs' : 'text-white/85 hover:bg-white/10 hover:text-white' }}">
                        @if($act)
                            <span class="absolute -left-[17px] top-2 bottom-2 w-1 bg-white rounded-r-md"></span>
                        @endif
                        <svg width="16" height="16" viewBox="0 0 17 17" fill="none" class="flex-none">
                            <path d="M14.875 10.625C14.875 11.3764 14.5765 12.0971 14.0451 12.6285C13.5138 13.1598 12.7931 13.4583 12.0417 13.4583H5.66667L2.125 15.5833V4.95833C2.125 4.20689 2.42351 3.48622 2.95486 2.95486C3.48622 2.42351 4.20689 2.125 4.95833 2.125H12.0417C12.7931 2.125 13.5138 2.42351 14.0451 2.95486C14.5765 3.48622 14.875 4.20689 14.875 4.95833V10.625Z" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                            <path d="M5.66797 6.375H11.3346M5.66797 9.20833H9.20964" stroke-width="1.275" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                        </svg>
                        <span class="text-[11.5px] leading-none">Pengajuan BK</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom Area (Tahun Ajaran & Keluar) --}}
    <div class="pt-3 border-t border-white/15 space-y-2">
        <div class="w-full px-3 py-2.5 bg-white/15 rounded-xl flex items-center gap-2.5">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="none" class="flex-none">
                <path d="M17.5 14.1666V8.33331L10 3.33331L2.5 8.33331L5 9.99998L10 13.3333L15 9.99998L17.5 8.33331M15 9.99998V14.1666L10 16.6666L5 14.1666V9.99998" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="white"/>
            </svg>
            <div class="leading-tight min-w-0">
                <div class="text-white/70 text-[8.5px] font-normal">Tahun Ajaran</div>
                <div class="text-white text-[11px] font-bold truncate">{{ $academicYear }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2.5 px-2.5 py-1.5 text-white/70 hover:text-white hover:bg-white/10 rounded-lg transition text-left cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" class="flex-none">
                    <path d="M8.33333 5.83331L12.5 9.99998L8.33333 14.1666M12.5 9.99998H2.5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                    <path d="M11.668 3.33331H15.8346C16.2767 3.33331 16.7006 3.50891 17.0131 3.82147C17.3257 4.13403 17.5013 4.55795 17.5013 4.99998V15C17.5013 15.442 17.3257 15.8659 17.0131 16.1785C16.7006 16.4911 16.2767 16.6666 15.8346 16.6666H11.668" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor"/>
                </svg>
                <span class="text-[11.5px] font-normal">Keluar</span>
            </button>
        </form>
    </div>
</aside>
