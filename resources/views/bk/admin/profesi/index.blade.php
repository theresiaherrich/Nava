<x-layouts.admin title="Master Profesi - Admin SIAKAD">

    <div class="max-w-6xl mx-auto py-3">
        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <x-page-label />
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
                <div>
                    <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Master Profesi</h1>
                    <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Kelola master data profesi untuk prospek karir program studi.</p>
                </div>
                <a href="{{ route('admin.profesi.create') }}"
                    class="px-4 py-2 bg-[#089BD5] text-white text-xs font-bold font-['Plus_Jakarta_Sans'] rounded-xl hover:bg-[#078ec4] transition flex items-center gap-1.5 w-fit shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19" stroke-width="2.5" stroke-linecap="round"></line>
                        <line x1="5" y1="12" x2="19" y2="12" stroke-width="2.5" stroke-linecap="round"></line>
                    </svg>
                    Tambah Profesi
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

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50/80 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-10">No</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Nama Profesi</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Deskripsi Singkat</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Program Studi Terkait</th>
                            <th class="px-4 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($profesi as $i => $item)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-4 py-3 text-xs text-gray-400 font-medium">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 text-xs font-bold text-slate-800">{{ $item->nama }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-sm">
                                    <p class="line-clamp-2">{{ $item->deskripsi ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @forelse($item->programStudi as $prd)
                                            <span class="px-2 py-0.5 bg-sky-50 text-[#089BD5] text-[10px] rounded-md">{{ $prd->nama }}</span>
                                        @empty
                                            <span class="text-[10px] text-gray-400 italic">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.profesi.edit', $item->id_profesi) }}"
                                            class="p-1.5 text-gray-400 hover:text-[#089BD5] hover:bg-sky-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.profesi.destroy', $item->id_profesi) }}" method="POST"
                                            onsubmit="return confirm('Hapus profesi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-xs text-gray-400">
                                    Belum ada data profesi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
