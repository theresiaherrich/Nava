<x-layouts.admin title="Dimensi RIASEC - Admin SIAKAD">

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <x-page-label />
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
                <div>
                    <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Dimensi RIASEC</h1>
                    <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Daftar 6 dimensi kepribadian Holland (RIASEC).</p>
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

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-16">Kode</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($dimensi as $i => $item)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-sky-100 text-sky-800 text-[10px] font-bold rounded-lg tracking-widest">
                                        {{ $item->kode }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-800 font-semibold">{{ $item->nama }}</td>
                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ \Illuminate\Support\Str::limit($item->deskripsi, 80) }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.dimensi.edit', $item->id_dimensi) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#089BD5] text-white text-[10px] font-bold rounded-lg hover:bg-[#078ec4] transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="text-xs font-medium">Tidak ada data dimensi</p>
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
