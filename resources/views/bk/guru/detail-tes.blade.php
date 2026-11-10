<x-layouts.admin title="Detail Hasil Tes RIASEC - SIAKAD SMAN 1 Malang">

    {{--
        Controller detailTes() mengirim:
          $rekap       → stdClass dari v_rekap_tes_bk (kolom: nama, nis, kelas, tingkat, kode_holland, tanggal_tes, skor_r, skor_i, skor_a, skor_s, skor_e, skor_c)
          $hasilMinat  → stdClass dari v_hasil_minat_siswa (satu baris, berisi data rekomendasi)
          $dimensi     → Collection DimensiRiasec, di-keyBy('kode')
    --}}

    @php
        $riasecMeta = [
            'R' => ['label' => 'Realistic',     'bg' => 'bg-sky-500',     'light' => 'bg-sky-50',     'text' => 'text-sky-700'],
            'I' => ['label' => 'Investigative', 'bg' => 'bg-indigo-500',  'light' => 'bg-indigo-50',  'text' => 'text-indigo-700'],
            'A' => ['label' => 'Artistic',      'bg' => 'bg-purple-500',  'light' => 'bg-purple-50',  'text' => 'text-purple-700'],
            'S' => ['label' => 'Social',        'bg' => 'bg-pink-500',    'light' => 'bg-pink-50',    'text' => 'text-pink-700'],
            'E' => ['label' => 'Enterprising',  'bg' => 'bg-amber-500',   'light' => 'bg-amber-50',   'text' => 'text-amber-700'],
            'C' => ['label' => 'Conventional',  'bg' => 'bg-emerald-500', 'light' => 'bg-emerald-50', 'text' => 'text-emerald-700'],
        ];

        // Skor per dimensi dari kolom skor_r, skor_i, ... di $rekap
        $skorMap = [
            'R' => $rekap->skor_r ?? 0,
            'I' => $rekap->skor_i ?? 0,
            'A' => $rekap->skor_a ?? 0,
            'S' => $rekap->skor_s ?? 0,
            'E' => $rekap->skor_e ?? 0,
            'C' => $rekap->skor_c ?? 0,
        ];
        $maxSkor = max($skorMap) ?: 1;

        // Tiga kode dominan dari kode_holland (mis. "RIS" → ['R','I','S'])
        $dominan = $rekap->kode_holland ? str_split(strtoupper($rekap->kode_holland), 1) : [];

        $initials = collect(explode(' ', $rekap->nama ?? ''))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
    @endphp

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Detail Hasil Tes</h1>
                <p class="text-xs text-gray-500 mt-1">Profil RIASEC dan rekomendasi minat siswa.</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('bk.rekap-tes') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="15 18 9 12 15 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kembali
                </a>
                <a href="#"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Unduh Laporan
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kartu Profil Siswa --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center gap-3">
                <div class="w-16 h-16 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xl font-black">
                    {{ $initials }}
                </div>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">{{ $rekap->nama }}</h2>
                    <p class="text-xs text-gray-500">{{ $rekap->kelas ?? '-' }}</p>
                </div>
                <div class="w-full border-t border-gray-100 pt-3 space-y-2 text-left">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">NIS</span>
                        <span class="text-xs font-semibold text-gray-800">{{ $rekap->nis ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Jenjang</span>
                        <span class="text-xs font-semibold text-gray-800">Kelas {{ $rekap->tingkat ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Tanggal Tes</span>
                        <span class="text-xs font-semibold text-gray-800">
                            {{ $rekap->tanggal_tes ? \Carbon\Carbon::parse($rekap->tanggal_tes)->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] text-gray-500">Kode Holland</span>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-black rounded-lg tracking-widest">
                            {{ $rekap->kode_holland ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Tiga Tipe Dominan --}}
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-xs font-bold text-slate-900 mb-3">Tipe Dominan</h3>
                <div class="space-y-2">
                    @forelse($dominan as $rank => $kode)
                        @php $meta = $riasecMeta[$kode] ?? ['label' => $kode, 'light' => 'bg-gray-50', 'text' => 'text-gray-700', 'bg' => 'bg-gray-400']; @endphp
                        <div class="flex items-center gap-3 p-2.5 {{ $meta['light'] }} rounded-xl">
                            <div class="w-6 h-6 rounded-full {{ $meta['bg'] }} text-white text-[10px] font-black flex items-center justify-center">
                                {{ $rank + 1 }}
                            </div>
                            <div>
                                <p class="text-xs font-bold {{ $meta['text'] }}">{{ $kode }} – {{ $meta['label'] }}</p>
                                <p class="text-[10px] text-gray-500">Urutan #{{ $rank + 1 }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-2">Data tidak tersedia</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Skor + Rekomendasi --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Profil 6 Dimensi RIASEC --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Profil 6 Dimensi RIASEC</h3>
                <p class="text-xs text-gray-400 mb-5">Skor per dimensi dari hasil tes minat</p>

                <div class="space-y-3">
                    @foreach($skorMap as $kode => $skor)
                        @php
                            $meta = $riasecMeta[$kode];
                            $pct = $maxSkor > 0 ? round(($skor / $maxSkor) * 100) : 0;
                            $dim = $dimensi[$kode] ?? null;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg {{ $meta['bg'] }} text-white text-[10px] font-black flex items-center justify-center">{{ $kode }}</span>
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ $dim?->nama ?? $meta['label'] }}
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-gray-900">{{ $skor }}</span>
                            </div>
                            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $meta['bg'] }} rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Rekomendasi dari v_hasil_minat_siswa --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Rekomendasi Minat</h3>
                <p class="text-xs text-gray-400 mb-4">Bidang studi atau karier yang sesuai dengan profil RIASEC siswa</p>

                @if($hasilMinat)
                    <div class="space-y-3">
                        {{-- Tampilkan bidang rekomendasi dari view v_hasil_minat_siswa --}}
                        @foreach(array_filter((array) $hasilMinat, fn($v, $k) => str_starts_with($k, 'rekomendasi') && !empty($v), ARRAY_FILTER_USE_BOTH) as $key => $nilai)
                            <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-xl flex items-start gap-3">
                                <div class="w-8 h-8 rounded-xl bg-[#009bf2] text-white flex items-center justify-center flex-none mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $nilai }}</p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Fallback: tampilkan semua field yang tersedia dari $hasilMinat --}}
                        @if(!array_filter((array) $hasilMinat, fn($v, $k) => str_starts_with($k, 'rekomendasi') && !empty($v), ARRAY_FILTER_USE_BOTH))
                            <div class="p-4 bg-blue-50/50 border border-blue-100 rounded-xl">
                                <p class="text-xs font-semibold text-slate-800">{{ $hasilMinat->nama_prodi ?? $hasilMinat->nama_profesi ?? $hasilMinat->rekomendasi ?? 'Lihat detail rekomendasi' }}</p>
                                @if(!empty($hasilMinat->deskripsi))
                                    <p class="text-[11px] text-gray-500 mt-1">{{ $hasilMinat->deskripsi }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center gap-2 py-8 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <p class="text-xs font-medium">Belum ada rekomendasi tersedia</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

</x-layouts.admin>
