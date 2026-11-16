<x-layouts.admin title="Riwayat Bimbingan - SIAKAD SMA Negeri 1 Malang">
    <div class="mb-6">
        <x-page-label fitur="Riwayat Bimbingan" />
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Riwayat Bimbingan</h1>
                <p class="text-xs text-gray-500 mt-1">Status dan jadwal seluruh pengajuan bimbingan milikmu.</p>
            </div>
            <a href="{{ route('siswa.pengajuan-bk') }}" class="px-4 py-2 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600">
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
</x-layouts.admin>
