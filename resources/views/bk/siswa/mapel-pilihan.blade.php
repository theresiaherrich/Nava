<x-layouts.admin title="Rekomendasi Mapel Pilihan - Peminatan & Konseling">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider uppercase mb-1.5" style="color: #0284c7;">
            <span>SMA NEGERI 1 KOTA MALANG</span>
            <span>&bull;</span>
            <span>KELAS {{ $tingkat ?? 'X' }}</span>
            <span>&bull;</span>
            <span>MAPEL PILIHAN</span>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Rekomendasi Mata Pelajaran Pilihan</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar rekomendasi mapel pilihan berdasarkan hasil asesmen minat RIASEC Kelas X kamu.</p>
            </div>
            <a href="{{ route('siswa.dashboard') }}"
                class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 shadow-xs transition flex items-center gap-1.5 w-fit">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Banner Kelas Info --}}
    <div class="mb-6 p-5 rounded-2xl border flex items-center justify-between flex-wrap gap-4 shadow-xs"
         style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl text-white flex items-center justify-center font-black text-base shadow-xs"
                 style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                {{ $tingkat ?? 'X' }}
            </div>
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider block" style="color: #0369a1;">
                    INFORMASI KELAS {{ $tingkat ?? 'X' }}
                </span>
                <p class="text-xs font-bold text-slate-800">Pemetaan Minat Mata Pelajaran Pilihan</p>
                <p class="text-[11px] text-slate-500">
                    @if(($tingkat ?? 'X') === 'X')
                        Rekomendasi mata pelajaran pilihan berdasarkan hasil asesmen minat RIASEC kamu saat ini.
                    @else
                        Riwayat rekomendasi mata pelajaran pilihan berdasarkan hasil asesmen minat saat Kelas X.
                    @endif
                </p>
            </div>
        </div>
        <div>
            <span class="px-3 py-1.5 text-xs font-black font-mono tracking-widest rounded-xl border shadow-xs"
                  style="background-color: #ffffff; color: #0369a1; border-color: #bae6fd;">
                Profil: {{ $sesiTesX->kode_dominan ?? $sesiTesX->kode_holland ?? '-' }}
            </span>
        </div>
    </div>

    {{-- Daftar Rekomendasi Mapel --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
        <div class="mb-5">
            <span class="text-[10px] font-bold uppercase tracking-widest block mb-0.5" style="color: #0284c7;">
                REKOMENDASI TERBAIK
            </span>
            <h2 class="text-base font-bold text-slate-900">5 Rekomendasi Mata Pelajaran Pilihan</h2>
            <p class="text-xs text-gray-400">Mata pelajaran yang paling sesuai dengan karakteristik dan potensi belajarmu</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($rekomendasiMapel as $mapel)
                <div class="p-4 rounded-xl border flex items-start justify-between gap-3 hover:shadow-xs transition"
                     style="background-color: #f8fafc; border-color: #e2e8f0;">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center text-xs font-black flex-none mt-0.5 shadow-2xs"
                             style="background-color: #0284c7;">
                            {{ $mapel->peringkat ?? $loop->iteration }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ $mapel->nama }}</p>
                            <p class="text-[10px] text-gray-500">{{ $mapel->rumpun ?? 'Mapel Pilihan' }}</p>
                            @if(!empty($mapel->kode_riasec))
                                <span class="inline-block mt-1.5 px-2 py-0.5 text-[9px] font-bold font-mono rounded"
                                      style="background-color: #e0f2fe; color: #0369a1;">
                                    RIASEC: {{ $mapel->kode_riasec }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if(isset($mapel->skor))
                        <span class="px-2 py-1 text-[10px] font-bold rounded-lg flex-none border shadow-2xs"
                              style="background-color: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                            {{ round($mapel->skor, 1) }}% Match
                        </span>
                    @endif
                </div>
            @empty
                <div class="col-span-3 text-center py-10 text-gray-400">
                    <p class="text-xs">Rekomendasi mapel belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

</x-layouts.admin>
