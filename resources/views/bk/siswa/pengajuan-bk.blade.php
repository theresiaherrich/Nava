<x-layouts.admin title="Pengajuan BK - Peminatan & Konseling">

    {{-- Breadcrumb & Header --}}
    <div class="mb-6">
        <x-page-label />
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0f1b33]">Bimbingan & Konseling</h1>
                <p class="text-xs text-gray-500 mt-1">Konsultasikan minat belajar, perencanaan studi lanjut, atau kendala belajarmu dengan Guru BK.</p>
            </div>
            <a href="{{ route('siswa.dashboard') }}"
                class="px-4 py-2 bg-gray-100 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-200 transition flex items-center gap-1.5 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs font-semibold text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-2xl">
            <p class="text-xs font-bold text-red-700 mb-1">Periksa kembali formulir pengajuanmu:</p>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li class="text-xs text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Form Pengajuan BK --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Form Pengajuan Bimbingan Baru</h2>
            <p class="text-xs text-gray-400 mb-5">Pilih topik dan sampaikan hal yang ingin kamu diskusikan.</p>
            @if($guruBkList->isEmpty())
                <p class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                    Pengajuan belum dapat dikirim karena belum ada Guru BK yang ditugaskan untuk jenjangmu pada semester aktif.
                </p>
            @endif

            <form method="POST" action="{{ route('siswa.pengajuan-bk.store') }}" class="space-y-4">
                @csrf

                {{-- Pilihan Topik --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Topik Bimbingan <span class="text-red-500">*</span>
                    </label>
                    <select name="topik" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">— Pilih Topik Konsultasi —</option>
                        @foreach($topikList as $topik)
                            <option value="{{ $topik }}" {{ old('topik') === $topik ? 'selected' : '' }}>
                                {{ $topik }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Deskripsi Masalah --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        Deskripsi Masalah / Pertanyaan <span class="text-red-500">*</span>
                        <span class="text-[10px] text-gray-400 font-normal">(Minimal 20 karakter)</span>
                    </label>
                    <textarea name="deskripsi" rows="4" required minlength="20" maxlength="500"
                        placeholder="Ceritakan kendala, pertanyaan minat bakat, atau kebutuhan konsultasimu secara singkat..."
                        class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 resize-none">{{ old('deskripsi') }}</textarea>
                </div>

                <button type="submit"
                    @disabled($guruBkList->isEmpty())
                    class="px-6 py-2.5 bg-[#009bf2] text-white text-xs font-bold rounded-xl hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Pengajuan
                </button>
            </form>
        </div>

        {{-- Kartu Guru BK Pendamping --}}
        <div class="space-y-4">
            <div class="bg-gradient-to-br from-[#009bf2] to-blue-600 p-5 rounded-2xl text-white shadow-md">
                <p class="text-[10px] font-bold uppercase tracking-wider text-sky-100 mb-1">Guru BK Penanggung Jawab</p>
                <h3 class="text-lg font-black mb-3">Layanan Konseling</h3>
                <div class="space-y-3">
                    @forelse($guruBkList as $guruBk)
                        <div class="p-3 bg-white/10 backdrop-blur-md rounded-xl border border-white/20 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white text-blue-600 flex items-center justify-center font-black text-xs flex-none">
                                BK
                            </div>
                            <div>
                                <p class="text-xs font-bold text-white">{{ $guruBk->nama }}</p>
                                <p class="text-[10px] text-sky-100">{{ $guruBk->email ?? 'Guru Bimbingan Konseling' }}</p>
                                <p class="text-[10px] text-sky-200 mt-1">Guru BK yang ditugaskan untuk jenjangmu.</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-sky-100">Belum ada Guru BK yang ditugaskan untuk jenjang ini pada semester aktif.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Riwayat Pengajuan Siswa --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-sm font-bold text-slate-900">Riwayat Pengajuan Bimbingan</h2>
            <p class="text-xs text-gray-400">Pantau status pengajuan konsultasi yang telah kamu kirimkan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Topik</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jadwal Bimbingan</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Catatan Guru BK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengajuan as $i => $item)
                        @php
                            $status = strtolower($item->status ?? 'menunggu');
                            $badgeMap = [
                                'menunggu'    => 'bg-amber-100 text-amber-800',
                                'diproses'    => 'bg-sky-100 text-sky-800',
                                'dijadwalkan' => 'bg-purple-100 text-purple-800',
                                'selesai'     => 'bg-emerald-100 text-emerald-800',
                                'ditolak'     => 'bg-red-100 text-red-800',
                            ];
                            $badge = $badgeMap[$status] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-5 py-4 text-xs text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-bold text-slate-800">{{ $item->topik }}</p>
                                <p class="text-[11px] text-gray-500 max-w-xs line-clamp-1 mt-0.5">{{ $item->deskripsi }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600">
                                {{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2.5 py-1 {{ $badge }} text-[10px] font-bold rounded-full">
                                    {{ ucfirst($status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-slate-800">
                                @if(!empty($item->tanggal_bimbingan))
                                    {{ \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d M Y') }}
                                    <span class="text-gray-400 font-normal">({{ substr($item->jam_bimbingan ?? '08:00', 0, 5) }})</span>
                                @else
                                    <span class="text-gray-400 italic">Belum dijadwalkan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-gray-600 max-w-xs">
                                {{ $item->balasan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-gray-400">
                                <p class="text-xs font-medium">Belum ada pengajuan bimbingan yang dikirim.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.admin>
