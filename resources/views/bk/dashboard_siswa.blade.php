<x-layouts.admin title="Dashboard Siswa - Peminatan & Konseling">

    <!-- Breadcrumb & Header Greeting -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-[10px] font-semibold text-blue-600 tracking-wider uppercase mb-1">
            <span>SIAKAD</span>
            <span>&gt;</span>
            <span>MODUL A3</span>
            <span>&gt;</span>
            <span class="text-gray-400">DASHBOARD SISWA</span>
        </div>
        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">MODUL A3 · PEMINATAN DAN KONSELING</p>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Selamat pagi, Nadia</h1>
                <p class="text-xs text-gray-500 mt-1">Berikut ringkasan pemetaan minat dan rekomendasi belajarmu hari ini.</p>
            </div>
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs text-gray-600 shadow-sm w-fit">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>Senin, 18 Agustus 2025</span>
            </div>
        </div>
    </div>

    <!-- Periode Peminatan Banner -->
    <div class="mb-6 p-4 bg-sky-50/60 border border-sky-100 rounded-2xl flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-white border border-sky-200 flex items-center justify-center text-sky-600 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <span class="text-[9px] font-bold text-sky-700 uppercase tracking-wider block">PERIODE PEMINATAN AKTIF</span>
                <p class="text-xs font-bold text-slate-800">Semester Ganjil · Tahun Ajaran 2025/2026</p>
                <p class="text-[11px] text-gray-500">Tes RIASEC dan layanan rekomendasi mata pelajaran sedang berlangsung.</p>
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

    <!-- 4 Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Identitas Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Identitas Siswa</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">X-3</div>
                <span class="text-[10px] text-gray-400">NIS 2410073</span>
            </div>
        </div>

        <!-- Status Tes -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></polyline>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Status Tes</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">Selesai</div>
                <span class="text-[10px] text-gray-400">18 Agustus 2025</span>
            </div>
        </div>

        <!-- Kode Dominan -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                    <circle cx="12" cy="12" r="6" stroke-width="2"></circle>
                    <circle cx="12" cy="12" r="2" stroke-width="2"></circle>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Kode Dominan</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">IAS</div>
                <span class="text-[10px] text-gray-400">Skor tertinggi 91</span>
            </div>
        </div>

        <!-- Rekomendasi -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <span class="text-[11px] text-gray-500 font-medium block">Rekomendasi</span>
                <div class="text-2xl font-black text-slate-900 leading-tight">3 Paket</div>
                <span class="text-[10px] text-purple-600 font-medium">Siap dipelajari</span>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Profil Minat RIASEC & Aktivitas Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Kolom Kiri: Bar Chart RIASEC Siswa -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Profil Minat RIASEC</h2>
                        <p class="text-xs text-gray-400">Skor enam dimensi dari tes terakhirmu</p>
                    </div>
                    <a href="{{ url('/riasec/hasil-view/1') }}" class="text-xs font-semibold text-sky-600 hover:underline flex items-center gap-1">
                        <span>Lihat hasil</span>
                        <span>&gt;</span>
                    </a>
                </div>

                <!-- Bar Chart 6 Dimensi -->
                <div class="h-56 flex items-end justify-between gap-4 pt-6 pb-2 px-4">
                    <!-- R: 72% -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">72%</span>
                        <div class="w-full max-w-[42px] bg-sky-100 rounded-t-xl relative overflow-hidden h-[72%]">
                            <div class="absolute inset-0 bg-[#009bf2] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">R</p>
                            <p class="text-[9px] text-gray-400">Realistic</p>
                        </div>
                    </div>

                    <!-- I: 91% (Dominan) -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">91%</span>
                        <div class="w-full max-w-[42px] bg-indigo-100 rounded-t-xl relative overflow-hidden h-[91%]">
                            <div class="absolute inset-0 bg-[#6366f1] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">I</p>
                            <p class="text-[9px] text-gray-400">Investigative</p>
                        </div>
                    </div>

                    <!-- A: 84% -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">84%</span>
                        <div class="w-full max-w-[42px] bg-pink-100 rounded-t-xl relative overflow-hidden h-[84%]">
                            <div class="absolute inset-0 bg-[#ec4899] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">A</p>
                            <p class="text-[9px] text-gray-400">Artistic</p>
                        </div>
                    </div>

                    <!-- S: 76% -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">76%</span>
                        <div class="w-full max-w-[42px] bg-emerald-100 rounded-t-xl relative overflow-hidden h-[76%]">
                            <div class="absolute inset-0 bg-[#10b981] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">S</p>
                            <p class="text-[9px] text-gray-400">Social</p>
                        </div>
                    </div>

                    <!-- E: 65% -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">65%</span>
                        <div class="w-full max-w-[42px] bg-amber-100 rounded-t-xl relative overflow-hidden h-[65%]">
                            <div class="absolute inset-0 bg-[#f59e0b] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">E</p>
                            <p class="text-[9px] text-gray-400">Enterprising</p>
                        </div>
                    </div>

                    <!-- C: 58% -->
                    <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end">
                        <span class="text-[11px] font-bold text-gray-600">58%</span>
                        <div class="w-full max-w-[42px] bg-slate-200 rounded-t-xl relative overflow-hidden h-[58%]">
                            <div class="absolute inset-0 bg-[#64748b] rounded-t-xl"></div>
                        </div>
                        <div class="text-center mt-1">
                            <p class="text-xs font-bold text-slate-800">C</p>
                            <p class="text-[9px] text-gray-400">Conventional</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner Mulai Tes Bawah -->
            <div class="mt-6 p-4 bg-sky-50/70 border border-sky-100 rounded-xl flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-lg">✨</span>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Kenali Minat dan Potensimu</p>
                        <p class="text-[10px] text-gray-500">Perbarui hasil untuk rekomendasi yang lebih akurat.</p>
                    </div>
                </div>
                <a href="{{ url('/riasec/soal-view') }}" class="px-5 py-2 bg-[#009bf2] hover:bg-[#0088d4] text-white text-xs font-bold rounded-xl shadow-md transition">
                    Mulai Tes
                </a>
            </div>
        </div>

        <!-- Kolom Kanan: Aktivitas Terbaru Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Aktivitas Terbaru</h2>
                <p class="text-xs text-gray-400 mb-4">Pembaruan modul A3</p>

                <div class="space-y-4">
                    <!-- Item 1: Rekomendasi Mapel -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">3 rekomendasi mapel tersedia</p>
                            <p class="text-[11px] text-gray-500">Sains & Teknologi Kreatif paling sesuai</p>
                            <span class="text-[10px] text-gray-400">12 menit lalu</span>
                        </div>
                    </div>

                    <!-- Item 2: Tes Selesai -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <line x1="18" y1="20" x2="18" y2="10" stroke-width="2"></line>
                                <line x1="12" y1="20" x2="12" y2="4" stroke-width="2"></line>
                                <line x1="6" y1="20" x2="6" y2="14" stroke-width="2"></line>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Tes RIASEC selesai</p>
                            <p class="text-[11px] text-gray-500">Profil dominanmu adalah IAS</p>
                            <span class="text-[10px] text-gray-400">18 Agustus 2025</span>
                        </div>
                    </div>

                    <!-- Item 3: Layanan Konseling -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-none mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 leading-tight">Layanan bimbingan konseling</p>
                            <p class="text-[11px] text-gray-500">Diskusikan pilihan mapel dengan guru BK</p>
                            <span class="text-[10px] text-gray-400">Tersedia setiap hari sekolah</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin>

