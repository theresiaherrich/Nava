<x-layouts.admin title="Tes Belum Dibuka - Peminatan & Konseling">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Periode Tes Tidak Aktif</h1>
                <p class="text-xs text-gray-500 mt-1">Tes RIASEC belum dibuka untuk semester ini.</p>
            </div>
        </div>
    </div>

    {{-- Alert Info --}}
    <div class="mb-6 p-4 bg-sky-50 border border-sky-100 rounded-2xl flex items-start gap-3">
        <svg class="w-5 h-5 text-sky-500 flex-none mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="text-xs font-semibold text-sky-700">Informasi</p>
            <p class="text-[11px] text-sky-600 mt-0.5">Periode pengerjaan tes RIASEC belum dibuka oleh guru BK. Harap tunggu pemberitahuan lebih lanjut.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Konten Utama --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Ilustrasi & Pesan Utama --}}
            <div class="bg-white p-10 rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full bg-sky-50 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-extrabold text-[#0f1b33] mb-2">Tes Belum Dibuka</h2>
                <p class="text-sm text-gray-500 max-w-md">
                    Guru BK belum membuka periode pengerjaan tes RIASEC untuk semester ini.
                    Kamu akan mendapat pemberitahuan ketika tes sudah dapat dikerjakan.
                </p>

                @if($semester)
                    <div class="mt-6 w-full max-w-sm p-4 bg-gray-50 border border-gray-200 rounded-xl text-left">
                        <p class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3">Semester Aktif</p>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Nama Semester</span>
                                <span class="text-xs font-semibold text-gray-800">{{ $semester->nama_semester ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Tahun Ajaran</span>
                                <span class="text-xs font-semibold text-gray-800">{{ $semester->tahun_ajaran ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Status</span>
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full">Belum Dibuka</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('siswa.dashboard') }}"
                        class="px-6 py-2.5 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Kembali ke Dashboard
                    </a>
                    <a href="{{ route('siswa.riwayat-tes') }}"
                        class="px-6 py-2.5 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Lihat Riwayat Tes
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Info Siswa --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 mb-4">Profil Siswa</h2>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-sm font-bold flex-none">
                        {{ strtoupper(substr($siswa->nama ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">{{ $siswa->nama ?? '-' }}</p>
                        <p class="text-[11px] text-gray-500">{{ $siswa->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
                <div class="space-y-2 border-t border-gray-100 pt-3">
                    <div class="flex justify-between">
                        <span class="text-[11px] text-gray-500">NIS</span>
                        <span class="text-[11px] font-semibold text-gray-800">{{ $siswa->nis ?? '-' }}</span>
                    </div>
                    @if($riwayat)
                        <div class="flex justify-between">
                            <span class="text-[11px] text-gray-500">Kelas Saat Ini</span>
                            <span class="text-[11px] font-semibold text-gray-800">{{ $riwayat->kelas->nama_kelas ?? '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tips --}}
            <div class="bg-amber-50 p-5 rounded-2xl border border-amber-100">
                <h2 class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    Tips Persiapan
                </h2>
                <ul class="space-y-2">
                    <li class="text-[11px] text-amber-700 flex items-start gap-2">
                        <span class="font-bold">•</span>
                        Pelajari tentang tes RIASEC dan 6 dimensi kepribadian karir.
                    </li>
                    <li class="text-[11px] text-amber-700 flex items-start gap-2">
                        <span class="font-bold">•</span>
                        Refleksikan minat dan kesukaanmu dalam berbagai bidang.
                    </li>
                    <li class="text-[11px] text-amber-700 flex items-start gap-2">
                        <span class="font-bold">•</span>
                        Siapkan waktu 30 menit tanpa gangguan saat tes dibuka.
                    </li>
                </ul>
            </div>
        </div>
    </div>

</x-layouts.admin>
