<x-layouts.admin title="Dashboard Guru BK - SIAKAD SMAN 1 Malang">

    <!-- Breadcrumb & Header Greeting -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-[10px] font-semibold text-blue-600 tracking-wider uppercase mb-1">
            <span>SIAKAD</span>
            <span>&gt;</span>
            <span>MODUL A3</span>
            <span>&gt;</span>
            <span class="text-gray-400">DASHBOARD BK</span>
        </div>
        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">MODUL A3 · BIMBINGAN DAN KONSELING</p>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Selamat pagi, Bu Rina</h1>
                <p class="text-xs text-gray-500 mt-1">Berikut ringkasan layanan bimbingan dan pemetaan minat siswa hari ini.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-600 shadow-sm w-fit">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Senin, 12 Oktober 2026</span>
            </div>
        </div>
    </div>

    <!-- Periode Layanan Banner -->
    <div class="mb-6 p-4 bg-sky-50/60 border border-sky-100 rounded-2xl flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-white border border-sky-200 flex items-center justify-center text-sky-600 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-[9px] font-bold text-sky-700 uppercase tracking-wider block">PERIODE LAYANAN AKTIF</span>
                <p class="text-xs font-bold text-slate-800">Semester Ganjil · Tahun Ajaran 2026/2027</p>
                <p class="text-[11px] text-gray-500">Tes RIASEC dan layanan pengajuan BK sedang berlangsung.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-bold rounded-full">Aktif</span>
            <a href="#" class="text-xs font-semibold text-sky-600 hover:underline flex items-center gap-1">
                <span>Lihat riwayat</span>
                <span>&gt;</span>
            </a>
        </div>
    </div>

    <!-- 4 Stat Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Total Siswa</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">960</div>
                <span class="text-[10px] text-gray-400">Kelas X, XI, dan XII</span>
            </div>
        </div>

        <!-- Sudah Tes -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Sudah Tes</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">742</div>
                <span class="text-[10px] text-emerald-600 font-medium">77,3% siswa</span>
            </div>
        </div>

        <!-- Belum Tes -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Belum Tes</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">218</div>
                <span class="text-[10px] text-amber-600 font-medium">Perlu tindak lanjut</span>
            </div>
        </div>

        <!-- Pengajuan Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Pengajuan Aktif</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">3</div>
                <span class="text-[10px] text-purple-600 font-medium">Menunggu penanganan</span>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Chart RIASEC & Aktivitas Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Kolom Kiri (2 Span): Bar Chart Distribusi Minat RIASEC -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Distribusi Minat RIASEC</h2>
                    <p class="text-xs text-gray-400">Persentase skor dominan siswa yang telah tes</p>
                </div>
                <select class="text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-1.5 text-gray-700 focus:outline-none">
                    <option>Semua Jenjang</option>
                    <option>Kelas X</option>
                    <option>Kelas XI</option>
                    <option>Kelas XII</option>
                </select>
            </div>

            <!-- Bar Chart Custom Component -->
            <div class="h-56 flex items-end justify-between gap-4 pt-8 pb-4 px-2">
                <!-- Bar R (Realistic) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">68%</span>
                    <div class="w-full max-w-[42px] bg-sky-100 rounded-t-xl relative overflow-hidden h-[68%]">
                        <div class="absolute inset-0 bg-[#009bf2] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">R</p>
                        <p class="text-[9px] text-gray-400">Realistic</p>
                    </div>
                </div>

                <!-- Bar I (Investigative) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">84%</span>
                    <div class="w-full max-w-[42px] bg-indigo-100 rounded-t-xl relative overflow-hidden h-[84%]">
                        <div class="absolute inset-0 bg-[#6366f1] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">I</p>
                        <p class="text-[9px] text-gray-400">Investigative</p>
                    </div>
                </div>

                <!-- Bar A (Artistic) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">57%</span>
                    <div class="w-full max-w-[42px] bg-purple-100 rounded-t-xl relative overflow-hidden h-[57%]">
                        <div class="absolute inset-0 bg-[#a855f7] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">A</p>
                        <p class="text-[9px] text-gray-400">Artistic</p>
                    </div>
                </div>

                <!-- Bar S (Social) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">76%</span>
                    <div class="w-full max-w-[42px] bg-pink-100 rounded-t-xl relative overflow-hidden h-[76%]">
                        <div class="absolute inset-0 bg-[#ec4899] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">S</p>
                        <p class="text-[9px] text-gray-400">Social</p>
                    </div>
                </div>

                <!-- Bar E (Enterprising) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">63%</span>
                    <div class="w-full max-w-[42px] bg-amber-100 rounded-t-xl relative overflow-hidden h-[63%]">
                        <div class="absolute inset-0 bg-[#f59e0b] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">E</p>
                        <p class="text-[9px] text-gray-400">Enterprising</p>
                    </div>
                </div>

                <!-- Bar C (Conventional) -->
                <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                    <span class="text-[11px] font-bold text-gray-600">71%</span>
                    <div class="w-full max-w-[42px] bg-emerald-100 rounded-t-xl relative overflow-hidden h-[71%]">
                        <div class="absolute inset-0 bg-[#10b981] rounded-t-xl"></div>
                    </div>
                    <div class="text-center mt-1">
                        <p class="text-xs font-bold text-slate-800">C</p>
                        <p class="text-[9px] text-gray-400">Conventional</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan (1 Span): Aktivitas Terbaru -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Aktivitas Terbaru</h2>
                <p class="text-xs text-gray-400 mb-4">Pembaruan modul A3</p>

                <div class="space-y-4">
                    <!-- Item 1 -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Tes RIASEC selesai</p>
                            <p class="text-[11px] text-gray-500">Alya Safitri · X-B</p>
                            <span class="text-[10px] text-gray-400">12 menit lalu</span>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Pengajuan baru diterima</p>
                            <p class="text-[11px] text-gray-500">Konsultasi pilihan jurusan</p>
                            <span class="text-[10px] text-gray-400">35 menit lalu</span>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Bimbingan dijadwalkan</p>
                            <p class="text-[11px] text-gray-500">Citra Maharani · XI-A</p>
                            <span class="text-[10px] text-gray-400">1 jam lalu</span>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" stroke-width="2"></polyline>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Bimbingan diselesaikan</p>
                            <p class="text-[11px] text-gray-500">Farah Nabila · XI-C</p>
                            <span class="text-[10px] text-gray-400">Kemarin</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Antrean Bimbingan Widget (Bottom) -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Antrean Bimbingan</h2>
                <p class="text-xs text-gray-400">Pengajuan yang perlu ditindaklanjuti</p>
            </div>
            <a href="#" class="text-xs font-semibold text-blue-600 hover:underline">Lihat semua</a>
        </div>

        <div class="space-y-3">
            <!-- Antrean 1: Alya Safitri (Menunggu) -->
            <div class="p-3.5 bg-gray-50/70 hover:bg-gray-100/70 rounded-xl flex items-center justify-between transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xs font-bold">
                        AS
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Alya Safitri</p>
                        <p class="text-[11px] text-gray-500">Konsultasi pilihan jurusan · X-B</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-amber-100/80 text-amber-800 text-[10px] font-bold rounded-full">Menunggu</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Antrean 2: Bima Arya Nugraha (Diproses) -->
            <div class="p-3.5 bg-gray-50/70 hover:bg-gray-100/70 rounded-xl flex items-center justify-between transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xs font-bold">
                        BA
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Bima Arya Nugraha</p>
                        <p class="text-[11px] text-gray-500">Motivasi belajar menurun · X-C</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-sky-100/80 text-sky-800 text-[10px] font-bold rounded-full">Diproses</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </div>

            <!-- Antrean 3: Citra Maharani (Dijadwalkan) -->
            <div class="p-3.5 bg-gray-50/70 hover:bg-gray-100/70 rounded-xl flex items-center justify-between transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xs font-bold">
                        CM
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900">Citra Maharani</p>
                        <p class="text-[11px] text-gray-500">Perencanaan studi lanjut · XI-A</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-purple-100/80 text-purple-800 text-[10px] font-bold rounded-full">Dijadwalkan</span>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>
