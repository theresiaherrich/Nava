<x-layouts.admin title="Rekap Tes RIASEC - SIAKAD SMAN 1 Malang">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Rekap Tes RIASEC</h1>
                <p class="text-xs text-gray-500 mt-1">Daftar hasil tes pemetaan minat seluruh siswa.</p>
            </div>
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 transition w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
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
    {{-- Controller: ?cari=, ?tingkat=X/XI/XII, ?kode=R/I/A/S/E/C --}}
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-4">
        <form method="GET" action="{{ route('bk.rekap-tes') }}" class="flex flex-wrap gap-3 items-center">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[180px]">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" stroke-width="2"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                </svg>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama / NIS..."
                    class="w-full pl-9 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>

            {{-- Filter Tingkat / Jenjang --}}
            <select name="tingkat" class="border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Semua Jenjang</option>
                <option value="X"   {{ request('tingkat') === 'X'   ? 'selected' : '' }}>Kelas X</option>
                <option value="XI"  {{ request('tingkat') === 'XI'  ? 'selected' : '' }}>Kelas XI</option>
                <option value="XII" {{ request('tingkat') === 'XII' ? 'selected' : '' }}>Kelas XII</option>
            </select>

            {{-- Filter Kode Holland --}}
            <select name="kode" class="border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                <option value="">Semua Kode</option>
                @foreach(['R','I','A','S','E','C'] as $k)
                    <option value="{{ $k }}" {{ request('kode') === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition">
                Filter
            </button>
            @if(request()->hasAny(['cari','tingkat','kode']))
                <a href="{{ route('bk.rekap-tes') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
    {{-- Kolom v_rekap_tes_bk: id_riwayat_kelas, nama, nis, kelas, tingkat, tanggal_tes, kode_holland, status --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">NIS</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Tes</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kode Holland</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($data as $i => $item)
                        @php
                            $initials = collect(explode(' ', $item->nama ?? ''))->take(2)->map(fn($w) => strtoupper(substr($w, 0, 1)))->implode('');
                            $sudahTes = strtolower($item->status ?? '') === 'selesai';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $data->firstItem() + $i }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#009bf2] text-white flex items-center justify-center text-xs font-bold flex-none">
                                        {{ $initials }}
                                    </div>
                                    <span class="text-xs font-semibold text-gray-800">{{ $item->nama }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-800">{{ $item->nis ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">{{ $item->kelas ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">
    {{ !empty($item->tanggal_tes) ? \Carbon\Carbon::parse($item->tanggal_tes)->format('d M Y') : '-' }}
</td>
                            <td class="px-4 py-3">
                                @if($item->kode_holland)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-lg tracking-widest">
                                        {{ $item->kode_holland }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($sudahTes)
                                    <span class="px-3 py-1 bg-emerald-100/80 text-emerald-800 text-[10px] font-bold rounded-full">Selesai</span>
                                @else
                                    <span class="px-3 py-1 bg-amber-100/80 text-amber-800 text-[10px] font-bold rounded-full">Belum</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($sudahTes)
                                    <a href="{{ route('bk.rekap-tes.detail', $item->id_riwayat_kelas) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#009bf2] text-white text-[10px] font-bold rounded-lg hover:bg-blue-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Detail
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum tes</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs font-medium">Tidak ada data ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <p class="text-[11px] text-gray-500">
                    Menampilkan {{ $data->firstItem() }}–{{ $data->lastItem() }} dari {{ $data->total() }} data
                </p>
                {{ $data->appends(request()->query())->links('pagination::tailwind') }}
            </div>
        @endif
    </div>

</x-layouts.admin>
