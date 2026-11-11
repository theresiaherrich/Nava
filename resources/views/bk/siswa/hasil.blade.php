<x-layouts.admin title="Hasil Tes RIASEC - Peminatan & Konseling">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Hasil Tes RIASEC</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Diselesaikan pada {{ $sesiTes->waktu_selesai ? \Carbon\Carbon::parse($sesiTes->waktu_selesai)->isoFormat('D MMMM Y, HH:mm') : '-' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('siswa.riwayat-tes') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Riwayat Tes
                </a>
            </div>
        </div>
    </div>

    {{-- Card Kode Holland Dominan (Fixed Layout & High Contrast Text) --}}
    <div class="mb-6 bg-gradient-to-br from-[#009bf2] to-blue-600 p-6 rounded-2xl text-white shadow-lg">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
            <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center flex-none border border-white/30 shadow-inner">
                <span class="text-3xl font-black tracking-widest text-white drop-shadow-sm">
                    {{ $sesiTes->kode_holland ?? $sesiTes->kode_dominan ?? '—' }}
                </span>
            </div>
            <div class="text-center sm:text-left">
                <p class="text-sky-100 text-xs font-bold uppercase tracking-widest mb-1">Kode Holland Dominan</p>
                <h2 class="text-3xl font-black text-white mb-2 tracking-wide">
                    {{ $sesiTes->kode_holland ?? $sesiTes->kode_dominan ?? '—' }}
                </h2>
                <p class="text-sky-50 text-xs leading-relaxed max-w-lg">
                    Profil kepribadian karirmu berdasarkan hasil tes RIASEC.
                    Kode ini mencerminkan tiga dimensi minat tertinggi yang paling sesuai dengan karakteristik dan potensimu.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Bar Chart 6 Dimensi --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Profil Dimensi RIASEC</h2>
                        <p class="text-xs text-gray-400">Skor enam dimensi dari hasil tesmu</p>
                    </div>
                </div>

                @php
                    $riasecColors = [
                        'R' => ['bar' => '#009bf2', 'bg' => 'bg-blue-100',    'text' => 'Realistic'],
                        'I' => ['bar' => '#6366f1', 'bg' => 'bg-indigo-100',  'text' => 'Investigative'],
                        'A' => ['bar' => '#ec4899', 'bg' => 'bg-pink-100',    'text' => 'Artistic'],
                        'S' => ['bar' => '#10b981', 'bg' => 'bg-emerald-100', 'text' => 'Social'],
                        'E' => ['bar' => '#f59e0b', 'bg' => 'bg-amber-100',   'text' => 'Enterprising'],
                        'C' => ['bar' => '#64748b', 'bg' => 'bg-slate-200',   'text' => 'Conventional'],
                    ];
                    $kodeHolland = $sesiTes->kode_holland ?? $sesiTes->kode_dominan ?? '';

                    $skorPerKode = [
                        'R' => $sesiTes->skor_r ?? 0,
                        'I' => $sesiTes->skor_i ?? 0,
                        'A' => $sesiTes->skor_a ?? 0,
                        'S' => $sesiTes->skor_s ?? 0,
                        'E' => $sesiTes->skor_e ?? 0,
                        'C' => $sesiTes->skor_c ?? 0,
                    ];
                @endphp

                <div class="h-56 flex items-end justify-between gap-3 sm:gap-5 pt-6 pb-2 px-2">
                    @foreach(['R','I','A','S','E','C'] as $kode)
                        @php
                            $skor = $skorPerKode[$kode] ?? 0;
                            $persen = min(100, max(0, round($skor)));
                            $color = $riasecColors[$kode];
                            $isDominan = str_contains($kodeHolland, $kode);
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end">
                            <span class="text-[11px] font-bold text-gray-600">{{ $persen }}%</span>
                            <div class="w-full max-w-[42px] {{ $color['bg'] }} rounded-t-xl relative overflow-hidden h-full flex items-end">
                                <div class="w-full rounded-t-xl transition-all duration-500" 
                                     style="height: {{ max(4, $persen) }}%; background-color: {{ $color['bar'] }}"></div>
                            </div>
                            <div class="text-center mt-1">
                                <p class="text-xs font-black text-slate-800 {{ $isDominan ? 'text-[#009bf2]' : '' }}">{{ $kode }}</p>
                                <p class="text-[9px] text-gray-400 leading-tight">{{ $color['text'] }}</p>
                                @if($isDominan)
                                    <span class="text-[8px] font-bold text-[#009bf2]">★</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach(['R','I','A','S','E','C'] as $kode)
                    @php
                        $color = $riasecColors[$kode];
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full flex-none" style="background-color: {{ $color['bar'] }}"></span>
                        <span class="text-[10px] text-gray-600 font-medium">{{ $kode }} – {{ $color['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sidebar: Deskripsi Dimensi Dominan --}}
        <div class="space-y-4">
            @if(!empty($kodeHolland))
                @foreach(str_split($kodeHolland) as $kode)
                    @php
                        $color = $riasecColors[$kode] ?? ['bar' => '#009bf2', 'text' => $kode];
                        $dim = $dimensi[$kode] ?? null;
                    @endphp
                    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-black flex-none"
                                style="background-color: {{ $color['bar'] }}">
                                {{ $kode }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $dim->nama ?? $color['text'] }}</p>
                                <p class="text-[10px] text-gray-400">Dimensi Dominan</p>
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-600 leading-relaxed">
                            {{ $dim->deskripsi ?? 'Memiliki ketertarikan yang kuat pada kelompok dimensi ini untuk mendukung pilihan minat belajar dan karirmu.' }}
                        </p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- Rekomendasi Mapel (Untuk Kelas X & XI) --}}
    @if(isset($rekomendasiMapel) && $rekomendasiMapel->count() > 0)
        <div class="mb-6 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Rekomendasi Mata Pelajaran Pilihan</h2>
                    <p class="text-xs text-gray-400">5 mapel pilihan yang paling sesuai berdasarkan Cosine Similarity</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($rekomendasiMapel as $mapel)
                    <div class="p-4 bg-blue-50/60 border border-blue-100 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-[#009bf2] text-white flex items-center justify-center flex-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $mapel->nama ?? $mapel->nama_mapel ?? '-' }}</p>
                                <p class="text-[10px] text-gray-500">{{ $mapel->kode ?? $mapel->rumpun ?? 'Mapel Pilihan' }}</p>
                            </div>
                        </div>
                        @if(isset($mapel->skor))
                            <span class="px-2 py-1 bg-sky-100 text-sky-700 text-[10px] font-bold rounded-lg flex-none">
                                {{ round($mapel->skor, 1) }}% Match
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Rekomendasi Prodi & Profesi (Untuk Kelas XII) --}}
    @if(isset($rekomendasiProdi) && $rekomendasiProdi->count() > 0)
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
            <div class="mb-5">
                <h2 class="text-sm font-bold text-slate-900">Rekomendasi Program Studi & Profesi</h2>
                <p class="text-xs text-gray-400">Program studi perguruan tinggi dan karir impian yang paling sesuai</p>
            </div>
            <div class="space-y-3">
                @foreach($rekomendasiProdi as $prodi)
                    <div class="p-4 bg-gray-50 border border-gray-100 rounded-xl flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center flex-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800">{{ $prodi->nama ?? $prodi->nama_prodi ?? '-' }}</p>
                                <p class="text-[10px] text-gray-500">{{ $prodi->kategori ?? $prodi->deskripsi ?? 'Studi Lanjut' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!empty($prodi->kode_holland))
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full tracking-widest flex-none">
                                    {{ $prodi->kode_holland }}
                                </span>
                            @endif
                            @if(isset($prodi->skor))
                                <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-[10px] font-bold rounded-lg flex-none">
                                    {{ round($prodi->skor, 1) }}% Match
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Fallback Kosong --}}
    @if((!isset($rekomendasiMapel) || $rekomendasiMapel->count() === 0) && (!isset($rekomendasiProdi) || $rekomendasiProdi->count() === 0))
        <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
            <svg class="w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-gray-500">Rekomendasi belum tersedia</p>
            <p class="text-[11px] text-gray-400 mt-1 max-w-md">Hasil pemetaan rekomendasi sedang diproses atau belum diatur oleh guru BK. Silakan berkonsultasi dengan guru BK untuk informasi lebih lanjut.</p>
        </div>
    @endif

</x-layouts.admin>