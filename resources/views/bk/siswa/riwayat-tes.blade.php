<x-layouts.admin title="Riwayat Tes RIASEC - Peminatan & Konseling">

    <div class="max-w-6xl mx-auto py-3">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="mt-1">
            <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Riwayat Tes Minat</h1>
            <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Daftar semua sesi tes RIASEC yang pernah kamu selesaikan.</p>
        </div>
    </div>

    {{-- Tabel Riwayat Tes --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Tes</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tingkat</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Semester</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kode Dominan</th>
                        <th class="px-5 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($riwayat as $i => $item)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-4 text-xs text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-800">
                                {{ $item->waktu_mulai ? \Carbon\Carbon::parse($item->waktu_mulai)->isoFormat('D MMMM Y, HH:mm') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-lg">
                                    Kelas {{ $item->tingkat ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $item->kelas ?? '-' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $item->semester ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="px-3 py-1 bg-sky-100 text-sky-800 text-xs font-black rounded-lg tracking-widest">
                                    {{ $item->kode_dominan ?? $item->kode_holland ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('siswa.hasil') }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#009bf2] text-white text-[10px] font-bold rounded-lg hover:bg-blue-600 transition">
                                    Lihat Hasil
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-gray-500">Belum ada riwayat tes</p>
                                    <p class="text-[11px] text-gray-400">Kamu belum pernah menyelesaikan tes RIASEC sebelumnya.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
