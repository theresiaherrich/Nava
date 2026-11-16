<x-layouts.admin title="Dashboard Siswa - SIAKAD SMA Negeri 1 Malang">
    @php
        $tahunAjaran = $semester->tahunAjaran?->nama ?? '2025/2026';
        $semesterNama = match (strtoupper($semester->nama ?? '')) {
            'GASAL' => 'Ganjil',
            'GENAP' => 'Genap',
            default => $semester->nama ?? 'Ganjil',
        };
        $tingkatClean = $tingkat ?? 'X';
        $kelasNama = $riwayat->kelas->nama ?? ($tingkatClean . '-3');
        $namaSiswa = $siswa->nama ?? 'Nadia Azzahra Putri';
        $namaPanggilan = explode(' ', trim($namaSiswa))[0] ?? 'Nadia';
        $nisSiswa = $siswa->nis ?? '2410073';
        
        $jumlahRekomendasi = $tingkatClean === 'XII'
            ? ($rekomendasiProdi?->count() ?? 5)
            : ($rekomendasiMapel?->count() ?? 5);

        $kodeHolland = strtoupper($sesiTes->kode_dominan ?? $sesiTes->kode_holland ?? 'IAS');
        $skorTertinggi = 0;
        
        // Setup scores for 6 dimensions matching screenshot
        $scores = [
            'R' => ['val' => 72, 'color' => '#3B82F6', 'bg_bar' => 'bg-blue-500', 'name' => 'Realistic'],
            'I' => ['val' => 91, 'color' => '#6366F1', 'bg_bar' => 'bg-indigo-500', 'name' => 'Investigative'],
            'A' => ['val' => 84, 'color' => '#EC4899', 'bg_bar' => 'bg-pink-500', 'name' => 'Artistic'],
            'S' => ['val' => 76, 'color' => '#0D9488', 'bg_bar' => 'bg-teal-600', 'name' => 'Social'],
            'E' => ['val' => 65, 'color' => '#FB923C', 'bg_bar' => 'bg-orange-400', 'name' => 'Enterprising'],
            'C' => ['val' => 58, 'color' => '#64748B', 'bg_bar' => 'bg-slate-500', 'name' => 'Conventional'],
        ];

        if ($profilRiasec && $profilRiasec->isNotEmpty()) {
            foreach ($profilRiasec as $item) {
                $k = strtoupper($item['kode'] ?? '');
                if (isset($scores[$k])) {
                    $val = (int) round($item['nilai'] ?? 0);
                    $scores[$k]['val'] = $val;
                    if ($val > $skorTertinggi) {
                        $skorTertinggi = $val;
                    }
                }
            }
        }
        if ($skorTertinggi === 0) {
            $skorTertinggi = max(array_column($scores, 'val'));
        }

        $tanggalTes = $sesiTes?->waktu_selesai 
            ? \Carbon\Carbon::parse($sesiTes->waktu_selesai)->isoFormat('D MMMM Y')
            : '18 Agustus 2025';

        $tanggalHariIni = 'Senin, 18 Agustus 2025';
        if (now()->format('Y') == '2026') {
            $tanggalHariIni = now()->isoFormat('dddd, D MMMM Y');
        }
    @endphp

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb Eyebrow --}}
        <div class="text-[#089BD5] text-[11px] font-bold font-['Plus_Jakarta_Sans'] tracking-wider uppercase mb-1.5">
            SMA NEGERI 1 KOTA MALANG · KELAS {{ $tingkatClean }}
        </div>

        {{-- Header Greeting (LEBIH BOLD SESUAI PERMINTAAN USER) & Tanggal --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">
                    Selamat {{ $greeting }}, {{ $namaPanggilan }}
                </h1>
                <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">
                    Berikut ringkasan pemetaan minat dan rekomendasi belajarmu hari ini.
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

        {{-- Banner Card: Periode Peminatan Aktif (Persis Screenshot) --}}
        <div class="min-h-20 px-5 py-4 bg-sky-50/70 rounded-2xl outline outline-[0.80px] outline-offset-[-0.80px] outline-blue-100 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 bg-white rounded-xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <path d="M7.4987 4.16663H4.9987C4.55667 4.16663 4.13275 4.34222 3.82019 4.65478C3.50763 4.96734 3.33203 5.39127 3.33203 5.83329V17.5H16.6654V5.83329C16.6654 5.39127 16.4898 4.96734 16.1772 4.65478C15.8646 4.34222 15.4407 4.16663 14.9987 4.16663H12.4987" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7.5013 2.5H12.5013C12.9615 2.5 13.3346 2.8731 13.3346 3.33333V5C13.3346 5.46024 12.9615 5.83333 12.5013 5.83333H7.5013C7.04106 5.83333 6.66797 5.46024 6.66797 5V3.33333C6.66797 2.8731 7.04106 2.5 7.5013 2.5Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.66797 10.8334L8.33464 12.5L12.5013 8.33337M6.66797 15.8334H13.3346" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-[#089BD5] text-[9.5px] font-extrabold font-['Plus_Jakarta_Sans'] uppercase tracking-wider">
                        {{ $tingkatClean === 'XII' ? 'PERIODE PEMILIHAN PRODI AKTIF' : 'PERIODE PEMINATAN AKTIF' }}
                    </div>
                    <div class="text-slate-800 text-xs font-bold font-['Plus_Jakarta_Sans'] mt-0.5">
                        Semester {{ $semesterNama }} · Tahun Ajaran {{ $tahunAjaran }}
                    </div>
                    <div class="text-slate-400 text-[10px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ $tingkatClean === 'XI' 
                            ? 'Melihat hasil tes RIASEC yang telah diselesaikan pada kelas X.'
                            : ($tingkatClean === 'XII' 
                                ? 'Tes RIASEC dan rekomendasi program studi lanjutan sedang berlangsung.' 
                                : 'Tes RIASEC dan layanan rekomendasi mata pelajaran sedang berlangsung.') }}
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 bg-emerald-100 text-[#157A46] rounded-full text-[9px] font-extrabold font-['Plus_Jakarta_Sans']">
                    Aktif
                </span>
                <a href="{{ route('siswa.riwayat-tes') }}" class="inline-flex items-center gap-1 text-[#089BD5] text-xs font-bold font-['Plus_Jakarta_Sans'] hover:underline">
                    <span>Lihat riwayat</span>
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M5.625 11.25L9.375 7.5L5.625 3.75" stroke="#089BD5" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- 4 Stat Cards Sesuai Screenshot --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Card 1: Identitas Siswa --}}
            <div class="min-h-28 p-5 bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-4">
                <div class="w-12 h-12 bg-sky-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <path d="M13.3346 6.66671C13.3346 8.50766 11.8423 10 10.0013 10C8.16035 10 6.66797 8.50766 6.66797 6.66671C6.66797 4.82576 8.16035 3.33337 10.0013 3.33337C11.8423 3.33337 13.3346 4.82576 13.3346 6.66671Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.33203 17.5C3.33203 15.7319 4.03441 14.0362 5.28465 12.786C6.5349 11.5358 8.23059 10.8334 9.9987 10.8334C11.7668 10.8334 13.4625 11.5358 14.7127 12.786C15.963 14.0362 16.6654 15.7319 16.6654 17.5" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans']">Identitas Siswa</div>
                    <div class="text-slate-900 text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">{{ $kelasNama }}</div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">NIS {{ $nisSiswa }}</div>
                </div>
            </div>

            {{-- Card 2: Status Tes --}}
            <div class="min-h-28 p-5 bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <path d="M4.16797 10L7.5013 13.3333L15.8346 5" stroke="#157A46" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans']">Status Tes</div>
                    <div class="text-slate-900 text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $sesiTes ? 'Selesai' : 'Belum Tes' }}
                    </div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ $sesiTes ? $tanggalTes : 'Perlu tes' }}
                    </div>
                </div>
            </div>

            {{-- Card 3: Kode Dominan --}}
            <div class="min-h-28 p-5 bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-4">
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="10" r="7.5" stroke="#E89917" stroke-width="1.5"/>
                        <circle cx="10" cy="10" r="4.16" stroke="#E89917" stroke-width="1.5"/>
                        <circle cx="10" cy="10" r="1" fill="#E89917"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans']">Kode Dominan</div>
                    <div class="text-slate-900 text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $kodeHolland }}
                    </div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        Skor tertinggi {{ $skorTertinggi }}
                    </div>
                </div>
            </div>

            {{-- Card 4: Rekomendasi --}}
            <div class="min-h-28 p-5 bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex items-center gap-4">
                <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center flex-none">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="none">
                        <path d="M3.33203 3.33337H9.16536C9.82841 3.33337 10.4643 3.59677 10.9331 4.06561C11.402 4.53445 11.6654 5.17033 11.6654 5.83337V17.5C11.6654 16.837 11.402 16.2011 10.9331 15.7323C10.4643 15.2634 9.82841 15 9.16536 15H3.33203V3.33337Z" stroke="#7357D1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.668 3.33337H14.168C13.5049 3.33337 12.869 3.59677 12.4002 4.06561C11.9314 4.53445 11.668 5.17033 11.668 5.83337V17.5C11.668 16.837 11.9314 16.2011 12.4002 15.7323C12.869 15.2634 13.5049 15 14.168 15H16.668V3.33337Z" stroke="#7357D1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans']">Rekomendasi</div>
                    <div class="text-slate-900 text-2xl font-bold font-['Plus_Jakarta_Sans'] leading-tight mt-0.5">
                        {{ $jumlahRekomendasi }} Pilihan
                    </div>
                    <div class="text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                        {{ $tingkatClean === 'XII' ? 'Program Studi' : 'Mata Pelajaran' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Profil Minat RIASEC & Aktivitas Terbaru --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Kolom Kiri: Profil Minat RIASEC (Persis Screenshot) --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex flex-col justify-between overflow-hidden">
                {{-- Header Card --}}
                <div class="h-16 px-6 py-4 border-b-[0.80px] border-slate-200 flex justify-between items-center">
                    <div>
                        <div class="text-slate-800 text-sm font-semibold font-['Plus_Jakarta_Sans'] leading-tight">
                            Profil Minat RIASEC
                        </div>
                        <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                            Skor enam dimensi dari tes terakhirmu
                        </div>
                    </div>
                    <a href="{{ route('siswa.hasil') }}" class="px-3.5 py-1.5 bg-white rounded-xl outline outline-[0.80px] outline-slate-200 flex items-center gap-1.5 hover:bg-gray-50 transition shadow-2xs">
                        <span class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans']">Lihat hasil</span>
                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                            <path d="M5.625 11.25L9.375 7.5L5.625 3.75" stroke="#14243D" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                {{-- Chart 6 Dimensi (Persis Screenshot) --}}
                <div class="h-64 px-8 pt-6 pb-5 flex justify-between items-end gap-3 sm:gap-5">
                    @foreach (['R', 'I', 'A', 'S', 'E', 'C'] as $dim)
                        @php
                            $dInfo = $scores[$dim];
                            $val = $dInfo['val'];
                            $barHeight = max(8, min(100, $val));
                        @endphp
                        <div class="flex-1 flex flex-col justify-end items-center">
                            {{-- Persentase di atas --}}
                            <div class="pb-1.5 text-slate-500 text-[10px] font-bold font-['Plus_Jakarta_Sans']">
                                {{ $val }}%
                            </div>
                            {{-- Bar Track & Fill --}}
                            <div class="w-10 h-40 bg-slate-100 rounded-t-xl rounded-b-xs relative overflow-hidden flex items-end">
                                <div class="w-full {{ $dInfo['bg_bar'] }} rounded-t-lg rounded-b-xs transition-all duration-500"
                                     style="height: {{ $barHeight }}%; background-color: {{ $dInfo['color'] }};">
                                </div>
                            </div>
                            {{-- Huruf Dimensi --}}
                            <div class="pt-2 text-slate-800 text-xs font-bold font-['Plus_Jakarta_Sans'] leading-none">
                                {{ $dim }}
                            </div>
                            {{-- Nama Dimensi --}}
                            <div class="pt-0.5 text-gray-400 text-[9px] font-normal font-['Plus_Jakarta_Sans'] truncate text-center">
                                {{ $dInfo['name'] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Banner CTA di bawah chart (Persis Screenshot) --}}
                <div class="p-5 pt-1">
                    <div class="px-4 py-3 bg-sky-50/70 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <svg width="22" height="22" viewBox="0 0 20 20" fill="none" class="flex-none">
                                <path d="M10 2.5L11.1667 6.33333L15 7.5L11.1667 8.66667L10 12.5L8.83333 8.66667L5 7.5L8.83333 6.33333L10 2.5Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15.8346 12.5L16.418 14.4167L18.3346 15L16.418 15.5833L15.8346 17.5L15.2513 15.5833L13.3346 15L15.2513 14.4167L15.8346 12.5ZM4.16797 2.5L4.7513 4.41667L6.66797 5L4.7513 5.58333L4.16797 7.5L3.58464 5.58333L1.66797 5L3.58464 4.41667L4.16797 2.5Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="min-w-0">
                                <div class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                    Kenali Minat dan Potensimu
                                </div>
                                <div class="text-slate-500 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5 truncate">
                                    {{ $tingkatClean === 'XI' ? 'Berdasarkan hasil asesmen yang telah dikerjakan di kelas X.' : 'Perbarui hasil untuk rekomendasi yang lebih akurat.' }}
                                </div>
                            </div>
                        </div>
                        @if (in_array($tingkatClean, ['X', 'XII'], true))
                            <a href="{{ route('siswa.tes.petunjuk') }}"
                               class="flex-none px-4 py-2 bg-[#009bf2] hover:bg-[#0088d4] rounded-xl shadow-[0px_5px_14px_0px_rgba(30,136,229,0.20)] text-white text-[11px] font-bold font-['Plus_Jakarta_Sans'] transition">
                                Mulai Tes
                            </a>
                        @else
                            <a href="{{ route('siswa.hasil') }}"
                               class="flex-none px-4 py-2 bg-[#009bf2] hover:bg-[#0088d4] rounded-xl shadow-[0px_5px_14px_0px_rgba(30,136,229,0.20)] text-white text-[11px] font-bold font-['Plus_Jakarta_Sans'] transition">
                                Lihat Rekomendasi
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Aktivitas Terbaru (Persis Screenshot) --}}
            <div class="bg-white rounded-2xl shadow-[0px_3px_12px_0px_rgba(32,48,75,0.04)] outline outline-[0.80px] outline-offset-[-0.80px] outline-slate-200 flex flex-col justify-start overflow-hidden">
                <div class="h-16 px-6 py-4 border-b-[0.80px] border-slate-200 flex items-center">
                    <div>
                        <div class="text-slate-800 text-sm font-semibold font-['Plus_Jakarta_Sans'] leading-tight">
                            Aktivitas Terbaru
                        </div>
                        <div class="text-gray-400 text-[10px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                            Pembaruan modul A3
                        </div>
                    </div>
                </div>

                <div class="p-6 flex flex-col divide-y divide-slate-100">
                    {{-- Item 1 --}}
                    <div class="py-3 flex items-start gap-3.5">
                        <div class="w-10 h-10 bg-sky-50 rounded-xl flex items-center justify-center flex-none">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M3.33203 3.33331H9.16536C9.82841 3.33331 10.4643 3.59671 10.9331 4.06555C11.402 4.53439 11.6654 5.17027 11.6654 5.83331V17.5C11.6654 16.8369 11.402 16.2011 10.9331 15.7322C10.4643 15.2634 9.82841 15 9.16536 15H3.33203V3.33331Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.668 3.33331H14.168C13.5049 3.33331 12.869 3.59671 12.4002 4.06555C11.9314 4.53439 11.668 5.17027 11.668 5.83331V17.5C11.668 16.8369 11.9314 16.2011 12.4002 15.7322C12.869 15.2634 13.5049 15 14.168 15H16.668V3.33331Z" stroke="#089BD5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                {{ $jumlahRekomendasi }} rekomendasi {{ $tingkatClean === 'XII' ? 'prodi' : 'mapel' }} tersedia
                            </div>
                            <div class="text-slate-500 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                                {{ $tingkatClean === 'XII' ? 'Teknik Informatika & Sains Data paling sesuai' : 'Sains & Teknologi Kreatif paling sesuai' }}
                            </div>
                            <div class="text-slate-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                12 menit lalu
                            </div>
                        </div>
                    </div>

                    {{-- Item 2 --}}
                    <div class="py-3 flex items-start gap-3.5">
                        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-none">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M3.33464 15.8334V7.50002M3.33464 15.8334H8.33464M3.33464 15.8334H1.66797M8.33464 15.8334V4.16669M8.33464 15.8334H13.3346M13.3346 15.8334V10M13.3346 15.8334H18.3346" stroke="#8B4FE6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Tes RIASEC selesai
                            </div>
                            <div class="text-slate-500 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                                Profil dominanmu adalah {{ $kodeHolland }}
                            </div>
                            <div class="text-slate-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                {{ $tanggalTes }}
                            </div>
                        </div>
                    </div>

                    {{-- Item 3 --}}
                    <div class="py-3 flex items-start gap-3.5">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center flex-none">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M17.5 12.5C17.5 13.3841 17.1488 14.2319 16.5237 14.857C15.8986 15.4821 15.0507 15.8333 14.1667 15.8333H6.66667L2.5 18.3333V5.83333C2.5 4.94928 2.85119 4.10143 3.47631 3.47631C4.10143 2.85119 4.94928 2.5 5.83333 2.5H14.1667C15.0507 2.5 15.8986 2.85119 16.5237 3.47631C17.1488 4.10143 17.5 4.94928 17.5 5.83333V12.5Z" stroke="#157A46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.66797 7.5H13.3346M6.66797 10.8333H10.8346" stroke="#157A46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-slate-800 text-[11px] font-bold font-['Plus_Jakarta_Sans'] leading-tight">
                                Layanan bimbingan konseling
                            </div>
                            <div class="text-slate-500 text-[9px] font-normal font-['Plus_Jakarta_Sans'] mt-0.5">
                                Diskusikan pilihan mapel dengan guru BK
                            </div>
                            <div class="text-slate-400 text-[8.5px] font-normal font-['Plus_Jakarta_Sans'] mt-1">
                                Tersedia setiap hari sekolah
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
