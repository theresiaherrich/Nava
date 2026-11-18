<x-layouts.admin title="Riwayat Bimbingan - SIAKAD SMA Negeri 1 Malang">

    <div class="max-w-6xl mx-auto py-3">

    <div class="mb-6">
        <x-page-label fitur="Riwayat Bimbingan" />
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mt-1">
            <div>
                <h1 class="text-slate-900 text-3xl sm:text-4xl font-black font-['Plus_Jakarta_Sans'] leading-tight tracking-tight">Riwayat Bimbingan</h1>
                <p class="text-slate-500 text-xs font-normal font-['Plus_Jakarta_Sans'] mt-1.5">Status dan jadwal seluruh pengajuan bimbingan milikmu.</p>
            </div>
            <a href="{{ route('siswa.pengajuan-bk') }}" class="px-4 py-2 bg-[#089BD5] text-white text-xs font-bold font-['Plus_Jakarta_Sans'] rounded-xl hover:bg-[#078ec4] transition flex items-center gap-1.5 w-fit shadow-sm flex-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Pengajuan
            </a>
        </div>
    </div>

    <section class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Topik</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Guru BK</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Diajukan</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Jadwal</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($pengajuan as $item)
                        @php
                            $statusClass = match ($item->status) {
                                'menunggu' => 'bg-amber-100 text-amber-800',
                                'diproses' => 'bg-sky-100 text-sky-800',
                                'dijadwalkan' => 'bg-purple-100 text-purple-800',
                                'selesai' => 'bg-emerald-100 text-emerald-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-4">
                                <p class="text-xs font-bold text-slate-800">{{ $item->topik }}</p>
                                <p class="text-[11px] text-gray-500 max-w-xs line-clamp-2 mt-0.5">{{ $item->deskripsi }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $item->guru?->nama ?? 'Belum ditugaskan' }}</td>
                            <td class="px-5 py-4 text-xs text-gray-600">{{ $item->created_at?->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 {{ $statusClass }} text-[10px] font-bold rounded-full">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                @if ($item->tanggal_bimbingan)
                                    {{ $item->tanggal_bimbingan->locale('id')->translatedFormat('d M Y') }}
                                    · {{ $item->jam_bimbingan ? substr($item->jam_bimbingan, 0, 5) : '-' }}
                                @else
                                    <span class="text-gray-400">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 max-w-xs">{{ $item->balasan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-xs text-gray-400">
                                Belum ada riwayat pengajuan bimbingan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    </div>{{-- /max-w-6xl --}}

</x-layouts.admin>
