<x-layouts.admin title="Dashboard Admin - SIAKAD SMA Negeri 1 Malang">
    @php
        $namaAdmin = auth()->user()?->name ?? auth()->user()?->username ?? 'Administrator';
        $semesterNama = match (strtoupper($semester?->nama ?? '')) {
            'GASAL' => 'Ganjil',
            'GENAP' => 'Genap',
            default => $semester?->nama ?? 'Ganjil',
        };
        $tahunAjaran = $semester?->tahunAjaran?->nama ?? '2026/2027';
        $tanggalHariIni = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y');

        // Setup 6-dimension data with colors matching Figma
        $dimensiPalette = [
            'R' => ['color' => '#3B82F6', 'bg_bar' => 'bg-blue-500', 'nama' => 'Realistic'],
            'I' => ['color' => '#6366F1', 'bg_bar' => 'bg-indigo-500', 'nama' => 'Investigative'],
            'A' => ['color' => '#EC4899', 'bg_bar' => 'bg-pink-500', 'nama' => 'Artistic'],
            'S' => ['color' => '#0D9488', 'bg_bar' => 'bg-teal-600', 'nama' => 'Social'],
            'E' => ['color' => '#FB923C', 'bg_bar' => 'bg-orange-400', 'nama' => 'Enterprising'],
            'C' => ['color' => '#64748B', 'bg_bar' => 'bg-slate-500', 'nama' => 'Conventional'],
        ];

        $chartData = [
            'R' => ['val' => 68, 'color' => '#3B82F6', 'bg_bar' => 'bg-blue-500', 'nama' => 'Realistic'],
            'I' => ['val' => 74, 'color' => '#6366F1', 'bg_bar' => 'bg-indigo-500', 'nama' => 'Investigative'],
            'A' => ['val' => 65, 'color' => '#EC4899', 'bg_bar' => 'bg-pink-500', 'nama' => 'Artistic'],
            'S' => ['val' => 70, 'color' => '#0D9488', 'bg_bar' => 'bg-teal-600', 'nama' => 'Social'],
            'E' => ['val' => 62, 'color' => '#FB923C', 'bg_bar' => 'bg-orange-400', 'nama' => 'Enterprising'],
            'C' => ['val' => 67, 'color' => '#64748B', 'bg_bar' => 'bg-slate-500', 'nama' => 'Conventional'],
        ];

        if (isset($distribusi) && $distribusi->isNotEmpty()) {
            foreach ($distribusi as $item) {
                $k = strtoupper($item['kode'] ?? '');
                if (isset($chartData[$k])) {
                    $val = (int) round($item['nilai'] ?? 0);
                    if ($val > 0) {
                        $chartData[$k]['val'] = $val;
                    }
                }
            }
        }
    @endphp

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb Eyebrow --}}
        <div class="text-[#089BD5] text-[10px] font-bold font-['Plus_Jakarta_Sans'] tracking-wider uppercase mb-1">
            SMA NEGERI 1 KOTA MALANG · ADMIN
        </div>

        {{-- Header Greeting (LEBIH BOLD SESUAI PERMINTAAN USER) & Tanggal --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">
                    Selamat {{ $greeting }}, {{ $namaAdmin }}
                </h1>
                <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">
                    Berikut ringkasan data pemetaan minat dan karier siswa hari ini.
                </p>
            </div>
            <div class="flex-none">
                <div class="h-11 px-4 bg-white rounded-xl outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-2.5 shadow-2xs">
                    <svg width="18" height="18" viewBox="0 0 20 20" fill="none" stroke="#64748B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="10" cy="10" r="7.5"/>
                        <polyline points="10 6 10 10 12.5 11.5"/>
                    </svg>
                    <span class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans']">
                        {{ $tanggalHariIni }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Banner Card: Semester Aktif (Sesuai Referensi PDF Figma Admin) --}}
        <div class="min-h-20 px-5 py-4 bg-sky-50 rounded-xl outline outline-[0.80px] outline-offset-[-0.80px] outline-blue-100 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <path d="M7.4987 4.16663H4.9987C4.55667 4.16663 4.13275 4.34222 3.82019 4.65478C3.50763 4.96734 3.33203 5.39127 3.33203 5.83329V17.5H16.6654V5.83329C16.6654 5.39127 16.4898 4.96734 16.1772 4.65478C15.8646 4.34222 15.4407 4.16663 14.9987 4.16663H12.4987" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.5013 2.5H12.5013C12.9615 2.5 13.3346 2.8731 13.3346 3.33333V5C13.3346 5.46024 12.9615 5.83333 12.5013 5.83333H7.5013C7.04106 5.83333 6.66797 5.46024 6.66797 5V3.33333C6.66797 2.8731 7.04106 2.5 7.5013 2.5Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.66797 10.8334L8.33464 12.5L12.5013 8.33337M6.66797 15.8334H13.3346" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-[#089BD5] text-[9px] font-extrabold font-['Plus_Jakarta_Sans'] uppercase tracking-wider">
                        SEMESTER AKTIF
                    </div>
                    <div class="text-slate-800 text-[12px] font-bold font-['Plus_Jakarta_Sans'] mt-0.5">
                        Semester {{ $semesterNama }} · Tahun Ajaran {{ $tahunAjaran }}
                    </div>
                    <div class="text-slate-500 text-[9.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Tes RIASEC dan pemetaan program studi sedang berlangsung mengikuti semester aktif.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 bg-emerald-100 text-[#157A46] rounded-full text-[9px] font-extrabold font-['Plus_Jakarta_Sans']">
                    Aktif
                </span>
                <a href="{{ route('admin.dimensi') }}" class="inline-flex items-center gap-1 text-[#089BD5] text-[11px] font-bold font-['Plus_Jakarta_Sans'] hover:underline">
                    <span>Lihat riwayat</span>
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M5.625 11.25L9.375 7.5L5.625 3.75" stroke="#089BD5" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 4 Stat Cards Sesuai Desain Figma Admin --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            {{-- Card 1: Total Pertanyaan --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.5 7.5C7.5 6.11929 8.61929 5 10 5C11.3807 5 12.5 6.11929 12.5 7.5C12.5 8.5 11.5 9 10 10V11M10 14H10.01" stroke="#089BD5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Total Pertanyaan</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $stats['pertanyaan_aktif'] }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ $stats['pertanyaan_aktif'] }} pertanyaan aktif
                    </div>
                </div>
            </div>

            {{-- Card 2: Dimensi RIASEC --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-purple-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 2.5L16.6667 6.25V13.75L10 17.5L3.33333 13.75V6.25L10 2.5Z" stroke="#8B4FE6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M10 10V17.5M10 10L16.6667 6.25M10 10L3.33333 6.25" stroke="#8B4FE6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Dimensi RIASEC</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $stats['dimensi'] }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Semua dimensi aktif
                    </div>
                </div>
            </div>

            {{-- Card 3: Mapel Pilihan --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-pink-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3.33203 3.33337H9.16536C9.82841 3.33337 10.4643 3.59677 10.9331 4.06561C11.402 4.53445 11.6654 5.17033 11.6654 5.83337V17.5C11.6654 16.837 11.402 16.2011 10.9331 15.7323C10.4643 15.2634 9.82841 15 9.16536 15H3.33203V3.33337Z" stroke="#EC4899" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.668 3.33337H14.168C13.5049 3.33337 12.869 3.59677 12.4002 4.06561C11.9314 4.53445 11.668 5.17033 11.668 5.83337V17.5C11.668 16.837 11.9314 16.2011 12.4002 15.7323C12.869 15.2634 13.5049 15 14.168 15H16.668V3.33337Z" stroke="#EC4899" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Mapel Pilihan</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $stats['mapel'] }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ $stats['mapel'] }} Mapel dapat dipilih
                    </div>
                </div>
            </div>

            {{-- Card 4: Program Studi --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4.16667 8.75V13.3333C4.16667 13.3333 6.66667 15.8333 10 15.8333C13.3333 15.8333 15.8333 13.3333 15.8333 13.3333V8.75" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Program Studi</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $stats['prodi'] }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Telah terdata di sistem
                    </div>
                </div>
            </div>
        </div>

        {{-- Dua Kolom: Distribusi Minat RIASEC & Aktivitas Terbaru --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            {{-- Kolom Kiri: Distribusi Minat RIASEC --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex flex-col justify-between overflow-hidden">
                <div class="h-16 px-5 py-4 border-b-[0.80px] border-slate-200 flex justify-between items-center">
                    <div>
                        <div class="text-slate-800 text-xs font-semibold font-['Plus_Jakarta_Sans'] leading-tight">
                            Distribusi Minat RIASEC
                        </div>
                        <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                            Persentase skor dominan siswa yang telah tes
                        </div>
                    </div>
                    <form method="GET" action="{{ route('admin.dashboard') }}">
                        <select id="jenjang" name="jenjang" onchange="this.form.submit()"
                            class="text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#089BD5]/20 font-medium">
                            <option value="">Semua Jenjang</option>
                            <option value="X" @selected(($validated['jenjang'] ?? '') === 'X')>Kelas X</option>
                            <option value="XII" @selected(($validated['jenjang'] ?? '') === 'XII')>Kelas XII</option>
                        </select>
                    </form>
                </div>

                {{-- Chart 6 Dimensi Sesuai Figma --}}
                <div class="h-64 px-7 pt-6 pb-6 flex justify-between items-end gap-3 sm:gap-4">
                    @foreach (['R', 'I', 'A', 'S', 'E', 'C'] as $dim)
                        @php
                            $dInfo = $chartData[$dim];
                            $val = $dInfo['val'];
                            $barHeight = max(8, min(100, $val));
                        @endphp
                        <div class="flex-1 flex flex-col justify-end items-center">
                            <div class="pb-1.5 text-slate-500 text-[9px] font-bold font-['Plus_Jakarta_Sans']">
                                {{ $val }}%
                            </div>
                            <div class="w-9 h-40 bg-slate-100 rounded-t-[10px] rounded-b-xs relative overflow-hidden flex items-end">
                                <div class="w-full {{ $dInfo['bg_bar'] }} rounded-t-lg rounded-b-xs transition-all duration-500"
                                     style="height: {{ $barHeight }}%; background-color: {{ $dInfo['color'] }};">
                                </div>
                            </div>
                            <div class="pt-2 text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-none">
                                {{ $dim }}
                            </div>
                            <div class="pt-0.5 text-gray-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] truncate text-center">
                                {{ $dInfo['nama'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom Kanan: Aktivitas Terbaru Sesuai PDF Admin --}}
            <div class="bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex flex-col justify-start overflow-hidden">
                <div class="h-16 px-5 py-4 border-b-[0.80px] border-slate-200 flex items-center">
                    <div>
                        <div class="text-slate-800 text-xs font-semibold font-['Plus_Jakarta_Sans'] leading-tight">
                            Aktivitas Terbaru
                        </div>
                        <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                            Pembaruan sistem terkini
                        </div>
                    </div>
                </div>

                <div class="p-5 flex flex-col divide-y divide-slate-100">
                    {{-- Item 1 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-indigo-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Profil paket tervalidasi
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Sains & Teknologi oleh Guru BK
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                10 menit lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 2 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-purple-100 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M7.5 7.5C7.5 6.11929 8.61929 5 10 5C11.3807 5 12.5 6.11929 12.5 7.5C12.5 8.5 11.5 9 10 10V11M10 14H10.01" stroke="#8B4FE6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Pertanyaan diperbarui
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Q-008 oleh Administrator
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                35 menit lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 3 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-emerald-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M4.16797 10L7.5013 13.3333L15.8346 5" stroke="#157A46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Tes siswa selesai
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                12 siswa Kelas XII MIPA 2
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                1 jam lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 4 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-amber-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M10 2.5L18.3333 6.66667L10 10.8333L1.66667 6.66667L10 2.5Z" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Program studi ditambahkan
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Teknik Lingkungan
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                2 jam lalu
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
