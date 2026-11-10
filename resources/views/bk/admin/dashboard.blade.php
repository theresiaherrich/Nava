<x-layouts.admin title="Dashboard Admin - SIAKAD SMAN 1 Malang">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Dashboard Admin</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Ringkasan data sistem RIASEC
                    @if($semester)
                        — Semester {{ $semester->nama }} ({{ $semester->tahunAjaran->nama }})
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        {{-- Total Soal --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#009bf2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Total Soal</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['total_soal'] ?? 0 }}</p>
        </div>

        {{-- Soal Aktif --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Soal Aktif</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['soal_aktif'] ?? 0 }}</p>
        </div>

        {{-- Total Prodi --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7v-6.5"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Total Prodi</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['total_prodi'] ?? 0 }}</p>
        </div>

        {{-- Total Profesi --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Total Profesi</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['total_profesi'] ?? 0 }}</p>
        </div>

        {{-- Total Mapel --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Total Mapel</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['total_mapel'] ?? 0 }}</p>
        </div>

        {{-- Periode Aktif --}}
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col gap-2">
            <div class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide">Periode Aktif</p>
            <p class="text-2xl font-extrabold text-[#0f1b33]">{{ $stats['periode_aktif'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Tabel Periode Semester Ini --}}
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
        <h2 class="text-sm font-bold text-[#0f1b33] mb-4">Periode Tes Semester Ini</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama Periode</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tingkat</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Mulai</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Selesai</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($periodeSemesterIni as $i => $item)
                        @php
                            $statusColor = match($item->status ?? '') {
                                'aktif'     => 'bg-emerald-100/80 text-emerald-800',
                                'selesai'   => 'bg-blue-100/80 text-blue-800',
                                default     => 'bg-gray-100/80 text-gray-600',
                            };
                            $statusLabel = match($item->status ?? '') {
                                'aktif'     => 'Aktif',
                                'selesai'   => 'Selesai',
                                default     => 'Terjadwal',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800 font-semibold">{{ $item->nama }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">Kelas {{ $item->tingkat }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">{{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">{{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 text-[10px] font-bold rounded-full {{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs font-medium">Belum ada periode tes semester ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.admin>
