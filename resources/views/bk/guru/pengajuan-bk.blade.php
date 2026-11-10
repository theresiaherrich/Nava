<x-layouts.admin title="Pengajuan BK - SIAKAD SMAN 1 Malang">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Pengajuan BK</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar pengajuan bimbingan dan konseling dari siswa.</p>
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

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4">
        <form method="GET" action="{{ route('bk.pengajuan') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[180px]">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke-width="2"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / topik..."
                    class="w-full pl-9 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            {{-- Filter Status --}}
            <select name="status" class="border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="menunggu"    {{ request('status') === 'menunggu'    ? 'selected' : '' }}>Menunggu</option>
                <option value="diproses"    {{ request('status') === 'diproses'    ? 'selected' : '' }}>Diproses</option>
                <option value="dijadwalkan" {{ request('status') === 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                Filter
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('bk.pengajuan') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Topik</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($data as $i => $item)
                        @php
                            $namaSiswa = $item->siswa->nama ?? $item->nama_siswa ?? '-';
                            $initials = collect(explode(' ', $namaSiswa))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                            $status = strtolower($item->status ?? '');
                            $badgeMap = [
                                'menunggu'    => 'bg-amber-100/80 text-amber-800',
                                'diproses'    => 'bg-sky-100/80 text-sky-800',
                                'dijadwalkan' => 'bg-purple-100/80 text-purple-800',
                                'selesai'     => 'bg-emerald-100/80 text-emerald-800',
                                'ditolak'     => 'bg-red-100/80 text-red-800',
                            ];
                            $badgeClass = $badgeMap[$status] ?? 'bg-gray-100 text-gray-700';
                            $statusLabel = ucfirst($status);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $data->firstItem() + $i }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xs font-bold flex-none">
                                        {{ $initials }}
                                    </div>
                                    <span class="text-xs font-semibold text-gray-800">{{ $namaSiswa }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-800">{{ $item->siswa->kelas ?? $item->kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800 max-w-[160px] truncate" title="{{ $item->topik ?? '-' }}">{{ $item->topik ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">
                                {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 {{ $badgeClass }} text-[10px] font-bold rounded-full">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('bk.pengajuan.tindak-lanjut', $item->getKey()) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#009bf2] text-white text-[10px] font-bold rounded-lg hover:bg-blue-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-xs font-medium">Belum ada pengajuan BK</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-[11px] text-gray-500">
                    Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data
                </p>
                <div class="flex gap-1">
                    {{ $data->appends(request()->query())->links('vendor.pagination.simple-tailwind') }}
                </div>
            </div>
        @endif
    </div>

</x-layouts.admin>
