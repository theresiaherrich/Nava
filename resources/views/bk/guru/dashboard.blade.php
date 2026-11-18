<x-layouts.admin title="Dashboard Guru BK - SIAKAD SMA Negeri 1 Malang">
    @php
        $tahunAjaran = $semester->tahunAjaran?->nama ?? '2026/2027';
        $semesterNama = match (strtoupper($semester->nama ?? '')) {
            'GASAL' => 'Ganjil',
            'GENAP' => 'Genap',
            default => $semester->nama ?? 'Ganjil',
        };
        $tanggalHariIni = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y');
        $namaGuru = $guru->nama ?? 'Guru BK';

        $totalSiswa = $rekapStats->total_siswa ?? 960;
        $sudahTes = $rekapStats->sudah_tes ?? 742;
        $belumTes = $rekapStats->belum_tes ?? 218;
        $persenSudahTes = $totalSiswa > 0 ? round(($sudahTes / $totalSiswa) * 100, 1) : 77.3;
        $pengajuanAktif = ($pengajuanStats['menunggu'] ?? 0) + ($pengajuanStats['diproses'] ?? 0);

        // Chart RIASEC data
        $chartData = [
            'R' => ['val' => 68, 'color' => '#3B82F6', 'bg_bar' => 'bg-blue-500', 'nama' => 'Realistic'],
            'I' => ['val' => 84, 'color' => '#6366F1', 'bg_bar' => 'bg-indigo-500', 'nama' => 'Investigative'],
            'A' => ['val' => 57, 'color' => '#EC4899', 'bg_bar' => 'bg-pink-500', 'nama' => 'Artistic'],
            'S' => ['val' => 76, 'color' => '#0D9488', 'bg_bar' => 'bg-teal-600', 'nama' => 'Social'],
            'E' => ['val' => 63, 'color' => '#FB923C', 'bg_bar' => 'bg-orange-400', 'nama' => 'Enterprising'],
            'C' => ['val' => 71, 'color' => '#64748B', 'bg_bar' => 'bg-slate-500', 'nama' => 'Conventional'],
        ];

        if (isset($distribusiMinat) && $distribusiMinat->isNotEmpty()) {
            foreach ($distribusiMinat as $item) {
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
            SMA NEGERI 1 KOTA MALANG · BK
        </div>

        {{-- Header Greeting (LEBIH BOLD SESUAI PERMINTAAN USER) & Tanggal --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">
                    Selamat {{ $greeting }}, {{ $namaGuru }}
                </h1>
                <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">
                    Berikut ringkasan layanan bimbingan dan pemetaan minat siswa hari ini.
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

        {{-- Banner Card: Periode Layanan Aktif (Sesuai PDF A3-BK) --}}
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
                        PERIODE LAYANAN AKTIF
                    </div>
                    <div class="text-slate-800 text-[12px] font-bold font-['Plus_Jakarta_Sans'] mt-0.5">
                        Semester {{ $semesterNama }} · Tahun Ajaran {{ $tahunAjaran }}
                    </div>
                    <div class="text-slate-500 text-[9.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Tes RIASEC dan layanan pengajuan BK sedang berlangsung.
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 bg-emerald-100 text-[#157A46] rounded-full text-[9px] font-extrabold font-['Plus_Jakarta_Sans']">
                    Aktif
                </span>
                <a href="{{ route('bk.rekap-tes') }}" class="inline-flex items-center gap-1 text-[#089BD5] text-[11px] font-bold font-['Plus_Jakarta_Sans'] hover:underline">
                    <span>Lihat riwayat</span>
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M5.625 11.25L9.375 7.5L5.625 3.75" stroke="#089BD5" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 4 Stat Cards Sesuai Desain Figma A3-BK --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            {{-- Card 1: Total Siswa --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-indigo-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M13.3346 6.66671C13.3346 8.50766 11.8423 10 10.0013 10C8.16035 10 6.66797 8.50766 6.66797 6.66671C6.66797 4.82576 8.16035 3.33337 10.0013 3.33337C11.8423 3.33337 13.3346 4.82576 13.3346 6.66671Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.33203 17.5C3.33203 15.7319 4.03441 14.0362 5.28465 12.786C6.5349 11.5358 8.23059 10.8334 9.9987 10.8334C11.7668 10.8334 13.4625 11.5358 14.7127 12.786C15.963 14.0362 16.6654 15.7319 16.6654 17.5" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Total Siswa</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ number_format($totalSiswa) }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Kelas X, XI, dan XII
                    </div>
                </div>
            </div>

            {{-- Card 2: Sudah Tes --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-emerald-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M4.16797 10L7.5013 13.3333L15.8346 5" stroke="#157A46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Sudah Tes</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ number_format($sudahTes) }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ str_replace('.', ',', (string) $persenSudahTes) }}% siswa
                    </div>
                </div>
            </div>

            {{-- Card 3: Belum Tes --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="7.5" stroke="#F59E0B" stroke-width="1.5"/>
                        <path d="M10 6.66663V10M10 13.3333H10.01" stroke="#F59E0B" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Belum Tes</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ number_format($belumTes) }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Perlu tindak lanjut
                    </div>
                </div>
            </div>

            {{-- Card 4: Pengajuan Aktif --}}
            <div class="min-h-28 p-5 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-3.5">
                <div class="w-11 h-11 bg-violet-100 rounded-xl flex items-center justify-center flex-none">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M14.875 10.625C14.875 11.3764 14.5765 12.0971 14.0451 12.6285C13.5138 13.1598 12.7931 13.4583 12.0417 13.4583H5.66667L2.125 15.5833V4.95833C2.125 4.20689 2.42351 3.48622 2.95486 2.95486C3.48622 2.42351 4.20689 2.125 4.95833 2.125H12.0417C12.7931 2.125 13.5138 2.42351 14.0451 2.95486C14.5765 3.48622 14.875 4.20689 14.875 4.95833V10.625Z" stroke="#7357D1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5.66797 6.375H11.3346M5.66797 9.20833H9.20964" stroke="#7357D1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans']">Pengajuan Aktif</div>
                    <div class="text-slate-900 text-xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ number_format($pengajuanAktif) }}
                    </div>
                    <div class="text-gray-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Menunggu penanganan
                    </div>
                </div>
            </div>
        </div>

        {{-- Dua Kolom: Distribusi Minat RIASEC & Aktivitas Terbaru --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">
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
                    <a href="{{ route('bk.rekap-tes') }}" class="px-3 py-1.5 bg-white rounded-[10px] outline outline-[0.80px] outline-slate-200 flex items-center gap-1.5 hover:bg-gray-50 transition shadow-2xs">
                        <span class="text-slate-800 text-[10px] font-bold font-['Plus_Jakarta_Sans']">Lihat Rekap</span>
                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                            <path d="M5.625 11.25L9.375 7.5L5.625 3.75" stroke="#14243D" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Chart 6 Dimensi Sesuai A3-BK --}}
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

            {{-- Kolom Kanan: Aktivitas Terbaru Sesuai A3-BK --}}
            <div class="bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex flex-col justify-start overflow-hidden">
                <div class="h-16 px-5 py-4 border-b-[0.80px] border-slate-200 flex items-center">
                    <div>
                        <div class="text-slate-800 text-xs font-semibold font-['Plus_Jakarta_Sans'] leading-tight">
                            Aktivitas Terbaru
                        </div>
                        <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                            Pembaruan modul A3
                        </div>
                    </div>
                </div>

                <div class="p-5 flex flex-col divide-y divide-slate-100">
                    {{-- Item 1 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-emerald-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M4.16797 10L7.5013 13.3333L15.8346 5" stroke="#157A46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Tes RIASEC selesai
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Alya Safitri · X-B
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                12 menit lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 2 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-indigo-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M14.875 10.625C14.875 11.3764 14.5765 12.0971 14.0451 12.6285C13.5138 13.1598 12.7931 13.4583 12.0417 13.4583H5.66667L2.125 15.5833V4.95833C2.125 4.20689 2.42351 3.48622 2.95486 2.95486C3.48622 2.42351 4.20689 2.125 4.95833 2.125H12.0417C12.7931 2.125 13.5138 2.42351 14.0451 2.95486C14.5765 3.48622 14.875 4.20689 14.875 4.95833V10.625Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Pengajuan baru diterima
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Konsultasi pilihan jurusan
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                35 menit lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 3 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-purple-100 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <circle cx="10" cy="10" r="7.5" stroke="#8B4FE6" stroke-width="1.5"/>
                                <polyline points="10 6 10 10 13 12" stroke="#8B4FE6" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Bimbingan dijadwalkan
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Citra Maharani · XI-A
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                1 jam lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 4 --}}
                    <div class="py-3 flex items-start gap-3">
                        <div class="w-9 h-9 bg-sky-50 rounded-[10px] flex items-center justify-center flex-none">
                            <svg width="18" height="18" viewBox="0 0 20 20" fill="none">
                                <path d="M4.16797 10L7.5013 13.3333L15.8346 5" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[10.5px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Bimbingan diselesaikan
                            </div>
                            <div class="text-slate-500 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                Farah Nabila · XI-C
                            </div>
                            <div class="text-slate-400 text-[8px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                Kemarin
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Antrean Bimbingan Sesuai PDF A3-BK --}}
        <div class="bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-slate-800 text-xs font-semibold font-['Plus_Jakarta_Sans']">Antrean Bimbingan</h2>
                    <p class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">Pengajuan yang perlu ditindaklanjuti</p>
                </div>
                <a href="{{ route('bk.pengajuan') }}" class="text-xs font-semibold text-sky-600 hover:underline">Lihat semua</a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse ($antrean as $pengajuan)
                    @php
                        $namaSiswa = $pengajuan->siswa?->nama ?? 'Siswa';
                        $riwayatAktif = $pengajuan->siswa?->riwayatKelasAktif;
                        $namaKelas = $riwayatAktif?->kelas?->nama ?? 'Kelas tidak tersedia';
                        $inisial = collect(explode(' ', $namaSiswa))->take(2)->map(fn ($b) => mb_substr($b, 0, 1))->implode('');
                    @endphp
                    <a href="{{ route('bk.pengajuan.tindak-lanjut', $pengajuan->id_pengajuan) }}"
                       class="p-3 bg-gray-50 hover:bg-gray-100 rounded-xl flex items-center justify-between gap-3 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-r from-pink-600 to-rose-400 text-white flex items-center justify-center text-xs font-bold flex-none shadow-2xs">
                                {{ $inisial ?: 'SW' }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 truncate">{{ $namaSiswa }}</div>
                                <div class="text-[10px] text-gray-500 truncate">{{ $pengajuan->topik }} · {{ $namaKelas }}</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[9px] font-bold rounded-full flex-none">
                            Tindak lanjut
                        </span>
                    </a>
                @empty
                    <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-r from-pink-600 to-rose-400 text-white flex items-center justify-center text-xs font-bold flex-none">
                                AS
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 truncate">Alya Safitri</div>
                                <div class="text-[10px] text-gray-500 truncate">Konsultasi pilihan jurusan · X-B</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[9px] font-bold rounded-full">
                            Menunggu
                        </span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-r from-indigo-600 to-sky-400 text-white flex items-center justify-center text-xs font-bold flex-none">
                                CM
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 truncate">Citra Maharani</div>
                                <div class="text-[10px] text-gray-500 truncate">Perencanaan studi lanjut · XI-A</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-sky-100 text-sky-800 text-[9px] font-bold rounded-full">
                            Diproses
                        </span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.admin>
