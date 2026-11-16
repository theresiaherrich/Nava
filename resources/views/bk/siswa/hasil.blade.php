<x-layouts.admin title="Hasil Tes RIASEC - Peminatan & Konseling">

    @php
        $kodeHolland = strtoupper($sesiTes->kode_dominan ?? $sesiTes->kode_holland ?? '');
        $hurufDominan = str_split($kodeHolland);
        $skorPerKode = method_exists($sesiTes, 'skorPerDimensi') ? $sesiTes->skorPerDimensi() : [];

        $dimensiData = [
            'R' => [
                'nama' => 'Realistic',
                'label' => 'Praktis & Teknikal',
                'color' => '#0284c7', // Sky Blue
                'bg_soft' => '#e0f2fe',
                'border' => '#bae6fd',
                'desc' => 'Praktis, realistis, dan menyukai aktivitas teknis. Kamu terampil bekerja dengan alat, mesin, atau kegiatan lapangan yang konkret.',
            ],
            'I' => [
                'nama' => 'Investigative',
                'label' => 'Analitis & Ilmiah',
                'color' => '#6366f1', // Indigo
                'bg_soft' => '#e0e7ff',
                'border' => '#c7d2fe',
                'desc' => 'Analitis, penuh rasa ingin tahu, dan menikmati tantangan intelektual. Kamu senang memahami cara kerja fenomena dan memecahkan masalah kompleks.',
            ],
            'A' => [
                'nama' => 'Artistic',
                'label' => 'Kreatif & Ekspresif',
                'color' => '#ec4899', // Pink
                'bg_soft' => '#fce7f3',
                'border' => '#fbcfe8',
                'desc' => 'Imajinatif, ekspresif, dan terbuka pada gagasan baru. Kamu menikmati ruang bebas untuk mengekspresikan ide dan menciptakan karya orisinal.',
            ],
            'S' => [
                'nama' => 'Social',
                'label' => 'Empatik & Kolaboratif',
                'color' => '#10b981', // Emerald
                'bg_soft' => '#d1fae5',
                'border' => '#a7f3d0',
                'desc' => 'Empatik, komunikatif, dan suka berkolaborasi. Kamu merasa berarti ketika dapat membantu, mengajar, dan mendampingi orang lain untuk berkembang.',
            ],
            'E' => [
                'nama' => 'Enterprising',
                'label' => 'Persuasif & Kepemimpinan',
                'color' => '#f59e0b', // Amber
                'bg_soft' => '#fef3c7',
                'border' => '#fde68a',
                'desc' => 'Percaya diri, persuasif, dan berjiwa pemimpin. Kamu senang mengambil inisiatif, mengarahkan kelompok, dan melihat peluang baru.',
            ],
            'C' => [
                'nama' => 'Conventional',
                'label' => 'Terstruktur & Sistematis',
                'color' => '#64748b', // Slate
                'bg_soft' => '#f1f5f9',
                'border' => '#e2e8f0',
                'desc' => 'Teratur, teliti, dan mengutamakan akurasi. Kamu sangat nyaman bekerja dengan data sistematis, alur kerja yang jelas, dan standar mutu.',
            ],
        ];

        // Format nama tiga dimensi dominan (mis. "Investigative – Artistic – Social")
        $namaDominanArr = [];
        foreach ($hurufDominan as $h) {
            if (isset($dimensiData[$h])) {
                $namaDominanArr[] = $dimensiData[$h]['nama'];
            }
        }
        $namaDominanString = !empty($namaDominanArr) ? implode(' – ', $namaDominanArr) : 'Investigative – Artistic – Social';
    @endphp

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider uppercase mb-1.5" style="color: #0284c7;">
            <span>SMA NEGERI 1 KOTA MALANG</span>
            <span>&bull;</span>
            <span>KELAS {{ $tingkat ?? 'X' }}</span>
            <span>&bull;</span>
            <span>HASIL &amp; REKOMENDASI</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Hasil Tes RIASEC-mu</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Diselesaikan pada {{ $sesiTes->waktu_mulai ? \Carbon\Carbon::parse($sesiTes->waktu_mulai)->isoFormat('D MMMM Y, HH:mm') : '-' }} &bull; Kenali pola minat dominanmu sebagai panduan studi.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.riwayat-tes') }}"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Riwayat Tes
                </a>
            </div>
        </div>
    </div>

    {{-- Hero Banner: Sesuai Desain Figma PDF (Gradient Biru Kaya) --}}
    <div class="mb-6 p-6 sm:p-8 rounded-2xl text-white shadow-md relative overflow-hidden"
         style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);">
        {{-- Background decorative glow --}}
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold tracking-wide uppercase"
                  style="background-color: #d1fae5; color: #065f46;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
                TES SELESAI
            </span>
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <span class="text-[10px] font-bold uppercase tracking-widest text-sky-200">KODE RIASEC</span>
                <span class="px-3.5 py-1 text-xl sm:text-2xl font-black font-mono tracking-widest rounded-xl text-white shadow-xs"
                      style="background: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.4); backdrop-filter: blur(4px);">
                    {{ $kodeHolland ?: 'â€”' }}
                </span>
            </div>
        </div>

        <div class="relative z-10 max-w-2xl">
            <h2 class="text-xl sm:text-2xl font-black text-white mb-2 leading-tight">
                Profil dominanmu adalah {{ $namaDominanString }}
            </h2>
            <p class="text-xs sm:text-sm text-sky-100 leading-relaxed font-medium">
                Kamu memiliki kombinasi potensi unik yang mencerminkan cara berpikir analitis, kreativitas dalam pemecahan masalah, serta orientasi kerja sama yang kuat untuk mendukung masa depanmu.
            </p>
        </div>
    </div>

    {{-- Grid 2 Kolom: Visualisasi & Skor Dimensi (Sesuai Desain Figma PDF) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Kolom Kiri: Visualisasi Peta Minat RIASEC --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[10px] font-bold text-sky-600 uppercase tracking-widest block mb-0.5">VISUALISASI</span>
                        <h2 class="text-sm font-bold text-slate-900">Peta Minat RIASEC</h2>
                    </div>
                    <span class="px-2.5 py-1 bg-sky-50 text-sky-700 text-[10px] font-bold rounded-lg border border-sky-100">
                        6 Dimensi
                    </span>
                </div>

                {{-- Chart Diagram Batang Modern dengan Warna Khas Tiap Dimensi --}}
                <div class="h-64 flex items-end justify-between gap-3 sm:gap-4 pt-8 pb-3 px-2">
                    @foreach(['R','I','A','S','E','C'] as $kode)
                        @php
                            $skor = $skorPerKode[$kode] ?? 0;
                            $persen = min(100, max(0, round($skor)));
                            $dInfo = $dimensiData[$kode];
                            $isDominan = in_array($kode, $hurufDominan);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <span class="text-[11px] font-black {{ $isDominan ? 'text-slate-900' : 'text-gray-400' }}">
                                {{ $persen }}%
                            </span>
                            <div class="w-full max-w-[44px] rounded-t-xl relative overflow-hidden h-full flex items-end"
                                 style="background-color: {{ $dInfo['bg_soft'] }}; border: 1px solid {{ $dInfo['border'] }};">
                                <div class="w-full rounded-t-xl transition-all duration-700 relative"
                                     style="height: {{ max(6, $persen) }}%; background-color: {{ $dInfo['color'] }};">
                                    @if($isDominan)
                                        <div class="absolute inset-x-0 top-0 h-1 bg-white/40"></div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-center mt-1">
                                <span class="w-6 h-6 rounded-lg inline-flex items-center justify-center text-xs font-black text-white shadow-xs"
                                      style="background-color: {{ $dInfo['color'] }};">
                                    {{ $kode }}
                                </span>
                                <p class="text-[10px] font-bold text-slate-700 mt-1 truncate">{{ $dInfo['nama'] }}</p>
                                @if($isDominan)
                                    <span class="inline-block px-1.5 py-0.2 bg-amber-100 text-amber-800 text-[8px] font-bold rounded mt-0.5">Top</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-[11px] text-gray-400 text-center mt-3 pt-3 border-t border-gray-50">
                Pilar berwarna penuh menunjukkan 3 dimensi tertinggi yang membentuk kode dominanmu.
            </p>
        </div>

        {{-- Kolom Kanan: Rincian Hasil Skor Dimensi (Skala 100) --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <span class="text-[10px] font-bold text-sky-600 uppercase tracking-widest block mb-0.5">SKOR DIMENSI</span>
                        <h2 class="text-sm font-bold text-slate-900">Rincian Hasil</h2>
                    </div>
                    <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-lg">
                        Skala 100
                    </span>
                </div>

                {{-- List 6 Bar Skor --}}
                <div class="space-y-3.5 pt-1">
                    @foreach(['R','I','A','S','E','C'] as $kode)
                        @php
                            $skor = $skorPerKode[$kode] ?? 0;
                            $persen = min(100, max(0, round($skor)));
                            $dInfo = $dimensiData[$kode];
                            $isDominan = in_array($kode, $hurufDominan);
                        @endphp
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md inline-flex items-center justify-center text-[10px] font-black text-white flex-none"
                                          style="background-color: {{ $dInfo['color'] }};">
                                        {{ $kode }}
                                    </span>
                                    <span class="font-bold text-slate-800">{{ $dInfo['nama'] }}</span>
                                    <span class="text-[10px] text-gray-400 hidden sm:inline">&bull; {{ $dInfo['label'] }}</span>
                                </div>
                                <span class="font-black text-slate-900 text-xs">{{ $persen }}</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full overflow-hidden"
                                 style="background-color: {{ $dInfo['bg_soft'] }};">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width: {{ $persen }}%; background-color: {{ $dInfo['color'] }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                <span>Rata-rata Skor: <strong>{{ count($skorPerKode) > 0 ? round(array_sum($skorPerKode) / count($skorPerKode), 1) : 0 }}</strong></span>
                <span class="text-sky-600 font-bold">Terverifikasi SIAKAD</span>
            </div>
        </div>

    </div>

    {{-- Tiga Tipe Dominan: Kekuatan Utamamu (Sesuai Desain Figma PDF) --}}
    <div class="mb-6">
        <div class="mb-4">
            <span class="text-[10px] font-bold text-sky-600 uppercase tracking-widest block mb-0.5">TIGA TIPE DOMINAN</span>
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Kekuatan Utamamu</h2>
            <p class="text-xs text-gray-500">Penjelasan karakteristik kepribadian dari tiga dimensi minat tertinggimu</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($hurufDominan as $idx => $kode)
                @php
                    $dInfo = $dimensiData[$kode] ?? [
                        'nama' => 'Dimensi ' . $kode,
                        'label' => 'Karakteristik Minat',
                        'color' => '#0284c7',
                        'bg_soft' => '#e0f2fe',
                        'border' => '#bae6fd',
                        'desc' => 'Menunjukkan potensi dan minat belajar yang kuat pada kelompok dimensi ini.',
                    ];
                @endphp
                <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-10 h-10 rounded-xl inline-flex items-center justify-center text-white text-base font-black shadow-xs"
                                  style="background-color: {{ $dInfo['color'] }};">
                                {{ $kode }}
                            </span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-bold rounded-md">
                                Dominan #{{ $idx + 1 }}
                            </span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 mb-0.5">{{ $dInfo['nama'] }}</h3>
                        <p class="text-[11px] font-semibold text-sky-600 mb-2">{{ $dInfo['label'] }}</p>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ $dInfo['desc'] }}
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px]">
                        <span class="text-gray-400">Tingkat Skor:</span>
                        <span class="font-extrabold text-slate-800">{{ $skorPerKode[$kode] ?? 0 }}%</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Call-To-Action (CTA) Banner Menuju Rekomendasi (Sesuai Desain Figma PDF) --}}
    <div class="mb-6 p-5 sm:p-6 rounded-2xl border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
         style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
        <div>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 mb-1">
                Siap melihat pilihan yang cocok?
            </h3>
            <p class="text-xs text-slate-600">
                Kami telah menyusun 5 rekomendasi {{ $tingkat === 'XII' ? 'program studi & prospek karir' : 'mata pelajaran pilihan' }} berdasarkan profil {{ $kodeHolland }}-mu.
            </p>
        </div>
        @if(in_array($tingkat, ['X', 'XI']))
            <a href="{{ route('siswa.mapel-pilihan') }}"
               class="px-5 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm hover:opacity-95 transition flex items-center justify-center gap-2 flex-none"
               style="background-color: #0284c7;">
                <span>Lihat Rekomendasi Mapel</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="9 18 15 12 9 6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                </svg>
            </a>
        @endif
    </div>

    {{-- Rekomendasi Mapel Pilihan (Tampil langsung jika ada) --}}
    @if(isset($rekomendasiMapel) && $rekomendasiMapel->count() > 0)
        <div class="mb-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <span class="text-[10px] font-bold text-sky-600 uppercase tracking-widest block mb-0.5">REKOMENDASI KELAS {{ $tingkat }}</span>
                    <h2 class="text-sm font-bold text-slate-900">5 Rekomendasi Mata Pelajaran Pilihan</h2>
                    <p class="text-xs text-gray-400">Mata pelajaran dengan indeks kesesuaian tertinggi berdasarkan profil RIASEC kamu</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($rekomendasiMapel as $mapel)
                    <div class="p-4 rounded-xl border flex items-center justify-between gap-3 hover:shadow-xs transition"
                         style="background-color: #f8fafc; border-color: #e2e8f0;">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center font-black text-xs flex-none"
                                 style="background-color: #0284c7;">
                                {{ $mapel->peringkat ?? $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $mapel->nama ?? '-' }}</p>
                                <p class="text-[10px] text-gray-500">{{ $mapel->rumpun ?? 'Mapel Pilihan' }}</p>
                                @if(!empty($mapel->kode_riasec))
                                    <span class="inline-block mt-0.5 px-2 py-0.2 text-[9px] font-bold font-mono rounded"
                                          style="background-color: #e0f2fe; color: #0369a1;">
                                        RIASEC: {{ $mapel->kode_riasec }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if(isset($mapel->skor))
                            <span class="px-2 py-1 text-[10px] font-bold rounded-lg flex-none border"
                                  style="background-color: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                {{ round($mapel->skor, 1) }}% Match
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rekomendasi Program Studi & Prospek Karir (Untuk Kelas XII) --}}
    @if(isset($rekomendasiProdi) && $rekomendasiProdi->count() > 0)
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
            <div class="mb-5">
                <span class="text-[10px] font-bold text-purple-600 uppercase tracking-widest block mb-0.5">REKOMENDASI STUDI LANJUT</span>
                <h2 class="text-sm font-bold text-slate-900">5 Rekomendasi Program Studi &amp; Prospek Karier</h2>
                <p class="text-xs text-gray-400">Pilihan program studi dan karir masa depan yang paling selaras dengan potensi kepribadianmu</p>
            </div>
            <div class="space-y-4">
                @foreach($rekomendasiProdi as $prodi)
                    <div class="p-5 rounded-2xl border space-y-3"
                         style="background-color: #faf5ff; border-color: #f3e8ff;">
                        <div class="flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-black text-sm flex-none"
                                     style="background-color: #7e22ce;">
                                    {{ $prodi->peringkat ?? $loop->iteration }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-bold text-slate-800">{{ $prodi->nama ?? '-' }}</h3>
                                    <p class="text-[10px] text-gray-500">{{ $prodi->rumpun ?? 'Program Studi Perguruan Tinggi' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(!empty($prodi->kode_riasec))
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold font-mono rounded-full border"
                                          style="background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe;">
                                        RIASEC: {{ $prodi->kode_riasec }}
                                    </span>
                                @endif
                                @if(isset($prodi->skor))
                                    <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg border"
                                          style="background-color: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                        {{ round($prodi->skor, 1) }}% Match
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($prodi->deskripsi) || !empty($prodi->tentang))
                            <p class="text-xs text-gray-600 leading-relaxed bg-white/70 p-3 rounded-xl border border-purple-100">
                                {{ $prodi->deskripsi ?: $prodi->tentang }}
                            </p>
                        @endif

                        @if(isset($prodi->profesi) && $prodi->profesi->count() > 0)
                            <div class="pt-2 border-t border-purple-100">
                                <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider block mb-1.5">Prospek Profesi:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($prodi->profesi as $pf)
                                        <span class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-white border border-purple-200 text-slate-700 shadow-2xs">
                                            {{ $pf->nama }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</x-layouts.admin>
