<x-layouts.admin title="Daftar Soal Tes - Admin SIAKAD">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Soal Tes RIASEC</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola bank soal pemetaan minat siswa.</p>
            </div>
            <a href="{{ route('admin.soal.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 transition w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Soal
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

    {{-- Error Alert --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Teks Soal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Dimensi</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($soal as $i => $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-xs text-gray-800">
                                {{ \Illuminate\Support\Str::limit($item->teks, 60) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-lg">
                                    {{ $item->dimensi->kode ?? '-' }}
                                </span>
                                <span class="ml-1 text-xs text-gray-500">{{ $item->dimensi->nama ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->aktif)
                                    <span class="px-3 py-1 bg-emerald-100/80 text-emerald-800 text-[10px] font-bold rounded-full">Aktif</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100/80 text-gray-600 text-[10px] font-bold rounded-full">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.soal.edit', $item->id_soal_riasec) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#009bf2] text-white text-[10px] font-bold rounded-lg hover:bg-blue-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>

                                    {{-- Toggle Aktif/Nonaktif --}}
                                    <form method="POST" action="{{ route('admin.soal.toggle', $item->id_soal_riasec) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-[10px] font-bold rounded-lg transition
                                                       {{ $item->aktif ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                            {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form method="POST" action="{{ route('admin.soal.destroy', $item->id_soal_riasec) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Yakin ingin menghapus soal ini?')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white text-[10px] font-bold rounded-lg hover:bg-red-600 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs font-medium">Belum ada soal. Tambahkan soal pertama!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.admin>
