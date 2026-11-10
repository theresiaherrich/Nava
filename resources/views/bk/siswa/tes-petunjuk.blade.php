<x-layouts.admin title="Petunjuk Tes RIASEC - Peminatan & Konseling">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Petunjuk Tes RIASEC</h1>
                <p class="text-xs text-gray-500 mt-1">Baca petunjuk berikut dengan saksama sebelum memulai tes.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-600 shadow-sm w-fit">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>{{ $semester->nama_semester ?? 'Semester Aktif' }}</span>
            </div>
        </div>
    </div>

    {{-- Info Siswa --}}
    <div class="mb-6 p-4 bg-sky-50/60 border border-sky-100 rounded-2xl flex items-center gap-4">
        <div class="w-11 h-11 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-sm font-bold flex-none">
            {{ strtoupper(substr($siswa->nama ?? 'S', 0, 1)) }}
        </div>
        <div>
            <p class="text-xs font-bold text-slate-800">{{ $siswa->nama ?? '-' }}</p>
            <p class="text-[11px] text-gray-500">{{ $siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $siswa->nis ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kartu Informasi Tes --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Ringkasan Tes --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#009bf2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Tes
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-blue-50 rounded-xl text-center">
                        <p class="text-2xl font-black text-[#009bf2]">{{ $jumlahSoal }}</p>
                        <p class="text-[11px] text-gray-500 mt-1">Jumlah Soal</p>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-xl text-center">
                        <p class="text-2xl font-black text-amber-500">30</p>
                        <p class="text-[11px] text-gray-500 mt-1">Menit Pengerjaan</p>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-xl text-center">
                        <p class="text-2xl font-black text-emerald-500">1&times;</p>
                        <p class="text-[11px] text-gray-500 mt-1">Kesempatan Tes</p>
                    </div>
                </div>
            </div>

            {{-- Skala Jawaban --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#009bf2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Skala Jawaban
                </h2>
                <div class="space-y-2">
                    @foreach([
                        ['nilai' => 1, 'label' => 'Sangat Tidak Setuju', 'color' => 'bg-red-100 text-red-700'],
                        ['nilai' => 2, 'label' => 'Tidak Setuju',         'color' => 'bg-orange-100 text-orange-700'],
                        ['nilai' => 3, 'label' => 'Netral',               'color' => 'bg-gray-100 text-gray-700'],
                        ['nilai' => 4, 'label' => 'Setuju',               'color' => 'bg-blue-100 text-blue-700'],
                        ['nilai' => 5, 'label' => 'Sangat Setuju',        'color' => 'bg-emerald-100 text-emerald-700'],
                    ] as $skala)
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg {{ $skala['color'] }} flex items-center justify-center text-xs font-black flex-none">
                                {{ $skala['nilai'] }}
                            </span>
                            <span class="text-xs text-gray-700">{{ $skala['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Petunjuk Umum --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#009bf2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Petunjuk Umum
                </h2>
                <ul class="space-y-3">
                    @foreach([
                        'Bacalah setiap pernyataan dengan teliti sebelum menjawab.',
                        'Pilih skala 1 hingga 5 yang paling sesuai dengan perasaan atau minatmu.',
                        'Jawablah semua soal tanpa melewati satu pun pernyataan.',
                        'Tidak ada jawaban benar atau salah — jawablah dengan jujur sesuai dirimu.',
                        'Tes hanya dapat dikerjakan satu kali pada periode aktif ini.',
                        'Pastikan koneksi internet stabil selama mengerjakan tes.',
                    ] as $petunjuk)
                        <li class="flex items-start gap-2 text-xs text-gray-700">
                            <svg class="w-4 h-4 text-[#009bf2] flex-none mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $petunjuk }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Status Riwayat --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4">Status Tes</h2>
                @if($riwayat && $riwayat->sesiTes)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-bold text-emerald-700">Tes Sudah Dikerjakan</p>
                            <p class="text-[11px] text-emerald-600">{{ \Carbon\Carbon::parse($riwayat->sesiTes->waktu_selesai)->format('d M Y') }}</p>
                        </div>
                    </div>
                @else
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-bold text-amber-700">Belum Mengerjakan</p>
                            <p class="text-[11px] text-amber-600">Tes belum diselesaikan</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tombol Aksi --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 mb-4">Dengan menekan tombol di bawah, kamu menyatakan telah membaca dan memahami semua petunjuk.</p>
                @if($riwayat && $riwayat->sesiTes)
                    <a href="{{ route('siswa.hasil') }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-emerald-500 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 transition mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Lihat Hasil Tes
                    </a>
                @else
                    <a href="{{ route('siswa.tes.soal') }}"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition mb-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Mulai Tes
                    </a>
                @endif
                <a href="{{ route('siswa.dashboard') }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

</x-layouts.admin>
