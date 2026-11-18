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
                <div class="p-5 rounded-2xl border space-y-3.5"
                     style="background-color: #f8fafc; border-color: #e2e8f0;">
                    
                    {{-- Header Rekomendasi --}}
                    <div class="flex items-center justify-between gap-3 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center font-black text-sm flex-none shadow-2xs"
                                 style="background-color: #0284c7;">
                                {{ $prodi->peringkat ?? $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-800">{{ $prodi->nama ?? '-' }}</h3>
                                    @if(!empty($prodi->jenjang_gelar))
                                        <span class="px-2 py-0.5 text-[9px] font-semibold rounded bg-slate-200/70 text-slate-600">
                                            {{ $prodi->jenjang_gelar }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-500 mt-0.5">{{ $prodi->rumpun ?? 'Program Studi Perguruan Tinggi' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-none">
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

                    {{-- Deskripsi Ringkas & Relevan --}}
                    @php
                        $deskripsiTampil = $prodi->deskripsi_ringkas ?? ($prodi->deskripsi ?: $prodi->tentang);
                    @endphp
                    @if(!empty($deskripsiTampil))
                        <p class="text-xs text-slate-700 leading-relaxed bg-white/80 p-3.5 rounded-xl border border-sky-100">
                            {{ $deskripsiTampil }}
                        </p>
                    @endif

                    {{-- Highlight Keselarasan RIASEC Singkat --}}
                    @if(!empty($prodi->kesesuaian_singkat))
                        <div class="px-3.5 py-2.5 rounded-xl bg-sky-50/70 border border-sky-100 text-[11px] text-sky-900 leading-relaxed flex items-start gap-2">
                            <span class="text-sky-600 font-bold flex-none text-xs leading-none mt-0.5">💡</span>
                            <div>
                                <span class="font-bold text-sky-800">Alasan Keselarasan:</span> {{ $prodi->kesesuaian_singkat }}
                            </div>
                        </div>
                    @endif

                    {{-- Prospek Profesi & Aksi Konsultasi --}}
                    @php
                        $profesiItems = !empty($prodi->profesi_list) 
                            ? $prodi->profesi_list 
                            : ($prodi->profesi ? $prodi->profesi->pluck('nama')->toArray() : []);
                    @endphp
                    @if(!empty($profesiItems))
                        <div class="pt-3 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider block mb-1.5" style="color: #0284c7;">
                                    Prospek Profesi / Peluang Karier:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($profesiItems as $pf)
                                        <span class="px-2.5 py-1 text-[10px] font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 shadow-2xs hover:border-sky-300 hover:text-sky-700 transition">
                                            {{ is_array($pf) ? ($pf['nama'] ?? '') : $pf }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="flex-none pt-1 sm:pt-0">
                                <a href="{{ route('siswa.pengajuan-bk', ['topik' => 'Perencanaan studi lanjut', 'prodi' => $prodi->nama]) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold text-sky-700 bg-sky-50 hover:bg-sky-100 border border-sky-200 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    <span>Konsultasi BK</span>
                                </a>
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
