<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun Anda - SIAKAD SMAN 1 Malang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex antialiased text-slate-800 overflow-hidden h-screen">

    <!-- =============================================
         KIRI: Panel Branding (Gradient Biru)
         ============================================= -->
    <div class="hidden lg:flex w-[42%] flex-shrink-0 relative bg-gradient-to-br from-[#12a8dc] via-[#009bf2] to-[#1b6fd0] text-white p-12 flex-col justify-between overflow-hidden h-screen">

        <!-- Dekorasi lingkaran di belakang -->
        <div class="absolute -bottom-24 -right-24 w-[380px] h-[380px] rounded-full border-[50px] border-white/[0.07] pointer-events-none"></div>
        <div class="absolute bottom-32 -right-16 w-[280px] h-[280px] rounded-full border-[40px] border-white/[0.05] pointer-events-none"></div>

        <!-- Logo SIAKAD -->
        <div class="flex items-center gap-3 relative z-10">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center shadow-sm border border-white/20">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold leading-none tracking-wide">SIAKAD</p>
                <p class="text-[11px] text-white/75 mt-0.5">SMA Negeri 1 Malang</p>
            </div>
        </div>

        <!-- Konten Tengah -->
        <div class="relative z-10 my-auto pb-8">
            <span id="left-badge" class="inline-block px-3 py-1 bg-white/15 backdrop-blur-sm rounded-full text-[10px] font-bold tracking-widest uppercase text-white/90 mb-5 border border-white/10">
                SISTEM INFORMASI AKADEMIK
            </span>
            <h1 id="left-heading" class="text-4xl font-extrabold tracking-tight leading-[1.18] mb-5">
                Bimbingan siswa,<br>lebih terarah.
            </h1>
            <p id="left-desc" class="text-sm text-white/80 leading-relaxed max-w-xs">
                Data akademik, hasil tes RIASEC, dan rekomendasi studi dalam satu layanan Guru BK yang terintegrasi.
            </p>
        </div>

        <!-- Alamat Sekolah -->
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm px-4 py-3 rounded-2xl w-fit relative z-10 border border-white/10">
            <div class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center flex-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold leading-tight">SMA Negeri 1 Malang</p>
                <p class="text-[10px] text-white/65 mt-0.5">Jl. Tugu Utara No. 1, Kota Malang</p>
            </div>
        </div>
    </div>

    <!-- =============================================
         KANAN: Form Login
         ============================================= -->
    <div class="flex-1 flex flex-col justify-between overflow-y-auto px-10 py-10 lg:px-16 xl:px-24">

        <!-- Mobile Logo -->
        <div class="flex lg:hidden items-center gap-2 mb-8">
            <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>
            <span class="text-xs font-bold">SIAKAD SMAN 1 MALANG</span>
        </div>

        <!-- Form Area (centered vertically) -->
        <div class="max-w-md w-full mx-auto my-auto">

            <!-- Heading -->
            <div class="mb-7">
                <span class="text-[10px] font-black tracking-widest text-[#009bf2] uppercase block mb-2">SELAMAT DATANG</span>
                <h2 class="text-[2rem] font-extrabold text-[#0f1b33] leading-tight">Masuk ke akun Anda</h2>
                <p id="form-subtext" class="text-xs text-gray-500 mt-1.5">Gunakan akun Guru BK untuk melanjutkan.</p>
            </div>

            <!-- Role Pill (satu pill aktif, klik untuk ganti) -->
            <div class="mb-7">
                <!-- Pill Role Aktif -->
                <button type="button" id="role-pill"
                        onclick="toggleRole()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#f0408f]/60 bg-pink-50 text-[#e8147e] text-xs font-bold transition hover:bg-pink-100 select-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span id="role-label">Guru Bimbingan dan Konseling</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </button>
                <!-- Hint ganti role -->
                <span class="ml-2 text-[10px] text-gray-400">(Klik untuk ganti ke <span id="hint-role">Siswa</span>)</span>
            </div>

            <!-- Flash Error -->
            @if (session('error'))
            <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600 flex items-center gap-2">
                <svg class="w-4 h-4 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            <!-- Form Login -->
            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="role" id="input_role" value="BK">

                <!-- Field Username -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5" id="label-username">
                        Nama pengguna atau NIP
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text"
                               name="username"
                               id="input-username"
                               value="{{ old('username', 'rina.wulandari') }}"
                               required
                               class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400/30 focus:border-blue-400 transition">
                    </div>
                </div>

                <!-- Field Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-700">Kata sandi</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"></rect>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password"
                               name="password"
                               id="input-password"
                               placeholder="Masukkan kata sandi"
                               required
                               class="w-full pl-10 pr-16 py-3 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400/30 focus:border-blue-400 transition">
                        <button type="button" onclick="togglePwd()"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-semibold text-gray-500 hover:text-gray-700 transition">
                            <span id="pwd-toggle-text">Lihat</span>
                        </button>
                    </div>
                </div>

                <!-- Lupa Kata Sandi -->
                <div class="flex justify-end -mt-1">
                    <a href="#" class="text-[11px] font-semibold text-blue-500 hover:underline">Lupa kata sandi?</a>
                </div>

                <!-- Tombol Masuk -->
                <button type="submit"
                        class="w-full py-3.5 bg-[#009bf2] hover:bg-[#0088d4] active:bg-[#007abf] text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-500/20 transition duration-200 flex items-center justify-center gap-2 mt-2">
                    <span>Masuk</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                    </svg>
                </button>
            </form>

            <!-- Keterangan keamanan -->
            <div class="mt-5 flex items-center justify-center gap-2 text-[11px] text-gray-400">
                <svg class="w-3.5 h-3.5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"></rect>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <span>Akun dilindungi dan hanya dapat diakses oleh pengguna terdaftar.</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-[11px] text-gray-400 mt-6">
            &copy; 2025 SMA Negeri 1 Malang &nbsp;·&nbsp;
            <a href="#" class="hover:underline">Pusat Bantuan</a>
        </div>
    </div>

    <!-- =============================================
         Script: Toggle Role
         ============================================= -->
    <script>
        // State role saat ini: 'BK' atau 'SISWA'
        let currentRole = 'BK';

        const data = {
            BK: {
                roleLabel: 'Guru Bimbingan dan Konseling',
                hintRole: 'Siswa',
                subtext: 'Gunakan akun Guru BK untuk melanjutkan.',
                labelUsername: 'Nama pengguna atau NIP',
                defaultUsername: 'rina.wulandari',
                leftHeading: 'Bimbingan siswa,\nlebih terarah.',
                leftDesc: 'Data akademik, hasil tes RIASEC, dan rekomendasi studi dalam satu layanan Guru BK yang terintegrasi.',
                roleIcon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>`,
                inputRole: 'BK',
            },
            SISWA: {
                roleLabel: 'Siswa',
                hintRole: 'Guru BK',
                subtext: 'Gunakan akun Siswa untuk melanjutkan.',
                labelUsername: 'NIS atau Nama Pengguna',
                defaultUsername: 'nadia',
                leftHeading: 'Kenali minat\ndan potensimu.',
                leftDesc: 'Ikuti tes RIASEC, lihat hasil peminatan, dan ajukan konsultasi ke Guru BK kapan saja.',
                roleIcon: `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>`,
                inputRole: 'SISWA',
            }
        };

        function toggleRole() {
            currentRole = currentRole === 'BK' ? 'SISWA' : 'BK';
            applyRole(currentRole);
        }

        function applyRole(role) {
            const d = data[role];

            // Pill label
            document.getElementById('role-label').textContent = d.roleLabel;
            document.getElementById('hint-role').textContent = d.hintRole;

            // Form
            document.getElementById('form-subtext').textContent = d.subtext;
            document.getElementById('label-username').textContent = d.labelUsername;
            document.getElementById('input-username').value = d.defaultUsername;
            document.getElementById('input_role').value = d.inputRole;
            document.getElementById('input-password').value = '';

            // Kiri
            const h = document.getElementById('left-heading');
            if (h) {
                const lines = d.leftHeading.split('\n');
                h.innerHTML = lines.join('<br>');
            }
            const desc = document.getElementById('left-desc');
            if (desc) desc.textContent = d.leftDesc;

            // Ikon pill
            document.querySelector('#role-pill svg:first-child').innerHTML = d.roleIcon;
        }

        function togglePwd() {
            const pwd = document.getElementById('input-password');
            const txt = document.getElementById('pwd-toggle-text');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                txt.textContent = 'Sembunyikan';
            } else {
                pwd.type = 'password';
                txt.textContent = 'Lihat';
            }
        }
    </script>
</body>
</html>
