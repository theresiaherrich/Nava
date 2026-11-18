<x-layouts.admin title="Rekomendasi Program Studi & Prospek Karier - Peminatan & Konseling">

    <div class="max-w-6xl mx-auto py-3">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="mt-1">
            <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Rekomendasi Program Studi &amp; Karier</h1>
            <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Daftar rekomendasi program studi perguruan tinggi dan prospek profesi berdasarkan hasil asesmen minat RIASEC kamu.</p>
        </div>
    </div>

    {{-- Banner Kelas Info --}}
    <div class="mb-6 p-5 rounded-2xl border flex items-center justify-between flex-wrap gap-4 shadow-xs"
         style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl text-white flex items-center justify-center font-black text-base shadow-xs"
                 style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                {{ $tingkat ?? 'XII' }}
            </div>
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider block" style="color: #0369a1;">
                    INFORMASI KELAS {{ $tingkat ?? 'XII' }}
                </span>
                <p class="text-xs font-bold text-slate-800">Eksplorasi Studi Lanjut &amp; Perencanaan Karier Masa Depan</p>
                <p class="text-[11px] text-slate-500">
                    Pilihan program studi dan bidang karir yang paling selaras dengan profil kepribadian dan potensi belajarmu.
                </p>
            </div>
        </div>
        <div>
            <span class="px-3 py-1.5 text-xs font-black font-mono tracking-widest rounded-xl border shadow-xs bg-white border-blue-200"
                  style="color: #0284c7;">
                Profil: {{ $sesiTes->kode_dominan ?? $sesiTes->kode_holland ?? '-' }}
            </span>
        </div>
    </div>

    {{-- Daftar Rekomendasi Program Studi --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm mb-6">
        <div class="mb-5">
            <span class="text-[10px] font-bold uppercase tracking-widest block mb-0.5" style="color: #0284c7;">
                REKOMENDASI TERBAIK
            </span>
            <h2 class="text-base font-bold text-slate-900">5 Rekomendasi Program Studi &amp; Prospek Karier</h2>
            <p class="text-xs text-gray-400">Pilihan program studi dan karir masa depan yang paling selaras dengan potensi kepribadianmu</p>
        </div>

        <div class="space-y-4">
            @forelse($rekomendasiProdi as $prodi)
                <div class="p-5 rounded-2xl border space-y-3"
                     style="background-color: #f8fafc; border-color: #e2e8f0;">
                    <div class="flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-black text-sm flex-none shadow-2xs"
                                 style="background-color: #0284c7;">
                                {{ $prodi->peringkat ?? $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xs font-bold text-slate-800">{{ $prodi->nama ?? '-' }}</h3>
                                <p class="text-[10px] text-gray-500">{{ $prodi->rumpun ?? 'Program Studi Perguruan Tinggi' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!empty($prodi->kode_riasec))
                                <span class="px-2.5 py-0.5 text-[10px] font-bold font-mono rounded-full border shadow-2xs"
                                      style="background-color: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                                    RIASEC: {{ $prodi->kode_riasec }}
                                </span>
                            @endif
                            @if(isset($prodi->skor))
                                <span class="px-2.5 py-1 text-[11px] font-extrabold rounded-lg border shadow-2xs"
                                      style="background-color: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                    {{ round($prodi->skor, 1) }}% Match
                                </span>
                            @endif
                        </div>
                    </div>

                    @if(!empty($prodi->deskripsi) || !empty($prodi->tentang))
                        <p class="text-xs text-gray-600 leading-relaxed bg-white/80 p-3 rounded-xl border border-sky-100">
                            {{ $prodi->deskripsi ?: $prodi->tentang }}
                        </p>
                    @endif

                    @if(isset($prodi->profesi) && $prodi->profesi->count() > 0)
                        <div class="pt-2 border-t border-slate-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider block mb-1.5" style="color: #0284c7;">Prospek Profesi / Peluang Karier:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($prodi->profesi as $pf)
                                    <span class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 shadow-2xs hover:border-sky-300 hover:text-sky-700 transition">
                                        {{ $pf->nama }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-10 text-gray-400">
                    <p class="text-xs">Rekomendasi program studi belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
