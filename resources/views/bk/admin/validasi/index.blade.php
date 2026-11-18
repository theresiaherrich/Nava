<x-layouts.admin title="Validasi Profil RIASEC - Guru BK SIAKAD">

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <x-page-label />
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
                <div>
                    <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Validasi Profil RIASEC</h1>
                    <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Tinjau dan validasi profil mata pelajaran pilihan serta program studi.</p>
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

        {{-- Section 1: Validasi Mapel Pilihan --}}
        <div id="mapel" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>Pengajuan Mapel Pilihan Menunggu Validasi</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-black rounded-full">
                        {{ $mapelMenunggu->count() }}
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Rumpun</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Profil RIASEC</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($mapelMenunggu as $i => $item)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-xs text-gray-400 font-medium">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-slate-800">
                                    {{ $item->nama }}
                                    <p class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $item->kode }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ $item->rumpun ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 bg-sky-100 text-sky-800 text-[11px] font-mono font-black rounded-lg tracking-wider">
                                        {{ $item->kode_riasec ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('bk.validasi.mapel', $item->id_mapel) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_validasi" value="tervalidasi">
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 text-white text-[11px] font-bold rounded-lg hover:bg-emerald-700 transition">
                                                Validasi
                                            </button>
                                        </form>
                                        <form action="{{ route('bk.validasi.mapel', $item->id_mapel) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_validasi" value="perlu_revisi">
                                            <input type="text" name="catatan_validasi" required maxlength="500" placeholder="Catatan revisi"
                                                class="w-32 rounded-lg border border-gray-200 px-2 py-1 text-[10px]" aria-label="Catatan revisi {{ $item->nama }}">
                                            <button type="submit" class="px-3 py-1 bg-rose-50 text-rose-700 text-[11px] font-bold rounded-lg hover:bg-rose-100 transition">
                                                Perlu Revisi
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">
                                    Tidak ada mapel pilihan yang menunggu validasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2: Validasi Program Studi --}}
        <div id="prodi" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>Pengajuan Program Studi Menunggu Validasi</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-black rounded-full">
                        {{ $prodiMenunggu->count() }}
                    </span>
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Program Studi</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Rumpun</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Profil RIASEC</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($prodiMenunggu as $i => $item)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-xs text-gray-400 font-medium">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-slate-800">{{ $item->nama }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ $item->rumpun ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 bg-sky-100 text-sky-800 text-[11px] font-mono font-black rounded-lg tracking-wider">
                                        {{ $item->kode_riasec ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('bk.validasi.prodi', $item->id_prodi) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_validasi" value="tervalidasi">
                                            <button type="submit" class="px-3 py-1 bg-emerald-600 text-white text-[11px] font-bold rounded-lg hover:bg-emerald-700 transition">
                                                Validasi
                                            </button>
                                        </form>
                                        <form action="{{ route('bk.validasi.prodi', $item->id_prodi) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status_validasi" value="perlu_revisi">
                                            <input type="text" name="catatan_validasi" required maxlength="500" placeholder="Catatan revisi"
                                                class="w-32 rounded-lg border border-gray-200 px-2 py-1 text-[10px]" aria-label="Catatan revisi {{ $item->nama }}">
                                            <button type="submit" class="px-3 py-1 bg-rose-50 text-rose-700 text-[11px] font-bold rounded-lg hover:bg-rose-100 transition">
                                                Perlu Revisi
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">
                                    Tidak ada program studi yang menunggu validasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
