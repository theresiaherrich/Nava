<x-layouts.admin title="Rekomendasi Mapel Pilihan - Peminatan & Konseling">

    <div class="max-w-6xl mx-auto py-3">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="mt-1">
            <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Rekomendasi Mata Pelajaran Pilihan</h1>
            <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Daftar rekomendasi mapel pilihan berdasarkan hasil asesmen minat RIASEC Kelas X kamu.</p>
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
                @php
                    $deskripsiMapel = $mapel->deskripsi ?? \App\Services\MapelDetailHelper::getDeskripsi($mapel->nama, null, $mapel->rumpun ?? null);
                @endphp
                <div class="p-4 rounded-xl border flex flex-col justify-between hover:shadow-xs transition"
                     style="background-color: #f8fafc; border-color: #e2e8f0;">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl text-white flex items-center justify-center text-xs font-black flex-none mt-0.5 shadow-2xs"
                                 style="background-color: #0284c7;">
                                {{ $mapel->peringkat ?? $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800">{{ $mapel->nama }}</p>
                                <p class="text-[10px] text-gray-500">{{ $mapel->rumpun ?? 'Mapel Pilihan' }}</p>
                                @if(!empty($mapel->kode_riasec))
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[9px] font-bold font-mono rounded"
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

                    @if(!empty($deskripsiMapel))
                        <p class="text-[11px] text-slate-600 leading-relaxed mt-2.5 pt-2 border-t border-slate-200/70">
                            {{ $deskripsiMapel }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="col-span-3 text-center py-10 text-gray-400">
                    <p class="text-xs">Rekomendasi mapel belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
