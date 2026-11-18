<x-layouts.admin title="Soal Tes RIASEC - Peminatan & Konseling">

    <div class="max-w-6xl mx-auto py-3">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
            <div>
                <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Tes RIASEC</h1>
                <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Jawab semua pernyataan berikut dengan jujur sesuai minat dan kepribadianmu.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-xl outline outline-[0.80px] outline-slate-200 text-xs text-slate-600 font-['Plus_Jakarta_Sans'] shadow-2xs w-fit flex-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span id="timer-display">30:00</span>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="mb-6 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-gray-700">Progres Pengisian</span>
            <span class="text-xs text-gray-500"><span id="answered-count">0</span> / {{ $soal->count() }} soal dijawab</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div id="progress-bar" class="bg-[#089BD5] h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    {{-- Form Soal --}}
    <form method="POST" action="{{ route('siswa.tes.submit') }}" id="form-tes">
        @csrf

        <div class="space-y-4">
            @foreach($soal as $i => $item)
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm soal-card" data-soal="{{ $item->id_soal_riasec }}">
                    <div class="flex items-start gap-4">
                        {{-- Nomor & Dimensi --}}
                        <div class="flex-none">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#089BD5] flex items-center justify-center text-xs font-black">
                                {{ $i + 1 }}
                            </div>
                        </div>
                        {{-- Konten Soal --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full tracking-wider">
                                    {{ $item->dimensi->kode ?? '?' }}
                                </span>
                            </div>
                            <p class="text-xs font-medium text-gray-800 leading-relaxed mb-4">{{ $item->teks }}</p>

                            {{-- Skala Radio --}}
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                @foreach([
                                    1 => 'Sangat Tidak Setuju',
                                    2 => 'Tidak Setuju',
                                    3 => 'Netral',
                                    4 => 'Setuju',
                                    5 => 'Sangat Setuju',
                                ] as $nilai => $labelSkala)
                                    <label class="flex flex-col items-center gap-1 cursor-pointer group">
                                        <input
                                            type="radio"
                                            name="jawaban[{{ $item->id_soal_riasec }}]"
                                            value="{{ $nilai }}"
                                            class="sr-only soal-radio"
                                            data-soal-id="{{ $item->id_soal_riasec }}"
                                            required
                                        />
                                        <span class="w-9 h-9 rounded-xl border-2 border-gray-200 flex items-center justify-center text-xs font-black text-gray-400
                                            group-has-[:checked]:bg-[#089BD5] group-has-[:checked]:border-[#089BD5] group-has-[:checked]:text-white
                                            hover:border-[#089BD5] hover:text-[#089BD5] transition-all duration-150">
                                            {{ $nilai }}
                                        </span>
                                        <span class="text-[9px] text-gray-400 text-center leading-tight max-w-[52px] hidden sm:block">{{ $labelSkala }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tombol Submit --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="w-4 h-4 text-amber-400 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Pastikan semua soal sudah dijawab sebelum menekan tombol selesaikan.
            </div>
            <button type="submit"
                class="px-8 py-3 bg-[#089BD5] text-white text-xs font-bold rounded-xl hover:bg-[#078ec4] transition flex items-center gap-2 w-full sm:w-auto justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Selesaikan Tes
            </button>
        </div>
    </form>

    </div>{{-- /max-w-6xl --}}

    @vite('resources/js/bk/student-x.js')

</x-layouts.admin>
